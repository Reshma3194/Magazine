<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "magazine"; // Replace with your database name

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Error: Form not submitted.");
}

// Validate all required fields are present
$required_fields = ['name', 'college', 'branch', 'title', 'abstract'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        die("Error: All fields are required. Missing field: $field");
    }
}

// Get form data with proper sanitization
$name = htmlspecialchars($_POST['name']);
$college = htmlspecialchars($_POST['college']);
$branch = htmlspecialchars($_POST['branch']);
$title = htmlspecialchars($_POST['title']);
$sub_authors = isset($_POST['sub_authors']) ? htmlspecialchars($_POST['sub_authors']) : '';
$abstract = htmlspecialchars($_POST['abstract']);
// Calculate word count (for display only, no validation)
$wordCount = str_word_count($abstract);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO mag (name, college, branch, title, subname, abstract,date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssssss", $name, $college, $branch, $title, $sub_authors, $abstract);

// Execute the statement
if ($stmt->execute()) {
    // Display the submitted data in a nicely formatted way
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Submission Successful</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                color: #333;
            }
            h1 {
                color: #2c3e50;
                text-align: center;
                border-bottom: 2px solid #4CAF50;
                padding-bottom: 10px;
            }
            .details {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            .detail-item {
                margin-bottom: 8px;
            }
            .detail-label {
                font-weight: bold;
                display: inline-block;
                width: 100px;
            }
            .abstract {
                background-color: #e8f4fc;
                padding: 15px;
                border-radius: 5px;
                border-left: 4px solid #3498db;
            }
            .abstract-title {
                font-weight: bold;
                margin-bottom: 10px;
            }
            .success-message {
                color: #4CAF50;
                text-align: center;
                font-weight: bold;
                margin-bottom: 20px;
            }
            .error-message {
                color: #f44336;
                text-align: center;
                font-weight: bold;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <h1>Submission Successful</h1>
        <div class='success-message'>Thank you for your submission!</div>
        
        <div class='details'>
            <div class='detail-item'>
                <span class='detail-label'>Name:</span>
                <span>$name</span>
            </div>
            <div class='detail-item'>
                <span class='detail-label'>College:</span>
                <span>$college</span>
            </div>
            <div class='detail-item'>
                <span class='detail-label'>Branch:</span>
                <span>$branch</span>
            </div>
            <div class='detail-item'>
                <span class='detail-label'>Sub Authors:</span>
                <span>$sub_authors</span>
            </div>
        </div>
        
        <div class='abstract'>
            <div class='abstract-title'>Title: $title</div>
            <p>$abstract</p>
            <p><strong>Word count:</strong> $wordCount words</p>
        </div>
    </body>
    </html>";
} else {
    echo "<div class='error-message'>Error: " . $stmt->error . "</div>";
}

// Close connections
$stmt->close();
$conn->close();
?>