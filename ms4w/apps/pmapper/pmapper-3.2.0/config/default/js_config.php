<?php
/****************************************************

 JavaScript specific configuration settings

*****************************************************/

session_start();

require_once($_SESSION['PM_INCPHP'] . "/common.php");
$gLanguage = $_SESSION['gLanguage'];
require_once($_SESSION['PM_INCPHP'] . "/locale/language_" . $gLanguage . ".php");

?>


//<script type="text/javascript">

<?php 
//include_once($_SESSION['PM_INCPHP'] . "/js_locales.php");
?>


/*************************************************************
 *                                                           *
 *                     MODIFY HERE BELOW                     *
 *                                                           *
 *************************************************************/

/**
 * Define layout of the application GUI
 */
var Layout = new PM_Layout();

// Master DIV around all other p.mapper DIV elements
// only used if DIV 'pm_master' is existing
Layout.MasterLeft    = 100;
Layout.MasterTop     = 50;
Layout.MasterWidth   = 700;
Layout.MasterHeight  = 600;
Layout.MasterMarginE = 50;
Layout.MasterMarginS = 20;
Layout.MasterResize  = true;

// Top and bottom
Layout.NorthHeight = 0;
Layout.SouthHeight = 35;
Layout.WestWidth   = 0;
Layout.EastWidth   = 250;

// Map Element
Layout.MapNorthHeight  = 34;
Layout.MapSouthHeight  = 34;
Layout.MapWestWidth    = 0;
Layout.MapEastWidth    = 0;
Layout.MapWestEastFull = 1;    // 1: W and E full height of mapZone;  2: N and S full width of mapZone 

// Info Zone, eg. for query results
Layout.InfoZoneHeight = 0;
Layout.InfoZoneStyle  = 2;     // 1: mapZone + east;  2: mapZone + west;  3: full width

// Reference map
Layout.RefZoneVertPos  = 's';  // 'n' or 's'
Layout.RefZoneHorizPos = 'e';  // 'w' or 'e'
Layout.RefZoneHeight   = 160;

// Margins
Layout.MarginOuterVertW  = 8;
Layout.MarginOuterVertE  = 10;
Layout.MarginInnerVertW  = 0;
Layout.MarginInnerVertE  = 8;
Layout.MarginOuterHorizN = 8;
Layout.MarginOuterHorizS = 10;
Layout.MarginInnerHoriz  = 6;


/**
 * Define scale selection list: 
 * ==> adapt to scale range of your data
 * ==> set empty array for disabling function 
 * values can be numbers or numbers containing 1000-separators [. , ' blank]
 */
//var scaleSelectList = []; 
//var scaleSelectList = [5000, 10000, 25000, 50000, 100000, 250000, 500000, 1000000, 2500000]; 
//var scaleSelectList = [100000, 250000, 500000, 1000000, 2500000, 5000000, 10000000, 25000000]; 
//var scaleSelectList = ["100.000", "250.000", "500.000", "1.000.000", "2.500.000", "5.000.000", "10.000.000", "25.000.000"];
//var scaleSelectList = ["100,000", "250,000", "500,000", "1,000,000", "2,500,000", "5,000,000", "10,000,000", "25,000,000"];
//var scaleSelectList = ["100'000", "250'000", "500'000", "1'000'000", "2'500'000", "5'000'000", "10'000'000", "25'000'000"];
var scaleSelectList = ["100 000", "250 000", "500 000", "1 000 000", "2 500 000", "5 000 000", "10 000 000", "25 000 000"];


/**
 * Enable pan mode if right mouse button is pressed
 * independent of selected tool
 */
var enableRightMousePan = true;


/**
 * Define query result layout: tree or table
 */
//var queryResultLayout = 'tree';
var queryResultLayout = 'table';

/**
 * Define tree style for queryResultLayout = 'tree'
 * css: "red", "black", "gray"; default: none; styles defined in /templates/treeview.css
 * treeview:
 *   @option String|Number speed Speed of animation, see animate() for details. Default: none, no animation
 *   @option Boolean collapsed Start with all branches collapsed. Default: true
 *   @option Boolean unique Set to allow only one branch on one level to be open
 *         (closing siblings which opening). Default: true
 */
//var queryTreeStyle = {css: "red", treeview: {collapsed: true, unique: true}};
var queryTreeStyle = {treeview: {collapsed: true, unique: true}};


/**
 * Define if zoom slider is vertical
 */
var zsliderVertical = true;


/**
 * Define if zoom with mouse wheel is always centered 
 * on pointer position (true) or on map center (false = default setting)
 */
var wheelZoomPointerPosition = true;

/**
 * Invert wheel zoom action to follow Google behaviour
 */
var wheelZoomGoogleStyle = false;


/**
 * Decide if SELECT/NQUERY includes also IQUERY (auto-identify)
 */
var combinedSelectIquery = false;

/**
 * Decide if auto-identify shall show pop-up element at mouse pointer
 */
var autoIdentifyFollowMouse = false;


/**
 * Define if internal (default) cursors should be used for mouse cursors
 */
var pmUseInternalCursors = false;


/**
 * Define if select a SUGGEST row will directly launch the search
 */
var pmSuggestLaunchSearch = true;


/**
 * Units for measurement (distance, area)
 */
//var pmMeasureUnits = {distance:" [m]", area:" [m&sup2;]", factor:1}; 
var pmMeasureUnits = {distance:" [m]", area:" [m&sup2;]", factor:0.000017988847};

/**
 * Lines and polygon styles for measurement
 */
var pmMeasureObjects = {line: {color:"#FF0000", width:2}}; 


/**
 * Definitions of context menus
 * parameters for styles are: menuStyle, itemStyle, itemHoverStyle
 * for details see http://www.trendskitchens.co.nz/jquery/contextmenu/
 */
var pmContextMenuList = [     
    {bindto: 'th.grp',        
     menuid: 'cmenu_tocgroup',
     menulist: [   
        {id:'info',   imgsrc:'info-bw.png', text: 'layer_info',  run:'showGroupInfo'},
        {id:'open',   imgsrc:'transparency-bw.png', text: 'Transparency',   run:'cmOpenTranspDlg'},
        {id:'email',  imgsrc:'zoomtolayer-bw.png',  text: 'zoomto_layer',  run:'zoom2group' }], 
     styles: {menuStyle: {width:'auto'}}
    },
    {bindto: 'th.cat',
     menuid: 'cmenu_toccat',
     menulist: [{id:'info',   imgsrc:'info-bw.png', text: 'info',  run:'showCategoryInfo'}], 
     styles: {menuStyle: {width:'auto'}}
    }
];




//</script>
