<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'order-list';

include '../database/config.php';

$order_id = $_POST['ord_id'];

$query = "CALL tools.cancel_order($1)";
$params = [$order_id];

$result = pg_query_params($db, $query, $params);

if ($result) {
    $_SESSION['message'] = "Zamówienie zostało anulowane";
    header("Location: ../home.php");
} else {
    $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
    header("Location: ../home.php");
}

?>