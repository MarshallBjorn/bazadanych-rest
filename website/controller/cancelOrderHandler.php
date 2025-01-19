<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Brak ID zamówienia.']);
        exit;
    }

    $order_id = intval($data['order_id']);

    include '../database/config.php';

    $query = "CALL tools.cancel_order($1)";
    $result = pg_query_params($db, $query, [$order_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Zamówienie zostało anulowane.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Błąd: ' . pg_last_error($db)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda żądania.']);
}
?>