<?php
header('Content-Type: application/json');

include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$type = $_GET['type'] ?? null;

if ($type === 'dishes') {
    $query = "SELECT (dish).dish_id, (dish).dish_name, (dish).dish_type, (dish).price, (dish).is_served, (dish).description 
              FROM display.list_all_dishes() AS dish(dish_id INT, dish_name VARCHAR, dish_type VARCHAR, price NUMERIC, is_served BOOLEAN, description TEXT)
              WHERE (dish).is_served = TRUE";
} elseif ($type === 'additions') {
    $query = "SELECT (addition).addition_id, (addition).addition_name, (addition).price, (addition).availability 
              FROM display.list_all_additions() AS addition(addition_id INT, addition_name VARCHAR, price NUMERIC, availability BOOLEAN)
              WHERE (addition).availability = TRUE";
}

$result = pg_query($db, $query);

if (!$result) {
    echo json_encode(['error' => pg_last_error($db)]);
    exit;
}

$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>