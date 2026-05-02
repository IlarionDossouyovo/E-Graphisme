<?php
/**
 * Déconnexion Admin - E-Graphisme
 */
require_once 'config.php';

// Logger la déconnexion
if (is_logged_in()) {
    admin_log('LOGOUT', 'Déconnexion réussie');
}

// Détruire la session
$_SESSION = array();
session_destroy();

// Rediriger vers la page de connexion
header('Location: login.php');
exit;
