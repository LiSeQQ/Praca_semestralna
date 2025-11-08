<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$username = $user['username'];

// Pobierz auth_code z bazy danych
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
        // sukces – logowanie zakończone
        $_SESSION['authenticated'] = true;

        if ($userData['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Niepoprawny kod uwierzytelniający.";
    }
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Uwierzytelnienie</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="wrap">
    <h1>Uwierzytelnienie</h1>
    <p>Podaj kod, który otrzymałeś przy rejestracji.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>
        Kod uwierzytelniający
        <input type="text" name="code" maxlength="6" required>
      </label>
      <button type="submit" class="btn">Potwierdź</button>
    </form>
  </div>
</body>
</html>
