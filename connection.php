<?php
    include 'template/ini.php';
    include 'template/chekEtat.php';
    session_start();
    include "component/componentDocType.php";
?>
<body>
    <div class="connecInscrip">
        <div class="connection-container">
            <h2>Connexion</h2>
            <form action="connection.php" method="POST">
                <div class="form-group">
                    <label for="nom">Nom d'utilisateur</label>
                    <input type="text" id="nom" name="username" required>
                </div>
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp" name="password" required>
                </div>
                <div class="form-group">
                    <input type="button" value="Annuler" onclick="window.location.href='index.php'">
                    <input type="submit" value="Connexion" name="connexion">
                </div>
            </form>
            
            <?php
                if (isset($_POST['connexion'])) {
                    $username = isset($_POST['username']) ? $_POST['username'] : '';
                    $password = isset($_POST['password']) ? $_POST['password'] : '';
                    
                    if (!empty($username) && !empty($password)) {
                        try {
                            $dbh = db_connect();
                            $sql = "SELECT idUtilisateur, loginUtil, mdpUtil FROM utilisateur WHERE loginUtil = :username";
                            
                            $sth = $dbh->prepare($sql);
                            $sth->bindParam(':username', $username);
                            $sth->execute();
                            
                            $user = $sth->fetch(PDO::FETCH_ASSOC);
                            
                            if ($user && password_verify($password, $user['mdpUtil'])) {
                                // Connexion réussie - création de la session
                                $_SESSION['user_id'] = $user['idUtilisateur'];
                                $_SESSION['username'] = $user['loginUtil'];
                                $_SESSION['logged_in'] = true;
                                
                                // Vérifier l'état des commandes de l'utilisateur
                                // Si aucune commande n'existe, en créer une automatiquement
                                if (verifierEtatCommande($user['idUtilisateur'])) {
                                    // Redirection vers la page d'accueil connecté
                                    header('Location: accueilConnecte.php');
                                    exit();
                                } else {
                                    echo "<p style='color: orange;'>Connexion réussie, mais erreur lors de la vérification des commandes.</p>";
                                    header('Location: accueilConnecte.php');
                                    exit();
                                }
                            } else {
                                echo "<p style='color: red;'>Nom d'utilisateur ou mot de passe incorrect.</p>";
                            }
                        } catch (PDOException $ex) {
                            echo "<p style='color: red;'>Erreur lors de la connexion : " . $ex->getMessage() . "</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>Tous les champs sont obligatoires.</p>";
                    }
                }
            ?>
            
            <div class="register-link">
                <a href="inscription.php">Vous n'avez pas de compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>