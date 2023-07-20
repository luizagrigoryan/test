<?php

header('Content-type: image/png');
$image = imagecreate(200, 100);
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
imagestring($image, 5, 50, 40, 'Hello, PHP!', $text_color);
imagepng($image);
imagedestroy($image);

// Replace these variables with your MySQL server details
$host = 'localhost';
$username = 'root';
$password = 'root';
$database = 'test-app-2';

function getAllDataFromTable($tableName) {
    global $host, $username, $password, $database;

    try {
        // Step 1: Connect to the MySQL database using PDO
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);

        // Set PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 2: Execute a SELECT query
        $sql = "SELECT * FROM $tableName";
        $stmt = $conn->query($sql);

        // Step 3: Fetch and return the data as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle any errors that occurred during database connection or query execution
        echo 'Error: ' . $e->getMessage();
        return [];
    }
}

function storeAgentDataInTable($data) {
    global $host, $username, $password, $database;

    try {
        // Step 1: Connect to the MySQL database using PDO
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);

        // Set PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Step 2: Check if the IP address exists in the database
        $sql = "SELECT * FROM agent_data WHERE ip_address = :ip_address";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ip_address', $data['ip_address']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // If IP address exists, update views_count and view_date
            $sql = "UPDATE agent_data SET views_count = views_count + 1, view_date = :view_date
                    WHERE ip_address = :ip_address";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ip_address', $data['ip_address']);
            $stmt->bindParam(':view_date', $data['view_date']);
            $stmt->execute();
        } else {
            // If IP address does not exist, insert a new record
            $sql = "INSERT INTO agent_data (ip_address, user_agent, view_date, page_url, views_count)
                    VALUES (:ip_address, :user_agent, :view_date, :page_url, :views_count)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ip_address', $data['ip_address']);
            $stmt->bindParam(':user_agent', $data['user_agent']);
            $stmt->bindParam(':view_date', $data['view_date']);
            $stmt->bindParam(':page_url', $data['page_url']);
            $stmt->bindParam(':views_count', $data['views_count']);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Handle any errors that occurred during database connection or query execution
        echo 'Error: ' . $e->getMessage();
    }
}

$agentData = array(
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'view_date' => date('Y-m-d H:i:s'),
    'page_url' => $_SERVER['REQUEST_URI'],
    'views_count' => 1
);

storeAgentDataInTable($agentData);

// Example usage:
$dataArray = getAllDataFromTable("agent_data");

// Display the result
echo "<pre>";
print_r($dataArray);
echo "</pre>";

?>
