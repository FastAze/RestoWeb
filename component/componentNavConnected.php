<?php 
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}


include "template/ini.php";

$username = "Nom d'utilisateur";

if (isset($_SESSION['user_id'])) {
    $dbh = db_connect();
    $sql = "SELECT loginUtil FROM utilisateur WHERE idUtil = :user_id";
    try {
        $sth = $dbh->prepare($sql);
        $sth->execute([':user_id' => $_SESSION['user_id']]);
        
        $user = $sth->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $username = htmlspecialchars($user['loginUtil']);
        }
    } catch (PDOException $ex) {
        error_log("Erreur lors de la requête SQL : " . $ex->getMessage());
    }
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
            <a class="profile" href="profile.html"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
        </div>
    </nav>

    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>