<?php

/******************************************************************************
 *
 * Purpose: Initialize application settings and write settings to PHP session
 *          Create legend icons if the map file has been modified
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2006 Armin Burger
 *
 * This file is part of p.mapper.
 *
 * p.mapper is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * p.mapper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/


class Init_map
{
    
    // Class variables
    protected $map;
    protected $mapFile;
    protected $ini;
    protected $gLanguage;
    protected $jsReference;
    protected $cssReference;
    
    
    public function __construct($map, $mapFile, $ini, $gLanguage)
    {
        $this->map = $map;
        $this->mapFile = $mapFile;
        $this->ini = $ini;
        $this->gLanguage = $gLanguage;
    }
    
   /**
    * Initialize all parameters calling all local functions
    *
    */
    public function initAllParameters()
    {
        $this->_initExtParams();
        $this->_initConfig();
        $this->_initGroups();
        $this->_initQuery();
        $this->_initLegend();
        $this->_initDataPath();
        $this->jsReference = $this->initJSReference();
        $this->cssReference = $this->initCSSReference(); 
        $this->jsInitFunctions = $this->_initPlugins();
    }
    
    
   /**
    * Get external parameters via URL (eg from links)
    *
    */    
    function _initExtParams()
    {
        if (isset($_REQUEST['me'])) {
            $ext = explode(',', $_REQUEST['me']);
            $_SESSION['zoom_extparams'] = $ext;
        }
        
        if (isset($_REQUEST['up'])) {
            //$_SESSION['ul'] = $_REQUEST['ul'];
            $pointList = explode("@@@", $_REQUEST['up']);
            foreach($pointList as $p) {
                $upnt = explode("@@", $p);
                $url_points[] = $upnt;
            }
            $_SESSION['url_points'] = $url_points;
        }
        
        if (isset($this->ini['mapZoomToExtent'])) {
            $zoomExtList = explode(":", $this->ini['mapZoomToExtent']);
            $zExt = explode(",", $zoomExtList[0]);
            $_SESSION['zoom_extparams'] = $zExt;
            
            if ((bool)$zoomExtList[1]) {
                $_SESSION['mapMaxExt'] = array (
                    'minx'=>$zExt[0],
                    'miny'=>$zExt[1],
                    'maxx'=>$zExt[2],
                    'maxy'=>$zExt[3]
                );
            }
        }
        
        $_SESSION['geo_scale'] = 0;
        $_SESSION["historyBack"] = array();
        $_SESSION["historyFwd"]  = array();    
    }
    
    

   /**
    * Check INI settings for errors
    */    
    function _initConfig()
    {
        /*** Debug levels ***/
        $_SESSION['debugLevel'] = isset($this->ini['debugLevel']) ? $this->ini['debugLevel'] : 0;
        
        
        /*** Test if resolution tag is set ***/
        if ($this->map->resolution != "96") {
            pm_logDebug(1, "P.MAPPER-ERROR: RESOLUTION tag not set to 96. This value is needed for proper function of PDF print.");
        }

        /*** LAYERS ***/        
        // Test for groups with blanks
        $gList = $this->map->getAllGroupNames();
        foreach ($gList as $gr) {
            if (preg_match('/\s/', $gr)) {
                pm_logDebug(0, "P.MAPPER-ERROR: Group '$gr' defined in the map file has blanks in its name. This is not possible for the p.mapper application. Remove blanks or substitute with e.g. '_'.");
            }
        }
        // Test for layers with blanks
        $gList = $this->map->getAllLayerNames();
        foreach ($gList as $ly) {
            if (preg_match('/\s/', $ly)) {
                pm_logDebug (0,"P.MAPPER-ERROR: Layer '$ly' defined in the map file has blanks in its name. This is not possible for the p.mapper application. Remove blanks or substitute with e.g. '_'.");
            }
        }
        
        /*** Enable map pan with right mouse button pressed ***/
        $_SESSION['enableRightMousePan'] = isset($this->ini['enableRightMousePan']) ? $this->ini['enableRightMousePan'] : 1;
        
        /*** PDF print resolution ***/
        $_SESSION["pdfres"] = isset($this->ini["pdfres"]) ? $this->ini["pdfres"] : 1.5;
        
        /*** p.mapper version ***/
        $_SESSION['pmapper'] = isset($this->ini['pmapper']) ? $this->ini['pmapper'] : 3.1;
        
        /*** DPI for DL ***/
        $_SESSION['dpiLevels'] = isset($this->ini['dpiLevels']) ?  $this->ini['dpiLevels'] : "150 200 300";
        
        /*** PEAR DB class used ***/
        $_SESSION['pearDbClass'] = isset($this->ini['pearDbClass']) ?  $this->ini['pearDbClass'] : "MDB2";
   
        
        /*** USER AGENT (Browser type) ***/
        $ua = $_SERVER["HTTP_USER_AGENT"]; 
        if (preg_match("/gecko/i", $ua)) {
            $_SESSION['userAgent'] = "mozilla";
        } elseif (preg_match("/opera/i", $ua)) {
            $_SESSION['userAgent'] = "opera";
        } elseif (preg_match("/MSIE/i", $ua)) {
            $_SESSION['userAgent'] = "ie";
        }        
        
        
        $_SESSION['web_imagepath'] = str_replace('\\', '/', $this->map->web->imagepath);
        $_SESSION['web_imageurl']  = str_replace('\\', '/', $this->map->web->imageurl);
        
        $ms_Version = ms_GetVersion();
        $_SESSION['MS_VERSION'] = substr($ms_Version, strpos($ms_Version, "version") + 8, 5);
        
    }
    
    
    
   /**
    * Initialize p.mapper-related groups/layers
    */ 
    function _initGroups()
    {
        /*** LAYERS DEFINED MANUALLY ***
        displayed in TOC in this order
        without definition, the order from map file will be taken  */
        
        $mapGrps = $this->map->getAllGroupNames();
        $mapLays = $this->map->getAllLayerNames();
        $GrpLay  = array_merge($mapGrps, $mapLays);
        
        if (isset($this->ini["allGroups"])) {
            $allGroups = preg_split('/[\s,]+/', $this->ini["allGroups"]);
            // Check for errors
            foreach ($allGroups as $g) {
               if (!in_array($g, $GrpLay) ) {
                   pm_logDebug(0, "P.MAPPER-ERROR: Layer/Group '$g' not existing. Check '/config/config.ini' file definition for section 'allGroups'.");
               }
            }
        } else {
            $allGroups = $this->map->getAllGroupNames();
            foreach ($mapLays as $ml) {
                if (!$this->map->getLayerByName($ml)->group) {
                    $allGroups[] = $ml ;
                }
            }
        }
        
        /*** autoidentifygropus: layer where to apply auto_indentify() function ***/
        if (isset($this->ini["autoIdentifyGroups"])) {
            $autoIdentifyGroups = preg_split('/[\s,]+/', $this->ini["autoIdentifyGroups"]);
            // Check for errors
            foreach ($autoIdentifyGroups as $g) {
               if (!in_array($g, $GrpLay) ) {
                   pm_logDebug(1, "P.MAPPER-ERROR: Layer/Group '$g' not existing. Check '/config/config.ini' file definition for section 'autoIdentifyGroups'.");
               }
            }
        } else {
            $autoIdentifyGroups = $allGroups;
        }
        
        $_SESSION['allGroups'] = $allGroups;
        $_SESSION['allGroups0'] = $allGroups;
        $_SESSION['autoIdentifyGroups'] = $autoIdentifyGroups;
        
    
        /*** LAYERS SWITCHED ON BY DEFAULT ***
        default groups, visible at start
        without definition, ALL groups will be set visible   */
        
        // Check if layers are set externally via URL
        if (isset($_REQUEST['dg'])) {
            $defGroupsGET = explode(',', $_REQUEST['dg']);
            $defGroups = array();
            foreach ($defGroupsGET as $gG) {
                if (in_array($gG, $allGroups)) {
                    $defGroups[] = $gG;
                }
            }
            // if no valid layers supplied, take first from ini
            if (count($defGroups) < 1) $defGroups = array($allGroups[0]); 
        
        // Else take them from config.ini settings 
        } elseif (isset($this->ini["defGroups"])) {
            $defGroups = preg_split('/[\s,]+/', $this->ini["defGroups"]);
            // Check for errors
            foreach ($defGroups as $g) {
                if (!in_array($g, $GrpLay )) {
                   pm_logDebug(1, "P.MAPPER-ERROR: Layer/Group '$g' not existing. Check '/config/config.ini' file definition for section 'defGroups'.");
                   $defGroups = $allGroups; //$this->map->getAllGroupNames();
                }
            }
        // Else take all
        } else {
            $defGroups = array(); //$allGroups;
        }
        $_SESSION["defGroups"] = $defGroups;
        
        
        /*** LAYERS DISABLING EACH OTHER ***/
        if (isset($this->ini['mutualDisableList'])) {
            $mutualDisableList = preg_split('/[\s,]+/', $this->ini['mutualDisableList']); 
            foreach ($mutualDisableList as $mg) {
                if (!in_array($mg, $allGroups )) {
                    pm_logDebug(1, "P.MAPPER-ERROR: Layer/Group '$mg' not existing. Check '/config/config.ini' file definition for section 'mutualDisableList'.");
                    $mutualDisableList = array(); 
                }
            }
        } else {
            $mutualDisableList = array(); 
        }
        $_SESSION['mutualDisableList'] = $mutualDisableList; 
        
        
        /*** LAYERS CAUSING MAP TO SWITCH TO ALTERNATIVE IMAGE FORMAT ***/
        if (isset($this->ini['altImgFormatLayers'])) {
            $altImgFormatLayers = preg_split('/[\s,]+/', $this->ini['altImgFormatLayers']); 
            foreach ($altImgFormatLayers as $mg) {
                if (! @$mapLayer = $this->map->getLayerByName($mg)) {
                    pm_logDebug(0, "P.MAPPER-ERROR: Layer/Group '$mg' not existing. Check '/config/config.ini' file definition for section 'altImgFormatLayers'.", 0);
                }
            }
        } else {
            $altImgFormatLayers = 0; 
        }
        $_SESSION['altImgFormatLayers'] = $altImgFormatLayers;
        
        
        /*** Specify GROUP objects ***/
        require_once(PM_INCPHP . "/initgroups.php");
        $iG = new Init_groups($this->map, $allGroups, $this->gLanguage, $this->ini['map2unicode']);    
    }
    
    
   /**
    * Settings for identify/search/select
    *
    */
    function _initQuery()
    {
        // Limit for results of selection with select tool
        $_SESSION["limitResult"] = isset($this->ini["limitResult"]) ? $this->ini["limitResult"] : 300;
        
        //Defines if SELECT function causes feature highlight
        $_SESSION["highlightSelected"] = isset($this->ini["highlightSelected"]) ? $this->ini["highlightSelected"] : 1;
        
        // Highlight color for identify/search zoom in
        $_SESSION["highlightColor"] = isset($this->ini["highlightColor"]) ? $this->ini["highlightColor"] : "0, 255, 255";
        
        // Auto Zoom
        $autoZoom = $this->ini["autoZoom"];
        if (!preg_match ("/search|nquery|off/i", $autoZoom )) {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'autoZoom'. Can only be 'search', 'nquery' or 'off'");
            $autoZoom = "search";    // set to a default
        }
        $_SESSION["autoZoom"] = $autoZoom;
        
        // Zoom All Button
        $zoomAll = $this->ini["zoomAll"];
        if (!preg_match ("/search|nquery|off/i", $zoomAll )) {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'zoomAll'. Can only be 'search', 'nquery' or 'off'");
            $zoomAll = "search nquery";    // set to a default
        }
        $_SESSION["zoomAll"] = $zoomAll;
        
        // Automatically sort result tables of queries
        $_SESSION['alignQueryResults'] = isset($this->ini['alignQueryResults']) ? $this->ini['alignQueryResults'] : 1;
        
        // WINDOW DESIGN: Query results (identify/search) in separate WINDOW or FRAME
        $infoWin = $this->ini["infoWin"];
        if (!preg_match ("/window|frame|dynwin/", $infoWin))  {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'infoWin'. Must be either 'window', 'frame' or 'dynwin'");
            $infoWin = "window";   // set to a default
        }
        $_SESSION["infoWin"] = $infoWin;
        
        
        $_SESSION["layerAutoRefresh"] = isset($this->ini["layerAutoRefresh"]) ? $this->ini["layerAutoRefresh"] : 1;
        
        $_SESSION["pointBuffer"] = isset($this->ini["pointBuffer"]) ? $this->ini["pointBuffer"] : 1000;
        $_SESSION["shapeQueryBuffer"] = isset($this->ini["shapeQueryBuffer"]) ? $this->ini["shapeQueryBuffer"] : 0;
    
    }
    
    

   /**
    * Settings for legend/TOC
    *
    */
    function _initLegend()
    {
        // Style of CATEGORIES
        $catStyle = strtolower($this->ini["catStyle"]);
        if (!preg_match ("/flat|tree|Off/i", $catStyle)) {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'tocStyle'. Must be either 'flat', 'tree' or 'Off'");
            $catStyle = "Off";    // set to a default
        }
        $_SESSION["catStyle"] = $catStyle;     
        
        // Style of GROUPS
        $grpStyle = strtolower($this->ini["grpStyle"]);
        if (!preg_match ("/flat|tree|combi/i", $grpStyle)) {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'grpStyle'. Must be either 'flat', 'tree' or 'combi'");
            $grpStyle = "flat";    // set to a default
        }
        $_SESSION["grpStyle"] = $grpStyle;
        
        // Style of LEGEND
        $legStyle = trim(strtolower($this->ini["legStyle"]));
        if (!preg_match ("/^(attached|swap|popup)$/i", $legStyle)) {
            pm_logDebug(1, "P.MAPPER-ERROR: Wrong entry in '/config/config.ini' file for section 'legStyle'. Must be either attached, swap, or popup");
            $legStyle = "attached";    // set to a default
        }
        $_SESSION["legStyle"] = $legStyle;
        
        // Info/help link for gropus and categories
        $_SESSION['catInfoLink'] = isset($this->ini['catInfoLink']) ? $this->ini['catInfoLink'] : 0;
        $_SESSION['grpInfoLink'] = isset($this->ini['grpInfoLink']) ? $this->ini['grpInfoLink'] : 0;

        
        // LAYERS UPDATED ACCORDING TO SCALE
        $_SESSION["scaleLayers"] = isset($this->ini["scaleLayers"]) ? $this->ini["scaleLayers"] : 1;
        
        // Use categories to thematically group layers 
        $_SESSION["useCategories"] = isset($this->ini["useCategories"]) ? $this->ini["useCategories"] : 0;
        
        // Use checkboxes for categories 
        $_SESSION["catWithCheckbox"] = isset($this->ini["catWithCheckbox"]) ? $this->ini["catWithCheckbox"] : 0;
        
        
        // ICON SIZE AND FORMAT
        $_SESSION["icoW"] = isset($this->ini["icoW"]) ? $this->ini["icoW"] : 18;
        $_SESSION["icoH"] = isset($this->ini["icoH"]) ? $this->ini["icoH"] : 14;

        // Image Formats
        $imgFormat = isset($this->ini["imgFormat"]) ? $this->ini["imgFormat"] : "png";
        $this->map->selectOutputFormat($imgFormat);
        $selectedOutputFormat = $this->map->outputformat;
        $_SESSION["imgFormatExt"] = $selectedOutputFormat->extension;
        $_SESSION["imgFormat"] = $imgFormat;
        
        $altImgFormat = isset($this->ini["altImgFormat"]) ? $this->ini["altImgFormat"] : "jpeg";
        $this->map->selectOutputFormat($altImgFormat);
        $selectedAltOutputFormat = $this->map->outputformat;
        $_SESSION["altImgFormatExt"] = $selectedAltOutputFormat->extension;
        $_SESSION["altImgFormat"] = $altImgFormat;
        
        $printImgFormat = isset($this->ini["printImgFormat"]) ? $this->ini["printImgFormat"] : $imgFormat;
        $this->map->selectOutputFormat($printImgFormat);
        $selectedPrintOutputFormat = $this->map->outputformat;
        $_SESSION["printImgFormatExt"] = $selectedPrintOutputFormat->extension;
        $_SESSION["printImgFormat"] = $printImgFormat;
        
        $printAltImgFormat = isset($this->ini["printAltImgFormat"]) ? $this->ini["printAltImgFormat"] : $altImgFormat;
        $this->map->selectOutputFormat($printAltImgFormat);
        $selectedPrintAltOutputFormat = $this->map->outputformat;
        $_SESSION["printAltImgFormatExt"] = $selectedPrintAltOutputFormat->extension;
        $_SESSION["printAltImgFormat"] = $printAltImgFormat;
        
        
        /*** WRITES LEGEND ICONS ***/
        
        // Check if images have to be created
        // => if map file is newer than last written log file
        $writeNew = 0;
        
        $pwd = str_replace('\\', '/', getcwd());
        $legPath = "$pwd/images/legend/";
        $imgLogFile = $legPath.'createimg.log';
        
        if (!is_file($imgLogFile)) {
            $writeNew = 1;
        } else {
            $mapfile_mtime = filemtime($this->mapFile);
            $imglogfile_mtime = filemtime($imgLogFile);
            if ($mapfile_mtime > $imglogfile_mtime) {
                $writeNew = 1;
            }
        }
        
        // If necessary re-create legend icons
        if ($writeNew == 1) {
            $this->createLegendList();
        }
    }
    
   /**
    * Create all legend icons and group/layer 
    *
    */ 
    public function createLegendList()
    {
        $legPath = $_SESSION['PM_BASE_DIR'] . "/images/legend/";
        $imgLogFile = $legPath.'createimg.log';
        
        $this->map->selectOutputFormat($_SESSION["imgFormat"]);
        $allLayers = $this->map->getAllLayerNames();        

        // Define background image for legend icons
        $icoBGLayer = ms_newLayerObj($this->map);
        $icoBGLayer->set("type", 2);
        // Add class
        $bgClass = ms_newClassObj($icoBGLayer);
        $bgClStyle = ms_newStyleObj($bgClass);
        $bgClStyle->color->setRGB(255, 255, 255);
        $bgClStyle->outlinecolor->setRGB(180, 180, 180);
    
        foreach ($allLayers as $layName) {
            $qLayer = $this->map->getLayerByName($layName);
    
            // All layers but RASTER or ANNOTATION layers        
            $numclasses = $qLayer->numclasses;
            if ($numclasses > 0) {
                $clno = 0;
                for ($cl=0; $cl < $numclasses; $cl++) {
                    $class = $qLayer->getClass($cl);
                    if (!$class->keyimage) {
                        $clname = ($numclasses < 2 ? "" : $class->name);
                        $clStyle = ms_newStyleObj($class);
                        
                        // Set outline for line themes to background color
                        if ($qLayer->type == 1) {
                           #$clStyle->setcolor("outlinecolor", 180, 180, 180);
                           #$clStyle->outlinecolor->setRGB(180, 180, 180);
                        }
                        // set outline to main color if no outline defined (workaround for a bug in the icon creation)
                        if ($qLayer->type == 2) {
                            if ($clStyle->outlinecolor < 1) {
                               #$clStyle->setcolor("outlinecolor", $clStyle->color);
                               $clStyle->outlinecolor->setRGB($clStyle->color);
                            }
                        }
        
                        $icoImg = $class->createLegendIcon($_SESSION["icoW"], $_SESSION["icoH"]);  // needed for ms 3.7
                        $imgFile = $legPath.$layName.'_i'.$clno . '.'.$_SESSION["imgFormatExt"];
                        //error_log($imgFile);
                        
                        $icoUrl = $icoImg->saveImage($imgFile);
                        $icoImg->free();
                    }
                    if ($class->name) $clno++;
                }
            }
        }
      
        $today = getdate();
        $datestr =  $today['hours'].':'.$today['minutes'].':'.$today['seconds'].'; '.$today['mday'].'/'.$today['month'].'/'.$today['year'];
    
        $logStr = "Created legend icons newly on:  $datestr";
        $imgLogFileFH = fopen ($imgLogFile, 'w+');
        fwrite($imgLogFileFH, $logStr);
        fclose($imgLogFileFH);    
        
    }
    
    
    
   /**
    * Get absolute data path from map file
    *
    */ 
    function _initDataPath()
    {
        $shapepath = trim($this->map->shapepath);
        
        // absolute path in map file
        if ($shapepath{0} == "/" || $shapepath{1} == ":") {
            $_SESSION['datapath'] = str_replace('\\', '/', $shapepath);
        
        // relative path in map file, get absolute as combination of shapepath and map file location
        } else {
            $_SESSION['datapath'] = str_replace('\\', '/', realpath(dirname($_SESSION['PM_MAP_FILE']) . "/" . $shapepath) );
        }
    }
    
    
   /**
    * FUNCTION TO RETURN URL FOR MAPFRAME
    * used for starting application with pre-defined extent
    * extent read from shape features
    */
    function getMapInitURL($map, $zoomLayer, $zoomQuery) 
    {
        $qLayer = $this->map->getLayerByName($zoomLayer);
        
        // Query parameters
        $queryList = split('@', $zoomQuery);
        $queryField = $queryList[0];
        $queryFieldType = $queryList[1];
        //$queryValue = "/^" . $queryList[2] ."$/";
        $queryValue = $queryList[2];
        $highlFeature = $queryList[3];
        $setMaxExtent = $queryList[4];
        
        // Modify filter for PostGIS & Oracle layers
        if ($qLayer->connectiontype == 6 || $qLayer->connectiontype == 8) {
            $q = $queryFieldType == 1 ? "'" : "";
            $queryValue = "$queryField = $q$queryValue$q";
            //error_log($queryValue);
        }
        
        // Query layer
        @$qLayer->queryByAttributes($queryField, $queryValue, MS_MULTIPLE);
        $numResults = $qLayer->getNumResults();
        $qLayer->open();
        
        // Return query results (SINGLE FEATURE): shape index and feature extent
        /*
        $qRes = $qLayer->getResult(0);
        $qShape = $qLayer->getShape($qRes->tileindex,$qRes->shapeindex);
        $qShpIdx = $qShape->index;
        $qShpBounds = $qShape->bounds;
        */
        
        // Check if layer has different projection than map
        // if yes, re-project extent from layer to map projection 
        $mapProjStr = $this->map->getProjection();
        $qLayerProjStr = $qLayer->getProjection();
        if ($mapProjStr && $qLayerProjStr && $mapProjStr != $qLayerProjStr) {
            $mapProjObj = ms_newprojectionobj($mapProjStr);
            $qLayerProjObj = ms_newprojectionobj($qLayerProjStr);
            //$qShpBounds->project($this->qLayerProjObj, $this->mapProjObj);
            $reprojectShape = 1;
        }
        
        
        // initial max/min values
        $mExtMinx = 999999999;
        $mExtMiny = 999999999;
        $mExtMaxx = -999999999;
        $mExtMaxy = -999999999;
        
        // ABP: Store all shape indexes
        $qShpIdxArray = array();
        
        // Return query results: shape index and feature extent
        for ($iRes=0; $iRes < $numResults; $iRes++) {
            $qRes = $qLayer->getResult($iRes);
            $qShape = $qLayer->getShape($qRes->tileindex,$qRes->shapeindex);
            $qShpIdx = $qShape->index;
            $qShpIdxArray[] = $qShpIdx;
            $qShpBounds = $qShape->bounds;
            if ($reprojectShape) {
                $qShpBounds->project($qLayerProjObj, $mapProjObj);
            }

            $shpMinx = $qShpBounds->minx;
            $shpMiny = $qShpBounds->miny;
            $shpMaxx = $qShpBounds->maxx;
            $shpMaxy = $qShpBounds->maxy;
            
            // Get max/min values of ALL features
            $mExtMinx = min($mExtMinx, $shpMinx);
            $mExtMiny = min($mExtMiny, $shpMiny);
            $mExtMaxx = max($mExtMaxx, $shpMaxx);
            $mExtMaxy = max($mExtMaxy, $shpMaxy);
        }
        
        // Apply buffer (in units of features)
        if ($qLayer->type == 0) {
            $buffer = $_SESSION["pointBuffer"];
        } else {
            $buffer = $_SESSION["shapeQueryBuffer"] * ((($mExtMaxx - $mExtMinx) + ($mExtMaxy - $mExtMiny)) / 2);
        }
        $mExtMinx -= $buffer;
        $mExtMiny -= $buffer;
        $mExtMaxx += $buffer;
        $mExtMaxy += $buffer;
        
        $roundFact = ($map->units != 5 ? 0 : 6); 
        $shpMinx = round($mExtMinx, $roundFact);
        $shpMiny = round($mExtMiny, $roundFact);
        $shpMaxx = round($mExtMaxx, $roundFact);
        $shpMaxy = round($mExtMaxy, $roundFact);
                        
        $ext = array ($shpMinx, $shpMiny, $shpMaxx, $shpMaxy);
        $_SESSION['zoom_extparams'] = $ext;
        
        
        // Set Max Extent for map
        if ($setMaxExtent) {
            $mapMaxExt['minx'] = $shpMinx;
            $mapMaxExt['miny'] = $shpMiny;
            $mapMaxExt['maxx'] = $shpMaxx;
            $mapMaxExt['maxy'] = $shpMaxy;
            
            $_SESSION['mapMaxExt'] = $mapMaxExt;
        }

        // Add highlight feature if defined in URL parameters
        if ($highlFeature) { 
            $resultlayers[$zoomLayer] = $qShpIdxArray;
            $_SESSION["resultlayers"] = $resultlayers;
        }
        
        
        // Return URL
        $searchString = "&mode=map&zoom_type=zoomextent&extent=" . $shpMinx ."+". $shpMiny ."+". $shpMaxx ."+". $shpMaxy . ($highlFeature ? "&resultlayer=$zoomLayer+$qShpIdx" : ""); 
        $mapInitURL = "map.phtml?$searchString";
        
        return $mapInitURL;
    }
    
    
   /**
    * Calculate max scale for slider max settings (JS variable s1)
    * works only for units dd or meters
    */
    function returnMaxScale($map, $mapheight)
    {
        $initExtent = $this->map->extent;
        $y_dgeo = $initExtent->maxy - $initExtent->miny;
        $scrRes = $this->map->resolution;
        $this->mapUnits = $this->map->units;
        
        $y_dgeo_m = ($this->mapUnits == 5 ? $y_dgeo * 111120 : $y_dgeo);
        $maxScale = ($y_dgeo_m / $mapheight) / (0.0254 / $scrRes);
        
        return round($maxScale);
    }
    
    function returnXYGeoDimensions()
    {
        //$initExtent = $this->map->extent;
        if (isset($_SESSION['mapMaxExt'])) {
            $me = $_SESSION['mapMaxExt'];
            $initExtent = ms_newrectObj();
            $initExtent->setextent($me["minx"],$me["miny"],$me["maxx"],$me["maxy"]);
        } else {
            $initExtent = $this->map->extent;
        }
        
        $dgeo['x'] = $initExtent->maxx - $initExtent->minx;
        $dgeo['y'] = $initExtent->maxy - $initExtent->miny;
        $dgeo['c'] = $this->map->units == 5 ? 111120 : 1;

        return $dgeo;
    }

    /**
     * Get JS file references
     */
    function initJSReference()
    {
        $jsReference = "";
                 
        //- from jQuery dir
        $jqueryFiles = scandirByExt(PM_JAVASCRIPT_REALPATH ."/jquery", "js");
        sort($jqueryFiles);
        foreach ($jqueryFiles as $jqf) {
            $jsReference .= " <script type=\"text/javascript\" src=\"". PM_JAVASCRIPT ."/jquery/$jqf\"></script>\n";
        }
        
        //- from main JS dir
        $jsFiles = scandirByExt(PM_JAVASCRIPT_REALPATH, "js");
        sort($jsFiles);
        foreach ($jsFiles as $jsf) {
            $jsReference .= " <script type=\"text/javascript\" src=\"". PM_JAVASCRIPT ."/$jsf\"></script>\n";
        }
        
        //- from plugins
        $plugin_jsFileList = $_SESSION['plugin_jsFileList'];
        if (count($plugin_jsFileList) > 0) {
            foreach ($plugin_jsFileList as $pf) {
                $jsReference .= " <script type=\"text/javascript\" src=\"$pf\"></script>\n";
            }
        }
        
        //- from config dir
        $customJSFiles = scandirByExt(PM_CONFIG_DIR, "js");
        if (count($customJSFiles) > 0) {
            foreach ($customJSFiles as $cf) {
                $jsReference .= " <script type=\"text/javascript\" src=\"config/". PM_CONFIG_LOCATION ."/$cf\"></script>\n";
            }
        }
        
        //- from config/common dir
        if (file_exists(PM_BASECONFIG_DIR . "/common")) {
            $customJSFilesCommon = scandirByExt(PM_BASECONFIG_DIR . "/common", "js");
            if (count($customJSFilesCommon) > 0) {
                foreach ($customJSFilesCommon as $cf) {
                    $jsReference .= " <script type=\"text/javascript\" src=\"config/common/$cf\"></script>\n";
                }
            }
        }
        
        return $jsReference;
    }
    
    /**
     * Get CSS file references
     */
    function initCSSReference()
    {
        $plugin_cssFileList = $_SESSION['plugin_cssFileList'];
        if (count($plugin_cssFileList) > 0) {
            foreach ($plugin_cssFileList as $pf) {
                $cssReference .= " <link rel=\"stylesheet\" href=\"$pf\" type=\"text/css\" />\n";
            }
        }
        
        //- from config/common dir
        if (file_exists(PM_BASECONFIG_DIR . "/common")) {
            $cssFilesCommon = scandirByExt(PM_BASECONFIG_DIR . "/common", "css");
            if (count($cssFilesCommon) > 0) {
                foreach ($cssFilesCommon as $cf) {
                    $cssReference .= " <link rel=\"stylesheet\" href=\"config/common/$cf\" type=\"text/css\" />\n";
                }
            }
        }
        
        $cssFiles = scandirByExt(PM_CONFIG_DIR, "css");
        if (count($cssFiles) > 0) {
            foreach ($cssFiles as $cf) {
                $cssReference .= " <link rel=\"stylesheet\" href=\"config/". PM_CONFIG_LOCATION ."/$cf\" type=\"text/css\" />\n";
            }
        }
        
        return $cssReference;
    }

    function _initPlugins()
    {
        $plugin_jsInitList = $_SESSION['plugin_jsInitList'];
        if (count($plugin_jsInitList) > 0) {
            foreach ($plugin_jsInitList as $jsI) {
                $jsInitFunctions .= "$jsI;\n";
            }
        }
        return $jsInitFunctions;
    }
    
    function returnJSConfigReference()
    {
        $jsConfRef  = "<script type=\"text/javascript\" src=\"" . PM_INCPHP_LOCATION . "/js_custom.php?" . SID . "\"></script>\n";
        
        if (file_exists(PM_BASECONFIG_DIR . "/common/js_config.php")) {
            $jsConfRef .= " <script type=\"text/javascript\" src=\"config/common/js_config.php?" . SID . "\"></script>\n";
        }
        
        if (file_exists(PM_BASECONFIG_DIR . "/" . PM_JS_CONFIG)) {
            $jsConfRef .= " <script type=\"text/javascript\" src=\"config/" . PM_JS_CONFIG . "?" . SID . "\"></script>\n";
        }
        
        return $jsConfRef;
    }
    
    function returnJSReference()
    {
        return $this->jsReference;
    }
    
    function returnCSSReference()
    {
        return $this->cssReference;
    }
    
    function returnjsInitFunctions()
    {
        return $this->jsInitFunctions;
    }
}

?>