<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'reserve');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db(DB_NAME);

// Create Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'forest manager', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create Forest_Reserves table
$sql = "CREATE TABLE IF NOT EXISTS forest_reserves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserve_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_location (location)
)";

if ($conn->query($sql) === TRUE) {
    echo "Forest_Reserves table created successfully<br>";
} else {
    echo "Error creating forest_reserves table: " . $conn->error . "<br>";
}

// Create Trees table
$sql = "CREATE TABLE IF NOT EXISTS trees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserve_id INT NOT NULL,
    species VARCHAR(100) NOT NULL,
    MTH DECIMAL(10,4) NOT NULL,
    THT DECIMAL(10,4) NOT NULL,
    DBH DECIMAL(10,4) NOT NULL,
    basal_area DECIMAL(10,6) NOT NULL,
    volume DECIMAL(10,6) NOT NULL,
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_reserve_id (reserve_id),
    INDEX idx_species (species),
    INDEX idx_status (status)
)";

if ($conn->query($sql) === TRUE) {
    echo "Trees table created successfully<br>";
} else {
    echo "Error creating trees table: " . $conn->error . "<br>";
}

// Create Transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tree_id INT NOT NULL,
    user_id INT NOT NULL,
    reserve_id INT NOT NULL,
    payment_code VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('failed', 'completed') DEFAULT 'failed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tree_id) REFERENCES trees(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_tree_id (tree_id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_code (payment_code),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "Transactions table created successfully<br>";
} else {
    echo "Error creating transactions table: " . $conn->error . "<br>";
}

// Create Illegal_Reports table
$sql = "CREATE TABLE IF NOT EXISTS illegal_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    coordinates VARCHAR(100) NOT NULL,
    reserve_id INT NOT NULL,
    date_reported TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    resolution_notes TEXT,
    FOREIGN KEY (reserve_id) REFERENCES forest_reserves(id) ON DELETE CASCADE,
    INDEX idx_reserve_id (reserve_id),
    INDEX idx_status (status),
    INDEX idx_date_reported (date_reported)
)";

if ($conn->query($sql) === TRUE) {
    echo "Illegal_Reports table created successfully<br>";
} else {
    echo "Error creating illegal_reports table: " . $conn->error . "<br>";
}

// Create Logs table
$sql = "CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
)";

if ($conn->query($sql) === TRUE) {
    echo "Logs table created successfully<br>";
} else {
    echo "Error creating logs table: " . $conn->error . "<br>";
}

// Insert sample data for Idanre Forest Reserve
$forestReserveId = 1;
$forestReserveName = "Idanre Forest Reserve";
$forestReserveLocation = "Ondo State, Nigeria";
$forestReserveDescription = "One of the most biodiverse forest ecosystems in Nigeria, covering approximately 18,168 hectares of land.";

// Check if Idanre Forest Reserve already exists
$sql = "SELECT id FROM forest_reserves WHERE reserve_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $forestReserveName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Insert Idanre Forest Reserve
    $sql = "INSERT INTO forest_reserves (reserve_name, location, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $forestReserveName, $forestReserveLocation, $forestReserveDescription);
    
    if ($stmt->execute()) {
        $forestReserveId = $conn->insert_id;
        echo "Idanre Forest Reserve added successfully<br>";
    } else {
        echo "Error adding Idanre Forest Reserve: " . $stmt->error . "<br>";
    }
} else {
    $row = $result->fetch_assoc();
    $forestReserveId = $row['id'];
}

// Sample tree data for Idanre Forest Reserve
$treeData = [
    [13,17,0.27,0.05726295,0.602173857],
    [17,24,0.159,0.019858226,0.288385328],
    [19,27,0.222,0.038712582,0.642603411],
    [21,31,0.27,0.05726295,0.990351069],
    [25,33.5,0.254,0.050677318,1.190263699],
    [19,29,0.143,0.01606269,0.299857948],
    [12,18.5,0.175,0.024055938,0.291431824],
    [18,26.5,0.206,0.033333478,0.431219077],
    [18,24.5,0.206,0.033333478,0.270520243],
    [20,26.5,0.245,0.047149638,0.738643419],
    [16,18.6,0.159,0.019858226,0.231186082],
    [19,21.5,0.2,0.03142,0.326340099],
    [11.5,13.5,0.14,0.0153958,0.083739995],
    [18.5,23.5,0.184,0.026593888,0.2823773],
    [14,18.5,0.127,0.01266933,0.113543829],
    [20.5,23.5,0.152,0.018148192,0.179780789],
    [22,28.5,0.229,0.041192406,0.498866337],
    [10,12.7,0.159,0.019858226,0.082648255],
    [23,30.5,0.232,0.042278752,0.523940021],
    [21,27.2,0.248,0.048311392,0.730546839],
    [23,28.5,0.248,0.048311392,0.793539396],
    [22,28.5,0.248,0.048311392,0.789763498],
    [21.5,29.5,0.28,0.0615832,0.839607858],
    [9,13.5,0.143,0.01606269,0.143830745],
    [12,18.5,0.172,0.023238232,0.22853841],
    [14,21,0.261,0.053509046,0.508072397],
    [16,20,0.238,0.044493862,0.462107032],
    [14,19.6,0.203,0.03236967,0.318939393],
    [22,27.8,0.257,0.05188149,0.632473053],
    [13,17.6,0.159,0.019858226,0.196655476],
    [12,17,0.175,0.024055938,0.224162979],
    [10.5,15.5,0.143,0.01606269,0.192021955],
    [8,11.5,0.095,0.007089138,0.064059292],
    [24.5,31.5,0.271,0.057687906,1.25347655],
    [11,14,0.152,0.018148192,0.160072332],
    [15,19,0.149,0.017438886,0.233919151],
    [14,18.5,0.159,0.019858226,0.240122637],
    [12,16.5,0.127,0.01266933,0.146415433],
    [13,15,0.127,0.01266933,0.168293375],
    [12,17.8,0.184,0.026593888,0.366141685],
    [16,21.3,0.181,0.025733766,0.455354978],
    [14,17.4,0.159,0.019858226,0.215560131],
    [11,15.1,0.143,0.01606269,0.163089438],
    [7,13.2,0.127,0.01266933,0.113897343]
];

// Insert trees data
$species = "Tectona Grandis";
$status = "available";

$treeInsertSql = "INSERT INTO trees (reserve_id, species, MTH, THT, DBH, basal_area, volume, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$treeStmt = $conn->prepare($treeInsertSql);
$treeStmt->bind_param("isdssddd", $forestReserveId, $species, $MTH, $THT, $DBH, $basal_area, $volume, $status);

$treeCount = 0;
foreach ($treeData as $data) {
    list($MTH, $THT, $DBH, $basal_area, $volume) = $data;
    
    if ($treeStmt->execute()) {
        $treeCount++;
    } else {
        echo "Error inserting tree  " . $treeStmt->error . "<br>";
    }
}

echo "Inserted $treeCount trees for Idanre Forest Reserve<br>";

// Insert admin user
$adminName = "Admin User";
$adminEmail = "admin@timberguard.com";
$adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
$adminRole = "admin";

// Check if admin user already exists
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $adminName, $adminEmail, $adminPassword, $adminRole);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . $stmt->error . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

// Insert forest manager user
$managerName = "Forest Manager";
$managerEmail = "meetmarvelous@gmail.com";
$managerPassword = password_hash("manager123", PASSWORD_DEFAULT);
$managerRole = "forest manager";

// Check if forest manager user already exists
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $managerEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $managerName, $managerEmail, $managerPassword, $managerRole);
    
    if ($stmt->execute()) {
        echo "Forest manager user created successfully<br>";
    } else {
        echo "Error creating forest manager user: " . $stmt->error . "<br>";
    }
} else {
    echo "Forest manager user already exists<br>";
}

echo "<br>Database setup completed successfully!";

$conn->close();
?>