<?php

$gLanguage = $_SESSION["gLanguage"];


/**
 * Definition of categories for legend/TOC
 */
//$categories['cat_admin']  = array("countries", "cities10000eu", "settlements");
//$categories['cat_nature'] = array("rivers", "corine");
$categories['cat_raster'] = array("MOSAICO_LANDSAT"); 
$categories['cat_admin'] = array("DEPARTAMENTO","MUNICIPIO","LOCALIDAD"); 

$_SESSION['categories'] = $categories;


//$categories_pool['cat_admin']  = array("countries", "cities1000eu", "communes", "settlements", "cities_xylayer");
//$categories_pool['cat_nature'] = array("rivers", "corine");
$categories_pool['cat_raster'] = array("MOSAICO_LANDSAT");
//$categories_pool['cat_social'] = array("income", "population");
$categories_pool['cat_admin']  = array("DEPARTAMENTO","MUNICIPIO","LOCALIDAD"); 


/**
 * TOOLBAR BUTTONS
 * - First array element: text for tool tip (=> img 'alt' or 'title')
 * - Tools/buttons that change status (zoom, pan, identify, select, measure):
 *     => have "0" as last array element
 *     => are using javascript function "domouseclick('zoom')" in mapserver.js
 * - Tools/buttons that keep status (zoomfullext, print, back):
 *     => have the name of the javascript function as last array element
 * - spaceX: space between buttons, space in pixels as argument
 *     => need to have unique names (e.g. space1, space2, space...)
 * - separatorX: separator (line) between buttons, argument will be ignored
 *     => need to have unique names (e.g. separator1, separator2, separator...)
 */
$buttons = array (
    "home"        => array(_p("Zoom To Full Extent"), "zoomfullext"),
    "back"        => array(_p("Back"), "goback"),
    "fwd"         => array(_p("Forward"), "gofwd"),
//  "zoomselected" => array(_p("Zoom To Selected"), "zoom2selected"),
    "separator1"  => "1",
    "zoomin"      => array(_p("Zoom in"), "0"),
    "zoomout"     => array(_p("Zoom out"), "0"),
    //"space2"   => "15",
    "pan"         => array(_p("Pan"), "0"),
    "separator2"  => "1",
    "identify"    => array(_p("Identify"), "0"),
    "select"      => array(_p("Select"), "0"),
    "auto_identify"    => array(_p("Auto Identify"), "0"),
    "separator3"  => "1",
    "measure"     => array(_p("Measure"), "0"),
//   "poi"         => array(_p("Add Point of Interest"), "0"),
//   "coords"      => array(_p("Show Coordinates"), "0"),
//   "separator4"  => "1",
    "transparency" => array(_p("Transparency"), "openTransparencyDlg"),
    "reload"      => array(_p("Refresh Map"), "clearInfo")
    //"help"       => array(_p("Help"), "openHelp")
    //"download"    => array(_p("Download"), "openDownloadDlg")
);


$buttons2 = array (
    "home"        => array(_p("Zoom To Full Extent"), "zoomfullext"),
    "back"        => array(_p("Back"), "goback"),
    "fwd"         => array(_p("Forward"), "gofwd")
);


/**
 * List for menu; structure similat to toolbar definition
 * JavaScript functions without trailing parentheses '()'
 */
$menu1 = array (
    "print"       => array(_p("Print Map"), "openPrintDlg"),
    "download"    => array(_p("Download"), "openDownloadDlg"),
    "help"        => array(_p("Help"), "openHelp"),
	"creditos"    => array(_p("Creditos"), "openCreditos")
    //"transparency"       => array(_p("Transparency"), "openTransparencyDlg")
);


/**
 * List for menu; structure similat to toolbar definition
 * JavaScript functions without trailing parentheses '()'
 */
$toctabs = array (    
	"toc"       => array(_p("Layers"), "swapToLayerView"),
	"toclegend" => array(_p("Legend"), "swapToLegendView")
);


/**
 * Define which toolbar theme (folder under "/images/buttons") to use for toolbar
 */
$toolbarTheme = "theme2";//"default"; //"chameleon"; //"theme2_mono";

/**
 * Define if image src in toolbar shall swap between '*_on' '*_off' '*_over'
 * $toolbarImgType in case not using GIF files
 */
$toolbarImgSwap = 0; 
//$toolbarImgType = "png";


/**
 * Title and Heading of application and print
 */
/*
$pmTitle = "p.mapper $PM_VERSION: " . _p("MapServer PHP/MapScript Framework");

$pmHeading = "<a href=\"http://mapserver.gis.umn.edu\" id=\"mshref_1\" title=\"UMN MapServer homepage\" onclick=\"this.target = '_new';\">MapServer</a>&nbsp; 
              <a href=\"http://www.dmsolutions.ca\" id=\"dmsol_href\" title=\"DM Solutions homepage\" onclick=\"this.target = '_new';\">PHP/MapScript</a>&nbsp; 
              Framework, v3.2";

$pmPrintTitle = $pmTitle;
*/
$pmTitle = "";

$pmHeading = "";

$pmPrintTitle = $pmTitle;
// $pmLogoUrl = "";
// $pmLogoTitle = "";
// $pmLogo = "";

?>
