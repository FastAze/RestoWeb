<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="Déconnexion">
        <div class="connection-container">
            <h2>Déconnexion</h2>
            <form action="index.php">
                <div class="form-group">
                    <input type="text" id="nom">
                </div>
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" id="mdp">
                    
                </div>
                <div class="form-group">
                    <input type="button" value="Annuler" onclick="window.location.href='index.html'">
                    <input type="submit" value="Connection">
                </div>
            </form>
            <div class="register-link">
                <a href="inscription.html">Vous n'avez pas de compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>