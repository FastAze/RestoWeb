<?php
    /**
     * Vérifie si la fonction db_connect n'existe pas déjà
     * Évite les redéfinitions de fonction
     */
    if (!function_exists('db_connect')) {
        /**
         * Fonction de connexion à la base de données MySQL via PDO
         * 
         * Cette fonction crée et retourne une connexion PDO à la base de données
         * avec gestion d'erreurs et configuration UTF-8
         * 
         * @return PDO Instance de connexion PDO configurée
         * @throws PDOException En cas d'échec de connexion
         */
        function db_connect()
        {
            // Configuration de la connexion à la base de données
            $dsn = 'mysql:host=localhost;dbname=restoweb'; // DSN (Data Source Name) avec hôte et nom de la base
            $user = 'root';                                 // Nom d'utilisateur MySQL
            $password = '';                                 // Mot de passe MySQL (vide par défaut en local)
            
            try{
                // Création de la connexion PDO avec encodage UTF-8
                $dbh = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                
                // Configuration du mode d'erreur PDO pour lever des exceptions
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $ex){
                // Arrêt du script en cas d'erreur de connexion avec affichage du message
                die("Erreur lors de la connexion SQL : " . $ex->getMessage());
            }
            
            // Retour de l'instance PDO configurée
            return $dbh;
        }
    }
?>