<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


$stmt = $conn->prepare("SELECT * FROM services WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();

if (!$service) {
    echo "Nie znaleziono zlecenia.";
    exit;
}

// sprawdź, czy użytkownik ma prawo do edycji (admin lub właściciel)
if ($user['role'] !== 'admin' && $user['id'] !== $service['user_id']) {
    echo "Brak uprawnień do edycji tego zlecenia.";
    exit;
}


function add_log($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
}

$message = "";

// zapisz zmiany
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $car_model = trim($_POST['car_model']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $image = $service['image']; 

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $image);
    }

    $stmt = $conn->prepare("UPDATE services SET car_model=?, description=?, status=?, image=? WHERE id=?");
    $stmt->bind_param("ssssi", $car_model, $description, $status, $image, $id);
    $stmt->execute();
    $stmt->close();

    add_log($conn, $user['id'], "Edytowano zlecenie ID: $id");

    $message = "✅ Zmiany zostały zapisane!";
    $service['car_model'] = $car_model;
    $service['description'] = $description;
    $service['status'] = $status;
    $service['image'] = $image;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Edycja zlecenia</title>
  <style>
    body {
      background: #0f172a;
      color: #e2e8f0;
      font-family: 'Segoe UI', sans-serif;
      padding: 20px;
    }
    form {
      background: #1e293b;
      padding: 20px;
      border-radius: 10px;
      max-width: 600px;
      margin: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    h2 {
      color: #60a5fa;
      text-align: center;
    }
    input, textarea, select {
      width: 100%;
      padding: 8px;
      margin: 10px 0;
      border-radius: 8px;
      border: none;
      background: #334155;
      color: white;
    }
    button {
      background: #3b82f6;
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      font-size: 1rem;
    }
    button:hover {
      background: #2563eb;
    }
    .msg {
      text-align: center;
      margin-bottom: 10px;
      color: #22c55e;
    }
    img {
      display: block;
      max-width: 250px;
      margin: 10px auto;
      border-radius: 8px;
    }
    a {
      color: #93c5fd;
      text-decoration: none;
      display: inline-block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <h2>✏️ Edycja zlecenia nr <?= $service['id'] ?></h2>
  <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <label>Samochód:</label>
    <input type="text" name="car_model" value="<?= htmlspecialchars($service['car_model']) ?>" required>

    <label>Opis:</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($service['description']) ?></textarea>

    <label>Status:</label>
    <select name="status">
      <option value="do_zrobienia" <?= $service['status'] === 'do_zrobienia' ? 'selected' : '' ?>>⏳ Do zrobienia</option>
      <option value="zrobione" <?= $service['status'] === 'zrobione' ? 'selected' : '' ?>>✅ Zrobione</option>
    </select>

    <label>Zdjęcie:</label>
    <?php if ($service['image']): ?>
      <img src="uploads/<?= htmlspecialchars($service['image']) ?>" alt="zdjęcie auta">
    <?php endif; ?>
    <input type="file" name="image">

    <button type="submit">💾 Zapisz zmiany</button>
  </form>

  <div style="text-align:center;">
    <a href="<?= $user['role'] === 'admin' ? 'admin.php' : 'dashboard.php' ?>">⬅️ Wróć</a>
  </div>
</body>
</html>
