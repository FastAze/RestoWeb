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
        // Démarrage de la session pour gérer l'authentification
        session_start();

        // ===== GESTION DE LA DÉCONNEXION =====
        if (isset($_GET['logout'])) {
            session_destroy();  // Destruction de toutes les variables de session
            header("Location: index.php");  // Redirection vers la page d'accueil publique
            exit();
        }
    ?>

    <!-- Barre de navigation pour utilisateurs connectés -->
    <nav>
        <!-- Section supérieure de la navigation -->
        <div class="nav-top">
            <!-- Logo du site - lien vers l'accueil connecté -->
            <div class="logo"><a href="accueilConnecte.php">RestoWeb</a></div>
            
            <!-- Liens panier et notifications -->
            <div class="pannier-notif">
                <!-- Lien vers la page du panier -->
                <a class="pannier" href="panier.php">Panier</a>
                <!-- Icône de notification (toggle via JavaScript) -->
                <a><img src="image/notif.png" alt="notif"></a>
            </div>
        </div>
        
        <!-- Boutons d'authentification -->
        <div class="auth-buttons">
            <!-- Bouton de déconnexion -->
            <a class="logout" href="?logout=1">Déconnexion</a>
            <!-- Bouton profil avec nom d'utilisateur - lien vers la page de profil -->
            <a class="profile" href="profile.php"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></a>
        </div>
    </nav>

    <!-- Section de notification (masquée par défaut) -->
    <section class="notification">
        <div class="notification-boite">
            <p>Votre commande est en cours de route!</p>
        </div>
    </section>

    <!-- Section principale du profil utilisateur -->
    <section class="section-profile" id="sectionProfile">
        <div class="conteneur-profile">
            <!-- En-tête du profil avec avatar et nom -->
            <div class="profile-header">
                <!-- Avatar circulaire de l'utilisateur -->
                <div class="profile-avatar">
                    <div class="avatar-circle">
                        <div class="bat"></div>
                    </div>
                </div>
                <!-- Nom d'utilisateur affiché depuis la session -->
                <h2><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'nom utilisateur' ?></h2>
            </div>
            
            <!-- Contenu principal : historique des commandes -->
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
                            // Inclusion du fichier de configuration de la base de données
                            require_once 'template/ini.php';
                            $dbh = db_connect();
                            
                            // Récupération de l'ID utilisateur depuis la session (0 si non connecté)
                            $idUtilisateur = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            
                            // ===== RÉCUPÉRATION DE L'HISTORIQUE DES COMMANDES =====
                            // Requête avec jointure pour récupérer les commandes et leur état
                            // Filtre : idEtat != 1 pour exclure les commandes "initialisées" (panier en cours)
                            // Tri : par date décroissante (plus récente en premier)
                            $stmt = $dbh->prepare("SELECT c.idCommande, c.dateHeureCom, c.totalTTC, c.typeCom, e.libEtat
                            FROM commande c
                            INNER JOIN etat e ON c.idEtat = e.idEtat
                            WHERE c.idUtilisateur = :idUtilisateur
                            AND c.idEtat != 1
                            ORDER BY c.dateHeureCom DESC");
                            $stmt->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
                            $stmt->execute();
                            $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // ===== AFFICHAGE DES COMMANDES =====
                            if (count($commandes) > 0) {
                                // Parcours de toutes les commandes
                                foreach ($commandes as $commande) {
                                    // Conversion du type de commande en texte lisible
                                    if ($commande['typeCom'] == 1) {
                                        $typeCommande = 'À emporter';  // TVA 5.5%
                                    } else {
                                        $typeCommande = 'Sur place';   // TVA 10%
                                    }
                                    
                                    // Affichage d'une ligne du tableau pour chaque commande
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($commande['idCommande']) . '</td>';
                                    echo '<td>' . $commande['dateHeureCom'] . '</td>';
                                    echo '<td>' . $typeCommande . '</td>';
                                    echo '<td>' . $commande['totalTTC'] . '€</td>';
                                    echo '<td>' . htmlspecialchars($commande['libEtat']) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                // Affichage d'un message si aucune commande n'a été trouvée
                                echo '<tr><td colspan="5" style="text-align: center;">Aucune commande trouvée</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Script JavaScript pour l'interactivité -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== GESTION DE L'AFFICHAGE DES NOTIFICATIONS =====
            // Récupération des éléments de notification
            const notifSection = document.querySelector('.notification');
            const notifIcon = document.querySelector('.pannier-notif a img');
            
            // Masquer la notification au chargement de la page
            if (notifSection) {
                notifSection.style.display = 'none';
            }
            
            // Toggle de l'affichage des notifications au clic sur l'icône
            if (notifIcon) {
                notifIcon.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();  // Empêcher le comportement par défaut du lien
                    
                    // Alterner entre affichage et masquage
                    if (notifSection) {
                        notifSection.style.display = (notifSection.style.display === 'none' || notifSection.style.display === '') ? 'flex' : 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>