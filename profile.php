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
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            require_once 'template/ini.php';
                            $dbh = db_connect();
                            
                            $idUtilisateur = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            
                            $stmt = $dbh->prepare("SELECT c.idCommande, c.dateHeureCom, c.totalTTC, c.typeCom, e.libEtat
                            FROM commande c
                            INNER JOIN etat e ON c.idEtat = e.idEtat
                            WHERE c.idUtilisateur = :idUtilisateur
                            AND c.idEtat != 1
                            ORDER BY c.dateHeureCom DESC");
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
                                    echo '<td>' . htmlspecialchars($commande['libEtat']) . '</td>';
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
    </script>
</body>
</html>