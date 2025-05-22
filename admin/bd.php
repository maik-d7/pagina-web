
<?php 

$hostDB = "localhost";
$nameDB = "agency_db";
$userDB = "root";
$passDB = "";

try {
    $db = new PDO("mysql:host=$hostDB;dbname=$nameDB", $userDB, $passDB);
    // echo "Conectado"; // Removed for production
} catch(PDOException $e) {
    // echo $e->getMessage(); // Commented out for production, consider logging errors instead
}
?>