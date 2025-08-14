<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magazine";

// Create database connection
function getDBConnection() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// If abstract ID is provided, show the detailed view
if (isset($_GET['id'])) {
    if (isset($_GET['pdf'])) {
        generateAbstractPDF($_GET['id']);
    } else {
        showAbstractDetails($_GET['id']);
    }
    exit();
}

// Handle PDF generation for search results
if (isset($_GET['pdf_all'])) {
    generateAllAbstractsPDF();
    exit();
}

function generateAbstractPDF($abstractId) {
    $conn = getDBConnection();
    
    // Get abstract details using prepared statement
    $stmt = $conn->prepare("SELECT * FROM mag WHERE id = ?");
    $stmt->bind_param("i", $abstractId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        die("Abstract not found");
    }
    
    $abstract = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Include TCPDF library
    $tcpdf_path = __DIR__.'/tcpdf/tcpdf.php';
    if (!file_exists($tcpdf_path)) {
        die("TCPDF library not found at: $tcpdf_path");
    }
    require_once($tcpdf_path);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($abstract['name']);
    $pdf->SetTitle($abstract['title']);
    $pdf->SetSubject('Academic Abstract');
    $pdf->SetKeywords('Abstract, Academic, PDF');

    // Set default header data
    $pdf->SetHeaderData('', 0, $abstract['title'], 'Generated on ' . date('Y-m-d H:i:s'));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

    // College and branch information - Placed at the beginning
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, $abstract['college'] . " - " . $abstract['branch'], 0, 1, 'C');
    $pdf->Ln(10);

    // Title - Centered
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, $abstract['title'], 0, 1, 'C');
    $pdf->Ln(10);

    // Authors - Main author left, co-authors middle and right if available
    $pdf->SetFont('helvetica', 'I', 12);
    
    // Split co-authors if they exist
    $coauthors = [];
    if (!empty($abstract['subname'])) {
        $coauthors = explode(',', $abstract['subname']);
        $coauthors = array_map('trim', $coauthors);
    }
    
    // Calculate column widths
    $colWidth = 60; // 60mm per column (180mm total for 3 columns)
    
    // Main author (left)
    $pdf->Cell($colWidth, 10, "Author: " . $abstract['name'], 0, 0, 'L');
    
    // First co-author (middle) if exists
    if (isset($coauthors[0])) {
        $pdf->Cell($colWidth, 10, "Co-author: " . $coauthors[0], 0, 0, 'C');
    } else {
        $pdf->Cell($colWidth, 10, '', 0, 0, 'C');
    }
    
    // Second co-author (right) if exists
    if (isset($coauthors[1])) {
        $pdf->Cell($colWidth, 10, "Co-author: " . $coauthors[1], 0, 1, 'R');
    } else {
        $pdf->Cell($colWidth, 10, '', 0, 1, 'R');
    }
    
    $pdf->Ln(15);

    // Abstract heading
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Abstract', 0, 1, 'C');
    $pdf->Ln(5);

    // Abstract content - Justified
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 6, $abstract['abstract'], 0, 'J');
    $pdf->Ln(10);

    // Submission date
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, 'Submitted on: ' . date('F j, Y', strtotime($abstract['date'])), 0, 1, 'R');

    // Close and output PDF document
    $pdf->Output('abstract_' . $abstractId . '.pdf', 'D');
}

function generateAllAbstractsPDF() {
    $conn = getDBConnection();
    
    // Get search term if exists
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build query based on search term
    if (!empty($searchTerm)) {
        $searchTerm = $conn->real_escape_string($searchTerm);
        $query = "SELECT * FROM mag 
                 WHERE name LIKE '%$searchTerm%' 
                 OR college LIKE '%$searchTerm%'
                 OR branch LIKE '%$searchTerm%'
                 OR title LIKE '%$searchTerm%'
                 ORDER BY date DESC";
    } else {
        $query = "SELECT * FROM mag ORDER BY date DESC";
    }
    
    $result = $conn->query($query);
    $conn->close();

    // Include TCPDF library
    $tcpdf_path = __DIR__.'/tcpdf/tcpdf.php';
    if (!file_exists($tcpdf_path)) {
        die("TCPDF library not found at: $tcpdf_path");
    }
    require_once($tcpdf_path);

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Academic System');
    $pdf->SetTitle('Abstract Submissions');
    $pdf->SetSubject('Academic Abstracts');
    $pdf->SetKeywords('Abstracts, Academic, PDF');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'Abstract Submissions', 'Generated on ' . date('Y-m-d H:i:s'));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page
    $pdf->AddPage();

    // Title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Abstract Submissions', 0, 1, 'C');
    $pdf->Ln(5);
    
    if (!empty($searchTerm)) {
        $pdf->SetFont('helvetica', 'I', 12);
        $pdf->Cell(0, 10, "Search results for: $searchTerm", 0, 1, 'C');
        $pdf->Ln(10);
    }

    while ($abstract = $result->fetch_assoc()) {
        // College and branch (placed first)
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $abstract['college'] . " - " . $abstract['branch'], 0, 1, 'C');
        $pdf->Ln(5);
        
        // Title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $abstract['title'], 0, 1, 'C');
        $pdf->Ln(5);
        
        // Authors
        $pdf->SetFont('helvetica', 'I', 12);
        $colWidth = 60;
        
        // Main author (left)
        $pdf->Cell($colWidth, 10, "Author: " . $abstract['name'], 0, 0, 'L');
        
        // Co-authors if available
        $coauthors = [];
        if (!empty($abstract['subname'])) {
            $coauthors = explode(',', $abstract['subname']);
            $coauthors = array_map('trim', $coauthors);
        }
        
        // First co-author (middle)
        if (isset($coauthors[0])) {
            $pdf->Cell($colWidth, 10, "Co-author: " . $coauthors[0], 0, 0, 'C');
        } else {
            $pdf->Cell($colWidth, 10, '', 0, 0, 'C');
        }
        
        // Second co-author (right)
        if (isset($coauthors[1])) {
            $pdf->Cell($colWidth, 10, "Co-author: " . $coauthors[1], 0, 1, 'R');
        } else {
            $pdf->Cell($colWidth, 10, '', 0, 1, 'R');
        }
        
        $pdf->Ln(5);
        
        // Abstract content (justified)
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 6, $abstract['abstract'], 0, 'J');
        $pdf->Ln(5);
        
        // Submission date
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Submitted: ' . date('F j, Y', strtotime($abstract['date'])), 0, 1, 'R');
        
        // Separator
        $pdf->Ln(10);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(15);
    }

    // Close and output PDF document
    $pdf->Output('abstracts_' . date('Ymd_His') . '.pdf', 'D');
}

function showAbstractDetails($abstractId) {
    $conn = getDBConnection();
    
    // Get abstract details using prepared statement
    $stmt = $conn->prepare("SELECT * FROM mag WHERE id = ?");
    $stmt->bind_param("i", $abstractId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        die("Abstract not found");
    }
    
    $abstract = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Abstract Details</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                background-color: #f5f5f5;
                color: #333;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .college-info {
                text-align: center;
                font-weight: bold;
                font-size: 1.2em;
                margin-bottom: 20px;
                color: #2c3e50;
            }
            .title {
                text-align: center;
                font-size: 1.5em;
                font-weight: bold;
                margin-bottom: 20px;
            }
            .authors {
                display: flex;
                justify-content: space-between;
                margin-bottom: 30px;
                font-style: italic;
            }
            .author-col {
                flex: 1;
                padding: 0 10px;
            }
            .author-col:nth-child(1) { text-align: left; }
            .author-col:nth-child(2) { text-align: center; }
            .author-col:nth-child(3) { text-align: right; }
            .abstract-heading {
                text-align: center;
                font-weight: bold;
                font-size: 1.2em;
                margin-bottom: 10px;
            }
            .abstract-content {
                margin-bottom: 30px;
                text-align: justify;
                line-height: 1.8;
            }
            .submission-date {
                text-align: right;
                font-style: italic;
                color: #666;
            }
            .button-group {
                margin-top: 20px;
                display: flex;
                justify-content: space-between;
            }
            .button {
                display: inline-block;
                padding: 8px 15px;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }
            .back-button {
                background-color: #3498db;
                color: white;
            }
            .back-button:hover {
                background-color: #2980b9;
            }
            .pdf-button {
                background-color: #e74c3c;
                color: white;
            }
            .pdf-button:hover {
                background-color: #c0392b;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- College info at the top -->
            <div class="college-info">
                <?php echo htmlspecialchars($abstract['college']); ?> - <?php echo htmlspecialchars($abstract['branch']); ?>
            </div>
            
            <div class="title"><?php echo htmlspecialchars($abstract['title']); ?></div>
            
            <div class="authors">
                <div class="author-col">
                    <strong>Author:</strong><br>
                    <?php echo htmlspecialchars($abstract['name']); ?>
                </div>
                <?php 
                $coauthors = [];
                if (!empty($abstract['subname'])) {
                    $coauthors = explode(',', $abstract['subname']);
                    $coauthors = array_map('trim', $coauthors);
                }
                ?>
                <div class="author-col">
                    <?php if (isset($coauthors[0])): ?>
                        <strong>Co-author:</strong><br>
                        <?php echo htmlspecialchars($coauthors[0]); ?>
                    <?php endif; ?>
                </div>
                <div class="author-col">
                    <?php if (isset($coauthors[1])): ?>
                        <strong>Co-author:</strong><br>
                        <?php echo htmlspecialchars($coauthors[1]); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="abstract-heading">Abstract</div>
            
            <div class="abstract-content">
                <?php echo nl2br(htmlspecialchars($abstract['abstract'])); ?>
            </div>
            
            <div class="submission-date">
                Submitted on: <?php echo date('F j, Y, g:i a', strtotime($abstract['date'])); ?>
            </div>
            
            <div class="button-group">
                <a href="dashboard.php" class="button back-button">Back to Submissions</a>
                <a href="dashboard.php?id=<?php echo $abstractId; ?>&pdf=1" class="button pdf-button">Download as PDF</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Handle AJAX requests for abstract data
if (isset($_GET['action'])) {
    $conn = getDBConnection();
    
    if ($_GET['action'] == 'get_abstracts') {
        $query = "SELECT * FROM mag ORDER BY date DESC";
        $result = $conn->query($query);
        
        $abstracts = [];
        while ($row = $result->fetch_assoc()) {
            $abstracts[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($abstracts);
        exit();
    }
    elseif ($_GET['action'] == 'search_abstracts' && isset($_GET['term'])) {
        $searchTerm = $conn->real_escape_string($_GET['term']);
        $query = "SELECT * FROM mag 
                 WHERE name LIKE '%$searchTerm%' 
                 OR college LIKE '%$searchTerm%'
                 OR branch LIKE '%$searchTerm%'
                 OR title LIKE '%$searchTerm%'
                 ORDER BY date DESC";
        $result = $conn->query($query);
        
        $abstracts = [];
        while ($row = $result->fetch_assoc()) {
            $abstracts[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($abstracts);
        exit();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Abstract Submissions</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-container input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #2980b9;
        }
        .export-button {
            background-color: #27ae60;
            color: white;
        }
        .export-button:hover {
            background-color: #219653;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .logout {
            text-align: right;
            margin-bottom: 20px;
        }
        .logout a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .pdf-button {
            background-color: #e74c3c;
        }
        .pdf-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
        
        <h1>Abstract Submissions</h1>
        
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search abstracts by name, college, branch or title...">
            <button onclick="searchAbstracts()">Search</button>
            <button onclick="clearSearch()">Clear</button>
            <button onclick="exportToPDF()" class="export-button">Export to PDF</button>
        </div>
        
        <table id="abstractsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>College</th>
                    <th>Branch</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="abstractsTableBody">
                <!-- Abstracts will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <script>
        // Load abstracts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadAbstracts();
        });

        // Load all abstracts
        function loadAbstracts() {
            fetch('dashboard.php?action=get_abstracts')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const tableBody = document.getElementById('abstractsTableBody');
                    tableBody.innerHTML = '';
                    
                    data.forEach(abstract => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${abstract.id}</td>
                            <td>${abstract.name}</td>
                            <td>${abstract.college}</td>
                            <td>${abstract.branch}</td>
                            <td>${abstract.title}</td>
                            <td>${new Date(abstract.date).toLocaleString()}</td>
                            <td class="action-buttons">
                                <a href="dashboard.php?id=${abstract.id}"><button>View Details</button></a>
                                <a href="dashboard.php?id=${abstract.id}&pdf=1"><button class="pdf-button">PDF</button></a>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading abstracts:', error);
                    alert('Error loading abstracts. Please check console for details.');
                });
        }

        // Search abstracts
        function searchAbstracts() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            const url = searchTerm === '' 
                ? 'dashboard.php?action=get_abstracts' 
                : `dashboard.php?action=search_abstracts&term=${encodeURIComponent(searchTerm)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const tableBody = document.getElementById('abstractsTableBody');
                    tableBody.innerHTML = '';
                    
                    data.forEach(abstract => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${abstract.id}</td>
                            <td>${abstract.name}</td>
                            <td>${abstract.college}</td>
                            <td>${abstract.branch}</td>
                            <td>${abstract.title}</td>
                            <td>${new Date(abstract.date).toLocaleString()}</td>
                            <td class="action-buttons">
                                <a href="dashboard.php?id=${abstract.id}"><button>View Details</button></a>
                                <a href="dashboard.php?id=${abstract.id}&pdf=1"><button class="pdf-button">PDF</button></a>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error searching abstracts:', error);
                    alert('Error searching abstracts. Please check console for details.');
                });
        }

        // Export current view to PDF
        function exportToPDF() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            let url = 'dashboard.php?pdf_all=1';
            
            if (searchTerm !== '') {
                url += `&search=${encodeURIComponent(searchTerm)}`;
            }
            
            window.location.href = url;
        }

        // Clear search
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            loadAbstracts();
        }
    </script>
</body>
</html>