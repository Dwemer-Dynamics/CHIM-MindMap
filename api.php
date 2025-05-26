<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development, allow all origins

$host = 'localhost'; // or your host
$port = '5432'; // or your port
$dbname = 'dwemer';
$user = 'dwemer';
$password = 'dwemer';

$connStr = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";

try {
    $pdo = new PDO($connStr);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SELECT uid, summary, embedding, companions, gamets_truncated FROM memory_summary ORDER BY gamets_truncated ASC');
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // The embedding is likely a string like "[0.1,0.2,...]"
    // We need to parse it into an actual array of floats for Three.js
    foreach ($results as &$row) {
        if (isset($row['embedding'])) {
            // Remove brackets and split by comma
            $vectorString = trim($row['embedding'], '[]');
            $vectorArray = explode(',', $vectorString);
            // Convert to float
            $row['embedding'] = array_map('floatval', $vectorArray);
        }
    }

    echo json_encode($results);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
} 