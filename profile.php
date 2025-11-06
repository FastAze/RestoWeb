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
                            <th>Voir le panier</th>
                            <th>Prix (TTC)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>500500500</td>
                            <td><button class="voir-commande-btn">Voir la commande</button></td>
                            <td>52.80€</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
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