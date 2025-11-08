<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $desc = $_POST['desc'];
    $user_id = $_SESSION['user']['id'];

    $stmt = $conn->prepare("INSERT INTO items (name, description, owner_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $desc, $user_id);
    $stmt->execute();

    echo "Dodano pozycję!";
}
?>

<form method="post">
  <input type="text" name="name" placeholder="Nazwa" required><br>
  <textarea name="desc" placeholder="Opis"></textarea><br>
  <button type="submit">Dodaj</button>
</form>
