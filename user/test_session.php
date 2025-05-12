<?php
// test_session.php - à placer dans le même dossier que indexrecom.php
session_start();
echo "Contenu de la session:<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Non défini') . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Tous les éléments de session:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>