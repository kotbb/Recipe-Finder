<?php
// API URL for recipes: https://dummyjson.com/recipes
header('Content-Type: application/json');
error_reporting(0);
if (!isset($_GET['action'])) {
    echo json_encode([
            'success' => false,
            'error' => 'No action provided'
    ]);
    exit;
}

$action = $_GET['action'];

// Getting all recipes
if ($action === 'getAll') {

    $url = "https://dummyjson.com/recipes";

    $response = callAPI($url);

    if ($response['error']) {
        echo json_encode([
                'success' => false,
                'error' => 'Failed to connect to API'
        ]);
        exit;
    }

    $data = json_decode($response['data'], true);

    if (!$data || !isset($data['recipes'])) {
        echo json_encode([
                'success' => false,
                'error' => 'Invalid API response'
        ]);
        exit;
    }

    echo json_encode([
            'success' => true,
            'data' => $data['recipes']
    ]);
    exit;
}

// search recipes
elseif ($action === 'search') {

    if (!isset($_GET['query']) || empty(trim($_GET['query']))) {
        echo json_encode([
                'success' => false,
                'error' => 'Search query is required'
        ]);
        exit;
    }

    $query = urlencode(trim($_GET['query']));

    $url = "https://dummyjson.com/recipes/search?q=$query";

    $response = callAPI($url);

    if ($response['error']) {
        echo json_encode([
                'success' => false,
                'error' => 'Failed to connect to API'
        ]);
        exit;
    }

    $data = json_decode($response['data'], true);

    if (!$data || !isset($data['recipes'])) {
        echo json_encode([
                'success' => false,
                'error' => 'No recipes found'
        ]);
        exit;
    }

    echo json_encode([
            'success' => true,
            'data' => $data['recipes']
    ]);
    exit;
}

else {
    echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
    ]);
    exit;
}

// calling API
function callAPI($url) {

    $ch = curl_init();

    curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return [
                'error' => true,
                'data' => null
        ];
    }

    curl_close($ch);

    return [
            'error' => false,
            'data' => $result
    ];
}