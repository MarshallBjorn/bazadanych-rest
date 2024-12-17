<?php 
    include '../database/config.php';

    $loginID = $_POST['login'];
    $pass = $_POST['password'];
    

    $query = "SELECT auth.login($1, $2) AS login_result";
    $result = pg_query_params($db, $query, array($loginID, $pass));

    if(!$result) {
        echo "Wystąpił błąd podczas przetwarzania żądania". pg_last_error($db);
        exit();
    }

    while($row = pg_fetch_assoc($result)) {
        if($row['login_result'] == "t") {
            session_start();
            $_SESSION["logged"] = $loginID;
            header("location:../home.php");
           
        }
        else {
            header("location:../index.html");
        }
    }
?>