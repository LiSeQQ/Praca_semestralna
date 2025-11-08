<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin = $_SESSION['user'];
$username = htmlspecialchars($admin['username']);

// funkcja logująca akcje
function add_log($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}

// wylogowanie
if (isset($_POST['logout'])) {
    add_log($conn, $admin['id'], "Wylogowanie administratora");
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// usuwanie użytkownika
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id !== $admin['id']) { // nie można usunąć samego siebie
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        add_log($conn, $admin['id'], "Usunięto użytkownika ID: $id");
        $stmt->close();
    }
    header("Location: admin.php");
    exit;
}

// usuwanie zlecenia
if (isset($_GET['delete_service'])) {
    $id = (int)$_GET['delete_service'];
    $stmt = $conn->prepare("DELETE FROM services WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    add_log($conn, $admin['id'], "Usunięto zlecenie ID: $id");
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// pobranie danych
$users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC");
$services = $conn->query("
    SELECT s.id, s.car_model, s.description, s.status, s.image, s.created_at, u.username 
    FROM services s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC
");
$logs = $conn->query("
    SELECT l.id, u.username, l.action, l.created_at 
    FROM logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 30
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel Administratora - Serwis Samochodowy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      margin: 0;
    }
    header {
      background: #1e293b;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      color: #93c5fd;
      margin: 0;
      font-size: 1.5rem;
    }
    button, .del {
      background: #ef4444;
      border: none;
      color: white;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      font-size: 0.9rem;
    }
    button:hover, .del:hover {
      background: #dc2626;
    }
    .container {
      max-width: 1200px;
      margin: 30px auto;
      background: #1e293b;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    h2 {
      color: #60a5fa;
      border-bottom: 2px solid #334155;
      padding-bottom: 5px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 15px 0;
      font-size: 0.95rem;
    }
    th, td {
      padding: 10px;
      border-bottom: 1px solid #334155;
      text-align: left;
    }
    th {
      background: #1e3a8a;
      color: #e0f2fe;
    }
    tr:hover {
      background: #1e40af40;
    }
    img {
      max-width: 100px;
      border-radius: 6px;
    }
    .status {
      padding: 4px 8px;
      border-radius: 6px;
      font-weight: bold;
    }
    .zrobione {
      background: #16a34a;
      color: white;
    }
    .do_zrobienia {
      background: #dc2626;
      color: white;
    }
    .actions {
      display: flex;
      gap: 6px;
    }
  </style>
</head>
<body>
  <header>
    <h1>🛠️ Panel Administratora — <?= $username ?></h1>
    <form method="post">
      <button name="logout">Wyloguj</button>
    </form>
  </header>

  <div class="container">
    <h2>👥 Użytkownicy systemu</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Login</th>
        <th>Rola</th>
        <th>Data rejestracji</th>
        <th>Akcje</th>
      </tr>
      <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= $u['created_at'] ?></td>
          <td>
            <?php if ($u['id'] !== $admin['id']): ?>
              <a class="del" href="?delete_user=<?= $u['id'] ?>" onclick="return confirm('Na pewno usunąć użytkownika?')">Usuń</a>
            <?php else: ?>
              <i>(Ty)</i>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>

    <h2>🚗 Zlecenia serwisowe</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Samochód</th>
        <th>Opis</th>
        <th>Status</th>
        <th>Zdjęcie</th>
        <th>Pracownik</th>
        <th>Data</th>
        <th>Akcje</th>
      </tr>
      <?php while ($s = $services->fetch_assoc()): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['car_model']) ?></td>
          <td><?= htmlspecialchars($s['description']) ?></td>
          <td>
            <span class="status <?= $s['status'] ?>">
              <?= $s['status'] === 'zrobione' ? '✅ Zrobione' : '⏳ Do zrobienia' ?>
            </span>
          </td>
          <td>
            <?php if ($s['image']): ?>
              <img src="uploads/<?= htmlspecialchars($s['image']) ?>" alt="auto">
            <?php else: ?>
              brak
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($s['username']) ?></td>
          <td><?= $s['created_at'] ?></td>
          <td class="actions">
  <a class="del" href="edit_service.php?id=<?= $s['id'] ?>">Edytuj</a>
  <a class="del" href="?delete_service=<?= $s['id'] ?>" onclick="return confirm('Na pewno usunąć zlecenie?')">Usuń</a>
</td>
        </tr>
      <?php endwhile; ?>
    </table>

    <h2>📜 Logi aktywności</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Użytkownik</th>
        <th>Akcja</th>
        <th>Data</th>
      </tr>
      <?php while ($l = $logs->fetch_assoc()): ?>
        <tr>
          <td><?= $l['id'] ?></td>
          <td><?= htmlspecialchars($l['username'] ?? 'Nieznany') ?></td>
          <td><?= htmlspecialchars($l['action']) ?></td>
          <td><?= $l['created_at'] ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
