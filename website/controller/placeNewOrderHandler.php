<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location: ../index.html");
    exit;
}

$_SESSION['current_view'] = 'new-order';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method_name = $_POST['payment_method_name'];
    $client_contact = $_POST['client_contact'];
    $note = $_POST['note'];
    $street = $_POST['street'];
    $locality = $_POST['locality'];
    $post_code = $_POST['post_code'];
    $building_num = $_POST['building_num'];
    
    $address = json_encode([
        'street' => $street,
        'locality' => $locality,
        'post_code' => $post_code,
        'building_num' => $building_num,
    ]);
    
    $dishes_text = $_POST['dishes'] ?? '[]';
    $additions_text = $_POST['additions'] ?? '[]';

    $query = "CALL tools.create_new_order($1, $2, $3, $4, $5, $6)";
    $result = pg_query_params($db, $query, [
        $payment_method_name,
        $client_contact,
        $note,
        $address,
        $dishes_text,
        $additions_text
    ]);

    if ($result) {
        header("Location: ../home.php");
    } else {
        echo "Wystąpił błąd podczas tworzenia zamówienia: " . pg_last_error($db);
    }
}
?>