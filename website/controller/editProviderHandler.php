<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_id = $_POST['prod_id'];
    $prod_name = $_POST['prod_name'];
    $contact = $_POST['contact'];
    $partner = $_POST['partner'];
    $address = json_encode([
        $_POST['street'],
        $_POST['locality'],
        $_POST['post_code'],
        $_POST['building_num']
    ]);

    $query = "CALL tools.update_provider($1, $2, $3, $4::jsonb, $5)";
    $params = [$prod_id, $prod_name, $contact, $address, $partner];

    $result = pg_query_params($db, $query, $params);

    if ($result) {
        $_SESSION['message'] = "Danie zostało pomyślnie zaktualizowane.";
        header("Location: ../home.php");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header("Location: ../home.php");
    }
}
?>