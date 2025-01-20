<?php
session_start();

if (!isset($_SESSION['logged'])) {
    header("Location:index.html");
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
    $address = json_encode([
        $_POST['street'],
        $_POST['locality'],
        $_POST['post_code'],
        $_POST['building_num']
    ]);

    $query = "CALL tools.update_staff($1::varchar, $2::varchar, $3::varchar, $4::varchar, $5::varchar, $6::boolean, $7::date, $8::text, $9::jsonb)";
    $result = pg_query_params($db, $query, [
        $pesel, $firstname, $lastname, $position, $contact, $gender, $birthday, $status, $address
    ]);

    if ($result) {
        $_SESSION['message'] = "Pracownik został pomyślnie zaktualizowany.";
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji: " . pg_last_error($db);
    }
    header("Location: ../home.php");
}
?>