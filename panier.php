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
    include 'template/ini.php';
    include 'component/componentNavConnected.php';
    session_start();
?>
    <div class="conteneur-panier">
        <h2>Pannier</h2>
        
        <table class="table-panier">
            <thead>
                <tr>
                    <th class="col-nom">Nom de l'article</th>
                    <th class="col-quantite">Quantité</th>
                    <th class="col-supprimer">Supprimer</th>
                    <th class="col-prix">Prix</th>
                </tr>
            </thead>
            <tbody>
            <?php

            $dbh = db_connect();
            $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            if (!$user) {
                echo '<tr><td colspan="4" class="message-erreur">Veuillez vous connecter pour voir votre panier.</td></tr>';
                echo '</tbody></table></div></section></body></html>';
                exit;
            }

            // Traitement de la modification de quantité
            if (isset($_POST['modifier_quantite']) && isset($_POST['idProduit']) && isset($_POST['nouvelle_quantite'])) {
                try {
                    $idProduitModif = (int)$_POST['idProduit'];
                    $nouvelleQuantite = (int)$_POST['nouvelle_quantite'];
                    
                    if ($nouvelleQuantite > 0) {
                        $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                        $getCommandeSth = $dbh->prepare($getCommandeSql);
                        $getCommandeSth->execute([':utilisateur' => $user]);
                        $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                        
                        if ($commande) {
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
                    $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                    $getCommandeSth = $dbh->prepare($getCommandeSql);
                    $getCommandeSth->execute([':utilisateur' => $user]);
                    $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                    
                    if ($commande) {
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
                $checkPanierSql = "SELECT COUNT(*) as nbArticles FROM lignedecommande l JOIN commande c ON c.idCommande = l.idCommande WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1";
                $checkPanierSth = $dbh->prepare($checkPanierSql);
                $checkPanierSth->execute([':utilisateur' => $user]);
                $panierCount = $checkPanierSth->fetch(PDO::FETCH_ASSOC);
                
                if ($panierCount['nbArticles'] == 0) {
                    $_SESSION['message_erreur'] = 'Votre panier est vide.';
                } else {
                    if (isset($_POST['option-livraison'])) {
                        $option_livraison = ($_POST['option-livraison'] == 'sur_place') ? 0 : 1;
                        
                        try {
                            $getCommandeSql = "SELECT idCommande, typeCom FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                            $getCommandeSth = $dbh->prepare($getCommandeSql);
                            $getCommandeSth->execute([':utilisateur' => $user]);
                            $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                            
                            if ($commande && $commande['typeCom'] != $option_livraison) {
                                $updateSql = "UPDATE commande SET typeCom = :typeCom WHERE idCommande = :idCommande";
                                $updateSth = $dbh->prepare($updateSql);
                                $updateSth->execute([':typeCom' => $option_livraison, ':idCommande' => $commande['idCommande']]);
                            }
                            
                            header('Location: paiement.php');
                            exit();
                        } catch (PDOException $ex) {
                            $_SESSION['message_erreur'] = 'Erreur : ' . $ex->getMessage();
                        }
                    }
                }
            }

            $sql = "SELECT p.idProduit, p.libProduit, l.quantite, l.totalHT FROM lignedecommande l JOIN produit p ON l.idProduit = p.idProduit JOIN commande c ON c.idCommande = l.idCommande WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1 ORDER BY c.dateHeureCom DESC";

            try {
                $sth = $dbh->prepare($sql);
                $sth->execute([':utilisateur' => $user]);
                $panier = $sth->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $ex) {
                die("Erreur SQL : " . $ex->getMessage());
            }

            if (empty($panier)) {
                echo '<tr><td colspan="4" class="message-vide">Votre panier est vide.</td></tr>';
            }

            foreach ($panier as $panié) {    
                ?>
                <tr class="ligne-panier">
                    <td class="nom-produit"><?= htmlspecialchars($panié['libProduit']) ?></td>
                    <td class="quantite-produit">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                            <input type="hidden" name="nouvelle_quantite" value="<?= $panié['quantite'] - 1 ?>">
                            <button type="submit" name="modifier_quantite" class="btn-qte">-</button>
                        </form>
                        <span class="qte-val"><?= htmlspecialchars($panié['quantite']) ?></span>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                            <input type="hidden" name="nouvelle_quantite" value="<?= $panié['quantite'] + 1 ?>">
                            <button type="submit" name="modifier_quantite" class="btn-qte">+</button>
                        </form>
                    </td>
                    <td class="supprimer-produit">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                            <button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>
                        </form>
                    </td>
                    <td class="prix-produit"><?= htmlspecialchars($panié['totalHT']) ?>€</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="options-panier">
            <div class="bottom-options">
                <form method="POST" action="">
                    <button class="bouton-retour" type="button" onclick="window.location.href='accueilConnecte.php'">Retour</button>
                    <div class="options-livraison">
                        <label><input type="radio" name="option-livraison" value="sur_place" required> Sur Place</label>
                        <label><input type="radio" name="option-livraison" value="a_emporter" required> À Emporter</label>
                    </div>
                    <button class="bouton-valider" type="submit" name="valider">Valider</button>
                </form>
            </div>
        </div>
    </div>
        <script>
                document.addEventListener('DOMContentLoaded', function() {
            // Masquer la notification au chargement et toggle au clic
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (notifSection) {
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>