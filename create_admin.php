<?php
include 'db.php';

// dane admina
$username = 'admin';
$password_plain = 'admin123';
$hashed = password_hash($password_plain, PASSWORD_DEFAULT);
$role = 'admin';

// sprawdzamy, czy już istnieje
$result = $conn->query("SELECT * FROM users WHERE username='$username'");
if ($result->num_rows > 0) {
    echo "✅ Konto admina już istnieje.";
    exit;
}

// tworzymy nowe konto
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed, $role);
$stmt->execute();
$stmt->close();

echo "✅ Konto administratora utworzone!<br>";
echo "➡️ Login: admin<br>";
echo "➡️ Hasło: admin123<br>";
?>
