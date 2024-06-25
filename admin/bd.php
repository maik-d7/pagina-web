
<?php 

$hostDB = "localhost";
$nameDB = "bd_prueba";
$userDB = "root";
$passDB = "";

try {
    $dbConeccion = new PDO("mysql:host=$hostDB;dbname=$nameDB", $userDB, $passDB);
    echo "Conectado";
} catch(PDOException $e) {
    echo $e->getMessage();
}
?>