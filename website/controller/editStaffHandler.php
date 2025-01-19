<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header(header: "Location:index.html");
    exit;
}

$_SESSION['current_view'] = 'employee-list';

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesel = $_POST['staff_id'];
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $position = $_POST['fposition'];
    $contact = $_POST['fcontact'];
    $gender = $_POST['fgender'];
    $birthday = $_POST['fbirthday'];
    $status = $_POST['fstatus'];

    $query = "CALL tools.update_staff($1,$2,$3,$4,$5,$6,$7,$8)";
    $result = pg_query_params($db, $query, [
        $pesel, $firstname, $lastname, $position, $contact, $gender, $birthday, $status
    ]);

    if ($result) {
        $_SESSION['message'] = "Pracownik został pomyślnie zaktualizowany.";
        header(header: "Location: ../home.php");
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
        header(header: "Location: ../home.php");
    }
}
?>