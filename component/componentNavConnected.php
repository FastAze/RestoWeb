<?php 
// Vérifier si l'action de déconnexion est demandée
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<nav>
        <div class="nav-top">
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            <div class="pannier-notif">
                <a class="pannier" href="pannier.php">Pannier</a>
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <div class="auth-buttons">
            <a class="logout" href="?logout=1">Déconnexion</a>
            <a class="profile" href="profile.html">Nom d'utilisateur</a>
        </div>
    </nav>

    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>