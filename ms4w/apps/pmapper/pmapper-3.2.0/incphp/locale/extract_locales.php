<?php



if (!extension_loaded('pdo_sqlite')) {
        if (!dl(stristr(PHP_OS, "WIN") ? "php_pdo.dll" : "pdo.so"))
           exit("Could not load the SQLite extension.\n");
           
        if (!dl(stristr(PHP_OS, "WIN") ? "php_pdo_sqlite.dll" : "pdo_sqlite.so"))
           exit("Could not load the SQLite extension.\n");
}



$langList = array("en", "de", "it", "fr", "se", "nl", "cz", "br", "sk", "es", "ru");
//$langList = array("cz");
    
$dsn = "sqlite:localedb.db";
$dbh = new PDO($dsn);

foreach ($langList as $lang) {
    //$sql = "SELECT base, $lang  FROM locales";
    $sql = "SELECT base, $lang  FROM locales WHERE def = 1 ORDER BY UPPER(base)";
    $sth = $dbh->prepare($sql);
    $sth->execute();
    
    $result = $sth->fetchAll(PDO::FETCH_NUM);

    $rstr = '<?php' . "\n";
    foreach ($result as $row) {
        if (strlen($row[1]) > 0) { 
            //$rstr .= '$_sl[\'' . $r[0] . "'] = '" . $r[1] . "';" . "\n";
            $rstr .= '$_sl[\'' . $row[0] . "'] = '" . addslashes($row[1]) . "';" . "\n";            
        }
    }

    
    $rstr .= "\n" . '?>';
    //echo $rstr;
    
    $fh = fopen("language_$lang.php", "w+");
    fwrite($fh, $rstr);    
    fclose($fh);    
}

$dbh = null;

?>