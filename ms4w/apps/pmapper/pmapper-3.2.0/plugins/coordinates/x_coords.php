<?php
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

session_start();
require_once($_SESSION['PM_INCPHP'] . "/globals.php");
require_once("projection.php");
require("projections.inc");

$clickX = $_REQUEST['x'];
$clickY = $_REQUEST['y'];

$fromPrj = $map->getProjection();

$prjJson = "[";
$prjJson .= "{\"prjName\": \"$mapPrj\", \"x\": $clickX, \"y\": $clickY},";

foreach ($prjList as $prjN=>$toPrj) {
    $prj = new Projection($clickX, $clickY, $fromPrj, $toPrj);
    $x = $prj->getX();
    $y = $prj->getY();
    
    //round values
    if ($prjN != "latlon") {
        $x = round($x, 0);
        $y = round($y, 0);
    } else {
        $x = round($x, 6);
        $y = round($y, 6);
    }
    
    $prjJson .= "{\"prjName\": \"$prjN\", \"x\": $x, \"y\": $y},";
}
$prjJson = substr($prjJson, 0, -1) . "]";
//error_log($prjJson);

header("Content-Type: text/plain; charset=$defCharset");

// return JS object literals "{}" for XMLHTTP request 
echo "{\"prjJson\": $prjJson}";
?>