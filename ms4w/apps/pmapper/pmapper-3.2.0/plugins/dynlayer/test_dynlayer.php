<?php

require_once("dynlayer.php");

require_once("benchmark/Timer.php");
$timer = new Benchmark_Timer(0);
$timer->start();

/**
 * INITIALIZE MAP
 */
$PM_MAP_FILE = "D:/webdoc/eclipse/pmapper_test/config/default/pmapper_demo.map";
$map = ms_newMapObj($PM_MAP_FILE);


$jsonFile = "dynlayer_def0.txt";
session_start();
$_SESSION['dynLayers'] = preg_replace(array("/\s{2,}/", "/\n/"), array(" ", ""), file_get_contents($jsonFile));

$dyn = new DynLayer($map, $_SESSION['dynLayers']);
$dyn->createDynLayers();

$map->getLayerByName("cities_1")->set("status", MS_ON);
//$map->getLayerByName("cities_2")->set("status", MS_ON);
$map->getLayerByName("countries")->set("status", MS_ON);






//print_r($map->getAllLayerNames());
$map->selectOutputFormat("png");
$map_img = $map->draw();
$map_url = $map_img->saveWebImage();
$map_img->free();

$timer->stop();
$timer->display();

$map->save("D:/webdoc/tmp/dynlayer.map");

?>