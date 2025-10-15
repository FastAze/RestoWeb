<section class="section-panier" id="sectionPanier" style="display: none;">
    <div class="conteneur-panier">
        <h2>Pannier</h2>
        <div class="entete-panier">
            <div class="nom-article">Nom de l'article</div>
            <div class="quantité-article">Quantité</div>
            <div class="prix-article">Prix</div>
        </div>
        <div class="articles-panier">
            <?php

            $dbh = db_connect();
            $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Assurez-vous que la session est démarrée et que 'user_id' est défini

            // Requête correcte : joindre lignedecommande -> produit via idProduit, et commander via idCommande
            $sql = "SELECT p.libProduit, l.quantite, l.totalHT, c.totalTTC
                    FROM lignedecommande l
                    JOIN produit p ON l.idProduit = p.idProduit
                    JOIN commande c ON c.idCommande = l.idCommande
                    WHERE c.idUtilisateur = :utilisateur";

            try {
                    // Préparation et exécution de la requête avec paramètre lié
                    $sth = $dbh->prepare($sql);
                    $sth->execute([':utilisateur' => $user]);
                    // Récupération de tous les résultats
                    $panier = $sth->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                // Gestion des erreurs
                die("Erreur lors de la requête SQL : " . $ex->getMessage());
            }

            foreach ($panier as $panié) {    
            echo '<div class="article-panier">';
            echo '<div class="details-article">' . htmlspecialchars($panié['libProduit']) . '</div>';
            echo '<div class="quantité-article">' . htmlspecialchars($panié['quantite']) . '</div>';
            echo '<div class="cout-article">' . htmlspecialchars($panié['totalHT']) . '€</div>';
            echo '</div>';
                
            }
            
            ?>

        </div>
        <div class="options-panier">
            <div class="bottom-options">
                <form method="POST">
                    <button class="bouton-retour" type="button" onclick="afficherArticle()">Retour</button>
                    <div class="options-livraison">
                        <label><input type="radio" name="option-livraison" value="sur_place"> Sur Place</label>
                        <label><input type="radio" name="option-livraison" value="a_emporter"> À Emporter</label>
                    </div>
                    <button class="bouton-valider" type="submit">Valider</button>
                </form>
            </div>
        </div>
    </div>
</section>