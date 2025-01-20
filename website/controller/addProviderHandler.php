<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'provider_add';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_name = $_POST['prod_name'];
    $contact = $_POST['contact'];
    $address = json_encode([
        $_POST['street'],
        $_POST['locality'],
        $_POST['post_code'],
        $_POST['building_num']
    ]);

    $query = "CALL tools.add_provider($1::varchar, $2::varchar, $3::jsonb)";
    $params = [$prod_name, $contact, $address];

    $result = @pg_query_params($db, $query, $params);
    
    if ($result) {
        $_SESSION['message'] = "Dodatek został pomyślnie dodany.";
        header("Location: ../home.php");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header("Location: ../home.php");
    }
}
?>