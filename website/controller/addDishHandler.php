<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'dish-add';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dish_name = $_POST['dish_name'];
    $dish_type = $_POST['dish_type'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $components_text = isset($_POST['components']) ? $_POST['components'] : '[]';
    $additions_text = isset($_POST['additions']) ? $_POST['additions'] : '[]';
    
    $query = "CALL tools.add_new_dish($1, $2, $3, $4, $5, $6)";
    
    $result = pg_query_params($db, $query, [
        $dish_name, $dish_type, $price, $description, $components_text, $additions_text
    ]);
    
    if ($result) {
        header("Location: ../home.php");
    } else {
        echo "Wystąpił błąd podczas dodawania dania: " . pg_last_error($db);
    }
}
?>