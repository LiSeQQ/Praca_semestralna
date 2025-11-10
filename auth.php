<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$username = $user['username'];


$stmt = $conn->prepare("SELECT auth_code, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

if (!$userData) {
    die("Błąd: użytkownik nie istnieje w bazie danych.");
}

$auth_code_db = $userData['auth_code'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = trim($_POST['code']);

    if ($input_code === $auth_code_db) {
        $_SESSION['authenticated'] = true;

        if ($userData['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "❌ Niepoprawny kod uwierzytelniający.";
    }
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Uwierzytelnienie</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg, #1e88e5, #64b5f6);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
    }
    .auth-box {
      background: #fff;
      padding: 40px 45px;
      border-radius: 16px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
      text-align: center;
      width: 360px;
      transition: all 0.3s ease;
    }
    .auth-box:hover {
      transform: translateY(-4px);
    }
    h1 {
      color: #1976d2;
      margin-bottom: 10px;
      font-size: 1.8em;
    }
    p {
      color: #555;
      margin-bottom: 25px;
    }
    input[type="text"] {
      width: 100%;
      padding: 12px;
      font-size: 1rem;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
      text-align: center;
      transition: 0.3s;
    }
    input[type="text"]:focus {
      outline: none;
      border-color: #1976d2;
      box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
    }
    button {
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 12px 25px;
      font-size: 1rem;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s;
    }
    button:hover {
      background: #0d47a1;
    }
    .alert {
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .alert-error {
      background: #ffcdd2;
      color: #b71c1c;
      border: 1px solid #f44336;
    }
  </style>
</head>

<body>
  <div class="auth-box">
    <h1>🔒 Uwierzytelnienie</h1>
    <p>Podaj 6-cyfrowy kod, który otrzymałeś przy rejestracji.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="code" maxlength="6" placeholder="Wpisz kod..." required>
      <button type="submit">✅ Potwierdź</button>
    </form>
  </div>
</body>
</html>
