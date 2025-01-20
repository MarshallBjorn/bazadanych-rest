<?php
header('Content-Type: application/json');
include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$type = $_GET['type'] ?? null;

if ($type === 'components') {
    $query = "SELECT component_id, component_name, price 
              FROM display.list_all_components() AS t(component_id INT, component_name VARCHAR, price NUMERIC, prod_name VARCHAR, availability BOOLEAN) 
              WHERE availability = TRUE";
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