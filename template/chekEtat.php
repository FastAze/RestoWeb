<?php
/**
 * Fichier de vérification et gestion de l'état des commandes utilisateur
 * Vérifie si l'utilisateur a une commande active, sinon en crée une nouvelle
 */

if (!function_exists('verifierEtatCommande')) {
    /**
     * Vérifie si l'utilisateur a une commande active
     * Si aucune commande n'existe, en crée une nouvelle avec l'état "initialisée"
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return bool True si une commande existe ou a été créée avec succès
     */
    function verifierEtatCommande($idUtilisateur) {
        try {
            $dbh = db_connect();
            
            // Vérifier si l'utilisateur a déjà une commande
            $sql = "SELECT COUNT(*) as nb_commandes FROM commande WHERE idUtilisateur = :idUtilisateur";
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
            $sth->execute();
            
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            
            // Si l'utilisateur n'a aucune commande, en créer une nouvelle
            if ($result['nb_commandes'] == 0) {
                $sqlInsert = "INSERT INTO commande (dateHeureCom, totalTTC, typeCom, idEtat, idUtilisateur) 
                            VALUES (NOW(), 0.00, 0, 1, :idUtilisateur)";
                $sthInsert = $dbh->prepare($sqlInsert);
                $sthInsert->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
                
                if ($sthInsert->execute()) {
                    return true; // Commande créée avec succès
                } else {
                    return false; // Erreur lors de la création
                }
            }
            
            // L'utilisateur a déjà au moins une commande, rien à faire
            return true;
            
        } catch (PDOException $ex) {
            // En cas d'erreur, log l'erreur et retourner false
            error_log("Erreur lors de la vérification de l'état des commandes : " . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('obtenirCommandeActive')) {
    /**
     * Récupère la commande active (état "initialisée") de l'utilisateur
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return array|false Informations de la commande active ou false si aucune
     */
    function obtenirCommandeActive($idUtilisateur) {
        try {
            $dbh = db_connect();
            
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
            
            return $sth->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $ex) {
            error_log("Erreur lors de la récupération de la commande active : " . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('obtenirToutesCommandesUtilisateur')) {
    /**
     * Récupère toutes les commandes d'un utilisateur avec leur état
     * 
     * @param int $idUtilisateur ID de l'utilisateur connecté
     * @return array|false Tableau des commandes ou false en cas d'erreur
     */
    function obtenirToutesCommandesUtilisateur($idUtilisateur) {
        try {
            $dbh = db_connect();
            
            $sql = "SELECT c.*, e.libEtat 
                    FROM commande c 
                    JOIN etat e ON c.idEtat = e.idEtat 
                    WHERE c.idUtilisateur = :idUtilisateur 
                    ORDER BY c.dateHeureCom DESC";
            
            $sth = $dbh->prepare($sql);
            $sth->bindParam(':idUtilisateur', $idUtilisateur, PDO::PARAM_INT);
            $sth->execute();
            
            return $sth->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $ex) {
            error_log("Erreur lors de la récupération des commandes : " . $ex->getMessage());
            return false;
        }
    }
}
?>
