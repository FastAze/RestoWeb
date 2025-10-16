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
            $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            // Vérifier si l'utilisateur est connecté
            if (!$user) {
                echo '<div class="message-erreur">Veuillez vous connecter pour voir votre panier.</div>';
                echo '</div></div></section>';
                return;
            }

            // Traitement du formulaire de validation
            if (isset($_POST['valider'])) {
                // Vérifier que le panier n'est pas vide avant de procéder
                $checkPanierSql = "SELECT COUNT(*) as nbArticles FROM lignedecommande l
                                   JOIN commande c ON c.idCommande = l.idCommande
                                   WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1";
                $checkPanierSth = $dbh->prepare($checkPanierSql);
                $checkPanierSth->execute([':utilisateur' => $user]);
                $panierCount = $checkPanierSth->fetch(PDO::FETCH_ASSOC);
                
                if ($panierCount['nbArticles'] == 0) {
                    echo "<script>alert('Votre panier est vide. Ajoutez des articles avant de valider.');</script>";
                } else {
                    if (isset($_POST['option-livraison'])) {
                        if ($_POST['option-livraison'] == 'sur_place') {
                            $option_livraison = 0;
                        } elseif ($_POST['option-livraison'] == 'a_emporter') {
                            $option_livraison = 1;
                        }
                        
                        // Mise à jour de la commande avec le type sélectionné
                        try {
                            // D'abord, récupérer l'ID de la commande active de l'utilisateur
                            $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                            $getCommandeSth = $dbh->prepare($getCommandeSql);
                            $getCommandeSth->execute([':utilisateur' => $user]);
                            $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                            
                            if ($commande) {
                                // Mise à jour de la commande trouvée
                                $updateSql = "UPDATE commande SET typeCom = :typeCom WHERE idCommande = :idCommande";
                                $updateSth = $dbh->prepare($updateSql);
                                $result = $updateSth->execute([
                                    ':typeCom' => $option_livraison,
                                    ':idCommande' => $commande['idCommande']
                                ]);
                                
                                if ($updateSth->rowCount() > 0) {
                                    // Redirection vers la page de paiement après mise à jour réussie
                                    echo "<script>
                                        alert('Type de livraison mis à jour avec succès!');
                                        window.location.href = 'paiement.php';
                                    </script>";
                                    exit();
                                } else {
                                    echo "<script>alert('Erreur: Aucune modification effectuée.');</script>";
                                }
                            } else {
                                echo "<script>alert('Erreur: Aucune commande active trouvée.');</script>";
                            }

                        } catch (PDOException $ex) {
                            echo "<script>alert('Erreur lors de la mise à jour : " . addslashes($ex->getMessage()) . "');</script>";
                        }
                    }
                }
            }

            // Requête correcte : joindre lignedecommande -> produit via idProduit, et commander via idCommande
            // Sélectionner seulement les articles de la commande active (état = 1)
            $sql = "SELECT p.libProduit, l.quantite, l.totalHT, c.totalTTC, c.idCommande
                    FROM lignedecommande l
                    JOIN produit p ON l.idProduit = p.idProduit
                    JOIN commande c ON c.idCommande = l.idCommande
                    WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1
                    ORDER BY c.dateHeureCom DESC";

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

            if (empty($panier)) {
                echo '<div class="message-vide">Votre panier est vide.</div>';
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
                <form method="POST" action="">
                    <button class="bouton-retour" type="button" onclick="afficherArticle()">Retour</button>
                    <div class="options-livraison">
                        <label><input type="radio" name="option-livraison" value="sur_place" required> Sur Place</label>
                        <label><input type="radio" name="option-livraison" value="a_emporter" required> À Emporter</label>
                    </div>
                    <button class="bouton-valider" type="submit" name="valider">Valider</button>
                </form>
            </div>
        </div>
    </div>
</section>