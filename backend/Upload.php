<?php
header('Content-Type: application/json');

$target_dir = "../uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (!isset($_FILES["recipe_image"])) {
    echo json_encode(["error" => "No file was received."]);
    exit;
}

$target_file = $target_dir . basename($_FILES["recipe_image"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$errorMessage = ""; 

$check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
if($check !== false) {
    $uploadOk = 1;
} else {
    $errorMessage = "File is not an image.";
    $uploadOk = 0;
}


// limit size to 10 MB
$maxBytes = 10 * 1024 * 1024;
if ($_FILES["recipe_image"]["size"] > $maxBytes) {
    $errorMessage = "Sorry, your file is too large (max 10 MB).";
    $uploadOk = 0;
}

// validate file formats (matches index.php + client-side checks)
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($imageFileType, $allowed, true)) {
    $errorMessage = "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
    $uploadOk = 0;
}

// Check if uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo json_encode(["error" => $errorMessage]);
} else {
    if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
        
        echo json_encode([
            "success" => true,
            "file_path" => "uploads/" . basename($_FILES["recipe_image"]["name"])
        ]);

    } else {
        echo json_encode(["error" => "Sorry, there was an error uploading your file."]);
    }
}
?>