<?php
// upload.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$target_dir = "uploads/";

// Create folder if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$response = ["success" => false, "message" => "No file uploaded"];

if (isset($_FILES["file"])) {
    $original_name = basename($_FILES["file"]["name"]);
    // Add unique timestamp to prevent overwriting files with same name
    $target_file = $target_dir . time() . "_" . $original_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow specific file formats
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

    if (!in_array($imageFileType, $allowed)) {
        $response = ["success" => false, "message" => "Only JPG, PNG, PDF, & DOC files allowed."];
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // Return the new filename so React can send it to api.php
            $response = [
                "success" => true, 
                "filename" => basename($target_file), 
                "message" => "File uploaded successfully"
            ];
        } else {
            $response = ["success" => false, "message" => "Error saving file."];
        }
    }
}

echo json_encode($response);
?>