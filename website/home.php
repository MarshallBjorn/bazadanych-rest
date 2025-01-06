<?php session_start();
    if(!isset($_SESSION['logged'])) 
    {
        header("Location:index.html"); 
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="style.css">
        <script type="text/javascript" src="scripts/getDate.js"></script>
        <script type="text/javascript" src="scripts/changeViews.js" defer></script>
        <script type="text/javascript" src="scripts/logout.js"></script>
        <script type="text/javascript" src="scripts/dataEdit.js"></script>
    </head>
    <body>
        <div id="app">
            <div id="side-panel">
                <div id="account">
                    <img src="image/avatar.png" alt="avatar">
                    <p>Zalogowany: <?php echo $_SESSION['logged']; ?></p>
                </div>
                <button type="button">Nowe zamówienie</button>
                <button type="button" onclick="changeView('order-list')">Aktualne zamówienia</button>
                <button type="button" onclick="changeView('dish-add')">Nowe danie</button>
                <button type="button" onclick="changeView('item-list')">Lista dań</button>
                <button type="button">Pracownicy</button>
                <button type="button" onclick="logout()">Wyloguj</button>
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

                    $query = "SELECT * FROM display.list_all_dishes() AS t(dish_name VARCHAR, dish_type VARCHAR, price NUMERIC, is_served boolean, description TEXT)";
                    $query2 = "SELECT * FROM display.list_all_additions() AS t(addition_name VARCHAR, price NUMERIC, availability boolean)";
                    $result = pg_query($db, $query);
                    $result2 = pg_query($db, $query2);

                    if(!$result || !$result2) {
                        echo "Wystąpił błąd podczas przetwarzania żądania" . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<div class='item'>";
                        echo "<p class='item-element'><strong>Nazwa:</strong> {$row['dish_name']}</p>";
                        echo "<p class='item-element'><strong>Typ:</strong> {$row['dish_type']}</p>";
                        echo "<p class='item-element'><strong>Cena:</strong> {$row['price']}</p>";
                        echo "<p class='item-element'><strong>Opis:</strong> {$row['description']}</p>";
                        echo "<p class='item-element'><strong>Dostępne:</strong> " . ($row['is_served'] == 't' ? 'Tak' : 'Nie') . "</p>";
                        echo "<button type='button' onclick='toggleEditSection(this)'>Edytuj</button>";

                        echo "<div class='edit-section'>";
                        echo "<form onsubmit='event.preventDefault(); saveDish(this.querySelector(\"button[type=\\'submit\\']\"));'>";
                        echo "<label for='edit-name'>Nazwa:</label>";
                        echo "<input type='text' id='edit-name' name='dish_name' value='{$row['dish_name']}' required />";

                        echo "<label for='edit-type'>Typ:</label>";
                        echo "<input type='text' id='edit-type' name='dish_type' value='{$row['dish_type']}' required />";

                        echo "<label for='edit-price'>Cena:</label>";
                        echo "<input type='number' id='edit-price' name='price' value='{$row['price']}' step='0.01' required />";

                        echo "<label for='edit-description'>Opis:</label>";
                        echo "<textarea id='edit-description' name='description' required>{$row['description']}</textarea>";

                        echo "<label for='edit-served'>Dostępne:</label>";
                        echo "<select id='edit-served' name='is_served'>";
                        echo "<option value='t'" . ($row['is_served'] == 't' ? ' selected' : '') . ">Tak</option>";
                        echo "<option value='f'" . ($row['is_served'] == 'f' ? ' selected' : '') . ">Nie</option>";
                        echo "</select>";

                        echo "<button type='submit'>Zapisz</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                    
                    echo "<h2>Wszystkie dodatki</h2>";

                    while ($row = pg_fetch_assoc($result2)) {
                        echo "<div class='item'>";
                        echo "<p class=item-element> $row[addition_name]</p>".  
                            "<p class=item-element> $row[price]</p>".
                            "<p class=item-element> $row[availability]</p>";
                        echo "<button type=button> Edytuj </button>";
                        echo "</div>";
                    }

                    ?>
                </div>

                <div id="dish-add">
                    <h2>Nowe danie</h2>
                    <form action="./controller/addDishHandler.php" method="POST">
                    <label for="dish_name">Nazwa dania*</label>
                    <input type="text" id="dish_name" name="dish_name" required />

                    <label for="dish_type">Typ dania*</label>
                    <input type="text" id="dish_type" name="dish_type" required />

                    <label for="price">Cena*</label>
                    <input type="number" id="price" name="price" step="0.01" required />

                    <label for="description">Opis dania*</label>
                    <textarea id="description" name="description" required></textarea>

                    <label for="components">Składniki (opcjonalnie)</label>
                    <input type="text" id="components" name="components" />

                    <label for="additions">Dodatki (opcjonalnie)</label>
                    <input type="text" id="additions" name="additions" />

                    <button type="submit">Dodaj danie</button>
                    </form>

                </div>

                <div id="employee-list">
                </div>

                <div id="order-list">
                    <h2>Zamówienia</h2>
                <?php
                    $query = "SELECT * FROM display.list_client_orders($1)";
                    $result = pg_query_params($db, $query, [$client_contact]);

                    if (!$result) {
                        echo "Wystąpił błąd podczas pobierania zamówień: " . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<p class='order-element'><strong>ID zamówienia:</strong> {$row['ord_id']}</p>";
                        echo "<p class='order-element'><strong>Metoda płatności:</strong> {$row['pay_meth']}</p>";
                        echo "<p class='order-element'><strong>Dostawca:</strong> {$row['deliv']}</p>";
                        echo "<p class='order-element'><strong>Status zamówienia:</strong> {$row['ordr_stat']}</p>";
                        echo "<p class='order-element'><strong>Data zamówienia:</strong> {$row['ord_at']}</p>";
                        echo "<p class='order-element'><strong>Ostatnia aktualizacja:</strong> {$row['last_update']}</p>";
                        echo "<p class='order-element'><strong>Klient:</strong> {$row['client']}</p>";
                        echo "<p class='order-element'><strong>Numer adresu:</strong> {$row['address_number']}</p>";
                        echo "<p class='order-element'><strong>Notatka klienta:</strong> {$row['cust_note']}</p>";
                        echo "<button type='button' onclick='editOrder({$row['ord_id']})'>Edytuj</button>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>