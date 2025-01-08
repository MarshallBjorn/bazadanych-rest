<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
    exit;
}

include '../database/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pesel = $_POST['pesel'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position = $_POST['position'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $gender = $_POST['gender'] === 'true';
    $birthday = $_POST['birthday'];

    $query = "CALL tools.add_staff($1, $2, $3, $4, $5::jsonb, $6, $7, $8)";
    $result = pg_query_params($db, $query, [
        $pesel, $firstname, $lastname, $position, $address, $contact, $gender, $birthday
    ]);

    if ($result) {
        header("Location: home.php");
    } else {
        echo "Błąd: " . pg_last_error($db);
    }
}
?>