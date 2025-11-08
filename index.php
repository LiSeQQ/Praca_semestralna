<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: auth.php");
        exit;
    } else {
        $error = "❌ Nieprawidłowy login lub hasło.";
    }
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Logowanie</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .login-box {
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      width: 350px;
      text-align: center;
    }
    h2 {
      margin-bottom: 20px;
      color: #1e3c72;
    }
    input {
      width: 90%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      background: #1e3c72;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
      width: 100%;
    }
    button:hover {
      background: #2a5298;
    }
    .register-link {
      margin-top: 15px;
      display: block;
      color: #1e3c72;
      text-decoration: none;
    }
    .register-link:hover {
      text-decoration: underline;
    }
    .error {
      color: #d32f2f;
      font-size: 14px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔐 Logowanie</h2>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="text" name="username" placeholder="Login" required><br>
      <input type="password" name="password" placeholder="Hasło" required><br>
      <button type="submit">Zaloguj</button>
    </form>

    <a href="register.php" class="register-link">Nie masz konta? Zarejestruj się →</a>
  </div>
</body>
</html>
