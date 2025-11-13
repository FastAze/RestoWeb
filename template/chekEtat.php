<?php
/**
 * Fichier de vérification et gestion de l'état des commandes utilisateur
 * 
 * Ce fichier contient des fonctions utilitaires pour :
 * - Vérifier si un utilisateur a une commande active
 * - Créer automatiquement une commande si nécessaire
 * - Récupérer les informations des commandes
 * 
 * Toutes les fonctions utilisent PDO pour des requêtes sécurisées
 */

if (!function_exists('verifierEtatCommande')) {
    /**
     * Vérifie si l'utilisateur a une commande active
     * Si aucune commande n'existe, en crée une nouvelle avec l'état "initialisée"
     * 
     * Cette fonction garantit qu'un utilisateur a toujours au moins une commande
     * en état "initialisée" pour pouvoir ajouter des produits au panier
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return bool True si une commande existe ou a été créée avec succès, False en cas d'erreur
     */
    function verifierEtatCommande($idUtilisateur) {
        try {
            // Connexion à la base de données
            $dbh = db_connect();
            
            // Requête pour compter le nombre de commandes de l'utilisateur
            $sql = "SELECT COUNT(*) as nb_commandes FROM commande WHERE idUtilisateur = :idUtilisateur";
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
            $sth->execute();
            
            // Récupération du résultat
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            
            // Si l'utilisateur n'a aucune commande, en créer une nouvelle
            if ($result['nb_commandes'] == 0) {
                // Requête d'insertion d'une nouvelle commande
                // dateHeureCom : date/heure actuelle
                // totalTTC : 0.00 (sera calculé automatiquement)
                // typeCom : 0 (sur place par défaut)
                // idEtat : 1 (état "initialisée")
                $sqlInsert = "INSERT INTO commande (dateHeureCom, totalTTC, typeCom, idEtat, idUtilisateur) 
                            VALUES (NOW(), 0.00, 0, 1, :idUtilisateur)";
                $sthInsert = $dbh->prepare($sqlInsert);
                $sthInsert->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
                
                // Exécution de l'insertion et retour du résultat
                if ($sthInsert->execute()) {
                    return true; // Commande créée avec succès
                } else {
                    return false; // Erreur lors de la création
                }
            }
            
            // L'utilisateur a déjà au moins une commande, rien à faire
            return true;
            
        } catch (PDOException $ex) {
            // En cas d'erreur, log l'erreur dans les logs PHP et retourner false
            error_log("Erreur lors de la vérification de l'état des commandes : " . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('obtenirCommandeActive')) {
    /**
     * Récupère la commande active (état "initialisée") de l'utilisateur
     * 
     * Retourne les informations de la dernière commande en état "initialisée"
     * C'est la commande dans laquelle l'utilisateur peut ajouter des produits
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return array|false Tableau associatif avec les informations de la commande, ou false si aucune commande active
     */
    function obtenirCommandeActive($idUtilisateur) {
        try {
            // Connexion à la base de données
            $dbh = db_connect();
            
            // Requête pour récupérer la commande active avec son état
            // JOIN avec la table etat pour récupérer le libellé de l'état
            // Filtre sur idEtat = 1 (initialisée)
            // Tri par date décroissante et limite à 1 résultat
            $sql = "SELECT c.*, e.libEtat 
                    FROM commande c 
                    JOIN etat e ON c.idEtat = e.idEtat 
                    WHERE c.idUtilisateur = :idUtilisateur 
                    AND c.idEtat = 1 
                    ORDER BY c.dateHeureCom DESC 
                    LIMIT 1";
            
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
            $sth->execute();
            
            // Retourne le tableau associatif de la commande ou false
            return $sth->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $ex) {
            // Log de l'erreur et retour false
            error_log("Erreur lors de la récupération de la commande active : " . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('obtenirToutesCommandesUtilisateur')) {
    /**
     * Récupère toutes les commandes d'un utilisateur avec leur état
     * 
     * Retourne un tableau de toutes les commandes de l'utilisateur,
     * triées de la plus récente à la plus ancienne
     * Utile pour l'historique des commandes dans le profil
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return array|false Tableau de tableaux associatifs (une entrée par commande), ou false en cas d'erreur
     */
    function obtenirToutesCommandesUtilisateur($idUtilisateur) {
        try {
            // Connexion à la base de données
            $dbh = db_connect();
            
            // Requête pour récupérer toutes les commandes avec leur état
            // JOIN avec la table etat pour avoir le libellé lisible
            // Tri par date décroissante (plus récente en premier)
            $sql = "SELECT c.*, e.libEtat 
                    FROM commande c 
                    JOIN etat e ON c.idEtat = e.idEtat 
                    WHERE c.idUtilisateur = :idUtilisateur 
                    ORDER BY c.dateHeureCom DESC";
            
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
            $sth->execute();
            
            // Retourne un tableau de tous les résultats
            return $sth->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $ex) {
            // Log de l'erreur et retour false
            error_log("Erreur lors de la récupération des commandes : " . $ex->getMessage());
            return false;
        }
    }
}
?>
