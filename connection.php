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
    // Inclusion des fichiers nécessaires
    include 'template/ini.php';        // Configuration de la base de données
    include 'template/chekEtat.php';   // Fonctions de gestion des commandes
    session_start();                   // Démarrage de la session
    ?>
    
    <!-- Conteneur principal pour la connexion -->
    <div class="connecInscrip">
        <div class="connection-container">
            <h2>Connexion</h2>
            
            <!-- Formulaire de connexion -->
            <form action="connection.php" method="POST">
                <!-- Champ nom d'utilisateur -->
                <div class="form-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <input type="text" id="nom" name="username" required>
                </div>
                
                <!-- Champ mot de passe -->
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp" name="password" required>
                </div>
                
                <!-- Boutons d'action -->
                <div class="form-group">
                    <!-- Bouton pour annuler et retourner à l'accueil -->
                    <input type="button" value="Annuler" onclick="window.location.href='index.php'">
                    <!-- Bouton de soumission du formulaire -->
                    <input type="submit" value="Connexion" name="connexion">
                </div>
            </form>
            
            <?php
            // ===== TRAITEMENT DU FORMULAIRE DE CONNEXION =====
            if (isset($_POST['connexion'])) {
                // Récupération des données du formulaire
                $username = isset($_POST['username']) ? $_POST['username'] : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                
                // ===== VALIDATION DES CHAMPS =====
                if (!empty($username) && !empty($password)) {
                    try {
                        // Connexion à la base de données
                        $dbh = db_connect();
                        
                        // ===== RÉCUPÉRATION DE L'UTILISATEUR =====
                        // Requête pour récupérer les informations de l'utilisateur
                        $sql = "SELECT idUtilisateur, loginUtil, mdpUtil FROM utilisateur WHERE loginUtil = :username";
                        
                        $sth = $dbh->prepare($sql);
                        $sth->bindParam(':username', $username);
                        $sth->execute();
                        
                        // Récupération des données utilisateur
                        $user = $sth->fetch(PDO::FETCH_ASSOC);
                        
                        // ===== VÉRIFICATION DU MOT DE PASSE =====
                        // password_verify compare le mot de passe en clair avec le hash en base
                        if ($user && password_verify($password, $user['mdpUtil'])) {
                            // ===== CONNEXION RÉUSSIE - CRÉATION DE LA SESSION =====
                            $_SESSION['user_id'] = $user['idUtilisateur'];    // ID utilisateur
                            $_SESSION['username'] = $user['loginUtil'];        // Nom d'utilisateur
                            $_SESSION['logged_in'] = true;                     // Indicateur de connexion
                            
                            // ===== VÉRIFICATION DE L'ÉTAT DES COMMANDES =====
                            // Vérifier si l'utilisateur a une commande active
                            // Si aucune commande n'existe, en créer une automatiquement
                            if (verifierEtatCommande($user['idUtilisateur'])) {
                                // Redirection vers la page d'accueil connecté
                                header('Location: accueilConnecte.php');
                                exit();
                            } else {
                                // Affichage d'un avertissement mais redirection quand même
                                echo "<p style='color: orange;'>Connexion réussie, mais erreur lors de la vérification des commandes.</p>";
                                header('Location: accueilConnecte.php');
                                exit();
                            }
                        } else {
                            // ===== ÉCHEC DE L'AUTHENTIFICATION =====
                            // Identifiants incorrects
                            echo "<p style='color: red;'>Nom d'utilisateur ou mot de passe incorrect.</p>";
                        }
                    } catch (PDOException $ex) {
                        // ===== GESTION DES ERREURS SQL =====
                        echo "<p style='color: red;'>Erreur lors de la connexion : " . $ex->getMessage() . "</p>";
                    }
                } else {
                    // ===== VALIDATION : CHAMPS OBLIGATOIRES =====
                    echo "<p style='color: red;'>Tous les champs sont obligatoires.</p>";
                }
            }
            ?>
            
            <!-- Lien vers la page d'inscription -->
            <div class="register-link">
                <a href="inscription.php">Vous n'avez pas de compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>
