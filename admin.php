<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magazine";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Check if user is logged in (for all actions except login)
if (!isset($_SESSION['username']) ){
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Handle different actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_users':
        getUsers($conn);
        break;
    case 'add_user':
        addUser($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'delete_user':
        deleteUser($conn);
        break;
    case 'get_abstracts':
        getAbstracts($conn);
        break;
    case 'search_abstracts':
        searchAbstracts($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getUsers($conn) {
    $result = $conn->query("SELECT * FROM users");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
}

function addUser($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate data
    if (empty($data['first_name']) || empty($data['second_name']) || empty($data['email']) || 
        empty($data['username']) || empty($data['gender']) || empty($data['college'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Hash password if provided
    $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : '';
    
    $stmt = $conn->prepare("INSERT INTO users (first_name, second_name, email, phone, username, gender, college, password, registration_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssssss", 
        $data['first_name'],
        $data['second_name'],
        $data['email'],
        $data['phone'],
        $data['username'],
        $data['gender'],
        $data['college'],
        $password
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding user: ' . $stmt->error]);
    }
    $stmt->close();
}

function updateUser($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate data
    if (empty($data['id']) || empty($data['first_name']) || empty($data['second_name']) || 
        empty($data['email']) || empty($data['username']) || empty($data['gender']) || 
        empty($data['college'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Check if password needs to be updated
    $passwordUpdate = '';
    if (!empty($data['password'])) {
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $passwordUpdate = ", password = '$password'";
    }
    
    $sql = "UPDATE users SET 
            first_name = '{$data['first_name']}',
            second_name = '{$data['second_name']}',
            email = '{$data['email']}',
            phone = '{$data['phone']}',
            username = '{$data['username']}',
            gender = '{$data['gender']}',
            college = '{$data['college']}'
            $passwordUpdate
            WHERE id = {$data['id']}";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $conn->error]);
    }
}

function deleteUser($conn) {
    $id = $_GET['id'] ?? 0;
    if ($id == 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    if ($conn->query("DELETE FROM users WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $conn->error]);
    }
}

function getAbstracts($conn) {
    $result = $conn->query("SELECT * FROM mag ORDER BY date DESC");
    $abstracts = [];
    while ($row = $result->fetch_assoc()) {
        $abstracts[] = $row;
    }
    echo json_encode($abstracts);
}

function searchAbstracts($conn) {
    $searchTerm = $_GET['term'] ?? '';
    if (empty($searchTerm)) {
        getAbstracts($conn);
        return;
    }
    
    $searchTerm = $conn->real_escape_string($searchTerm);
    $result = $conn->query("SELECT * FROM mag 
                           WHERE name LIKE '%$searchTerm%' 
                           OR college LIKE '%$searchTerm%'
                           OR branch LIKE '%$searchTerm%'
                           OR title LIKE '%$searchTerm%'
                           ORDER BY date DESC");
    
    $abstracts = [];
    while ($row = $result->fetch_assoc()) {
        $abstracts[] = $row;
    }
    echo json_encode($abstracts);
}

$conn->close();
?>