<?php
ob_start();

ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json');

require_once __DIR__ . '/model/recipe.php';

/** One place for host/user/password/db — edit `backend/config/db.php` for your machine (e.g. XAMPP root + empty password). */
$conn = require __DIR__ . '/config/db.php';

if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

function jsonResponseAndExit(array $payload): void
{
    ob_end_clean();
    echo json_encode($payload);
    exit;
}

function addRecipe($conn, Recipe $recipe)
{
    $name         = $recipe->getName();
    $ingredients  = $recipe->getIngredients();
    $instructions = $recipe->getInstructions();
    $imagePath = $recipe->getImagePath();
    $stmt = $conn->prepare("INSERT INTO recipes(name,ingredients,instructions,image_path) VALUES (?,?,?,?)");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ssss', $name, $ingredients, $instructions, $imagePath);
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }
    $stmt->close();
    return false;
}

function getAllRecipes($conn)
{
    $stmt = $conn->prepare("SELECT * FROM recipes ORDER BY id DESC");
    if (!$stmt) {
        return [];
    }
    $stmt->execute();
    $recipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $recipes;
}

function updateRecipe($conn, int $id, Recipe $recipe)
{
    if ($id <= 0) return false;
    $name         = $recipe->getName();
    $ingredients  = $recipe->getIngredients();
    $instructions = $recipe->getInstructions();
    $imagePath = $recipe->getImagePath();
    $stmt = $conn->prepare("UPDATE recipes SET name=?, ingredients=?, instructions=?, image_path=? WHERE id=?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ssssi', $name, $ingredients, $instructions, $imagePath, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function deleteRecipe($conn, int $id)
{
    if (!$id) return false;
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id=?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

if (!isset($_GET['action'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'No action provided']);
    exit;
}

$action = $_GET['action'];

if ($action === 'getAll') {
    $data = getAllRecipes($conn);
    ob_end_clean();
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($action === 'add') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        jsonResponseAndExit(['success' => false, 'error' => 'Invalid or empty JSON body.']);
    }

    $name         = trim((string) ($body['name'] ?? ''));
    $ingredients  = trim((string) ($body['ingredients'] ?? ''));
    $instructions = trim((string) ($body['instructions'] ?? ''));
    $imageRaw     = (string) ($body['image_path'] ?? '');

    $err = Recipe::validateApiPayload($name, $ingredients, $instructions, $imageRaw);
    if ($err !== null) {
        jsonResponseAndExit(['success' => false, 'error' => $err]);
    }

    $recipe = new Recipe($name, $ingredients, $instructions);
    $recipe->setImagePath($imageRaw);
    $id = addRecipe($conn, $recipe);
    if ($id) {
        jsonResponseAndExit(['success' => true, 'id' => $id]);
    }
    jsonResponseAndExit(['success' => false, 'error' => 'Insert failed: ' . $conn->error]);
}

if ($action === 'update') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponseAndExit(['success' => false, 'error' => 'A valid recipe id is required for update.']);
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        jsonResponseAndExit(['success' => false, 'error' => 'Invalid or empty JSON body.']);
    }

    $name         = trim((string) ($body['name'] ?? ''));
    $ingredients  = trim((string) ($body['ingredients'] ?? ''));
    $instructions = trim((string) ($body['instructions'] ?? ''));
    $imageRaw     = (string) ($body['image_path'] ?? '');

    $err = Recipe::validateApiPayload($name, $ingredients, $instructions, $imageRaw);
    if ($err !== null) {
        jsonResponseAndExit(['success' => false, 'error' => $err]);
    }

    $recipe = new Recipe($name, $ingredients, $instructions);
    $recipe->setImagePath($imageRaw);
    $ok = updateRecipe($conn, $id, $recipe);
    if ($ok) {
        jsonResponseAndExit(['success' => true]);
    }
    jsonResponseAndExit(['success' => false, 'error' => 'Update failed: ' . $conn->error]);
}

if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponseAndExit(['success' => false, 'error' => 'A valid recipe id is required for delete.']);
    }
    $ok = deleteRecipe($conn, $id);
    if ($ok) {
        jsonResponseAndExit(['success' => true]);
    }
    jsonResponseAndExit(['success' => false, 'error' => 'Delete failed or recipe not found.']);
}

ob_end_clean();
echo json_encode(['success' => false, 'error' => 'Invalid action']);
exit;
