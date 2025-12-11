<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoWeb</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <?php
    // Inclusion des fichiers nécessaires
    include_once 'template/ini.php';  // Configuration de la base de données
    session_start();                  // Démarrage de la session

    // ===== VÉRIFICATION DE L'AUTHENTIFICATION =====
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: connection.php');
        exit();
    }

    // ===== TRAITEMENT DU FORMULAIRE DE PAIEMENT =====
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_paiement'])) {
        // Récupération et nettoyage des données du formulaire
        $carte = isset($_POST['carte']) ? preg_replace('/\s/', '', $_POST['carte']) : ''; // Suppression des espaces
        $ccv = isset($_POST['ccv']) ? $_POST['ccv'] : '';
        $date = isset($_POST['date']) ? $_POST['date'] : '';
        
        // Initialisation du tableau des erreurs
        $erreurs = [];
        
        // ===== VALIDATION DES CHAMPS =====
        // Vérification que tous les champs sont remplis
        if (empty($carte) || empty($ccv) || empty($date)) {
            $erreurs[] = 'Veuillez remplir tous les champs obligatoires.';
        }
        
        // Validation du format du numéro de carte (16 chiffres)
        if (!empty($carte) && !preg_match('/^\d{16}$/', $carte)) {
            $erreurs[] = 'Le numéro de carte doit contenir 16 chiffres.';
        }
        
        // Validation du format du CCV (3 chiffres)
        if (!empty($ccv) && !preg_match('/^\d{3}$/', $ccv)) {
            $erreurs[] = 'Le code CCV doit contenir 3 chiffres.';
        }
        
        // Validation du format de la date d'expiration (MM/AA)
        if (!empty($date) && !preg_match('/^\d{2}\/\d{2}$/', $date)) {
            $erreurs[] = 'La date d\'expiration doit être au format MM/AA.';
        }
        
        // ===== TRAITEMENT SI AUCUNE ERREUR =====
        if (empty($erreurs)) {
            try {
                // Connexion à la base de données
                $dbh = db_connect();
                $user_id = $_SESSION['user_id'];
                
                // Mise à jour de l'état de la commande (1 = initialisée -> 4 = En attente)
                $updateSql = "UPDATE commande SET idEtat = 4 WHERE idUtilisateur = :user_id AND idEtat = 1";
                $updateSth = $dbh->prepare($updateSql);
                $updateSth->execute([':user_id' => $user_id]);
                
                // Message de succès en session
                $_SESSION['message_succes'] = 'paiement_valide';
            } catch (PDOException $ex) {
                // Gestion des erreurs SQL
                $_SESSION['message_erreur'] = 'Erreur lors du traitement du paiement.';
                error_log("Erreur paiement : " . $ex->getMessage());
            }
            
            // Redirection vers l'accueil après paiement
            header('Location: accueilConnecte.php');
            exit();
        } else {
            // Stockage des erreurs en session pour affichage
            $_SESSION['message_erreur'] = implode('<br>', $erreurs);
        }
    }

    // ===== TRAITEMENT DU BOUTON ANNULER =====
    if (isset($_POST['annuler_paiement'])) {
        header('Location: accueilConnecte.php');
        exit();
    }

    // ===== RÉCUPÉRATION DES INFORMATIONS DE LA COMMANDE ACTIVE =====
    // Initialisation des variables
    $totalTTC = 0.00;
    $typeCommande = 'Non défini';
    $idCommande = null;

    if (isset($_SESSION['user_id'])) {
        $dbh = db_connect();
        $user_id = $_SESSION['user_id'];
        
        try {
            // Requête pour récupérer la commande active de l'utilisateur
            $sql = "SELECT c.idCommande, c.totalTTC, c.typeCom 
                    FROM commande c 
                    WHERE c.idUtilisateur = :user_id 
                    AND c.idEtat = 1 
                    ORDER BY c.dateHeureCom DESC 
                    LIMIT 1";
            
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $sth->execute();
            
            $commande = $sth->fetch(PDO::FETCH_ASSOC);
            
            // Si une commande active existe
            if ($commande) {
                $totalTTC = $commande['totalTTC'];
                $idCommande = $commande['idCommande'];
                
                // Conversion du type de commande en texte lisible
                if ($commande['typeCom'] == 0) {
                    $typeCommande = 'Sur place';       // TVA 10%
                } elseif ($commande['typeCom'] == 1) {
                    $typeCommande = 'À emporter';      // TVA 5.5%
                } else {
                    $typeCommande = 'Non défini';
                }
            }
        } catch (PDOException $ex) {
            // Log des erreurs sans interrompre l'affichage
            error_log("Erreur lors de la récupération de la commande : " . $ex->getMessage());
        }
    }

    // ===== GESTION DE LA VALIDATION DU PAIEMENT (code alternatif) =====
    if (isset($_POST['action']) && $_POST['action'] === 'valider_paiement') {
        try {
            // Récupération des informations de la commande avant de la finaliser
            $sqlCommande = "SELECT totalTTC FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1";
            $sthCommande = $dbh->prepare($sqlCommande);
            $sthCommande->execute([':utilisateur' => $user_id]);
            $commande = $sthCommande->fetch(PDO::FETCH_ASSOC);
            
            // Mise à jour de l'état de la commande à "finalisée"
            $sql = "UPDATE commande 
                    SET idEtat = 2 
                    WHERE idUtilisateur = :user 
                    AND idEtat = 1";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':user' => $user_id]);
            
            // Message de succès et redirection
            $_SESSION['message_succes'] = 'paiement_valide';
            header('Location: accueilConnecte.php');
            exit();
        } catch (PDOException $e) {
            // Log de l'erreur
            error_log("Erreur lors de la mise à jour de l'état: " . $e->getMessage());
        }
    }
    ?>

    <!-- Section principale du paiement -->
    <section class="section-paiement" id="sectionPaiement">
        <div class="conteneur-paiement">
            <h2>Paiement</h2>
            
            <!-- Informations de la commande -->
            <div class="info-commande">
                <div class="commande-details">
                    <span><strong>Commande N° :</strong> <?php echo $idCommande ? $idCommande : 'Non définie'; ?></span>
                    <span><strong>Type :</strong> <?php echo htmlspecialchars($typeCommande); ?></span>
                </div>
            </div>
            
            <!-- Formulaire de paiement -->
            <div class="formulaire-paiement">
                <form method="POST" id="formPaiement">
                    <!-- Champ hidden pour identifier la soumission -->
                    <input type="hidden" name="valider_paiement" value="1">
                    
                    <!-- Champ numéro de carte bancaire -->
                    <div class="champ-paiement">
                        <label for="carte">Numéro de carte bancaire :</label>
                        <input type="text" id="carte" name="carte" placeholder="1234 5678 9012 3456" required>
                    </div>
                    
                    <!-- Champs inline (CCV, Date, Montant) -->
                    <div class="champs-inline">
                        <!-- Code CCV -->
                        <div class="champ-ccv">
                            <label for="ccv">CCV :</label>
                            <input type="text" id="ccv" name="ccv" maxlength="3" required>
                        </div>
                        
                        <!-- Date d'expiration -->
                        <div class="champ-date">
                            <label for="date">Date :</label>
                            <input type="text" id="date" name="date" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        
                        <!-- Affichage du montant total -->
                        <div class="montant">
                            <span>Montant : <?php echo number_format($totalTTC, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="boutons-paiement">
                        <button type="button" class="bouton-retour" onclick="window.location.href='accueilConnecte.php'">Annuler</button>
                        <button type="submit" class="bouton-valider">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Popup de confirmation du paiement -->
    <div id="popupConfirmation" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="color: #28a745; margin-bottom: 20px;">✓ Paiement validé</h3>
            <p style="margin-bottom: 20px;">Vous serez notifié par mail quand la commande sera prête.</p>
            <button onclick="fermerPopup()" style="background: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 5px; cursor: pointer; font-size: 16px;">OK</button>
        </div>
    </div>

    <!-- Scripts JavaScript pour la validation et le formatage -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== FORMATAGE AUTOMATIQUE DU NUMÉRO DE CARTE =====
            const carteInput = document.getElementById('carte');
            if (carteInput) {
                carteInput.addEventListener('input', function(e) {
                    // Suppression des espaces et caractères non numériques
                    let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
                    // Formatage par groupes de 4 chiffres
                    let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
                    // Limitation à 19 caractères (16 chiffres + 3 espaces)
                    if (formattedValue.length > 19) formattedValue = formattedValue.substring(0, 19);
                    e.target.value = formattedValue;
                });
            }
            
            // ===== FORMATAGE DE LA DATE D'EXPIRATION =====
            const dateInput = document.getElementById('date');
            if (dateInput) {
                dateInput.addEventListener('input', function(e) {
                    // Suppression des caractères non numériques
                    let value = e.target.value.replace(/\D/g, '');
                    // Ajout automatique du slash après les 2 premiers chiffres
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }
            
            // ===== VALIDATION DU CCV (SEULEMENT DES CHIFFRES) =====
            const ccvInput = document.getElementById('ccv');
            if (ccvInput) {
                ccvInput.addEventListener('input', function(e) {
                    // Suppression de tous les caractères non numériques
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }
            
            // ===== VALIDATION AVANT SOUMISSION DU FORMULAIRE =====
            const formPaiement = document.getElementById('formPaiement');
            if (formPaiement) {
                formPaiement.addEventListener('submit', function(e) {
                    e.preventDefault(); // Empêcher la soumission par défaut
                    
                    // Récupération des valeurs des champs
                    const carte = document.getElementById('carte').value.trim();
                    const ccv = document.getElementById('ccv').value.trim();
                    const date = document.getElementById('date').value.trim();
                    
                    // Validation du numéro de carte (16 chiffres sans espaces)
                    if (carte.replace(/\s/g, '').length < 16) {
                        alert('Le numéro de carte doit contenir 16 chiffres.');
                        return false;
                    }
                    
                    // Validation du CCV (3 chiffres)
                    if (ccv.length < 3) {
                        alert('Le code CCV doit contenir 3 chiffres.');
                        return false;
                    }
                    
                    // Validation de la date (format MM/AA)
                    if (date.length < 5) {
                        alert('La date d\'expiration doit être au format MM/AA.');
                        return false;
                    }
                    
                    // Si toutes les validations passent, afficher le popup
                    document.getElementById('popupConfirmation').style.display = 'flex';
                });
            }
        });

        /**
         * Fonction pour fermer le popup et soumettre le formulaire
         * Appelée lors du clic sur le bouton OK du popup
         */
        function fermerPopup() {
            // Soumettre le formulaire après fermeture du popup
            document.getElementById('formPaiement').submit();
        }
    </script>
</body>
</html>
