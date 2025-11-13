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
    // Inclusion du fichier de configuration de la base de données
    include 'template/ini.php';
    
    // Démarrage de la session pour gérer l'authentification
    session_start();
    ?>
    
    <!-- Conteneur principal du panier -->
    <div class="conteneur-panier">
        <h2>Pannier</h2>
        
        <!-- Tableau d'affichage des articles du panier -->
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
                // Connexion à la base de données
                $dbh = db_connect();
                
                // Récupération de l'ID utilisateur depuis la session
                $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

                // Vérification si l'utilisateur est connecté
                if (!$user) {
                    // Affichage d'un message d'erreur si non connecté
                    echo '<tr><td colspan="4" class="message-erreur">Veuillez vous connecter pour voir votre panier.</td></tr>';
                    echo '</tbody></table></div></section></body></html>';
                    exit;
                }

                // ===== TRAITEMENT DE LA MODIFICATION DE QUANTITÉ =====
                if (isset($_POST['modifier_quantite']) && isset($_POST['idProduit']) && isset($_POST['nouvelle_quantite'])) {
                    try {
                        // Récupération et conversion des données POST
                        $idProduitModif = (int)$_POST['idProduit'];
                        $nouvelleQuantite = (int)$_POST['nouvelle_quantite'];
                        
                        // Vérification que la quantité est positive
                        if ($nouvelleQuantite > 0) {
                            // Récupération de l'ID de la commande active (état = 1)
                            $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                            $getCommandeSth = $dbh->prepare($getCommandeSql);
                            $getCommandeSth->execute([':utilisateur' => $user]);
                            $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                            
                            // Si une commande active existe
                            if ($commande) {
                                // Mise à jour de la quantité dans la ligne de commande
                                $updateSql = "UPDATE lignedecommande SET quantite = :quantite WHERE idCommande = :idCommande AND idProduit = :idProduit";
                                $updateSth = $dbh->prepare($updateSql);
                                $updateSth->execute([
                                    ':quantite' => $nouvelleQuantite,
                                    ':idCommande' => $commande['idCommande'],
                                    ':idProduit' => $idProduitModif
                                ]);
                                
                                // Message de succès et redirection
                                $_SESSION['message_succes'] = 'Quantité mise à jour.';
                                header('Location: ' . $_SERVER['PHP_SELF']);
                                exit();
                            }
                        }
                    } catch (PDOException $ex) {
                        // Gestion des erreurs SQL
                        $_SESSION['message_erreur'] = 'Erreur lors de la modification : ' . $ex->getMessage();
                    }
                }

                // ===== TRAITEMENT DE LA SUPPRESSION D'UN ARTICLE =====
                if (isset($_POST['supprimer']) && isset($_POST['idProduit'])) {
                    try {
                        // Récupération de l'ID du produit à supprimer
                        $idProduitSuppr = (int)$_POST['idProduit'];
                        
                        // Récupération de l'ID de la commande active
                        $getCommandeSql = "SELECT idCommande FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                        $getCommandeSth = $dbh->prepare($getCommandeSql);
                        $getCommandeSth->execute([':utilisateur' => $user]);
                        $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                        
                        // Si une commande active existe
                        if ($commande) {
                            // Suppression de la ligne de commande
                            $deleteSql = "DELETE FROM lignedecommande WHERE idCommande = :idCommande AND idProduit = :idProduit";
                            $deleteSth = $dbh->prepare($deleteSql);
                            $deleteSth->execute([
                                ':idCommande' => $commande['idCommande'],
                                ':idProduit' => $idProduitSuppr
                            ]);
                            
                            // Message de succès et redirection
                            $_SESSION['message_succes'] = 'Article supprimé du panier.';
                            header('Location: ' . $_SERVER['PHP_SELF']);
                            exit();
                        }
                    } catch (PDOException $ex) {
                        // Gestion des erreurs SQL
                        $_SESSION['message_erreur'] = 'Erreur lors de la suppression : ' . $ex->getMessage();
                    }
                }

                // ===== TRAITEMENT DU FORMULAIRE DE VALIDATION =====
                if (isset($_POST['valider'])) {
                    // Vérification que le panier n'est pas vide avant de procéder
                    $checkPanierSql = "SELECT COUNT(*) as nbArticles FROM lignedecommande l JOIN commande c ON c.idCommande = l.idCommande WHERE c.idUtilisateur = :utilisateur AND c.idEtat = 1";
                    $checkPanierSth = $dbh->prepare($checkPanierSql);
                    $checkPanierSth->execute([':utilisateur' => $user]);
                    $panierCount = $checkPanierSth->fetch(PDO::FETCH_ASSOC);
                    
                    // Si le panier est vide, afficher un message d'erreur
                    if ($panierCount['nbArticles'] == 0) {
                        $_SESSION['message_erreur'] = 'Votre panier est vide.';
                    } else {
                        // Vérification que l'option de livraison est sélectionnée
                        if (isset($_POST['option-livraison'])) {
                            // Conversion de l'option en booléen (0 = sur place, 1 = à emporter)
                            $option_livraison = ($_POST['option-livraison'] == 'sur_place') ? 0 : 1;
                            
                            try {
                                // Récupération de la commande active
                                $getCommandeSql = "SELECT idCommande, typeCom FROM commande WHERE idUtilisateur = :utilisateur AND idEtat = 1 ORDER BY dateHeureCom DESC LIMIT 1";
                                $getCommandeSth = $dbh->prepare($getCommandeSql);
                                $getCommandeSth->execute([':utilisateur' => $user]);
                                $commande = $getCommandeSth->fetch(PDO::FETCH_ASSOC);
                                
                                // Mise à jour du type de commande si nécessaire
                                if ($commande && $commande['typeCom'] != $option_livraison) {
                                    $updateSql = "UPDATE commande SET typeCom = :typeCom WHERE idCommande = :idCommande";
                                    $updateSth = $dbh->prepare($updateSql);
                                    $updateSth->execute([':typeCom' => $option_livraison, ':idCommande' => $commande['idCommande']]);
                                }
                                
                                // Redirection vers la page de paiement
                                header('Location: paiement.php');
                                exit();
                            } catch (PDOException $ex) {
                                // Gestion des erreurs SQL
                                $_SESSION['message_erreur'] = 'Erreur : ' . $ex->getMessage();
                            }
                        }
                    }
                }

                // ===== RÉCUPÉRATION DES ARTICLES DU PANIER =====
                // Requête avec jointures pour récupérer les informations complètes
                $sql = "SELECT p.idProduit, p.libProduit, l.quantite, l.totalHT 
                        FROM lignedecommande l 
                        JOIN produit p ON l.idProduit = p.idProduit 
                        JOIN commande c ON c.idCommande = l.idCommande 
                        WHERE c.idUtilisateur = :utilisateur 
                        AND c.idEtat = 1 
                        ORDER BY c.dateHeureCom DESC";

                try {
                    // Exécution de la requête
                    $sth = $dbh->prepare($sql);
                    $sth->execute([':utilisateur' => $user]);
                    $panier = $sth->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $ex) {
                    // Arrêt en cas d'erreur SQL critique
                    die("Erreur SQL : " . $ex->getMessage());
                }

                // Affichage d'un message si le panier est vide
                if (empty($panier)) {
                    echo '<tr><td colspan="4" class="message-vide">Votre panier est vide.</td></tr>';
                }

                // ===== AFFICHAGE DES LIGNES DU PANIER =====
                foreach ($panier as $panié) {    
                    ?>
                    <tr class="ligne-panier">
                        <!-- Nom du produit -->
                        <td class="nom-produit"><?= htmlspecialchars($panié['libProduit']) ?></td>
                        
                        <!-- Contrôles de quantité avec boutons - et + -->
                        <td class="quantite-produit">
                            <!-- Formulaire pour diminuer la quantité -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                                <input type="hidden" name="nouvelle_quantite" value="<?= $panié['quantite'] - 1 ?>">
                                <button type="submit" name="modifier_quantite" class="btn-qte">-</button>
                            </form>
                            
                            <!-- Affichage de la quantité actuelle -->
                            <span class="qte-val"><?= htmlspecialchars($panié['quantite']) ?></span>
                            
                            <!-- Formulaire pour augmenter la quantité -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                                <input type="hidden" name="nouvelle_quantite" value="<?= $panié['quantite'] + 1 ?>">
                                <button type="submit" name="modifier_quantite" class="btn-qte">+</button>
                            </form>
                        </td>
                        
                        <!-- Bouton de suppression de l'article -->
                        <td class="supprimer-produit">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="idProduit" value="<?= $panié['idProduit'] ?>">
                                <button type="submit" name="supprimer" class="btn-supprimer">Supprimer</button>
                            </form>
                        </td>
                        
                        <!-- Prix total HT de la ligne -->
                        <td class="prix-produit"><?= htmlspecialchars($panié['totalHT']) ?>€</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <!-- Options de livraison et boutons d'action -->
        <div class="options-panier">
            <div class="bottom-options">
                <form method="POST" action="">
                    <!-- Bouton retour vers l'accueil -->
                    <button class="bouton-retour" type="button" onclick="window.location.href='accueilConnecte.php'">Retour</button>
                    
                    <!-- Options de type de consommation -->
                    <div class="options-livraison">
                        <!-- Sur place : TVA 10% -->
                        <label><input type="radio" name="option-livraison" value="sur_place" required> Sur Place</label>
                        <!-- À emporter : TVA 5.5% -->
                        <label><input type="radio" name="option-livraison" value="a_emporter" required> À Emporter</label>
                    </div>
                    
                    <!-- Bouton de validation du panier -->
                    <button class="bouton-valider" type="submit" name="valider">Valider</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Script JavaScript pour la gestion des notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération des éléments de notification
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            
            // Masquer la notification au chargement de la page
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            
            // Gestion du clic sur l'icône de notification
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault(); // Empêcher le comportement par défaut du lien
                    
                    // Toggle de l'affichage de la notification
                    if (notifSection) {
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>
