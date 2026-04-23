<?php

use App\Entity\Recipe;

$conn = require_once __DIR__ . '/config/db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    echo "Connected successfully";
}

function addRecipe($conn,Recipe $recipe){

    $name = $recipe->getName();
    $ingredients = $recipe->getIngredients();
    $instructions = $recipe->getInstructions();
    $imagePath = $recipe->getImagePath();

    $stmt = $conn->prepare(
        "INSERT INTO recipes(name,ingredients,instructions,image_path)
        VALUES (? , ? , ? , ?)"
    );
    $stmt->bind_param('ssss',$name,$ingredients,$instructions,$imagePath);
    if($stmt->execute()){
        $id = $stmt->insert_id;
        $stmt->close;
        return $id;
    }
    else{
        $stmt->close;
        return false;
    }

}

function getAllRecipes($conn) {
    $stmt = $conn->prepare("SELECT * FROM recipes");
    $stmt->execute();
    
    $result  = $stmt->get_result();
    $recipes = $result->fetch_all(MYSQLI_ASSOC); 
    
    $stmt->close();
    return $recipes;
}

function updateRecipe($conn, int $id, Recipe $recipe) {
    if ($id <= 0) {
        return false;
    }

    $stmt = $conn->prepare("
        UPDATE recipes
        SET name = ?, ingredients = ?, instructions = ?, image_path = ?
        WHERE id = ?
    ");

    $name = $recipe->getName();
    $ingredients = $recipe->getIngredients();
    $instructions = $recipe->getInstructions();
    $imagePath = $recipe->getImagePath();

    $stmt->bind_param('ssssi', $name, $ingredients, $instructions, $imagePath, $id);

    if ($stmt->execute()) {
        $stmt->close();
        return true; 
    } else {
        $stmt->close();
        return false;
    }
}

function deleteRecipe($conn,int $id) {

    if (!$id) {
        return false;
    }

    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $stmt->close();
        return false;
    }

}

// $conn->close()
?>