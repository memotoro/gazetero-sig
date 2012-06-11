<?php
if (!extension_loaded('pdo_sqlite')) {
        if (!dl(stristr(PHP_OS, "WIN") ? "php_pdo.dll" : "pdo.so"))
           exit("Could not load the SQLite extension.\n");
           
        if (!dl(stristr(PHP_OS, "WIN") ? "php_pdo_sqlite.dll" : "pdo_sqlite.so"))
           exit("Could not load the SQLite extension.\n");
}

$dsn = "sqlite:localedb.db";
$dbh = new PDO($dsn);


//$langList = array("en", "de", "it", "fr", "se", "nl", "cz", "br", "sk", "es");
$langList = array("ru");

foreach ($langList as $lid) {
    require_once "language_$lid.php";
    foreach($_sl as $k => $s) {
         
        //$sql = "INSERT INTO locales(base, cz) VALUES('$k', '$s')";
        //$sql = "UPDATE locales set cz='" .utf8_decode($s) . "'  WHERE base='$k'";
        $ssl = str_replace("'", "''", $s);
        $sql = "UPDATE locales set $lid='$ssl'  WHERE base='$k'";
        $dbh->exec($sql);

    }
}

$dbh = null;

?>
