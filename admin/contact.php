<?php
require_once '../auth.php';
authorize('admin');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<?php
session_start();
include('config/database.php'); 

$host = "localhost";
$user = "root";
$password = "";
$dbname = "library"; 

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}



// Requête pour récupérer tous les messages
$query = "SELECT * FROM messages ORDER BY date_envoi DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>📬 Messages Reçus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Google Font pour un look moderne -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #4a90e2;
      --danger: #e74c3c;
      --success: #2ecc71;
      --background: #f5f7fa;
      --text: #2c3e50;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background-image: url('https://archea.fr/wp-content/uploads/2021/10/bibliotheque-encastree-salon-1600x1132.jpg');
      font-family: 'Inter', sans-serif;
      background-color: var(--background);
      padding: 40px;
      color: var(--text);
    }

    h1 {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      overflow: hidden;
    }

    th, td {
      padding: 16px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    th {
      background-color: #f0f4f8;
      font-weight: 600;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    .no-data {
      padding: 20px;
      background-color: #fff3cd;
      border: 1px solid #ffeeba;
      color: #856404;
      border-radius: 6px;
      margin-top: 20px;
    }
    .header-box {
      background-color: #fff;
      padding: 20px 30px;
      text-align: center;
      margin: 0 auto 30px;
      max-width: 600px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .header-box h1 {
      color: var(--primary);
      font-size: 2rem;
      margin: 0;
    }


    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      tr {
        margin-bottom: 15px;
      }

      td {
        padding-left: 50%;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 16px;
        font-weight: bold;
        color: #888;
      }

      th {
        display: none;
      }
    }
  </style>
</head>
<body>

  <div class="header-box">
    <h1>📬 Messages Reçus</h1>
  </div>


  <?php if ($result && $result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Nom</th>
          <th>Email</th>
          <th>Message</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td data-label="Nom"><?= htmlspecialchars($row['nom']) ?></td>
          <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
          <td data-label="Message"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
          <td data-label="Date"><?= htmlspecialchars($row['date_envoi']) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="no-data">Aucun message trouvé pour le moment.</div>
  <?php endif; ?>

</body>
</html>