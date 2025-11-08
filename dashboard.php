<?php
session_start();
include 'db.php';

// sprawdzenie czy użytkownik jest zalogowany i uwierzytelniony
if (!isset($_SESSION['user']) || !isset($_SESSION['authenticated'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$username = htmlspecialchars($user['username']);
$role = $user['role'];

// wylogowanie
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Panel użytkownika</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h1>👋 Witaj, <?= $username ?></h1>
      <form method="post" style="margin:0;">
        <button type="submit" name="logout" class="btn" style="background:#e53e3e;">Wyloguj</button>
      </form>
    </div>

    <p>Twoja rola: <strong><?= htmlspecialchars($role) ?></strong></p>

    <hr style="margin:20px 0;">

    <?php if ($role == 'admin'): ?>
      <h2>Panel administratora</h2>
      <p>Możesz przeglądać wszystkich użytkowników i ich pozycje.</p>

      <?php
      // admin widzi wszystkich użytkowników
      $users = $conn->query("SELECT id, username, role, auth_code FROM users ORDER BY id ASC");
      echo "<table border='1' cellpadding='6' style='border-collapse:collapse;width:100%;background:#fff'>";
      echo "<tr><th>ID</th><th>Login</th><th>Rola</th><th>Kod</th></tr>";
      while ($u = $users->fetch_assoc()) {
          echo "<tr><td>{$u['id']}</td><td>{$u['username']}</td><td>{$u['role']}</td><td>{$u['auth_code']}</td></tr>";
      }
      echo "</table>";
      ?>

    <?php else: ?>
      <h2>Panel użytkownika</h2>
      <p>Możesz tu dodawać swoje pozycje lub przeglądać własne dane.</p>

      <?php
      // pobranie pozycji tylko tego użytkownika
      $stmt = $conn->prepare("SELECT id, name, description, created_at FROM items WHERE owner_id = ? ORDER BY created_at DESC");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $items = $result->fetch_all(MYSQLI_ASSOC);
      $stmt->close();
      ?>

      <form method="post" action="add.php" style="margin-top:20px;">
        <h3>Dodaj nową pozycję</h3>
        <label>
          Nazwa:
          <input type="text" name="name" required>
        </label>
        <label>
          Opis:
          <textarea name="description" rows="3"></textarea>
        </label>
        <button type="submit" class="btn">Dodaj</button>
      </form>

      <h3 style="margin-top:24px;">Twoje pozycje</h3>
      <?php if (empty($items)): ?>
        <p>Brak pozycji. Dodaj coś!</p>
      <?php else: ?>
        <ul style="list-style:none;padding:0;">
          <?php foreach ($items as $item): ?>
            <li style="background:#fff;padding:10px;margin:8px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.05)">
              <strong><?= htmlspecialchars($item['name']) ?></strong><br>
              <small><?= htmlspecialchars($item['description']) ?></small><br>
              <small><i><?= $item['created_at'] ?></i></small><br>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</body>
</html>
