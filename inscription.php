<?php
    include 'template/ini.php';
    include 'template/chekEtat.php';
    session_start();
    include "component/componentDocType.php";
?>
<body>
    <div class="connecInscrip">
        <div class="connection-container">
            <h2>Inscription</h2>
            <form id="Inscription" action="inscription.php" method="POST">
                <div class="form-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <input type="text" id="nom" name="username">
                </div>
                <div class="form-group">
                    <label for="mail">E-mail</label>
                    <input type="email" id="mail" name="email">
                </div>
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp" name="password">
                </div>
                <div class="form-group">
                    <input type="button" value="Annuler" onclick="window.location.href='index.php'">
                    <input type="submit" value="S'inscrire" name="inscrire">
                </div>
            </form>
            <?php
                $MDP_H = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
                $username = isset($_POST['username'])? $_POST['username'] : '';
                $email = isset($_POST['email'])? $_POST['email'] : '';

                if (isset($_POST['inscrire'])) {
                    // Validation des données
                    if (!empty($username) && !empty($email) && !empty($_POST['password'])) {
                        $dbh = db_connect();
                        
                        // Vérification si l'utilisateur ou l'email existe déjà
                        $check_sql = "SELECT COUNT(*) FROM utilisateur WHERE loginUtil = :username OR emailUtil = :email";
                        try {
                            $check_sth = $dbh->prepare($check_sql);
                            $check_sth->bindParam(':username', $username);
                            $check_sth->bindParam(':email', $email);
                            $check_sth->execute();
                            $count = $check_sth->fetchColumn();
                            
                            if ($count > 0) {
                                echo "Ce nom d'utilisateur ou cette adresse email existe déjà.";
                            } else {
                                // Utilisation de paramètres liés pour éviter les injections SQL
                                $sql = "INSERT INTO utilisateur (idUtilisateur, loginUtil, emailUtil, mdpUtil) VALUES (NULL, :username, :email, :password)";
                                
                                $sth = $dbh->prepare($sql);
                                // Liaison des paramètres
                                $sth->bindParam(':username', $username);
                                $sth->bindParam(':email', $email);
                                $sth->bindParam(':password', $MDP_H);
                                
                                if ($sth->execute()) {
                                    // Récupérer l'ID du nouvel utilisateur en faisant une requête SELECT
                                    $get_user_sql = "SELECT idUtilisateur FROM utilisateur WHERE loginUtil = :username AND emailUtil = :email";
                                    $get_user_sth = $dbh->prepare($get_user_sql);
                                    $get_user_sth->bindParam(':username', $username);
                                    $get_user_sth->bindParam(':email', $email);
                                    $get_user_sth->execute();
                                    $user_data = $get_user_sth->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($user_data) {
                                        $user_id = $user_data['idUtilisateur'];
                                        
                                        // Créer la session
                                        $_SESSION['user_id'] = $user_id;
                                        $_SESSION['username'] = $username;
                                        $_SESSION['logged_in'] = true;
                                        
                                        // Vérifier l'état des commandes du nouvel utilisateur
                                        // Créer automatiquement une commande s'il n'en a pas
                                        if (verifierEtatCommande($user_id)) {
                                            header('Location: accueilConnecte.php');
                                            exit();
                                        } else {
                                            echo "<p style='color: orange;'>Inscription réussie, mais erreur lors de la création de la commande.</p>";
                                            header('Location: accueilConnecte.php');
                                            exit();
                                        }
                                    } else {
                                        echo "Erreur lors de la récupération de l'utilisateur.";
                                    }
                                } else {
                                    echo "Erreur lors de l'inscription.";
                                }
                            }
                        } catch (PDOException $ex) {
                            echo "Erreur lors de la requête SQL : " . $ex->getMessage();
                        }
                    } else {
                        echo "Tous les champs sont obligatoires.";
                    }
                }
            ?>
            <div class="register-link">
                <a href="connection.php">Vous avez déja un compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>