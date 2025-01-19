<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'item-list';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addition_id = $_POST['addition_id'];
    $addition_name = $_POST['addition_name'];
    $prod_name = $_POST['add_provider'];
    $price = $_POST['add_price'];
    $is_served = $_POST['is_served'];

    $query = "CALL tools.update_addition($1, $2, $3, $4, $5)";
    $params = [$addition_id, $addition_name, $price, $prod_name, $is_served];

    $result = @pg_query_params($db, $query, $params);
    
    if ($result) {
        $_SESSION['message'] = "Danie zostało pomyślnie zaktualizowane.";
        header("Location: ../home.php");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header("Location: ../home.php");
    }
}
?>