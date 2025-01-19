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
        <script type="text/javascript" src="scripts/dynamicDishSelect.js"></script>
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
                    <form id="order-form" action="./controller/placeNewOrderHandler.php" method="POST">
                        <label for="payment_method_name">Metoda płatności*</label>
                        <select id="payment_method_name" name="payment_method_name" required>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Cash">Cash</option>
                            <option value="Online Payment">Online Payment</option>
                        </select>
                        <label for="client_contact">Telefon*</label>
                        <input type="text" id="client_contact" name="client_contact" required />
                        <label for="note">Notatka</label>
                        <textarea id="note" name="note"></textarea>
                        <label>Ulica*</label>
                        <input type="text" name="street" required />
                        <label>Miasto*</label>
                        <input type="text" name="locality" required />
                        <label>Kod pocztowy*</label>
                        <input type="text" name="post_code" required />
                        <label>Numer budynku*</label>
                        <input type="text" name="building_num" required />

                <div id="menu-section">
                    <button type="button" onclick="fetchMenu('dishes')">Wybierz dania</button>
                    <button type="button" onclick="fetchMenu('additions')">Wybierz dodatki</button>

                    <div id="menu-items" class="hidden">
                        <h3>Lista dostępnych pozycji</h3>
                        <div id="menu-list"></div>
                            <button type="button" onclick="closeMenu()">Zamknij</button>
                        </div>
                    </div>
                    <input type="hidden" id="dishes" name="dishes" value="[]" />
                    <input type="hidden" id="additions" name="additions" value="[]" />

                    <div id="dishes-summary">
                        <h4>Wybrane dania</h4>
                    </div>
                    <div id="additions-summary">
                        <h4>Wybrane dodatki</h4>
                    </div>

                    <button type="submit">Dodaj zamówienie</button>
                    </form>
                </div>

                <div id="item-list">
                    <h2>Wszystkie dania</h2>
                    <?php 
                    include './database/config.php';

                    $query = "SELECT * FROM display.list_all_dishes() AS t(dish_id INT, dish_name VARCHAR, dish_type VARCHAR, price NUMERIC, is_served boolean, description TEXT)";
                    $query2 = "SELECT * FROM display.list_all_additions() AS t(addition_id INT, addition_name VARCHAR, price NUMERIC, prod_name VARCHAR, availability boolean)";
                    $query3 = "SELECT * FROM display.list_all_components() AS t(component_id INT, component_name VARCHAR, price NUMERIC, prod_name VARCHAR, availability boolean)";
                    $result = pg_query($db, $query);
                    $result2 = pg_query($db, $query2);
                    $result3 = pg_query($db, $query3);

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
                        echo "<p class='item-element'><strong>ID:</strong> {$row['addition_id']}</p>";
                        echo "<p class=item-element><strong>Nazwa:</strong> $row[addition_name]</p>".
                            "<p class=item-element><strong>Dostawca:</strong> $row[prod_name]</p>". 
                            "<p class=item-element><strong>Cena:</strong> $row[price]</p>".
                            "<p class=item-element><strong>Dostępne:</strong> ". ($row['availability'] == 't' ? 'Tak' : 'Nie') ."</p>";
                        echo "<button type=button onclick='toggleEditSection(this)'> Edytuj </button>";

                        echo "<div class='edit-section'>";
                        echo "<form method='POST' action='./controller/editAdditionHandler.php'>";
                        echo "<input type='hidden' id='edit-name' name='addition_id' value='{$row['addition_id']}'/>";
                        echo "<label for='edit-name'>Nazwa:</label>";
                        echo "<input type='text' id='edit-name' name='addition_name' value='{$row['addition_name']}' required />";
                        echo "<label for='edit-type'>Provider:</label>";
                        echo "<input type='text' id='edit-type' name='add_provider' value='{$row['prod_name']}' required />";
                        echo "<label for='edit-type'>Cena:</label>";
                        echo "<input type='text' id='edit-type' name='add_price' value='{$row['price']}' required />";
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

                    echo "<h2>Składniki</h2>";
                    while($row = pg_fetch_assoc($result3)) {
                        echo "<div class='item'>";
                        echo "<p class='item-element'><strong>ID:</strong> {$row['component_id']}</p>";
                        echo "<p class='item-element'><strong>Nazwa:</strong> $row[component_name]</p>" .
                            "<p class='item-element'><strong>Cena:</strong> $row[price]</p>" .
                            "<p class='item-element'><strong>Dostawca:</strong> $row[prod_name]</p>" .
                            "<p class='item-element'><strong>Dostępne:</strong> ". ($row['availability'] == 't' ? 'Tak' : 'Nie') ."</p>";
                        echo "<button type=button onclick='toggleEditSection(this)'> Edytuj </button>";
                        
                        echo "<div class='edit-section'";
                        echo "<form method='POST' action='./controller/editComponentHandler.php'>";
                        echo "<input type='hidden' id='edit-name' name='component_id' value='{$row['component_id']}'/>";
                        echo "<label for='edit-name'>Nazwa:</label>";
                        echo "<input type='text' id='edit-name' name='component_name' value='{$row['component_name']}' required />";
                        echo "<label for='edit-price'>Cena:</label>";
                        echo "<input type='text' id='edit-type' name='component_price' value='{$row['price']}' required />";
                        echo "<label for='edit-type'>Dostawca:</label>";
                        echo "<input type='text' id='edit-served' name='prod_name' value='{$row['prod_name']}'/>";
                        echo "<label for='edit-name'>Dostępne:</label>";
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
                        echo "<p class='staff-item-element'><strong>Telefon:</strong> {$row['fcontact']}</p>";
                        echo "<p class='staff-item-element'><strong>Adres:</strong> {$row['faddress']}</p>";
                        echo "<p class='staff-item-element'><strong>Płeć:</strong> " . ($row['fgender'] == 't' ? 'Mężczyzna' : 'Kobieta') . "</p>";
                        echo "<p class='staff-item-element'><strong>Data urodzenia:</strong> {$row['fbirthday']}</p>";
                        echo "<p class='staff-item-element'><strong>Data zatrudnienia:</strong> {$row['fhire_date']}</p>";
                        echo "<p class='staff-item-element'><strong>Status:</strong> {$row['fstatus']}</p>";
                        echo "<button type='button' onclick='toggleEditSection(this)'>Edytuj</button>";
                        echo "<button type='button' class='cancel_button'>Zawieś</button>";
                        echo "<button type='button' class='cancel_button'>Zwolnij</button>";
                        
                    
                        echo "<div class='edit-section'>";
                        echo "<form method='POST' action='./controller/editStaffHandler.php'>";
                        echo "<input type='hidden' name='staff_id' value='{$row['staff_id']}' />";
                        echo "<label for='edit-fname'>Imię:</label>";
                        echo "<input type='text' id='edit-fname' name='fname' value='{$row['fname']}' required />";
                        echo "<label for='edit-lname'>Nazwisko:</label>";
                        echo "<input type='text' id='edit-lname' name='lname' value='{$row['lname']}' required />";
                        echo "<label for='edit-position'>Stanowisko:</label>";
                        echo "<input type='text' id='edit-position' name='fposition' value='{$row['fposition']}' required />";
                        echo "<label for='edit-contact'>Telefon:</label>";
                        echo "<input type='text' id='edit-contact' name='fcontact' value='{$row['fcontact']}' required />";
                        echo "<label for='edit-gender'>Płeć:</label>";
                        echo "<select id='edit-gender' name='fgender'>";
                        echo "<option value='t'" . ($row['fgender'] == 'M' ? ' selected' : '') . ">Mężczyzna</option>";
                        echo "<option value='f'" . ($row['fgender'] == 'F' ? ' selected' : '') . ">Kobieta</option>";
                        echo "</select>";
                        echo "<label for='edit-birthday'>Data urodzenia:</label>";
                        echo "<input type='date' id='edit-birthday' name='fbirthday' value='{$row['fbirthday']}' required />";
                        echo "<label for='edit-status'>Status: </label>";
                        echo "<select id='edit-status' name='fstatus'>";
                        echo "<option value='HIRED'" . ($row['fstatus'] == 'HIRED' ? ' selected' : '') . ">Zatrudniony/na</option>";
                        echo "<option value='SUSPENDED'" . ($row['fstatus'] == 'SUSPENDED' ? ' selected' : '') . ">Zawieszony/na</option>";
                        echo "<option value='FIRED'" . ($row['fstatus'] == 'FIRED' ? ' selected' : '') . ">Zwolniony/na</option>";
                        echo "</select>";
                        echo "<button type='submit'>Zapisz</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                    ?>
                </div>

                <div id="order-list">
                    <h2>Zamówienia</h2>
                <?php
                    $query = "SELECT * FROM display.list_all_orders()";
                    $result = pg_query($db, $query);

                    if (!$result) {
                        echo "Wystąpił błąd podczas pobierania zamówień: " . pg_last_error($db);
                        exit;
                    }

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<div class='order-item'>";
                        echo "<p class='order-element'><strong>ID zamówienia:</strong> {$row['ord_id']}</p>";
                        echo "<p class='order-element'><strong>Metoda płatności:</strong> {$row['pay_meth']}</p>";
                        echo "<p class='order-element'><strong>Suma:</strong> {$row['summ']}</p>";
                        if($row['deliv'] != "") {
                            echo "<p class='order-element'><strong>Dostawca:</strong> {$row['deliv']}</p>";
                        }
                        echo "<p class='order-element'><strong>Status zamówienia:</strong> {$row['ordr_stat']}</p>";
                        echo "<p class='order-element'><strong>Data zamówienia:</strong> {$row['ord_at']}</p>";
                        echo "<p class='order-element'><strong>Ostatnia aktualizacja:</strong> {$row['last_update']}</p>";
                        echo "<p class='order-element'><strong>Klient:</strong> {$row['client']}</p>";
                        echo "<p class='order-element'><strong>Numer adresu:</strong> {$row['address_string']}</p>";
                        echo "<p class='order-element'><strong>Notatka klienta:</strong> {$row['cust_note']}</p>";
                        echo "<button type='button' onclick='editOrder({$row['ord_id']})'>Zmień status</button>";
                        echo "<button type='button' class='cancel_button' onclick='cancelOrder({$row['ord_id']})'>Anuluj zamówienie</button>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>