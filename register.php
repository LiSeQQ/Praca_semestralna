<?php
// register.php
session_start();
include 'db.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pobranie i sanitacja
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // podstawowa walidacja
    if (strlen($username) < 3) $errors[] = "Nazwa użytkownika musi mieć minimum 3 znaki.";
    if (strlen($password) < 6) $errors[] = "Hasło musi mieć minimum 6 znaków.";
    if ($password !== $password2) $errors[] = "Hasła nie są identyczne.";

    // czy username już istnieje?
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Nazwa użytkownika jest zajęta.";
        }
        $stmt->close();
    }

    // jeśli OK — wstawiamy do DB
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // generujemy 6-cyfrowy kod jako string (np. "042371")
        $auth_code = str_pad(strval(rand(0, 999999)), 6, "0", STR_PAD_LEFT);
        $role = 'user';

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, auth_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password_hash, $role, $auth_code);

        if ($stmt->execute()) {
            $success = "Rejestracja zakończona sukcesem. Możesz się teraz zalogować.";
            // dla testów developerskich możemy wyświetlić auth_code (w prawdziwym systemie wysyłamy e-mail/SMS)
            $success .= " (Dev auth code: <strong>" . htmlspecialchars($auth_code) . "</strong>)";
        } else {
            $errors[] = "Błąd zapisu do bazy: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <title>Rejestracja — Praca semestralna</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="wrap">
    <h1>Rejestracja</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?=htmlspecialchars($e)?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
      <p><a href="index.php">Przejdź do logowania</a></p>
    <?php else: ?>
      <form id="regForm" method="post" novalidate>
        <label>
          Nazwa użytkownika
          <input type="text" name="username" required minlength="3" value="<?=isset($username) ? htmlspecialchars($username) : ''?>">
        </label>

        <label>
          Hasło
          <input type="password" id="password" name="password" required minlength="6">
        </label>

        <label>
          Powtórz hasło
          <input type="password" id="password2" name="password2" required minlength="6">
        </label>

        <div class="helper">
          <input type="checkbox" id="showpass"><label for="showpass" class="inline">Pokaż hasła</label>
        </div>

        <button type="submit" class="btn">Zarejestruj</button>
      </form>

      <p class="small">Masz konto? <a href="index.php">Zaloguj się</a></p>
    <?php endif; ?>
  </div>

  <script src="js/script.js"></script>
</body>
</html>
