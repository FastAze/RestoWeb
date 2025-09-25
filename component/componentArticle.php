<?php
    include "template/ini.php";
    // Connexion à la base de données
    $dbh = db_connect();
    $sql = "SELECT idProduit, libProduit, prixProduitHT FROM produit";
    try {
        // Préparation et exécution de la requête
        $sth = $dbh->prepare($sql);
        $sth->execute();
        
        // Récupération de tous les résultats
        $produits = $sth->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        // Gestion des erreurs
        die("Erreur lors de la requête SQL : " . $ex->getMessage());
    }
?>

<?php foreach ($produits as $produit): ?>
<div class="article">
    <img src="image/pizza.jpg" alt="<?php echo htmlspecialchars($produit['libProduit']); ?>">
    <h2><?php echo htmlspecialchars($produit['libProduit']); ?></h2>
    <h3><?php echo htmlspecialchars($produit['prixProduitHT']); ?>€</h3>
</div>
<?php endforeach; ?>