<?php

/******************************************************************************
 *
 * Purpose: Initializes basic configuration settings and writes 
 *          settings to PHP session. 
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


/************************************************************************
   
   !!! THIS FILE IS NOT A CONFIG FILE FOR CUSTOMIZATION !!!
   !!! DO NOT EDIT THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING !!!
   
 ************************************************************************/

/**
 * Check for register_globals set to On
 */
if (ini_get('register_globals')) {
    echo "!!! 'register_globals' is enabled in your php.ini. This is a severe security leak!!!";
    exit();
}

/**
 * Reset SESSION if requested
 */
if (isset($_GET["resetsession"]))  {
	if (strtoupper(trim($_GET["resetsession"])=="Y")) {
		$_SESSION = array();		
		$_SESSION["session_alive"] = 1;
	}
}


/**
 * Config settings
 */

// Check if config is set via URL
if (isset($_REQUEST['config'])) {
    // Check for invalid GET variables
    if (preg_match("/\/|\./", $_REQUEST['config'])) exit();
    $config = trim($_REQUEST['config']);
} elseif (isset($_SESSION['config'])) {
    $config = $_SESSION['config'];
} else {
    $config = "default";
}

$PM_BASECONFIG_DIR = str_replace('\\', '/', dirname(__FILE__));
$PM_INI_FILE = $PM_BASECONFIG_DIR . "/config_$config.ini";
if (file_exists($PM_INI_FILE)) {
    $iniConfig = parse_ini_file($PM_INI_FILE);
}
if (file_exists($PM_BASECONFIG_DIR . "/config_common.ini")) {
    $iniCommon = parse_ini_file($PM_BASECONFIG_DIR . "/config_common.ini");
    if (isset($iniConfig)) {
        $ini = array_merge($iniCommon, $iniConfig);
    } else {
        $ini = $iniCommon;
    }
} else {
    $ini = $iniConfig;
}

if (isset($ini['pm_config_location'])) {
    $PM_CONFIG_LOCATION  = $ini['pm_config_location'];
} else {
    $PM_CONFIG_LOCATION  = "default";
}

$PM_CONFIG_DIR = str_replace('\\', '/', realpath($PM_BASECONFIG_DIR . "/" . $PM_CONFIG_LOCATION));
$PM_JS_CONFIG  = $PM_CONFIG_LOCATION . "/" . "js_config.php";
$PM_PHP_CONFIG = $PM_CONFIG_LOCATION . "/" . "php_config.php";
$PM_BASE_DIR   = str_replace('\\', '/', realpath($PM_BASECONFIG_DIR . "/../"));

/**
 * MapServer version 
 */
if (isset($ini["msVersion"])) {
    $msVersion = "_" . $ini["msVersion"];
} else {
    $msVersion = "";
}



/**
 * Map file
 */
$mapFileIni = trim($ini["mapFile"]);
// try as absolute path
if ($mapFileIni{0} == "/" || $mapFileIni{1} == ":") {
    $PM_MAP_FILE = str_replace('\\', '/', $mapFileIni);
// else as relative to $PM_CONFIG_DIR
} else {
    $PM_MAP_FILE = str_replace('\\', '/', realpath($PM_BASECONFIG_DIR . "/" . $mapFileIni));
    if (!file_exists($PM_MAP_FILE)) {
        $PM_MAP_FILE = str_replace('\\', '/', realpath($PM_CONFIG_DIR . "/" . $mapFileIni));
    }
}

if (!file_exists($PM_MAP_FILE)) {
    error_log ("P.MAPPER-ERROR: Cannot find map file '$mapFileIni'. Check 'config.ini' file at section 'mapFile'.", 0);
    exit();
}


/**
 * INITIALIZE MAP
 */
/*
if (!extension_loaded('MapScript')) {
    dl("php_mapscript$msVersion." . PHP_SHLIB_SUFFIX);
}
$map = ms_newMapObj($PM_MAP_FILE);
$web_imagepath = str_replace('\\', '/', $map->web->imagepath);
$mapTmpFile = $web_imagepath . session_id() . ".map";
$map->save($mapTmpFile);
*/



/**
 * Map file for query result highlighting
 */
if (isset($ini["hlMapFile"])) { 
    $hlMapFileIni = trim($ini["hlMapFile"]);
    // try as absolute path
    if ($hlMapFileIni{0} == "/" || $hlMapFileIni{1} == ":") {
        $PM_HL_MAP_FILE = str_replace('\\', '/', $hlMapFileIni);
    // else as relative to $PM_CONFIG_DIR
    } else {
        $PM_HL_MAP_FILE = str_replace('\\', '/', realpath($PM_BASECONFIG_DIR . "/" . $hlMapFileIni));  
    }
    if (!file_exists($PM_HL_MAP_FILE)) {
        error_log ("P.MAPPER-ERROR: Cannot find map file '$hlMapFileIni'. Check 'config.ini' file at section 'hlMapFile'.", 0);
        exit();
    }
} else {
    $PM_HL_MAP_FILE = 0;
}

/**
 * Set character set for correct display of special characters
 */
if (isset($ini['defCharset'])) {
    $defCharset = $ini['defCharset'];
} else {
    $defCharset = "UTF-8";
}


/**
 * WEB location INCPHP 
 */
if (isset($ini['pm_incphp_location'])) {
    $PM_INCPHP_LOCATION  = trim($ini['pm_incphp_location']);
    $PM_INCPHP = str_replace('\\', '/', realpath(dirname(__FILE__) . "/../" . $PM_INCPHP_LOCATION));
    if (!$PM_INCPHP) {
        error_log("P.MAPPER-ERROR: location '$PM_INCPHP_LOCATION' not existing. Check config at section 'pm_incphp_location'");
        exit();
    }
} else {
    $PM_INCPHP_LOCATION = "incphp";
    $PM_INCPHP = str_replace('\\', '/', realpath(dirname(__FILE__) . "/../incphp"));
} 


/**
 * Directory location XAJAX 
 */
$PM_XAJAX_LOCATION = $PM_INCPHP_LOCATION . "/xajax/";



/**
 * Directory JAVASCRIPT 
 */
if (isset($ini['pm_javascript_location'])) {
    $PM_JAVASCRIPT  = trim($ini['pm_javascript_location']);
} else {
    $PM_JAVASCRIPT = "javascript";
}

if (!realpath(dirname(__FILE__) . "/../" . $PM_JAVASCRIPT)) {
    error_log("P.MAPPER-ERROR: location '$PM_JAVASCRIPT' not existing. Check config at section 'pm_javascript'");
    exit();
} else {
    $PM_JAVASCRIPT_REALPATH = str_replace('\\', '/', realpath(dirname(__FILE__) . "/../" . $PM_JAVASCRIPT));
}


/**
 * Directory PLUGINS 
 */
if (isset($ini['pm_plugin_location'])) {
    $PM_PLUGIN_LOCATION  = trim($ini['pm_plugin_location']);
} else {
    $PM_PLUGIN_LOCATION = "plugins";
}

if (!realpath(dirname(__FILE__) . "/../" . $PM_PLUGIN_LOCATION)) {
    error_log("P.MAPPER-ERROR: location '$PM_PLUGIN_LOCATION' not existing. Check config at section 'pm_javascript'");
    exit();
} else {
    $PM_PLUGIN_REALPATH = str_replace('\\', '/', realpath(dirname(__FILE__) . "/../" . $PM_PLUGIN_LOCATION));
}



/**
 * Application language
 */
if (isset($_REQUEST['language'])) {
    $gLanguage = trim($_REQUEST['language']);
} elseif (isset($_SESSION['language'])) {
    $gLanguage = trim($_SESSION['language']);
} elseif (isset($ini['pm_default_language'])) {
    $gLanguage = trim($ini['pm_default_language']);
} elseif (isset($_ENV['PM_DEFAULT_LANGUAGE'])) {
    $gLanguage = trim($_ENV['PM_DEFAULT_LANGUAGE']);
} else {
    $gLanguage = "en";   // default language
}


/**
 * Config file for attribute search, default = search.xml
 */
if (isset($ini['pm_search_configfile'])) {
    $pm_search_configfile_ini = $ini['pm_search_configfile'];
    if ($pm_search_configfile_ini{0} == "/" || $pm_search_configfile_ini{1} == ":") {
        $PM_SEARCH_CONFIGFILE = $ini['pm_search_configfile'];
    } else {
        $PM_SEARCH_CONFIGFILE = $PM_CONFIG_DIR . "/". $ini['pm_search_configfile'];
    }
} else {
    $PM_SEARCH_CONFIGFILE = $PM_CONFIG_DIR . "/search.xml";
}

if (!is_file($PM_SEARCH_CONFIGFILE)) {
    error_log("P.MAPPER-ERROR: Wrong entry for 'pm_search_configfile' in config. File not existing");
}


/**
 * Config file for printing, default = print.xml
 */
if (isset($ini['pm_print_configfile'])) {
    $pm_search_configfile_ini = $ini['pm_search_configfile'];
    if ($pm_search_configfile_ini{0} == "/" || $pm_search_configfile_ini{1} == ":") {
        $PM_PRINT_CONFIGFILE = $ini['pm_search_configfile'];
    } else {
        $PM_PRINT_CONFIGFILE = $PM_BASECONFIG_DIR . "/". $ini['pm_print_configfile'];
    }
} else {
    $PM_PRINT_CONFIGFILE = $PM_BASECONFIG_DIR . "/common/print.xml";
}

if (!is_file($PM_PRINT_CONFIGFILE)) {
    error_log("P.MAPPER-ERROR: Wrong entry for 'pm_search_configfile' in config. File not existing");
}


/**
 * Plugin definitions
 */
$plugin_jsInitList = array();
$plugin_jsTocInitList = array();
$plugin_jsMapUpdateList = array();
$plugin_jsFileList = array();
$plugin_phpFileList = array();

if (isset($ini["plugins"])) {
    $plugins = preg_split('/[\s,]+/', $ini["plugins"]);  
    foreach ($plugins as $p) {
        $pluginDir = "$PM_PLUGIN_REALPATH/$p"; 
        
        if (is_dir($pluginDir)) {
            if (is_file("$pluginDir/config.inc")) {
                include_once("$pluginDir/config.inc");
                
                
                if (isset($jsInitFunction)) {
                    $plugin_jsInitList[] = $jsInitFunction;
                }
                unset($jsInitFunction);
                
                if (isset($jsTocInitFunction)) {
                    $plugin_jsTocInitList[] = $jsTocInitFunction;
                }
                unset($jsTocInitFunction);
                
                if (isset($jsMapUpdateFunction)) {
                    $plugin_jsMapUpdateList[] = $jsMapUpdateFunction;
                }
                unset($jsMapUpdateFunction);
                
                if (isset($jsFiles)) {
                    foreach($jsFiles as $jf) {
                        $plugin_jsFileList[] = "$PM_PLUGIN_LOCATION/$p/$jf";
                    }
                }
                unset($jsFiles);
                
                if (isset($cssFiles)) {
                    foreach($cssFiles as $jf) {
                        $plugin_cssFileList[] = "$PM_PLUGIN_LOCATION/$p/$jf";
                    }
                }
                unset($cssFiles);
                
                if (isset($phpFiles)) {
                    foreach($phpFiles as $pf) {
                        if (is_file("$pluginDir/$pf")) {
                            $plugin_phpFileList[] = "$pluginDir/$pf";
                        }
                    }
                }
                unset($phpFiles);
                
                
            } else {
                error_log("P.MAPPER-ERROR: plugin config file '$pluginDir/config.inc' not existing");
            }
        } else {
            error_log("P.MAPPER-ERROR: plugin directory '$pluginDir' not existing");
        }
    }
}


/**
 * Define constants
 */
define("PM_INCPHP",              $PM_INCPHP);
define("PM_INCPHP_LOCATION",     $PM_INCPHP_LOCATION);
define("PM_JAVASCRIPT",          $PM_JAVASCRIPT);
define("PM_JAVASCRIPT_REALPATH", $PM_JAVASCRIPT_REALPATH);
define("PM_PLUGIN_LOCATION",     $PM_PLUGIN_LOCATION);
define("PM_PLUGIN_REALPATH",     $PM_PLUGIN_REALPATH);
define("PM_CONFIG_LOCATION",     $PM_CONFIG_LOCATION);
define("PM_CONFIG_DIR",          $PM_CONFIG_DIR);
define("PM_BASECONFIG_DIR",      $PM_BASECONFIG_DIR);
define("PM_JS_CONFIG",           $PM_JS_CONFIG);


/**
 * Write vars to session
 */
$_SESSION['PM_BASE_DIR']          = $PM_BASE_DIR;
$_SESSION['PM_BASECONFIG_DIR']    = $PM_BASECONFIG_DIR;
$_SESSION['PM_CONFIG_LOCATION']   = $PM_CONFIG_LOCATION;
$_SESSION['PM_CONFIG_DIR']        = $PM_CONFIG_DIR;
$_SESSION['PM_SEARCH_CONFIGFILE'] = $PM_SEARCH_CONFIGFILE;
$_SESSION['PM_PRINT_CONFIGFILE']  = $PM_PRINT_CONFIGFILE;
$_SESSION['PM_INCPHP']            = $PM_INCPHP;
$_SESSION['PM_INCPHP_LOCATION']   = $PM_INCPHP_LOCATION;
$_SESSION['PM_JAVASCRIPT']        = $PM_JAVASCRIPT;
$_SESSION['PM_MAP_FILE']          = $PM_MAP_FILE;
$_SESSION['PM_HL_MAP_FILE']       = $PM_HL_MAP_FILE;
$_SESSION['PM_PLUGIN_LOCATION']   = $PM_PLUGIN_LOCATION;
$_SESSION['PM_PLUGIN_REALPATH']   = $PM_PLUGIN_REALPATH;

$_SESSION['gLanguage']  = $gLanguage;
$_SESSION['defCharset'] = $defCharset;
$_SESSION['msVersion']  = $msVersion;
$_SESSION['session_alive'] = 1;
$_SESSION['config']     = $config;

$_SESSION['plugin_jsFileList']    = $plugin_jsFileList;
$_SESSION['plugin_jsInitList']    = $plugin_jsInitList;
$_SESSION['plugin_jsTocInitList'] = $plugin_jsTocInitList;
$_SESSION['plugin_cssFileList']   = $plugin_cssFileList;
$_SESSION['plugin_phpFileList']   = $plugin_phpFileList;





?>