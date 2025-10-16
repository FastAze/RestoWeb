<section class="section-panier" id="sectionPanier" style="display: none;">
    <div class="conteneur-panier">
        <h2>Pannier</h2>
        <div class="entete-panier">
            <div class="nom-article">Nom de l'article</div>
            <div class="quantité-article">Quantité</div>
            <div class="prix-article">Prix</div>
            <div></div>
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

            // Traitement de la modification de quantité
            if (isset($_POST['modifier_quantite']) && isset($_POST['idProduit']) && isset($_POST['nouvelle_quantite'])) {
                try {
                    $idProduitModif = (int)$_POST['idProduit'];
                    $nouvelleQuantite = (int)$_POST['nouvelle_quantite'];
                    
                    if ($nouvelleQuantite > 0) {
                        // Récupérer l'ID de la commande active
                        $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                        $getCommandeSth = $dbh->prepare($getCommandeSql);
                        $getCommandeSth->execute([':utilisateur' => $user]);
                        $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                        
                        if ($commande) {
                            // Mettre à jour la quantité
                            $updateSql = "UPDATE lignedecommande SET quantite = :quantite WHERE idCommande = :idCommande AND idProduit = :idProduit";
                            $updateSth = $dbh->prepare($updateSql);
                            $updateSth->execute([
                                ':quantite' => $nouvelleQuantite,
                                ':idCommande' => $commande['idCommande'],
                                ':idProduit' => $idProduitModif
                            ]);
                            
                            $_SESSION['message_succes'] = 'Quantité mise à jour.';
                            header('Location: ' . $_SERVER['PHP_SELF']);
                            exit();
                        }
                    }
                } catch (PDOException $ex) {
                    $_SESSION['message_erreur'] = 'Erreur lors de la modification : ' . $ex->getMessage();
                }
            }

            // Traitement de la suppression d'un article
            if (isset($_POST['supprimer']) && isset($_POST['idProduit'])) {
                try {
                    $idProduitSuppr = (int)$_POST['idProduit'];
                    
                    // Récupérer l'ID de la commande active
                    $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                    $getCommandeSth = $dbh->prepare($getCommandeSql);
                    $getCommandeSth->execute([':utilisateur' => $user]);
                    $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                    
                    if ($commande) {
                        // Supprimer la ligne de commande
                        $deleteSql = "DELETE FROM lignedecommande WHERE idCommande = :idCommande AND idProduit = :idProduit";
                        $deleteSth = $dbh->prepare($deleteSql);
                        $deleteSth->execute([
                            ':idCommande' => $commande['idCommande'],
                            ':idProduit' => $idProduitSuppr
                        ]);
                        
                        $_SESSION['message_succes'] = 'Article supprimé du panier.';
                        header('Location: ' . $_SERVER['PHP_SELF']);
                        exit();
                    }
                } catch (PDOException $ex) {
                    $_SESSION['message_erreur'] = 'Erreur lors de la suppression : ' . $ex->getMessage();
                }
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
                    $_SESSION['message_erreur'] = 'Votre panier est vide. Ajoutez des articles avant de valider.';
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
                            $getCommandeSql = "SELECT idCommande, typeCom FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                            $getCommandeSth = $dbh->prepare($getCommandeSql);
                            $getCommandeSth->execute([':utilisateur' => $user]);
                            $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                            
                            if ($commande) {
                                // Vérifier si le type sélectionné est différent du type actuel
                                if ($commande['typeCom'] != $option_livraison) {
                                    // Mise à jour de la commande uniquement si le type a changé
                                    $updateSql = "UPDATE commande SET typeCom = :typeCom WHERE idCommande = :idCommande";
                                    $updateSth = $dbh->prepare($updateSql);
                                    $result = $updateSth->execute([
                                        ':typeCom' => $option_livraison,
                                        ':idCommande' => $commande['idCommande']
                                    ]);
                                    
                                    if ($updateSth->rowCount() > 0) {
                                        $_SESSION['message_succes'] = 'Type de livraison mis à jour avec succès!';
                                    } else {
                                        $_SESSION['message_erreur'] = 'Erreur: Aucune modification effectuée.';
                                    }
                                } else {
                                    // Le type est déjà correct, pas besoin de mise à jour
                                    $_SESSION['message_succes'] = 'Type de livraison confirmé.';
                                }
                                
                                // Redirection vers la page de paiement
                                header('Location: paiement.php');
                                exit();
                            } else {
                                $_SESSION['message_erreur'] = 'Erreur: Aucune commande active trouvée.';
                            }

                        } catch (PDOException $ex) {
                            $_SESSION['message_erreur'] = 'Erreur lors de la mise à jour : ' . $ex->getMessage();
                        }
                    }
                }
            }

            // Requête pour récupérer les articles du panier
            $sql = "SELECT p.idProduit, p.libProduit, l.quantite, l.totalHT, c.totalTTC, c.idCommande
                    FROM lignedecommande l
                    JOIN produit p ON l.idProduit = p.idProduit
                    JOIN commande c ON c.idCommande = l.idCommande
                    WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1
                    ORDER BY c.dateHeureCom DESC";

            try {
                    $sth = $dbh->prepare($sql);
                    $sth->execute([':utilisateur' => $user]);
                    $panier = $sth->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                die("Erreur lors de la requête SQL : " . $ex->getMessage());
            }

            if (empty($panier)) {
                echo '<div class="message-vide">Votre panier est vide.</div>';
            }

            foreach ($panier as $panié) {    
                echo '<div class="article-panier">';
                echo '<div class="details-article">' . htmlspecialchars($panié['libProduit']) . '</div>';
                echo '<div class="quantite-controls">';
                echo '<form method="POST" class="form-quantite" style="margin: 0;">';
                echo '<input type="hidden" name="idProduit" value="' . $panié['idProduit'] . '">';
                echo '<input type="hidden" name="nouvelle_quantite" value="' . ($panié['quantite'] - 1) . '">';
                echo '<button type="submit" name="modifier_quantite" class="btn-quantite btn-moins">-</button>';
                echo '</form>';
                echo '<span class="quantite-valeur">' . htmlspecialchars($panié['quantite']) . '</span>';
                echo '<form method="POST" class="form-quantite" style="margin: 0;">';
                echo '<input type="hidden" name="idProduit" value="' . $panié['idProduit'] . '">';
                echo '<input type="hidden" name="nouvelle_quantite" value="' . ($panié['quantite'] + 1) . '">';
                echo '<button type="submit" name="modifier_quantite" class="btn-quantite btn-plus">+</button>';
                echo '</form>';
                echo '</div>';
                echo '<div class="cout-article">' . htmlspecialchars($panié['totalHT']) . '€</div>';
                echo '<div class="action-article">';
                echo '<form method="POST" style="margin: 0;">';
                echo '<input type="hidden" name="idProduit" value="' . $panié['idProduit'] . '">';
                echo '<button type="submit" name="supprimer" class="bouton-supprimer">Supprimer</button>';
                echo '</form>';
                echo '</div>';
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