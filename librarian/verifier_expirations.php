<?php
include 'connexion.php';

$today = date('Y-m-d');
$sql = "SELECT id, date_reservation FROM reservations WHERE statut = 'active'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($res = $result->fetch_assoc()) {
        $date_reservation = $res['date_reservation'];
        $expiration_date = date('Y-m-d', strtotime($date_reservation . ' +2 days'));

        if ($today > $expiration_date) {
            $id = $res['id'];
            $conn->query("UPDATE reservations SET statut = 'expirée' WHERE id = $id");
        }
    }
}
?>
