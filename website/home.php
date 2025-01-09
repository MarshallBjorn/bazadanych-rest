<?php session_start();
    if(!isset($_SESSION['logged'])) 
    {
        header("Location:index.html"); 
    }

    if (isset($_SESSION['message'])) {
        echo "<script>alert('$_SESSION[message]');</script>";
        unset($_SESSION['message']);
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
        <script type="text/javascript" src="scripts/newEmployeeAdd.js"></script>
        <script type="text/javascript" src="scripts/editDish.js"></script>
    </head>
    <body>
        <div id="app">
            <div id="side-panel">
                <div id="account">
                    <img src="image/avatar.png" alt="avatar">
                    <p>Zalogowany: <strong><?php echo $_SESSION['logged'];?></strong></p>
                </div>
                <div id="line"></div>
                <button type="button" onclick="changeView('new-order')" class="menu">Nowe zamówienie</button>
                <button type="button" onclick="changeView('order-list')" class="menu">Aktualne zamówienia</button>
                <button type="button" onclick="changeView('dish-add')" class="menu">Nowe danie</button>
                <button type="button" onclick="changeView('item-list')" class="menu">Lista dań</button>
                <button type="button" onclick="changeView('employee-list')" class="menu">Pracownicy</button>
                <button type="button" onclick="logout()" class="menu">Wyloguj</button>
                <div id="date-div">
                    <p id="date"></p>
                    <p id="time"></p>
                </div>
            </div>
            <div id="content">
                <div id="new-order">
                <h2>Nowe zamówienie</h2>
                <form action="./controller/newOrderHandler.php" method="POST">
                    <label for="payment_method_name">Metoda płatności*</label>
                    <input type="text" id="payment_method_name" name="payment_method_name" required />

                    <label for="client_contact">Kontakt klienta*</label>
                    <input type="text" id="client_contact" name="client_contact" required />

                    <label for="note">Notatka</label>
                    <textarea id="note" name="note"></textarea>

                    <label for="address">Adres (JSON)*</label>
                    <input type="text" id="address" name="address" required />

                    <label for="dishes">Dania (JSON)*</label>
                    <textarea id="dishes" name="dishes" required></textarea>

                    <label for="additions">Dodatki (JSON)</label>
                    <textarea id="additions" name="additions"></textarea>

                    <button type="submit">Dodaj zamówienie</button>
                </form>
                </div>

                <div id="item-list">
                    <h2>Wszystkie dania</h2>
                    <?php 
                    include './database/config.php';

                    $query = "SELECT * FROM display.list_all_dishes() AS t(dish_id INT, dish_name VARCHAR, dish_type VARCHAR, price NUMERIC, is_served boolean, description TEXT)";
                    $query2 = "SELECT * FROM display.list_all_additions() AS t(addition_name VARCHAR, price NUMERIC, availability boolean)";
                    $result = pg_query($db, $query);
                    $result2 = pg_query($db, $query2);

                    if(!$result || !$result2) {
                        echo "Wystąpił błąd podczas przetwarzania żądania" . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<div class='item'>";
                        echo "<p class='item-element'><strong>ID:</strong> {$row['dish_id']}</p>";
                        echo "<p class='item-element'><strong>Nazwa:</strong> {$row['dish_name']}</p>";
                        echo "<p class='item-element'><strong>Typ:</strong> {$row['dish_type']}</p>";
                        echo "<p class='item-element'><strong>Cena:</strong> {$row['price']}</p>";
                        echo "<p class='item-element'><strong>Opis:</strong> {$row['description']}</p>";
                        echo "<p class='item-element'><strong>Dostępne:</strong> " . ($row['is_served'] == 't' ? 'Tak' : 'Nie') . "</p>";
                        echo "<button type='button' onclick='toggleEditSection(this)'>Edytuj</button>";

                        echo "<div class='edit-section'>";
                        echo "<form method='POST' action='./controller/editDishHandler.php'>";

                        echo "<input type='hidden' id='edit-name' name='dish_id' value='{$row['dish_id']}'/>";

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
                        echo "<p class=item-element><strong>Nazwa:</strong> $row[addition_name]</p>".  
                            "<p class=item-element><strong>Cena:</strong> $row[price]</p>".
                            "<p class=item-element><strong>Dostępne:</strong> ". ($row['availability'] == 't' ? 'Tak' : 'Nie') ."</p>";
                        echo "<button type=button onclick='toggleEditSection(this)'> Edytuj </button>";

                        echo "<div class='edit-section'>";
                        echo "<form onsubmit='event.preventDefault(); saveDish(this.querySelector(\"button[type=\\'submit\\']\"));'>";
                        echo "<label for='edit-name'>Nazwa:</label>";
                        echo "<input type='text' id='edit-name' name='dish_name' value='{$row['addition_name']}' required />";

                        echo "<label for='edit-type'>price:</label>";
                        echo "<input type='text' id='edit-type' name='dish_type' value='{$row['price']}' required />";

                        echo "<label for='edit-served'>Dostępne:</label>";
                        echo "<select id='edit-served' name='is_served'>";
                        echo "<option value='t'" . ($row['availability'] == 't' ? ' selected' : '') . ">Tak</option>";
                        echo "<option value='f'" . ($row['availability'] == 'f' ? ' selected' : '') . ">Nie</option>";
                        echo "</select>";

                        echo "<button type='submit'>Zapisz</button>";
                        echo "</form>";
                        echo "</div>";
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
                    <h2>Pracownicy</h2>
                    <div id="add-staff-container">
                        <button id="add-staff-button">Dodaj nowego pracownika</button>
                    </div>
                    <div id="overlay" class="hidden"></div>
                <div id="modal" class="hidden">
                    <h2>Dodaj Pracownika</h2>
                    <form id="add-staff-form" method="POST" action="./controller/addStaffHandler.php">
                        <label>PESEL: <input type="text" name="pesel" required></label><br><br>
                        <label>Imię: <input type="text" name="firstname" required></label><br><br>
                        <label>Nazwisko: <input type="text" name="lastname" required></label><br><br>
                        <label>Stanowisko: <input type="text" name="position" required></label><br><br>
                        <label>Ulica: <input type="text" name="street" required></label><br><br>
                        <label>Miasto: <input type="text" name="locality" required></label><br><br>
                        <label>Kod pocztowy: <input type="text" name="post_code" required></label><br><br>
                        <label>Numer budynku: <input type="text" name="building_num" required></label><br><br>
                        <label>Telefon: <input type="text" name="contact" required></label><br><br>
                        <label>Płeć: 
                            <select name="gender">
                                <option value="true">Mężczyzna</option>
                                <option value="false">Kobieta</option>
                            </select>
                        </label><br><br>
                        <label>Data urodzenia: <input type="date" name="birthday" required></label><br><br>
                        <button type="submit">Zapisz</button>
                        <button type="button" id="close-modal">Anuluj</button>
                    </form>
                </div>
                    <?php 
                    $query = "SELECT * FROM display.list_staff()";
    
                    $result = pg_query($db, $query);

                    if(!$result) {
                        echo "Wystąpił błąd podczas przetwarzania żądania" . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<div class='staff-item'>";
                        echo "<p class='staff-item-element'><strong>ID:</strong> {$row['staff_id']}</p>";
                        echo "<p class='staff-item-element'><strong>Imię:</strong> {$row['fname']}</p>";
                        echo "<p class='staff-item-element'><strong>Nazwisko:</strong> {$row['lname']}</p>";
                        echo "<p class='staff-item-element'><strong>Stanowisko:</strong> {$row['fposition']}</p>";
                        echo "<p class='staff-item-element'><strong>Kontakt:</strong> {$row['fcontact']}</p>";
                        echo "<p class='staff-item-element'><strong>Płeć:</strong> {$row['fgender']}</p>";
                        echo "<p class='staff-item-element'><strong>Data urodzenia:</strong> {$row['fbirthday']}</p>";
                        echo "<p class='staff-item-element'><strong>Data zatrudnienia:</strong> {$row['fhire_date']}</p>";
                        echo "<p class='staff-item-element'><strong>Status:</strong> {$row['fstatus']}</p>";
                        echo "<button type='button'>Edytuj</button>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <div id="order-list">
                    <h2>Zamówienia</h2>
                <?php
                    $query = "SELECT * FROM display.list_client_orders()";
                    $result = pg_query($db, $query);

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