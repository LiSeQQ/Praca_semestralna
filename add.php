<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$username = htmlspecialchars($_SESSION['user']['username']);
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');  // <--- poprawione
    $user_id = $_SESSION['user']['id'];

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO items (name, description, owner_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $desc, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php?success=1");
        exit;
    } else {
        $message = "⚠️ Nazwa pozycji nie może być pusta.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Dodaj pozycję</title>
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
    header a {
      color: white;
      text-decoration: none;
      background: #1d4ed8;
      padding: 8px 14px;
      border-radius: 8px;
      transition: 0.2s;
    }
    header a:hover {
      background: #1e40af;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #2563eb;
    }
    input, textarea, button {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      box-sizing: border-box;
    }
    textarea {
      resize: vertical;
      min-height: 80px;
    }
    button {
      background: #2563eb;
      color: white;
      border: none;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
    }
    button:hover {
      background: #1d4ed8;
    }
    .message {
      text-align: center;
      margin-bottom: 10px;
      color: #dc2626;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <header>
    <h1>Dodaj pozycję — <?= $username ?></h1>
    <a href="dashboard.php">← Powrót</a>
  </header>

  <div class="container">
    <h2>📝 Nowa pozycja</h2>

    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <form method="post">
      <label for="name">Nazwa:</label>
      <input type="text" id="name" name="name" placeholder="Wpisz nazwę" required>

      <label for="desc">Opis (opcjonalnie):</label>
      <textarea id="desc" name="desc" placeholder="Dodaj krótki opis..."></textarea>

      <button type="submit">Dodaj pozycję</button>
    </form>
  </div>
</body>
</html>
