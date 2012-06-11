<?php
/****************************************************
 some annotations in the correct locale have to be 
 available directly from JavaScript
 They are produced as associated array 'localeList'
 creating the entries via PHP

*****************************************************/

session_start();
$gLanguage = $_SESSION["gLanguage"];
require_once($_SESSION['PM_INCPHP'] . "/common.php");
require_once($_SESSION['PM_INCPHP'] . "/locale/language_" . $gLanguage . ".php");

?>

//<SCRIPT LANGUAGE="Javascript">

<?php 
include_once("js_locales.php")
?>


function PM_Layout(){}


/*************************************************************************************/



//</SCRIPT>