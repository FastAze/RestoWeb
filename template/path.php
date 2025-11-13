<?php
    /**
     * Définition de la constante 'path' pour gérer les chemins relatifs
     * Cette constante contient le chemin du répertoire du script en cours d'exécution
     * Utile pour la gestion des chemins d'inclusion et des liens
     */
    define('path', dirname($_SERVER['SCRIPT_NAME']));
?>