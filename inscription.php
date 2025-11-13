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
    // Inclusion des fichiers nécessaires
    include 'template/ini.php';        // Configuration de la base de données
    include 'template/chekEtat.php';   // Fonctions de gestion des commandes
    session_start();                   // Démarrage de la session
    ?>
    
    <!-- Conteneur principal pour l'inscription -->
    <div class="connecInscrip">
        <div class="connection-container">
            <h2>Inscription</h2>
            
            <!-- Formulaire d'inscription -->
            <form id="Inscription" action="inscription.php" method="POST">
                <!-- Champ nom d'utilisateur -->
                <div class="form-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <input type="text" id="nom" name="username">
                </div>
                
                <!-- Champ adresse e-mail -->
                <div class="form-group">
                    <label for="mail">E-mail</label>
                    <input type="email" id="mail" name="email">
                </div>
                
                <!-- Champ mot de passe -->
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp" name="password">
                </div>
                
                <!-- Boutons d'action -->
                <div class="form-group">
                    <!-- Bouton pour annuler et retourner à l'accueil -->
                    <input type="button" value="Annuler" onclick="window.location.href='index.php'">
                    <!-- Bouton de soumission du formulaire -->
                    <input type="submit" value="S'inscrire" name="inscrire">
                </div>
            </form>
            
            <?php
            // ===== TRAITEMENT DU FORMULAIRE D'INSCRIPTION =====
            
            // Hashage sécurisé du mot de passe avec bcrypt
            $MDP_H = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
            
            // Récupération des données du formulaire
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';

            // Vérification si le formulaire a été soumis
            if (isset($_POST['inscrire'])) {
                // ===== VALIDATION DES DONNÉES =====
                if (!empty($username) && !empty($email) && !empty($_POST['password'])) {
                    // Connexion à la base de données
                    $dbh = db_connect();
                    
                    // ===== VÉRIFICATION DE L'UNICITÉ =====
                    // Vérifier si l'utilisateur ou l'email existe déjà dans la base
                    $check_sql = "SELECT COUNT(*) FROM utilisateur WHERE loginUtil = :username OR emailUtil = :email";
                    try {
                        $check_sth = $dbh->prepare($check_sql);
                        $check_sth->bindParam(':username', $username);
                        $check_sth->bindParam(':email', $email);
                        $check_sth->execute();
                        $count = $check_sth->fetchColumn();
                        
                        // Si l'utilisateur ou l'email existe déjà
                        if ($count > 0) {
                            echo "Ce nom d'utilisateur ou cette adresse email existe déjà.";
                        } else {
                            // ===== INSERTION DU NOUVEL UTILISATEUR =====
                            // Requête préparée pour éviter les injections SQL
                            $sql = "INSERT INTO utilisateur (idUtilisateur, loginUtil, emailUtil, mdpUtil) 
                                    VALUES (NULL, :username, :email, :password)";
                            
                            $sth = $dbh->prepare($sql);
                            // Liaison sécurisée des paramètres
                            $sth->bindParam(':username', $username);
                            $sth->bindParam(':email', $email);
                            $sth->bindParam(':password', $MDP_H);  // Mot de passe hashé
                            
                            // Exécution de l'insertion
                            if ($sth->execute()) {
                                // ===== RÉCUPÉRATION DE L'ID DU NOUVEL UTILISATEUR =====
                                $get_user_sql = "SELECT idUtilisateur FROM utilisateur WHERE loginUtil = :username AND emailUtil = :email";
                                $get_user_sth = $dbh->prepare($get_user_sql);
                                $get_user_sth->bindParam(':username', $username);
                                $get_user_sth->bindParam(':email', $email);
                                $get_user_sth->execute();
                                $user_data = $get_user_sth->fetch(PDO::FETCH_ASSOC);
                                
                                // Si l'utilisateur a été récupéré avec succès
                                if ($user_data) {
                                    $user_id = $user_data['idUtilisateur'];
                                    
                                    // ===== CRÉATION DE LA SESSION =====
                                    $_SESSION['user_id'] = $user_id;           // ID utilisateur
                                    $_SESSION['username'] = $username;          // Nom d'utilisateur
                                    $_SESSION['logged_in'] = true;              // Indicateur de connexion
                                    
                                    // ===== VÉRIFICATION/CRÉATION DE LA COMMANDE =====
                                    // Vérifier l'état des commandes du nouvel utilisateur
                                    // Créer automatiquement une commande vide s'il n'en a pas
                                    if (verifierEtatCommande($user_id)) {
                                        // Redirection vers l'accueil connecté
                                        header('Location: accueilConnecte.php');
                                        exit();
                                    } else {
                                        // Affichage d'un avertissement mais redirection quand même
                                        echo "<p style='color: orange;'>Inscription réussie, mais erreur lors de la création de la commande.</p>";
                                        header('Location: accueilConnecte.php');
                                        exit();
                                    }
                                } else {
                                    // Erreur lors de la récupération de l'utilisateur
                                    echo "Erreur lors de la récupération de l'utilisateur.";
                                }
                            } else {
                                // Erreur lors de l'insertion
                                echo "Erreur lors de l'inscription.";
                            }
                        }
                    } catch (PDOException $ex) {
                        // Gestion des erreurs SQL
                        echo "Erreur lors de la requête SQL : " . $ex->getMessage();
                    }
                } else {
                    // Message d'erreur si des champs sont vides
                    echo "Tous les champs sont obligatoires.";
                }
            }
            ?>
            
            <!-- Lien vers la page de connexion -->
            <div class="register-link">
                <a href="connection.php">Vous avez déja un compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>
