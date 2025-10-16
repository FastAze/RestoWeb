<?php
// Inclusion des fichiers nécessaires
include "component/componentDocType.php";
include_once 'template/ini.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connection.php');
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
        
        header('Location: accueilConnecte.php');
        exit();
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour de l'état: " . $e->getMessage());
    }
}
?>

<body>
    
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
                <input type="hidden" name="action" value="valider_paiement">
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

<script>
// Script pour gérer les interactions de la page de paiement
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
            const carte = document.getElementById('carte').value.trim();
            const ccv = document.getElementById('ccv').value.trim();
            const date = document.getElementById('date').value.trim();
            
            if (carte.replace(/\s/g, '').length < 16) {
                e.preventDefault();
                alert('Le numéro de carte doit contenir 16 chiffres.');
                return false;
            }
            
            if (ccv.length < 3) {
                e.preventDefault();
                alert('Le code CCV doit contenir 3 chiffres.');
                return false;
            }
            
            if (date.length < 5) {
                e.preventDefault();
                alert('La date d\'expiration doit être au format MM/AA.');
                return false;
            }
        });
    }
});
</script>

</body>
</html>