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
            <div class="champ-paiement">
                <label for="carte">Numéro de carte bancaire :</label>
                <input type="text" id="carte" name="carte" placeholder="1234 5678 9012 3456">
            </div>
            <div class="champs-inline">
                <div class="champ-ccv">
                    <label for="ccv">CCV :</label>
                    <input type="text" id="ccv" name="ccv" maxlength="3">
                </div>
                <div class="champ-date">
                    <label for="date">Date :</label>
                    <input type="text" id="date" name="date" placeholder="MM/AA" maxlength="5">
                </div>
                <div class="montant">
                    <span>Montant : <?php echo number_format($totalTTC, 2, ',', ' '); ?>€</span>
                </div>
            </div>
            <div class="boutons-paiement">
                <button class="bouton-retour" onclick="window.location.href='accueilConnecte.php'">Annuler</button>
                <button class="bouton-valider" id="boutonValider">Valider</button>
            </div>
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
    
    // Gestionnaire pour le bouton de validation
    const boutonValider = document.getElementById('boutonValider');
    if (boutonValider) {
        boutonValider.addEventListener('click', function(e) {
            e.preventDefault();
            validerPaiement();
            // Retour à la page d'accueil après validation
            setTimeout(function() {
                window.location.href = 'accueilConnecte.php';
            }, 1);
        });
    }
});

// Fonction pour valider le paiement
function validerPaiement() {
    const carte = document.getElementById('carte').value.trim();
    const ccv = document.getElementById('ccv').value.trim();
    const date = document.getElementById('date').value.trim();
    
    // Validation basique des champs
    if (!carte || !ccv || !date) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }
    
    if (carte.replace(/\s/g, '').length < 16) {
        alert('Le numéro de carte doit contenir 16 chiffres.');
        return;
    }
    
    if (ccv.length < 3) {
        alert('Le code CCV doit contenir 3 chiffres.');
        return;
    }
    
    if (date.length < 5) {
        alert('La date d\'expiration doit être au format MM/AA.');
        return;
    }
}

// Fonction pour retourner à l'accueil
function retourAccueil() {
    window.location.href = 'accueilConnecte.php';
}
</script>

</body>
</html>