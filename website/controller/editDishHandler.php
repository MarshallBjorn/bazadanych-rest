<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_id = $_POST['dish_id'];
    $dish_name = $_POST['dish_name'];
    $dish_type = $_POST['dish_type'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $is_served = $_POST['is_served'];

    $query = "CALL tools.update_dish($1, $2, $3, $4, $5, $6)";
    $params = [$dish_id, $dish_name, $dish_type, $price, $description, $is_served];

    $result = pg_query_params($db, $query, $params);

    if ($result) {
        $_SESSION['message'] = "Danie zostało pomyślnie zaktualizowane.";
        header("Location: ../home.php?success");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header("Location: ../home.php?error");
    }
}
?>