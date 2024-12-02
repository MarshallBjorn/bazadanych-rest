<?php
    include 'database.php';

    $db_obj = new Database();
    $db_obj -> connection();
    $db = $db_obj -> getDB();
?>