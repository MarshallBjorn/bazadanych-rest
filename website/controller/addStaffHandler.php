<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'employee-list';

include '../database/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pesel = $_POST['pesel'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position = $_POST['position'];
    $address = json_encode([
        $_POST['street'],
        $_POST['locality'],
        $_POST['post_code'],
        $_POST['building_num']
    ]);
    $contact = $_POST['contact'];
    $gender = $_POST['gender'] === 'true';
    $birthday = $_POST['birthday'];

    $query = "CALL tools.add_staff($1, $2, $3, $4, $5::jsonb, $6, $7, $8)";
    $result = pg_query_params($db, $query, [
        $pesel, $firstname, $lastname, $position, $address, $contact, $gender, $birthday
    ]);

    if ($result) {
        header("Location: ../home.php");
    } else {
        echo "Błąd: " . pg_last_error($db);
    }
}
?>