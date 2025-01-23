<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

include '../database/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nie podano ID zamówienia.']);
    exit;
}

$order_id = intval($data['order_id']);

$query = "SELECT tools.update_order_status($1)";
$result = @pg_query_params($db, $query, [$order_id]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Status zamówienia został zaktualizowany.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Wystąpił błąd podczas aktualizacji: ' . pg_last_error($db)]);
}
?>