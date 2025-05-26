<?php
header('Content-Type: application/json');
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM books");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($books);
?>
