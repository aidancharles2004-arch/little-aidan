<?php
include 'connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['username'])) {
        $username = trim($input['username']);
        
        // Check if username exists
        $stmt = $conn->prepare("SELECT username, full_name FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode([
                'exists' => true,
                'message' => 'Username already exists',
                'user_name' => $user['full_name'],
                'redirect_to_login' => true
            ]);
        } else {
            echo json_encode([
                'exists' => false,
                'message' => 'Username available'
            ]);
        }
        
        $stmt->close();
    } elseif (isset($input['email'])) {
        $email = trim($input['email']);
        
        if (!empty($email)) {
            // Check if email exists
            $stmt = $conn->prepare("SELECT username, full_name FROM Users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                echo json_encode([
                    'exists' => true,
                    'message' => 'Email already registered',
                    'user_name' => $user['full_name'],
                    'username' => $user['username'],
                    'redirect_to_login' => true
                ]);
            } else {
                echo json_encode([
                    'exists' => false,
                    'message' => 'Email available'
                ]);
            }
            
            $stmt->close();
        } else {
            echo json_encode([
                'exists' => false,
                'message' => 'Email is optional'
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'Invalid request'
        ]);
    }
} else {
    echo json_encode([
        'error' => 'Method not allowed'
    ]);
}

$conn->close();
?>
