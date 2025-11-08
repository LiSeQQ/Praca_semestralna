<?php
session_start();
if ($_SESSION['user']['role'] != 'admin') {
    die("Brak dostępu!");
}
echo "Witaj, Adminie!";
?>
