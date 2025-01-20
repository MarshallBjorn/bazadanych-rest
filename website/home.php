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
        <script type="text/javascript" src="scripts/cancelOrder.js"></script>
        <script type="text/javascript" src="scripts/updateOrderStatus.js"></script>
        <script type="text/javascript" src="scripts/dynamicComponentSelect.js"></script>
    </head>
    <body>
        <div id="app">
            <div id="side-panel">
                <div id="account">
                    <img src="image/avatar.png" alt="avatar">
                    <p>Zalogowany: <strong><?php echo $_SESSION['logged'];?></strong></p>
                </div>
                <div id="line"></div>
                <button type="button" onclick="changeView('order-list')" class="menu">Zamówienia</button>
                <button type="button" onclick="changeView('item-list')" class="menu">Lista dań</button>
                <button type="button" onclick="changeView('new-order')" class="menu">Nowe zamówienie</button>
                <button type="button" onclick="changeView('dish-add')" class="menu">Nowe danie</button>
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
                    $query4 = "SELECT * FROM display.list_providers() AS t(prod_id INT, prod_name VARCHAR, contact VARCHAR, addr TEXT, is_partner BOOLEAN);";
                    $result = pg_query($db, $query);
                    $result2 = pg_query($db, $query2);
                    $result3 = pg_query($db, $query3);
                    $result4 = pg_query($db, $query4);

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

                    echo "<h2>Wszystkie składniki</h2>";
                    while($row = pg_fetch_assoc($result3)) {
                        echo "<div class='item'>";
                        echo "<p class='item-element'><strong>ID:</strong> {$row['component_id']}</p>";
                        echo "<p class='item-element'><strong>Nazwa:</strong> $row[component_name]</p>" .
                            "<p class='item-element'><strong>Cena:</strong> $row[price]</p>" .
                            "<p class='item-element'><strong>Dostawca:</strong> $row[prod_name]</p>" .
                            "<p class='item-element'><strong>Dostępne:</strong> ". ($row['availability'] == 't' ? 'Tak' : 'Nie') ."</p>";
                        echo "<button type=button onclick='toggleEditSection(this)'> Edytuj </button>";
                        
                        echo "<div class='edit-section'>";
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

                    echo "<h2>Wszyscy producenci</h2>";
                    while($row = pg_fetch_assoc($result4)) {
                        echo "<div class='item'>";
                        echo "<p class='item-element'><strong>ID:</strong> $row[prod_id]</p>" . 
                            "<p class='item-element'><strong>Nazwa:</strong> $row[prod_name]</p>" .
                            "<p class='item-element'><strong>Kontakt:</strong> $row[contact]</p>" .
                            "<p class='item-element'><strong>Adres:</strong> $row[addr]</p>" .
                            "<p class='item-element'><strong>Partner:</strong> " . ($row['is_partner'] == 't' ? 'Tak' : 'Nie') ."</p>";
                        echo "<button type=button onclick='toggleEditSection(this)'>Edytuj</button>";

                        echo "<div class='edit-section'>";
                        echo "<form method='POST' action='./controller/editProviderHandler.php'>";
                        echo "<input type='hidden' id='edit-name' name='prod_id' value='{$row['prod_id']}'/>";
                        echo "<label for='edit-name'>Nazwa:</label>";
                        echo "<input type='text' id='edit-name' name='prod_name' value='{$row['prod_name']}' required />";
                        echo "<label for='edit-price'>Kontakt:</label>";
                        echo "<input type='text' id='edit-type' name='contact' value='{$row['contact']}' required />";
                        echo "<label for='edit-type'>Adres:</label>";
                        echo "<input type='text' id='edit-served' name='adres' value='{$row['addr']}'/>";
                        echo "<label for='edit-name'>Partner:</label>";
                        echo "<select id='edit-served' name='partner'>";
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
                        
                        <label for="components">Składniki</label>
                        <div>
                            <button type="button" onclick="fetchMenu2('components')">Wybierz składniki</button>
                            <div id="menu-items-2" class="hidden">
                                <h3>Lista dostępnych składników</h3>
                                <div id="menu-list-2"></div>
                                <button type="button" onclick="closeMenu2()">Zamknij</button>
                            </div>
                        </div>
                        <input type="hidden" id="components" name="components" value="[]" />
                        <div id="components-summary">
                            <h4>Wybrane składniki</h4>
                        </div>
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

                        echo "<label for='edit-street'>Ulica:";
                        echo "<input type='text' id='edit-contact' name='street' value='{$row['street']}' required />";
                        echo "<label for='edit-locality'>Miejscowosc:";
                        echo "<input type='text' id='edit-contact' name='locality' value='{$row['locality']}' required />";
                        echo "<label for='edit-post-code'>Kod pocztowy:";
                        echo "<input type='text' id='edit-post-code' name='post_code' value='{$row['post_code']}' required />";
                        echo "<label for='edit-build-num'>Numer domu:";
                        echo "<input type='text' id='edit-build-num' name='building_num' value='{$row['building_num']}' required />";


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
                        $query = "SELECT * FROM display.list_all_orders() ORDER BY ordr_stat, ord_at";
                        $result = pg_query($db, $query);

                        if (!$result) {
                            echo "Wystąpił błąd podczas pobierania zamówień: " . pg_last_error($db);
                            exit;
                        }

                        $groups = [
                            'W trakcie' => [],
                            'W doręczeniu' => [],
                            'Dostarczone' => [],
                            'Anulowane' => []
                        ];

                        while ($row = pg_fetch_assoc($result)) {
                            switch ($row['ordr_stat']) {
                                case 'PROCESSING':
                                    $groups['W trakcie'][] = $row;
                                    break;
                                case 'IN DELIVERY':
                                    $groups['W doręczeniu'][] = $row;
                                    break;
                                case 'COMPLETED':
                                    $groups['Dostarczone'][] = $row;
                                    break;
                                case 'CANCELED':
                                    $groups['Anulowane'][] = $row;
                                    break;
                                default:
                                    echo "Nieznany status: {$row['ordr_stat']}<br>";  // Debugowanie
                                    break;
                            }
                        }                   

                        foreach ($groups as $status => $orders) {
                            echo "<h2>{$status}</h2>";
                            if (count($orders) === 0) {
                                echo "<p>Brak zamówień w tej kategorii.</p>";
                            } else {
                                foreach ($orders as $order) {
                                    echo "<div class='order-item'>";
                                    echo "<p class='order-element'><strong>ID zamówienia:</strong> {$order['ord_id']}</p>";
                                    echo "<p class='order-element'><strong>Metoda płatności:</strong> {$order['pay_meth']}</p>";
                                    echo "<p class='order-element'><strong>Suma:</strong> {$order['summ']}</p>";
                                    if ($order['deliv'] != "") {
                                        echo "<p class='order-element'><strong>Dostawca:</strong> {$order['deliv']}</p>";
                                    }
                                    echo "<p class='order-element'><strong>Status zamówienia:</strong> {$order['ordr_stat']}</p>";
                                    echo "<p class='order-element'><strong>Data zamówienia:</strong> {$order['ord_at']}</p>";
                                    echo "<p class='order-element'><strong>Ostatnia aktualizacja:</strong> {$order['last_update']}</p>";
                                    echo "<p class='order-element'><strong>Klient:</strong> {$order['client']}</p>";
                                    echo "<p class='order-element'><strong>Numer adresu:</strong> {$order['address_string']}</p>";
                                    echo "<p class='order-element'><strong>Notatka klienta:</strong> {$order['cust_note']}</p>";
                                    $dish_query = "SELECT order_id, dish_name, quantity FROM display.list_order_dishes({$order['ord_id']}) AS dishes(order_id INT, dish_name TEXT, quantity INT)";
                                    $dish_result = pg_query($db, $dish_query);
                                    if ($dish_result) {
                                        echo "<h3>Dania:</h3><ul>";
                                        while ($dish = pg_fetch_assoc($dish_result)) {
                                            echo "<li>{$dish['dish_name']} - Ilość: {$dish['quantity']}</li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "<p>Brak dań w zamówieniu.</p>";
                                    }
                    
                                    $addition_query = "SELECT order_id, addition_name, quantity FROM display.list_order_additions({$order['ord_id']}) AS additions(order_id INT, addition_name TEXT, quantity INT)";
                                    $addition_result = pg_query($db, $addition_query);
                                    if ($addition_result) {
                                        echo "<h3>Dodatki:</h3><ul>";
                                        while ($addition = pg_fetch_assoc($addition_result)) {
                                            echo "<li>{$addition['addition_name']} - Ilość: {$addition['quantity']}</li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "<p>Brak dodatków w zamówieniu.</p>";
                                    }
                    
                                    if ($order['ordr_stat'] != 'COMPLETED' && $order['ordr_stat'] != 'CANCELED') {
                                        echo "<button type='button' onclick='updateOrderStatus({$order['ord_id']})'>Zmień status</button>";
                                        echo "<button type='button' class='cancel_button' onclick='cancelOrder({$order['ord_id']})'>Anuluj zamówienie</button>";
                                    }
                                    echo "</div>";  
                                }
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>