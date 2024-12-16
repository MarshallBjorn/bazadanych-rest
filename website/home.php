<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="style.css">
        <script type="text/javascript" src="scripts/getDate.js"></script>
    </head>
    <body>
        <div id="app">
            <div id="side-panel">
                <div id="account">
                    <img src="image/avatar.png" alt="avatar">
                    <p>Zalogowany <? ?></p>
                </div>
                <button type="button" onclick="changeView()">Nowe zamówienie</button>
                <button type="button">Aktualne zamówienia</button>
                <button type="button" onclick="changeView()">Nowe danie</button>
                <button type="button">Lista dań</button>
                <button type="button">Pracownicy</button>
                <button type="button">Wyloguj</button>
                <div id="date-div">
                    <p id="date"></p>
                    <p id="time"></p>
                </div>
            </div>
            <div id="content">
                <div id="item-list">
                    <h2>Wszystkie dania</h2>
                    <?php 
                    include './database/config.php';

                    $query = "SELECT * FROM list_served_dishes() AS t(dish_id INT, dish_name VARCHAR, dish_type VARCHAR, price NUMERIC, description TEXT)";
                    $result = pg_query($db, $query);

                    if(!$result) {
                        echo "Wystąpił błąd podczas przetwarzania żądania" . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<div class='item'>";
                        echo "<p class=item-element> $row[dish_name]</p>". 
                            "<p class=item-element> $row[dish_type]</p>". 
                            "<p class=item-element> $row[price]</p>".
                            "<p class=item-element> $row[description]</p>";
                        echo "<button type=button> Edytuj </button>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <div id="dish-add">
                    <h2>Nowe danie</h2>
                    <form action="" method="">
                        <label>Nazwa dania*</label>
                        <input/>
                        <label>Typ dania*</label>
                        <input/>
                        <label>Cena*</label>
                        <input/>
                        <label>Opis dania*</label>
                        <textarea></textarea>
                        <label>Składniki</label>
                        <input/>
                        <button type="button">Dodaj danie</button>
                    </form>

                </div>
            </div>
        </div>
    </body>
</html>