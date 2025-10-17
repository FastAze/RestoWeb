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
    include_once 'template/ini.php';
    session_start();

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: connection.php');
        exit();
    }

    // Traitement du formulaire de paiement
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_paiement'])) {
        $carte = isset($_POST['carte']) ? preg_replace('/\s/', '', $_POST['carte']) : '';
        $ccv = isset($_POST['ccv']) ? $_POST['ccv'] : '';
        $date = isset($_POST['date']) ? $_POST['date'] : '';
        
        $erreurs = [];
        
        // Validation des champs
        if (empty($carte) || empty($ccv) || empty($date)) {
            $erreurs[] = 'Veuillez remplir tous les champs obligatoires.';
        }
        
        if (!empty($carte) && !preg_match('/^\d{16}$/', $carte)) {
            $erreurs[] = 'Le numéro de carte doit contenir 16 chiffres.';
        }
        
        if (!empty($ccv) && !preg_match('/^\d{3}$/', $ccv)) {
            $erreurs[] = 'Le code CCV doit contenir 3 chiffres.';
        }
        
        if (!empty($date) && !preg_match('/^\d{2}\/\d{2}$/', $date)) {
            $erreurs[] = 'La date d\'expiration doit être au format MM/AA.';
        }
        
        if (empty($erreurs)) {
            // Traitement du paiement réussi
            try {
                $dbh = db_connect();
                $user_id = $_SESSION['user_id'];
                
                // Mettre à jour l'état de la commande
                $updateSql = "UPDATE commande SET idEtat = 2 WHERE idUtilisateur = :user_id AND idEtat = 1";
                $updateSth = $dbh->prepare($updateSql);
                $updateSth->execute([':user_id' => $user_id]);
                
                $_SESSION['message_succes'] = 'paiement_valide';
            } catch (PDOException $ex) {
                $_SESSION['message_erreur'] = 'Erreur lors du traitement du paiement.';
                error_log("Erreur paiement : " . $ex->getMessage());
            }
            
            header('Location: accueilConnecte.php');
            exit();
        } else {
            $_SESSION['message_erreur'] = implode('<br>', $erreurs);
        }
    }

    // Traitement du bouton Annuler
    if (isset($_POST['annuler_paiement'])) {
        header('Location: accueilConnecte.php');
        exit();
    }

    // Récupération des informations de la commande active
    $totalTTC = 0.00;
    $typeCommande = 'Non défini';
    $idCommande = null;

    if (isset($_SESSION['user_id'])) {
        $dbh = db_connect();
        $user_id = $_SESSION['user_id'];
        
        try {
            // Récupérer la commande active de l'utilisateur
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
            
            if ($commande) {
                $totalTTC = $commande['totalTTC'];
                $idCommande = $commande['idCommande'];
                
                // Convertir le type de commande en texte
                if ($commande['typeCom'] == 0) {
                    $typeCommande = 'Sur place';
                } elseif ($commande['typeCom'] == 1) {
                    $typeCommande = 'À emporter';
                } else {
                    $typeCommande = 'Non défini';
                }
            }
        } catch (PDOException $ex) {
            error_log("Erreur lors de la récupération de la commande : " . $ex->getMessage());
        }
    }

    // Gestion de la validation du paiement
    if (isset($_POST['action']) && $_POST['action'] === 'valider_paiement') {
        try {
            // Récupérer les informations de la commande avant de la finaliser
            $sqlCommande = "SELECT totalTTC FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1";
            $sthCommande = $dbh->prepare($sqlCommande);
            $sthCommande->execute([':utilisateur' => $user_id]);
            $commande = $sthCommande->fetch(PDO::FETCH_ASSOC);
            
            // Mettre à jour l'état de la commande
            $sql = "UPDATE commande 
                    SET idEtat = 2 
                    WHERE idUtilisateur = :user 
                    AND idEtat = 1";
            
            $stmt = $dbh->prepare($sql);
            $stmt->execute([':user' => $user_id]);
            
            $_SESSION['message_succes'] = 'paiement_valide';
            header('Location: accueilConnecte.php');
            exit();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'état: " . $e->getMessage());
        }
    }
    ?>

    <section class="section-paiement" id="sectionPaiement">
        <div class="conteneur-paiement">
            <h2>Paiement</h2>
            
            <div class="info-commande">
                <div class="commande-details">
                    <span><strong>Commande N° :</strong> <?php echo $idCommande ? $idCommande : 'Non définie'; ?></span>
                    <span><strong>Type :</strong> <?php echo htmlspecialchars($typeCommande); ?></span>
                </div>
            </div>
            
            <div class="formulaire-paiement">
                <form method="POST" id="formPaiement">
                    <input type="hidden" name="valider_paiement" value="1">
                    
                    <div class="champ-paiement">
                        <label for="carte">Numéro de carte bancaire :</label>
                        <input type="text" id="carte" name="carte" placeholder="1234 5678 9012 3456" required>
                    </div>
                    
                    <div class="champs-inline">
                        <div class="champ-ccv">
                            <label for="ccv">CCV :</label>
                            <input type="text" id="ccv" name="ccv" maxlength="3" required>
                        </div>
                        <div class="champ-date">
                            <label for="date">Date :</label>
                            <input type="text" id="date" name="date" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="montant">
                            <span>Montant : <?php echo number_format($totalTTC, 2, ',', ' '); ?>€</span>
                        </div>
                    </div>
                    
                    <div class="boutons-paiement">
                        <button type="button" class="bouton-retour" onclick="window.location.href='accueilConnecte.php'">Annuler</button>
                        <button type="submit" class="bouton-valider">Valider</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Popup de confirmation -->
    <div id="popupConfirmation" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="color: #28a745; margin-bottom: 20px;">✓ Paiement validé</h3>
            <p style="margin-bottom: 20px;">Vous serez notifié par mail quand la commande sera prête.</p>
            <button onclick="fermerPopup()" style="background: #28a745; color: white; border: none; padding: 10px 30px; border-radius: 5px; cursor: pointer; font-size: 16px;">OK</button>
        </div>
    </div>

    <script>
        // Script minimal pour le formatage côté client (amélioration UX uniquement)
        document.addEventListener('DOMContentLoaded', function() {
            // Formatage automatique du numéro de carte
            const carteInput = document.getElementById('carte');
            if (carteInput) {
                carteInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
                    let formattedValue = value.replace(/(.{4})/g, '$1 ').trim();
                    if (formattedValue.length > 19) formattedValue = formattedValue.substring(0, 19);
                    e.target.value = formattedValue;
                });
            }
            
            // Formatage de la date d'expiration
            const dateInput = document.getElementById('date');
            if (dateInput) {
                dateInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }
            
            // Validation du CCV (seulement des chiffres)
            const ccvInput = document.getElementById('ccv');
            if (ccvInput) {
                ccvInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '');
                });
            }
            
            // Validation avant soumission du formulaire
            const formPaiement = document.getElementById('formPaiement');
            if (formPaiement) {
                formPaiement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const carte = document.getElementById('carte').value.trim();
                    const ccv = document.getElementById('ccv').value.trim();
                    const date = document.getElementById('date').value.trim();
                    
                    if (carte.replace(/\s/g, '').length < 16) {
                        alert('Le numéro de carte doit contenir 16 chiffres.');
                        return false;
                    }
                    
                    if (ccv.length < 3) {
                        alert('Le code CCV doit contenir 3 chiffres.');
                        return false;
                    }
                    
                    if (date.length < 5) {
                        alert('La date d\'expiration doit être au format MM/AA.');
                        return false;
                    }
                    
                    // Afficher le popup
                    document.getElementById('popupConfirmation').style.display = 'flex';
                });
            }
        });

        function fermerPopup() {
            // Soumettre le formulaire après fermeture du popup
            document.getElementById('formPaiement').submit();
        }
    </script>
</body>
</html>
