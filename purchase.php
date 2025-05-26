<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$bookId = $data['bookId'] ?? 0;
$userId = $data['userId'] ?? 0; // In real app, get from session
$donation = $data['donation'] ?? 0;

// Get book price
$stmt = $pdo->prepare("SELECT price FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
$amount = $book['price'] + $donation;

// Initialize Paystack payment
$secretKey = 'sk_test_...'; // Your Paystack test secret key
$ch = curl_init('https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Bearer ' . $secretKey,
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'email' => $data['email'],
  'amount' => $amount,
  'metadata' => ['bookId' => $bookId, 'userId' => $userId, 'donation' => $donation]
]));
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);

if ($result['status']) {
  // Save payment reference to DB (for demo, just echo)
  echo json_encode([
    'paymentUrl' => $result['data']['authorization_url'],
    'reference' => $result['data']['reference']
  ]);
} else {
  echo json_encode(['error' => $result['message']]);
}
?>
