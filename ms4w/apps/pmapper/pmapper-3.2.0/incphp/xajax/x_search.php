<?php
session_start();
require_once("../globals.php");
require_once("../common.php");
require_once("../query/search.php");


$action = $_REQUEST['action'];

$search = new XML_search($_SESSION['PM_SEARCH_CONFIGFILE']);

if ($action == 'optionlist') {
    $search->validateSearchXML();
    $searchJson =  $search->createSearchOptions();
    $divelem = '1';
    //error_log("optionlist");
    //error_log($shtml);
} else {
    $searchitem = $_REQUEST['searchitem'];
    $searchJson = $search->createSearchItem($searchitem);
    $divelem = '2';
}

//error_log($searchJson);

header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{searchJson:$searchJson, action:'$action', divelem:'$divelem'}";
?>