
    <nav>
        <div class="nav-top">
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            <div class="pannier-notif">
                <a class="pannier" href="pannier.php">Pannier</a>
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <div class="auth-buttons">
            <a class="logout" onclick="déconnexion()">Déconnexion</a>
            <?php 
            function déconnexion() {
                // Code pour gérer la déconnexion de l'utilisateur
                session_start();
                session_destroy();
                header("Location: index.php");
                exit();
            }

            
            ?>
            <a class="profile" href="profile.php">Nom d'utilisateur</a>
        </div>
    </nav>

    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>

<?php include 'component/componentFooter.php'; ?>