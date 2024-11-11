<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/connection.php";

$response = []; // Array to store response message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input data
    $data = json_decode(file_get_contents("php://input"), true);

    $username = $data['Username'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    // Simple validation
    if (!empty($username) && !empty($email) && !empty($password)) {
        
        // Check if email already exists
        $checkEmail = "SELECT * FROM User WHERE Email = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response["message"] = "Email already exists.";
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database
            $query = "INSERT INTO User (Username, Email, Password, UserType) VALUES (?, ?, ?, 'Student')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                $response["message"] = "User registered successfully.";
            } else {
                $response["message"] = "Failed to register user.";
            }
        }

        $stmt->close();
    } else {
        $response["message"] = "All fields are required.";
    }
} else {
    $response["message"] = "Invalid request method.";
}

echo json_encode($response);
$conn->close();
?>