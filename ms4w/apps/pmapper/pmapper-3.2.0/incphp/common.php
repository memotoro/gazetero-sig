<?php

/******************************************************************************
 *
 * Purpose: various common functions for use in classes
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


/* 
 * GETTEXT-LIKE FUNCTION FOR MULTILINGUAL APPLICATION
 ******************************************************/

/**
 * USING PHP ARRAYS
 */
function _p($string)
{
    global $_sl;
    if (isset($_sl[$string])) {
        return $_sl[$string];
    } else {
        return $string;
    }
}

function _pjs($string)
{
    return addcslashes(_p($string), "\"'");
}


/**
 * USING SQLite DB
 * Load SQLite extension 
 */
function __p($string)
{   
    if (!extension_loaded('PDO')) dl("php_pdo." . PHP_SHLIB_SUFFIX);
    if (!extension_loaded('pdo_sqlite')) dl("php_pdo_sqlite." . PHP_SHLIB_SUFFIX);
    
    global $gLanguage;
    $localeDB = $_SESSION['PM_INCPHP'] . "/locale/localedb.db";
    $dsn = "sqlite:$localeDB";
    $dbh = new PDO($dsn);
    $sql = "SELECT $gLanguage FROM locales WHERE base='" . str_replace("'", "''", $string) . "' LIMIT 1";
    
    //error_log($localeDB);
    //error_log($sql);
    
    foreach ($dbh->query($sql) as $row) {
        $string = $row[0];
    }
    
    return $string;
}



/**
 * ENABLE/DISABLE GROUPS IN MAP
 */
function setGroups($map, $groups, $scale, $setLabelItem, $query=false)
{
    // APPLY ON LAYERS DEFINED IN MAP FILE
    //$grouplist = array_unique($_SESSION["grouplist"]);  // does NOT work with PHP4 
    $grouplist = array_unique_key($_SESSION["grouplist"]);
    $MS_VERSION = $_SESSION['MS_VERSION'];
    
    foreach ($grouplist as $grp){
        $glayerList = $grp->getLayers();

        if (in_array($grp->groupName, $groups, TRUE)) {
            $activeGroups[] = $grp;
            foreach ($glayerList as $glayer) {
                $layer = $map->getLayer($glayer->getLayerIdx());
                $layerType = $layer->type;
                
                // if defined use only layers visible at current scale (useful for queries)
                if ($scale > 0) {
                    if (checkScale($map, $layer, $scale) == 1) {
                        $querylayers[] = $layer;   
                        $layer->set("status", MS_ON);
                        //error_log("on: " . $glayer->glayerName);
                        
                        // set labelitem if defined
                        if ($setLabelItem) {
                            if ($glayer->getLabelItem()) {
                                $layer->set("labelitem", $glayer->getLabelItem());
                            }
                        }
                        
                        // Layer Transparency
                        if (floatval($MS_VERSION) >= 5) { 
                            $layer->set("opacity", $glayer->getOpacity());
                        } else {
                            $layer->set("transparency", $glayer->getOpacity());
                        }
                    } else {
                        $layer->set("status", MS_OFF);
                    }
                } else {
                    $layer->set("status", MS_ON);
                    // set labelitem if defined
                    if ($setLabelItem) {
                        if ($glayer->getLabelItem()) {
                            $layer->set("labelitem", $glayer->getLabelItem());
                        }
                    }
                    if (floatval($MS_VERSION) >= 5) { 
                        $layer->set("opacity", $glayer->getOpacity());
                    } else {
                        $layer->set("transparency", $glayer->getOpacity());
                    }
                }
            }
        } else {
            foreach ($glayerList as $glayer) {
                $layer = $map->getLayer($glayer->getLayerIdx());
                $layer->set("status", MS_OFF);
            }
        }
    }
}


/**
 * Removes duplicate keys from an array
 */
function array_unique_key($array) {
    $result = array();
    foreach (array_unique(array_keys($array)) as $tvalue) {
        $result[$tvalue] = $array[$tvalue];
    }
    return $result;
}


/**
 * CHECK IF LAYER IS IN VALID SCALE DIMENSION (USED FOR QUERIES)
 * Based on a script by CHIP HANKLEY found on MapServer Wiki 
 */
function checkScale($map, $qLayer, $scale)
{
    if ($qLayer->maxscale == -1 && $qLayer->minscale == -1) {
        return 1;
    } elseif ($scale > $qLayer->maxscale AND $qLayer->maxscale != -1) {
        return 0;
    } elseif ($scale < $qLayer->minscale AND $qLayer->minscale != -1) {
        return 0;
    } else {
        return 1;
    }
}


/**
 * Get the group to which a glayer belongs
 */
function returnGroupGlayer($layname)
{
    $grouplist = $_SESSION["grouplist"];
    foreach ($grouplist as $grp) {
        $glayerList = $grp->getLayers();
        foreach ($glayerList as $gl) {
            $glayername = $gl->getLayerName();
            if ($layname == $glayername) {
                return array($grp, $gl);
            }
        }
    }
}


/**
 * Print out debug info (including arrays)
 */
function pm_logDebug($dbglev, $dbgstr0, $headerstr=false)
{
    //write to PHP error log when log level = 0
    if ($dbglev < 1) error_log($dbgstr0);
        
    if ($_SESSION['debugLevel'] >= $dbglev) {
        ob_start();
        print_r($dbgstr0);
        $dbgstr = ob_get_contents();
        ob_end_clean();  
            
        $errlog_dir = str_replace('\\', '/', dirname(ini_get("error_log")));
        if (file_exists($errlog_dir)) {
            $outMapFN =  $errlog_dir . "/pm_debug.log";
        
            $header = "\n[" . date("d-M-Y H:i:s") ."] P.MAPPER debug info \n";
            if ($headerstr) $header .= "$headerstr\n";
            $fpOut = fopen($outMapFN, "a+");
            if (!$fpOut) {
                error_log("Cannot create debug log file $fpOut. Check permissions.");
                return false;
            }
            fwrite($fpOut, "$header $dbgstr");
            fclose($fpOut);
        } else {
            error_log("Incorrect setting for 'error_log' in 'php.ini'. Set to a valid file name.");    
        }
    }
}


/**
 * Workaround for Mapscript bug and temp image file names
 */
function mapSaveWebImage($map, $mapImg, $refImg=false)
{
    $now = (string)microtime();
    $now = explode(' ', $now);
    $microsec = $now[1].str_replace('.', '', $now[0]);
    unset($now);

    $imgFormat = $refImg ? substr(strtolower(trim($map->reference->image)), -3) : $map->outputformat->extension;

    $tmpImgBaseName = session_id() . $microsec . "." . $imgFormat;
    $tmpFileNameAbs = str_replace('\\', '/', $map->web->imagepath) . $tmpImgBaseName ;
    $imgURL =  $map->web->imageurl . $tmpImgBaseName ;
    $mapImg->saveImage($tmpFileNameAbs, $map);

    return $imgURL;
}


/**
 * Parses a JSON (www.json.org) string
 * using if available the PHP-JSON extension
 * or else the json.php parser by Michal Migurski
 * default action is decoding
 */
function parseJSON($input, $decode=1) 
{
    if (!extension_loaded('json')) {
        @dl("php_json." . PHP_SHLIB_SUFFIX);
    }
    
    if (extension_loaded('json')) {
        if ($decode) {
            return json_decode($input);
        } else {
            return json_encode($input);
        }
    
    } else {
        require_once($_SESSION['PM_INCPHP'] . "/extlib/json.php");
        $json = new Services_JSON();
        if ($decode) {
            return $json->decode($input);
        } else {
            return $json->encode($input);
        }
    }
}


/**
 * Log errors for PEAR DB connections/queries
 */
function db_logErrors($db)
{
    $err .=  "===== P.MAPPER: DB ERROR =====\n";
    $err .=  'Standard Message:   ' . $db->getMessage() . "\n";
    $err .=  'DBMS/Debug Message: ' . $db->getDebugInfo() . "\n";
    
    error_log($err);
}


/**
 * Scan a dir for files with a certain extension
 */
function scandirByExt($dir, $extension)
{
    $files = array();
    $dh  = opendir($dir);
    while (false !== ($filename = readdir($dh))) {
        if ($extension != "*") {
            if (substr(strrchr($filename, "."), 1) == $extension) {
                $files[] = $filename;
            }
        } else {
            if ($filename != "." && $filename != ".." && !is_dir($filename)) {
                $files[] = $filename;
            }
        }
    }
    return $files;
}
    

/**
 * Check if layer has same projection as map
 */
function checkProjection($map, $chkLayer)
{
    $mapProjStr     = trim($map->getProjection());
    $xyLayerProjStr = trim($chkLayer->getProjection());
    //error_log("$mapProjStr \n $xyLayerProjStr");

    if ($mapProjStr && $xyLayerProjStr && $mapProjStr != $xyLayerProjStr) {
        $changeLayProj['mapProj'] = ms_newprojectionobj($mapProjStr);
        $changeLayProj['layProj'] = ms_newprojectionobj($xyLayerProjStr);
    } else {
        $changeLayProj = false;
    }
    return $changeLayProj;
}   


/**
 * Create zip file and put in files from $files array
 */
function packFilesZip($zipFN, $files, $removepath=true, $movefiles2zip=false)
{
    if (!extension_loaded('zip')) {
        if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
            if (!dl("php_zip.dll")) error_log("P.MAPPER ERROR: PHP_ZIP.DLL could not be loaded");
        }
    }
    
    $zip = new ZipArchive();

    if ($zip->open($zipFN, ZIPARCHIVE::CREATE)!==TRUE) {
       error_log("P.MAPPER ERROR: cannot open <$zipFN>\n");
    }

    foreach ($files as $f) {
        $localFN = $removepath ? basename($f) : $f;  
        $zip->addFile($f, $localFN);
    }
    $zip->close();
}




?>