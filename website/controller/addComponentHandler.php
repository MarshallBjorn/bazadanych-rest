<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'component_add';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $component_name = $_POST['comp_name'];
    $prod_name = $_POST['prod_name'];
    $price = $_POST['price'];
    $is_served = $_POST['availability'];

    $query = "CALL tools.add_component($1::varchar, $2::varchar, $3::decimal(6,2), $4::boolean)";
    $params = [$component_name, $prod_name, $price, $is_served];

    $result = pg_query_params($db, $query, $params);
    
    if ($result) {
        $_SESSION['message'] = "Składnik został pomyślnie dodany.";
        header("Location: ../home.php");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header("Location: ../home.php");
    }
}
?>