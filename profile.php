<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoWeb</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <?php
        session_start();

        if (isset($_GET['logout'])) {
            session_destroy();
            header("Location: index.php");
            exit();
        }
    ?>

    <nav>
        <div class="nav-top">
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            <div class="pannier-notif">
                <a class="pannier" href="panier.php">Panier</a>
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <div class="auth-buttons">
            <a class="logout" href="?logout=1">Déconnexion</a>
            <a class="profile" href="profile.php"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
        </div>
    </nav>

    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>

    <section class="section-profile" id="sectionProfile">
        <div class="conteneur-profile">
            <div class="profile-header">
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <div class="bat"></div>
                    </div>
                </div>
                <h2><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></h2>
            </div>
            <div class="profile-content">
                <table class="commandes-table">
                    <thead>
                        <tr>
                            <th>ID commande</th>
                            <th>Date/Heure</th>
                            <th>Type</th>
                            <th>Prix (TTC)</th>
                            <th>Voir le panier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            require_once 'template/ini.php';
                            $dbh = db_connect();
                            
                            $idUtilisateur = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            
                            $stmt = $dbh->prepare("SELECT idCommande, dateHeureCom, totalTTC, typeCom 
                            FROM commande 
                            WHERE idUtilisateur = :idUtilisateur
                            AND NOT idEtat = 1
                            ORDER BY dateHeureCom DESC");
                            $stmt->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
                            $stmt->execute();
                            $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($commandes) > 0) {
                                foreach ($commandes as $commande) {
                                    if ($commande['typeCom'] == 1) {
                                        $typeCommande = 'À emporter';
                                    } else {
                                        $typeCommande = 'Sur place';
                                    }
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($commande['idCommande']) . '</td>';
                                    echo '<td>' . $commande['dateHeureCom'] . '</td>';
                                    echo '<td>' . $typeCommande . '</td>';
                                    echo '<td>' . $commande['totalTTC'] . '€</td>';
                                    echo '<td><button class="voir-commande-btn" data-id="' . $commande['idCommande'] . '">Voir la commande</button></td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" style="text-align: center;">Aucune commande trouvée</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="voir-commande-overlay" id="voirCommandeOverlay">
        <section class="voir-commande">
            <div class="voir-commande-header">
                <h2>Détail de la commande</h2>
                <button id="closeVoirCommande" class="close-btn">✕</button>
            </div>
            <div class="voir-commande-content">
                <div class="commande-info">
                    <div class="info-row">
                        <span class="label">N° de commande :</span>
                        <span class="value" id="numeroCommande">500500500</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Date :</span>
                        <span class="value" id="dateCommande">19/09/2025 - 14:30</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Statut :</span>
                        <span class="value statut-badge" id="statutCommande">En livraison</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Type :</span>
                        <span class="value" id="typeCommande">À emporter</span>
                    </div>
                </div>
                
                <div class="commande-articles">
                    <h3>Articles commandés :</h3>
                    <div class="articles-liste">
                        <div class="article-item">
                            <img src="image/pizza.jpg" alt="Pizza" class="article-img">
                            <div class="article-details">
                                <div class="article-nom">Pizza Margherita</div>
                                <div class="article-description">Sauce tomate, mozzarella, basilic frais</div>
                                <div class="article-quantite-prix">
                                    <span class="quantite">Quantité: 2</span>
                                    <span class="prix-unitaire">15€ × 2 = 30€</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="article-item">
                            <img src="image/pizza.jpg" alt="Pizza" class="article-img">
                            <div class="article-details">
                                <div class="article-nom">Pizza Pepperoni</div>
                                <div class="article-description">Sauce tomate, mozzarella, pepperoni</div>
                                <div class="article-quantite-prix">
                                    <span class="quantite">Quantité: 1</span>
                                    <span class="prix-unitaire">18€ × 1 = 18€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="commande-total">
                    <div class="total-ligne">
                        <span class="total-label">Sous-total :</span>
                        <span class="total-value">48€</span>
                    </div>
                    <div class="total-ligne">
                        <span class="total-label">TVA (10%) :</span>
                        <span class="total-value">4.80€</span>
                    </div>
                    <div class="total-ligne total-final">
                        <span class="total-label">Total TTC :</span>
                        <span class="total-value">52.80€</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des notifications
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

        // Afficher la fenêtre "Voir la commande"
        const voirCommandeOverlay = document.getElementById('voirCommandeOverlay');
        const closeVoirCommande = document.getElementById('closeVoirCommande');
        const voirCommandeBtns = document.querySelectorAll('.voir-commande-btn');

        // Ouvrir l'overlay au clic sur "Voir la commande"
        voirCommandeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
            const commandeId = this.getAttribute('data-id');
            // TODO: Charger les données de la commande via AJAX
            voirCommandeOverlay.style.display = 'flex';
            });
        });

        // Fermer l'overlay au clic sur le bouton de fermeture
        closeVoirCommande.addEventListener('click', function() {
            voirCommandeOverlay.style.display = 'none';
        });

        // Fermer l'overlay au clic en dehors de la fenêtre
        voirCommandeOverlay.addEventListener('click', function(e) {
            if (e.target === voirCommandeOverlay) {
            voirCommandeOverlay.style.display = 'none';
            }
        });
    </script>
</body>
</html>