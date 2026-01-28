<?php

require __DIR__ . '/database.php';
require __DIR__ . '/../../lib/utils_game_timestamp.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development, allow all origins

$host = 'localhost'; // or your host
$port = '5432'; // or your port
$dbname = 'dwemer';
$user = 'dwemer';
$password = 'dwemer';

$connStr = "pgsql:host={$host};port={$port};dbname={$dbname};user={$user};password={$password}";

$response = [
    'min_calendar_date' => null,
    'max_calendar_date' => null,
    'overall_min_gamets' => null,
    'overall_max_gamets' => null,
    'data' => [],
    'error' => null
];

try {
    $pdo = new PDO($connStr);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get min/max gamets for date picker bounds
    $stmtMinMax = $pdo->query('SELECT MIN(gamets_truncated) AS min_gamets, MAX(gamets_truncated) AS max_gamets FROM memory_summary WHERE gamets_truncated > 0');
    $minMaxGamets = $stmtMinMax->fetch(PDO::FETCH_ASSOC);

    if ($minMaxGamets) {
        if ($minMaxGamets['min_gamets']) {
            $response['overall_min_gamets'] = (float)$minMaxGamets['min_gamets'];
            if (function_exists('convert_gamets2skyrim_date')) {
                $minSkyrimDateTime = convert_gamets2skyrim_date($minMaxGamets['min_gamets']);
                $response['min_calendar_date'] = substr($minSkyrimDateTime, 0, 10); // Extract YYYY-MM-DD
            }
        }
        if ($minMaxGamets['max_gamets']) {
            $response['overall_max_gamets'] = (float)$minMaxGamets['max_gamets'];
            if (function_exists('convert_gamets2skyrim_date')) {
                $maxSkyrimDateTime = convert_gamets2skyrim_date($minMaxGamets['max_gamets']);
                $response['max_calendar_date'] = substr($maxSkyrimDateTime, 0, 10); // Extract YYYY-MM-DD
            }
        }
    }

    // Base query
    $sql = 'SELECT uid, summary, embedding, companions, gamets_truncated FROM memory_summary';
    $params = [];
    $conditions = [];

    if (isset($_GET['start_gamets']) && isset($_GET['end_gamets'])) {
        if (is_numeric($_GET['start_gamets']) && is_numeric($_GET['end_gamets'])) {
            $conditions[] = 'gamets_truncated BETWEEN :start_gamets AND :end_gamets';
            $params[':start_gamets'] = (float)$_GET['start_gamets'];
            $params[':end_gamets'] = (float)$_GET['end_gamets'];
        }
    }
    
    // Always ensure gamets_truncated > 0 for valid data, if not already filtered by range
    if (empty($conditions)) { // If no date range filter, apply default > 0
         $conditions[] = 'gamets_truncated > 0'; 
    } else { // If date range filter exists, still good to ensure the base is positive if not implied by range.
        // No, if a range is given, that range defines the filter. It might include 0 if user somehow makes it so.
        // However, the min/max for calendar is based on >0, so this should be fine.
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY gamets_truncated ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Diagnostic checks
    $diagnostics = [];
    
    $countQuery = $pdo->query('SELECT COUNT(*) as total, COUNT(embedding) as with_embedding FROM memory_summary WHERE gamets_truncated > 0');
    $counts = $countQuery->fetch(PDO::FETCH_ASSOC);
    
    if ($counts['total'] == 0) {
        $diagnostics[] = [
            'type' => 'error',
            'message' => 'No memory summaries found. Play the game with CHIM active, then sync memories in the CHIM Config Hub (Events and Memories page).'
        ];
    } elseif ($counts['with_embedding'] == 0) {
        $diagnostics[] = [
            'type' => 'error', 
            'message' => "Found {$counts['total']} memories but none have embeddings. Enable TXT2VEC in Global Settings > Memory, ensure the service is running on port 8082, then sync memories."
        ];
    } elseif ($counts['with_embedding'] < $counts['total']) {
        $pct = round(($counts['with_embedding'] / $counts['total']) * 100);
        $diagnostics[] = [
            'type' => 'warning',
            'message' => "{$counts['with_embedding']} of {$counts['total']} memories have embeddings ({$pct}%). Run memory sync in Events and Memories to complete."
        ];
    }
    
    $response['diagnostics'] = $diagnostics;

    foreach ($results as &$row) {
        if (isset($row['embedding'])) {
            $vectorString = trim($row['embedding'], '[]');
            $vectorArray = explode(',', $vectorString);
            $row['embedding'] = array_map('floatval', $vectorArray);
        }
        
        if (isset($row['gamets_truncated'])) {
            if (function_exists('convert_gamets2skyrim_long_date_no_time')) {
                $row['skyrim_date'] = convert_gamets2skyrim_long_date_no_time($row['gamets_truncated']);
            } else {
                $row['skyrim_date'] = 'Date Error: Conversion function not found';
            }
        } else {
            $row['skyrim_date'] = 'Date not available';
        }
    }
    $response['data'] = $results; // Put data into the response wrapper

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    $response['error'] = 'Database connection failed: ' . $e->getMessage();
    echo json_encode($response); // Use the same structure for error response
} 