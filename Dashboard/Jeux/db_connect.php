 <?php
$servername = "mysql5-5.pro";
$username = "autonomiu_sql";
$password = "qv33ekb";

try {
    $bdd = new PDO("mysql:host=https://phpmyadmin.cluster005.hosting.ovh.net/index.php?pma_username=mysql5-5.pro&pma_servername=autonomiu_sql.mysql.db&pma_password=qv33ekb", $username, $password);
    // set the PDO error mode to exception
    //$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //  echo "Connected successfully";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?> 

// https://phpmyadmin.cluster005.hosting.ovh.net/index.php?pma_username=autonomiu_sql&pma_servername=nmysql5-5.pro.mysql.db&pma_password=qv33ekb