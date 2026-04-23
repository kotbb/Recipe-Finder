<?php
header('Content-Type: application/json');
error_reporting(0);

// make this according to the project structure
require_once __DIR__ . '/model/recipe.php'; //
$conn = require __DIR__ . "/config/db.php";
require_once __DIR__ . "/DB_Ops.php";

if (!isset($_GET['action'])) {
    echo json_encode([
            'success' => false,
            'error' => 'No action provided'
    ]);
    exit;
}
$action = $_GET['action'];

// Get all
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
// search
elseif ($action === 'search') {

    $query = $_GET['query'] ?? '';

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
                'error' => 'API connection failed'
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

// create
elseif ($action === 'create') {

    $name = $_POST['name'] ?? '';
    $ingredients = $_POST['ingredients'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    $image = $_POST['image_path'] ?? null;

    if (!$name || !$ingredients || !$instructions) {
        echo json_encode([
                'success' => false,
                'error' => 'Missing fields'
        ]);
        exit;
    }

    $recipe = new Recipe($name, $ingredients, $instructions);
    $recipe->setImagePath($image);

    $id = addRecipe($conn, $recipe);

    echo json_encode(['success' => (bool)$id, 'id' => $id]);
    exit;
}

// update
elseif ($action === 'update') {

    $id = $_POST['id'] ?? 0;

    if (!$id) {
        echo json_encode([
                'success' => false,
                'error' => 'ID required'
        ]);
        exit;
    }

    $recipe = new Recipe(
            $_POST['name'],
            $_POST['ingredients'],
            $_POST['instructions']
    );

    $recipe->setImagePath($_POST['image_path'] ?? null);

    $result = updateRecipe($conn, $id, $recipe);

    echo json_encode([
            'success' => $result
    ]);

    exit;
}
// delete
elseif ($action === 'delete') {

    $id = $_POST['id'] ?? 0;

    if (!$id) {
        echo json_encode([
                'success' => false,
                'error' => 'ID required'
        ]);
        exit;
    }

    $result = deleteRecipe($conn, $id);

    echo json_encode([
            'success' => $result
    ]);

    exit;
}

// if action is invalid
else {
    echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
    ]);
    exit;
}

// calling api function
function callAPI($url)
{
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