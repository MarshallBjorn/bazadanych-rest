<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_name = $_POST['dish_name'];
    $dish_type = $_POST['dish_type'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $components_text = isset($_POST['components']) ? $_POST['components'] : '';
    $additions_text = isset($_POST['additions']) ? $_POST['additions'] : '';
    
    $components = $components_text ? array_map('trim', explode(',', $components_text)) : [];
    $additions = $additions_text ? array_map('trim', explode(',', $additions_text)) : [];

    $query = "CALL tools.add_new_dish($1, $2, $3, $4, $5, $6)";
    
    $result = pg_query_params($db, $query, [
        $dish_name, $dish_type, $price, $description, json_encode($components), json_encode($additions)
    ]);
    
    if ($result) {
        header("Location: home.php?alert=success");
    } else {
        echo "Wystąpił błąd podczas dodawania dania: " . pg_last_error($db);
    }
}
?>