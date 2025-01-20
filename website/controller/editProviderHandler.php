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
    $adres = $_POST['addr'];
    $partner = $_POST['partner'];

    $query = "CALL tools.update_provider($1, $2, $3, $4, $5)";
    $params = [$prod_id, $prod_name, $contact, $adres, $partner];

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