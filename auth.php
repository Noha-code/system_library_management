<?php
// Inclure le fichier de gestion des sessions
require_once 'config/session.php';

initSession();

function authorize($requiredRole) {
    if (!hasRole($requiredRole)) {
        header('Location: unauthorized.php');
        exit();
    }
}

