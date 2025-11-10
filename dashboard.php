<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['authenticated'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$username = htmlspecialchars($user['username']);
$role = $user['role'];


if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}


$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $car_model = trim($_POST['car_model']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    if (!empty($car_model)) {
        $stmt = $conn->prepare("INSERT INTO services (user_id, car_model, description, image, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $car_model, $description, $image_name, $status);
        $stmt->execute();
        $stmt->close();

        $conn->query("INSERT INTO logs (user_id, action) VALUES ($user_id, 'Dodał nowe zlecenie serwisowe')");
        $message = "✅ Zlecenie zostało dodane!";
    } else {
        $message = "⚠️ Uzupełnij dane pojazdu.";
    }
}


if (isset($_POST['update_status'])) {
    $service_id = $_POST['service_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE services SET status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sii", $new_status, $service_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->query("INSERT INTO logs (user_id, action) VALUES ($user_id, 'Zaktualizował status zlecenia #$service_id')");
    header("Location: dashboard.php");
    exit;
}


$stmt = $conn->prepare("SELECT * FROM services WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$services = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Panel serwisanta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f1f5f9;
      color: #333;
      margin: 0;
      padding: 0;
    }
    header {
      background: #2563eb;
      color: white;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      margin: 0;
      font-size: 1.4rem;
    }
    header form {
      margin: 0;
    }
    button.logout {
      background: #ef4444;
      border: none;
      color: white;
      padding: 8px 14px;
      border-radius: 8px;
      cursor: pointer;
    }
    .container {
      max-width: 900px;
      margin: 30px auto;
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #2563eb;
    }
    input, textarea, select, button {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
    }
    button.submit {
      background: #2563eb;
      color: white;
      font-weight: bold;
      border: none;
      cursor: pointer;
    }
    button.submit:hover {
      background: #1d4ed8;
    }
    .message {
      color: green;
      font-weight: 600;
      margin-bottom: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border-bottom: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #f3f4f6;
    }
    .status {
      font-weight: bold;
      padding: 4px 8px;
      border-radius: 6px;
    }
    .zrobione {
      background: #dcfce7;
      color: #166534;
    }
    .do_zrobienia {
      background: #fee2e2;
      color: #991b1b;
    }
    img {
      max-width: 120px;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <header>
    <h1>🚗 Panel serwisanta — <?= $username ?></h1>
    <form method="post">
      <button type="submit" name="logout" class="logout">Wyloguj</button>
    </form>
  </header>

  <div class="container">
    <h2>➕ Dodaj nowe zlecenie</h2>

    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <input type="text" name="car_model" placeholder="Marka i model samochodu" required>
      <textarea name="description" placeholder="Opis problemu"></textarea>
      <label>Zdjęcie pojazdu:</label>
      <input type="file" name="image" accept="image/*">
      <label>Status:</label>
      <select name="status">
        <option value="do_zrobienia">⏳ Do zrobienia</option>
        <option value="zrobione">✅ Zrobione</option>
      </select>
      <button type="submit" name="add_service" class="submit">Dodaj zlecenie</button>
    </form>

    <h2>📋 Twoje zlecenia</h2>
    <?php if (empty($services)): ?>
      <p>Nie masz jeszcze żadnych zleceń.</p>
    <?php else: ?>
      <table>
        <tr>
          <th>ID</th>
          <th>Samochód</th>
          <th>Opis</th>
          <th>Zdjęcie</th>
          <th>Status</th>
          <th>Akcja</th>
        </tr>
        <?php foreach ($services as $s): ?>
          <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['car_model']) ?></td>
            <td><?= htmlspecialchars($s['description']) ?></td>
            <td>
              <?php if ($s['image']): ?>
                <img src="uploads/<?= htmlspecialchars($s['image']) ?>" alt="auto">
              <?php else: ?>
                brak
              <?php endif; ?>
            </td>
            <td>
              <span class="status <?= $s['status'] ?>">
                <?= $s['status'] === 'zrobione' ? '✅ Zrobione' : '⏳ Do zrobienia' ?>
              </span>
            </td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                <input type="hidden" name="new_status" value="<?= $s['status'] === 'zrobione' ? 'do_zrobienia' : 'zrobione' ?>">
                <button name="update_status" class="submit" style="background:#10b981;">
                  <?= $s['status'] === 'zrobione' ? '↩️ Cofnij' : '✅ Oznacz zrobione' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
