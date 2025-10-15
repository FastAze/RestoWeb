<?php
// Inclusion des fichiers nécessaires pour la base de données
include_once 'template/ini.php';

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

<section class="section-paiement" id="sectionPaiement" style="display: none;">
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
                <button class="bouton-retour">Annuler</button>
                <button class="bouton-valider">Valider</button>
            </div>
        </div>
    </div>
</section>