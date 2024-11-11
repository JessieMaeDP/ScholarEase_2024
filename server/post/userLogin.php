<?php
header("Content-Type: application/json");

require_once "../config/connection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Read raw input data
    $rawData = file_get_contents("php://input");
    error_log("Raw Input Data: " . $rawData);

    // Decode JSON
    $data = json_decode($rawData, true);
    error_log("Decoded Data: " . print_r($data, true));

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    // Check received username and password
    error_log("Username: " . $username);
    error_log("Password: " . $password);

    // Simple validation
    if (!empty($username) && !empty($password)) {
        // Query to fetch user based on username
        $query = "SELECT * FROM User WHERE Username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password (use password_verify if passwords are hashed)
            if (password_verify($password, $user['Password'])) {
                // Prepare user data to send to frontend (without password)
                $userData = [
                    "UserID" => $user['UserID'],
                    "Username" => $user['Username'],
                    "UserType" => $user['UserType'],
                    "Email" => $user['Email']
                ];
                
                echo json_encode([
                    "message" => "Login successful.",
                    "user" => $userData
                ]);
            } else {
                echo json_encode(["message" => "Incorrect password."]);
            }
        } else {
            echo json_encode(["message" => "User not found."]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "All fields are required."]);
    }
}

$conn->close();
?>
