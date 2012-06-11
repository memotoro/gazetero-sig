<?php

/******************************************************************************
 *
 * Purpose: classes for queries on different layer types, extends query.php
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

require_once("query.php");


/**********************************************
 *  STANDARD QUERY RESULTS NORMAL LAYER
 **********************************************/
class DQuery extends Query
{ 

    function DQuery($map, $qLayer, $glayer, $zoomFull)
    {
        $this->map = $map;
        $this->qLayer = $qLayer;
        $this->qLayerName = $this->qLayer->name;
        $this->qLayerType = $this->qLayer->type;
        $this->glayer = $glayer;
        $this->selFields = $glayer->getResFields();
        $this->zoomFull = $zoomFull;
        $this->infoWin = $_SESSION["infoWin"];
        
        $this->limitResult = $_SESSION["limitResult"];
        $this->pointBuffer  = $_SESSION["pointBuffer"];
        $this->shapeQueryBuffer  = $_SESSION["shapeQueryBuffer"];
        $this->layerEncoding = $glayer->getLayerEncoding();
        
        // dump results to resultString 
        $this->dumpQueryResults();
    }
    
    
    function getResultString()
    {
        return $this->qStr;
    }
    
    
    /**
     * Get the number of results for query on the layer
     */ 
    function setNumResults()
    {
        $this->qLayer->open();       
        $this->numResults = $this->qLayer->getNumResults();

        // Apply limit for query results
        if ($this->numResults > $this->limitResult) $this->numResults = $this->limitResult;
        $this->qLayer->close();
    }



   /**
    * DUMP QUERY RESULTS INTO QUERY STRING $qStr
    *******************************************************/
    function dumpQueryResults()
    {
        if ($this->zoomFull) {
            // Maximum extents
            $this->mExtMinx = 999999999;
            $this->mExtMiny = 999999999;
            $this->mExtMaxx = -999999999;
            $this->mExtMaxy = -999999999;
        }
        
        $this->returnTableJoinProperties();
        
        $this->setNumResults();
        //error_log($this->numResults);
        
        $this->qLayer->open();
        for ($iRes=0; $iRes < $this->numResults; $iRes++) {
            $qRes = $this->qLayer->getResult($iRes);
            $qShape = $this->qLayer->getShape($qRes->tileindex,$qRes->shapeindex);
            
            $this->qStr .= $this->printResultRow($qShape);
            $this->qStr .= $iRes < ($this->numResults - 1) ? ", " : "";
            $this->resultindexes[] = $qRes->shapeindex;
            $this->resulttileindexes[$qRes->shapeindex] = $qRes->tileindex;
        }
        //error_log($this->qStr);
        $this->qLayer->close();
    }


    function printResultRow($qShape)
    {
        $this->qStr .= "[";
        
        // DO NOT PRINT LINK FOR RASTER LAYERS (TYPE 3), ONLY FOR VECTOR
        $this->qStr .= ($this->qLayerType != 3 ? $this->printShapeField($qShape) : "\"r\""); 
        
        $this->printFields($qShape);
        $this->qStr .= "]";
    }



   /** 
    * Print SHAPEINDEX COLUMN as HTML hyperlink (for zoom-to-feature link)
    *************************************************************************/
    function printShapeField($qShape)
    {
        // Add LINK with shape extent and javascript zoom function as new column  //
        $qShpIdx = $qShape->index;
        $qTileShpIdx = $qShape->tileindex;
        if ($qTileShpIdx != -1) {
            $qShpIdx = "$qTileShpIdx@$qShpIdx";
        }
        $qShpBounds = $qShape->bounds;
        $changeLayProj = $this->checkProjection();
        //$this->resultindexes[] = $qShpIdx;
        
        // Change PROJECTION to map projection if necessary
        if ($changeLayProj) {
            if ($this->qLayerType == 0) {
                // Apply buffer in order to have a correct re-projection of POINT layers
                $pjbuff = 0.0000001;    
                $sMinx = $qShpBounds->minx - $pjbuff;
                $sMiny = $qShpBounds->miny - $pjbuff;
                $sMaxx = $qShpBounds->maxx + $pjbuff;
                $sMaxy = $qShpBounds->maxy + $pjbuff;
                
                $qShpBounds = ms_newRectObj();
                $qShpBounds->set("minx", $sMinx);
                $qShpBounds->set("miny", $sMiny);
                $qShpBounds->set("maxx", $sMaxx);
                $qShpBounds->set("maxy", $sMaxy);
            }
            
            $qShpBounds->project($this->qLayerProjObj, $this->mapProjObj);
        }   
        
        //Get MIN/MAX values for shape extent rectangle       
        $shpMinx = $qShpBounds->minx;
        $shpMiny = $qShpBounds->miny;
        $shpMaxx = $qShpBounds->maxx;
        $shpMaxy = $qShpBounds->maxy;
        
        
        // Buffer for zoom extent
        if ($this->qLayerType == 0) {
            $buf = $this->pointBuffer;        // set buffer depending on dimensions of your coordinate system
        } else {
            if ($this->shapeQueryBuffer > 0) {
                $buf = $this->shapeQueryBuffer * ((($shpMaxx - $shpMinx) + ($shpMaxy - $shpMiny)) / 2);
            } else {
                $buf = 0;   
            }
        }

        if ($buf > 0) {
            $shpMinx -= $buf;
            $shpMiny -= $buf;
            $shpMaxx += $buf;
            $shpMaxy += $buf;
        }
        
        
        // Get maximum extents if zoomAll or autoZoom is enabled
        if ($this->zoomFull) {
            $this->mExtMinx = min($this->mExtMinx, $shpMinx);
            $this->mExtMiny = min($this->mExtMiny, $shpMiny);
            $this->mExtMaxx = max($this->mExtMaxx, $shpMaxx);
            $this->mExtMaxy = max($this->mExtMaxy, $shpMaxy);
        }     
        // Define if for zoom2extent for select and search the single click should change highlight       
        $qShpLink = "{\"shplink\": [" . ($this->zoomFull ? "\"0\",\"0"  :  "\"" . $this->qLayerName ."\",\"".$qShpIdx) ."\",\"". $shpMinx ."+". $shpMiny ."+". $shpMaxx ."+". $shpMaxy ."\"]}";
        
        return $qShpLink;
    }
    
    

        

   /**
    * Print results for rest of fields (all but shape)
    *************************************************/
    function printFields($qShape)
    {
        
        // PRINT RESULT ROW
        $loop = 2;     // Used for one-to-many joins to break while loop when all DB data printed
        $dbloop = 0;   // Used for one-to-many joins as index to step through join table

        while($loop > 0) {

            ##$this->qStr .= "pippo anzahl felder:" . sizeof($this->selFields);
            // Add shape index to array, used for highlight
            //$resultindexes[] = $qShpIdx;

            // Print all OTHER COLUMNS from SHAPE
            for ($iField=0; $iField < sizeof($this->selFields); $iField++) {
                $fldName  = $this->selFields[$iField];
                $fldValue = $qShape->values[$fldName];
                //$this->qStr .= $fldValue;
                
                $this->qStr .= $this->printFieldValues($fldName, $fldValue);
            }

            // Now add JOIN COLUMNS from DB if defined
            if ($this->joinList && $this->dbh) {
                $toValue = $qShape->values[$this->toField];
                $joinFieldList = split(',', $this->joinFields);
                // get data only once from DB
                if ($dbloop == 0) {
                    $data = $this->returnData($this->dbh, $this->sql, $toValue, $this->fromFieldType, $this->one2many);
                    $dbresCount = count($data);
                }

                if ($dbresCount > 0) {
                    $jfldi = 0;
                    foreach($data[$dbloop] as $val) {
                        $fldName =  trim($joinFieldList[$jfldi]);
                        $jfldi++;
                        $this->qStr .= $this->printFieldValues($fldName, $val);
                    }
                    $dbloop++;

                    // if NO one2many set $dbloop to end value and stop after first record
                    if (!$this->one2many) {
                        $dbloop = $dbresCount;
                    }

                    // if all recors from one2many retrieved (or only one2one) stop loop
                    if ($dbloop == $dbresCount) $loop = 0;
                } else {
                    $loop = 0;
                }

            // NO JOIN field defined, so break while loop and continue with next record
            } else {
                $loop = 0;
            }
            
        }
    
    }

    
   /**
    * FUNCTIONS FOR JOINING DB TABLES TO QUERY RESULT
    ************************************************************/
    
    /**
     * Get properties for DB table join
     */ 
    function returnTableJoinProperties()
    {
        if ($this->glayer->getTableJoin() != "") {
            $pearDbClass = $_SESSION['pearDbClass'];    
            require_once ("$pearDbClass.php");
            
            //$dsn, $fromTable, $fromField, $fromFieldType, $joinFields, $toField, $one2many
            $joinList = $this->glayer->getTableJoin();

            // Join table properties
            $fromTable  = $joinList["fromTable"];
            $fromField  = $joinList["fromField"];
            $this->fromFieldType  = $joinList["fromFieldType"];
            $this->joinFields = $joinList["joinFields"];
    
            // Layer field to join TO
            $this->toField =  $joinList["toField"];
    
            // Join type: one-to-one (0) or one-to-many (1)
            $this->one2many = $joinList["one2many"];
    
            // init DB class
            $db = new $pearDbClass;

            // Connect to DB   
            $dsn = $joinList["dsn"];            
            $dbh = $db->connect($dsn);
            if ($db->isError($dbh)) {
                db_logErrors($dbh);
                error_log ("P.MAPPER JOIN ERROR: Could not connect to DB defined for Layer '" 
                . $this->glayer->getLayerName() . "'. \nCheck map file entry for JOIN definition.", 0);
                die();
            }

            $this->sql = "SELECT " . $this->joinFields . " FROM $fromTable WHERE $fromField=";
            $this->dbh = $dbh;
                
            $this->joinList = $joinList;
            
            pm_logDebug(3, "returnTableJoinProperties(), SQL command for table join:\n $this->sql");
            pm_logDebug(3, $joinList, "returnTableJoinProperties() - join list for table join:");
        } else {
            $this->joinList = false;
        }
    }
    
    
    
    /**
     * Get data from DB
     */ 
    function returnData($dbh, $sql, $toValue, $fromFieldType)
    {
        $quote = ($fromFieldType == "1" ? "'" : "");
        $sqlRun = $sql.$quote.$toValue.$quote;
        $res = $this->dbh->query($sqlRun);
        if (PEAR::isError($res)) {
            db_logErrors($res);
            die();
        }
        while ($row = $res->fetchRow()) {
            $data[] = $row;
        }
        $res->free();
        return $data;
    }



} // end CLASS DQUERY




/**********************************************
 *  QUERY RESULTS FOR WMS LAYER
 **********************************************/
class WMSQuery extends Query
{
    function WMSQuery($grp, $qLayer, $x_pix, $y_pix )
    {
        //$this->QUERY($qLayer);
        $this->grp = $grp;
        $this->qLayer = $qLayer;
        $this->x_pix = $x_pix;
        $this->y_pix = $y_pix;
        
        // dump results to resultString 
        $this->dumpWMSQueryResults();
    }


function dumpWMSQueryResults()
    {
        $wmsResultURL = $this->qLayer->getWMSFeatureInfoURL($this->x_pix, $this->y_pix, 10, "MIME");
        pm_logDebug(3, $wmsResultURL, "dumpWMSQueryResults() - FeatureInfoURL:");
        
        // Check for availability of 'allow_url_fopen'
        if (ini_get("allow_url_fopen")) {
            $wmsResult = file($wmsResultURL);
        // if no 'allow_url_fopen', use CURL functions to get Info URL content
        } else {
            if (!extension_loaded('curl')) {
                dl("php_curl." . PHP_SHLIB_SUFFIX);
            }
            ob_start();
            $ch = curl_init($wmsResultURL);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            $wmsResult = preg_split("/\n/", ob_get_contents());
            ob_end_clean();
        }
        pm_logDebug(3, $wmsResult, "dumpWMSQueryResults() - Query Result:");
        
        $wmsNumRes = count($wmsResult);
        if ($wmsNumRes > 4) {
            $firstRun = 1;
            $featureCount = 0;
            $fldHeaderStr = "[\"#\",";
            $resRowStr = "\"values\": [ [";
            
            foreach ($wmsResult as $row) {
                if (preg_match ("/\sFeature\s/i", $row)) {
                    $featureCount++;
                    if (!$firstRun) {
                        $resRowStr = substr($resRowStr, 0, -1);
                        $resRowStr .= "],[";
                    }
                    $firstRun = 0;
                    $resRowStr .= "\"w\",";
                } elseif (preg_match ("/\=/", $row)) {
                    $resRowStr .= "";
                    $resFld = preg_split ("/\=/", $row);
                    if ($featureCount < 2) {
                        $fldHeaderStr .= "\"" . trim($resFld[0]) . "\",";
                    }                    
                    $resRowStr .= "\"" . utf8_encode(trim(str_replace("'","",$resFld[1]))) . "\",";
                }
            }
            
            $fldHeaderStr = "\"header\": " . substr($fldHeaderStr, 0, -1) . "], ";
            $resRowStr = substr($resRowStr, 0, -1) . "]";
            
            $this->numResults = $wmsNumRes - 4;
            $this->colspan = $colspan;
            $this->fieldHeaderStr = $fldHeaderStr; 
            $this->qStr = "$fldHeaderStr $resRowStr";
        }
    }



} // end CLASS WMSQUERY




/**********************************************
 *  QUERY RESULTS FOR XY ('EVENT') LAYER
 **********************************************/
class XYQuery extends Query
{ 

    function XYQUERY($map, $qLayer, $glayer, $xyLayQueryList, $search, $zoomFull)
    {
        $this->map = $map;
        $this->glayer = $glayer;
        $this->qLayer = $qLayer;
        $this->qLayerName = $qLayer->name;
        $this->xyLayQueryList = $xyLayQueryList;
        $this->zoomFull = $zoomFull;
        $this->search = $search;
        $this->layerEncoding = $glayer->getLayerEncoding();
        $this->limitResult = $_SESSION["limitResult"];
        $this->pointBuffer  = $_SESSION["pointBuffer"];

        // dump results to resultString 
        $this->dumpXYQueryResults();
        
    }



    function dumpXYQueryResults()
    {
        $pearDbClass = $_SESSION['pearDbClass'];    
        require_once ("$pearDbClass.php");
        $eqr = $_SESSION["equeryRect"];
        
        // XY Layer Properties
        $XYLayerProperties = $this->glayer->getXYLayerProperties();
        
        if ($XYLayerProperties['noQuery']) return false;
        
        $dsn = $XYLayerProperties["dsn"];
        $xyTable = $XYLayerProperties["xyTable"];
        $x_fld = $XYLayerProperties["x_fld"];
        $y_fld = $XYLayerProperties["y_fld"];
        $classidx_fld = $XYLayerProperties["classidx_fld"];
        
        $resFieldList = $this->glayer->getResFields();
        $resFldStr = join(',', $resFieldList);
        
        
        // Prepare SQL query
        if (preg_match("/@/", $xyTable)) {          // Check for WHERE filter in table definition
            $xyList = preg_split('/@/', $xyTable);
            $whereFilter = $xyList[1];
            $xyTable = $xyList[0];
        }

        $sql_select = "SELECT $x_fld, $y_fld, $resFldStr FROM $xyTable";
        
        $qr = $this->xyLayQueryList;
        $changeLayProj = checkProjection($this->map, $this->qLayer);
                        
        // Map extent for limiting query 
        if ($changeLayProj) {
            $mapExt = ms_newRectObj();
            $mapExt->setExtent($qr["xmin"], $qr["ymin"], $qr["xmax"], $qr["ymax"]); 
            $mapExt->project($changeLayProj['mapProj'], $changeLayProj['layProj']);
            $qxmin = $mapExt->minx;
            $qymin = $mapExt->miny;
            $qxmax = $mapExt->maxx;
            $qymax = $mapExt->maxy;
        } else {
            $qxmin = $qr["xmin"];
            $qymin = $qr["ymin"];
            $qxmax = $qr["xmax"];
            $qymax = $qr["ymax"];
        }        
        
        if ($this->search) {
            $sql_where  = "WHERE " . ($whereFilter ? $whereFilter . " AND " : "") . $qr;
        } else {
            $sql_where  = "WHERE " . ($whereFilter ? $whereFilter . " AND " : "") . " $x_fld >= $qxmin AND $x_fld <= $qxmax AND $y_fld >= $qymin AND $y_fld <= $qymax ";
        }

        $sql = "$sql_select  $sql_where";
        pm_logDebug(3, $query, "P.MAPPER-DEBUG: squery.php/dumpXYQueryResults() - SQL Cmd:");
        
        
        // init DB class
        $db = new $pearDbClass;

        // Connect to DB       
        $dbh = $db->connect($dsn);
        if ($db->isError($dbh)) {
            db_logErrors($dbh);
            die();
        }
        
        // Execute query 
        $res = $dbh->query($sql);
        if ($db->isError($res)) {
            db_logErrors($res);
            die();
        } 
        
        
        $this->mExtMinx = 999999999;
        $this->mExtMiny = 999999999;
        $this->mExtMaxx = -999999999;
        $this->mExtMaxy = -999999999;
        
        // Now print results as JSON
        $nres = 0;
        $numrows = $res->numRows();
        while ($row = $res->fetchRow(2)) {
            if ($changeLayProj) {
                $nP = ms_newpointobj();
                $nP->setXY($row["$x_fld"], $row["$y_fld"]);         
                $nP->project($changeLayProj['layProj'], $changeLayProj['mapProj']);
                $rx = $nP->x;
                $ry = $nP->y;
            } else {
                $rx = $row["$x_fld"];
                $ry = $row["$y_fld"];
            }

            $buf = $this->pointBuffer;
            //error_log($buf);
            $shpMinx = $rx - $buf;
            $shpMiny = $ry - $buf;
            $shpMaxx = $rx + $buf;
            $shpMaxy = $ry + $buf;

            
            if ($this->zoomFull) {
                $this->mExtMinx = min($this->mExtMinx, $shpMinx);
                $this->mExtMiny = min($this->mExtMiny, $shpMiny);
                $this->mExtMaxx = max($this->mExtMaxx, $shpMaxx);
                $this->mExtMaxy = max($this->mExtMaxy, $shpMaxy); 
            }
            
            // Link for zoom to feature
            $qShpIdx = $row["$oid_fld"];
            $this->resultindexes[] = $row["$x_fld"] ."@". $row["$y_fld"];
            
            // Output JSON
            $qShpLink = "{\"shplink\": [\"0\",\"0\",\"" . $shpMinx ."+". $shpMiny ."+". $shpMaxx ."+". $shpMaxy ."\"]}";

            if ($nres > 0)  $this->qStr .= ", "; 
            
            // Add shape link
            $this->qStr .= "[" . $qShpLink;
            
            // Add 'normal' field values
            foreach ($resFieldList as $fn) {
                $this->qStr .= $this->printFieldValues($fn, $row["$fn"]);
            }
            
            $this->qStr .= "]";
            
            $nres++;
            
            // Stop query if result records exceed limit set in config.ini
            if ($nres > $this->limitResult) break;
        }
        
        $this->numResults = $nres;
        
        $res->free();
        $dbh->disconnect();
          
    }


} // END CLASS XYQUERY



/**********************************************
 *  ATTRIBUTE QUERY RESULTS FOR POSTGIS LAYER
 **********************************************/
class PGQuery extends Query
{ 
    function PGQuery($map, $qLayer, $glayer, $queryStr, $zoomFull)
    {
        $this->map = $map;
        $this->qLayer = $qLayer;
        $this->qLayerType = $this->qLayer->type;
        $this->qLayerName = $qLayer->name;
        $this->glayer = $glayer;
        $this->zoomFull = $zoomFull;
        $this->layerEncoding = $glayer->getLayerEncoding();
        
        $this->limitResult  = $_SESSION["limitResult"];
        $this->pointBuffer  = $_SESSION["pointBuffer"];
        
        $this->changeLayProj = $this->checkProjection();
        //error_log("change proj: " . $this->changeLayProj);
        
        // dump results to resultString 
        $this->dumpPGQueryResults($queryStr);
    }
    
    
    function dumpPGQueryResults($queryStr)
    {
        $layerDataList = $this->pgLayerParseData();
        $geom = $layerDataList['geocol'];
        $dbtable = $layerDataList['dbtable'];
        $unique_field = $layerDataList['unique_field'];
                
        // Load PGSQL extension if necessary
        if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
            if (! extension_loaded('pgsql')) {
                dl('php_pgsql.dll');
            }
        }
        
        // CONNECT TO DB
        $connString = $this->qLayer->connection;
        if (!($connection = pg_Connect($connString))){
           error_log ("P.MAPPER: Could not connect to database");
           error_log ("P.MAPPER: PG Connection error: " . pg_last_error($connection));
           exit();
        }
    
        // FIELDS and FIELD HEADERS for result   
        $selFields   = $this->glayer->getResFields();
        foreach ($selFields as $f) {
            $s .= "$f,";
        }
        
        // Select string for DB query
        $select = substr($s, 0, -1);
        
        // Apply already existing filter on layer
        $pg_filter = trim(str_replace('"', '', $this->qLayer->getFilter()));
        if (strlen($pg_filter) > 2 &&  $pg_filter != "(null)") {
            $queryStr .= " AND $pg_filter ";
        }
        
        // Limit search to limit set in INI file
        $searchlimit = $this->limitResult + 1;
    
        // RUN DB DEFINE QUERY
        $query = "SELECT $unique_field, 
                         xmin(box3d($geom)), 
                         ymin(box3d($geom)), 
                         xmax(box3d($geom)), 
                         ymax(box3d($geom)), 
                         $select 
                    FROM $dbtable 
                   WHERE $queryStr
                   LIMIT $searchlimit";
        //error_log($query);
        pm_logDebug(3, $query, "P.MAPPER-DEBUG: squery.php/dumpPGQueryResults() - SQL Cmd:");
        
        $qresult = pg_query ($connection, $query);
        if (!$qresult) {
            error_log("P.MAPPER: PG Query error for : $query" . pg_result_error($qresult));
        }
        $numrows = pg_numrows ($qresult);
        $this->numResults = $numrows;
    
    
        // CREATE HTML OUPTPUT
        if ($numrows > 0){
            
            if ($this->zoomFull) {
                // Maximum start extents
                $mExtMinx = 999999999;
                $mExtMiny = 999999999;
                $mExtMaxx = -999999999;
                $mExtMaxy = -999999999;
            }
            
            // Fetch records from db and print them out
            for ($r=0; $r < $numrows; ++$r){
                $a = pg_fetch_row($qresult, $r);
                $a_rows = count($a);
                $qShpIdx = $a[0]; 
                $oids[] = $qShpIdx;
                
                // If map and layer have different proj, re-project extents
                if ($this->changeLayProj) {
                    $pb = $this->reprojectExtent($a);
                    $xmin = $pb['shpMinx'];
                    $ymin = $pb['shpMiny'];
                    $xmax = $pb['shpMaxx'];
                    $ymax = $pb['shpMaxy'];
                } else {
                    $xmin = $a[1];
                    $ymin = $a[2];
                    $xmax = $a[3];
                    $ymax = $a[4];
                }
                
                // Set buffer for zoom extent
                if ($this->qLayerType == 0) {
                    $buf = $this->pointBuffer;        // set buffer depending on dimensions of your coordinate system
                } else {
                    if ($this->shapeQueryBuffer > 0) {
                        $buf = $this->shapeQueryBuffer * ((($xmax - $xmin) + ($ymax - $ymin)) / 2);
                    } else {
                        $buf = 0;   
                    }
                }
        
                if ($buf > 0) {
                    $xmin -= $buf;
                    $ymin -= $buf;
                    $xmax += $buf;
                    $ymax += $buf;
                }     
                
                // Look for min/max extents of ALL features
                if ($this->zoomFull) {
                    $mExtMinx = min($mExtMinx, $xmin);
                    $mExtMiny = min($mExtMiny, $ymin);
                    $mExtMaxx = max($mExtMaxx, $xmax);
                    $mExtMaxy = max($mExtMaxy, $ymax);
                }
                
                // Output JSON
                $qShpLink = "{\"shplink\": [" . ($this->zoomFull ? "\"0\",\"0"  :  "\"" . $this->qLayerName ."\",\"".$qShpIdx) ."\",\"". $xmin ."+". $ymin ."+". $xmax ."+". $ymax ."\"]}";
                
                // print SHAPEINDEX link
                $this->qStr .= "[" . $qShpLink;
    
                // Print all OTHER FIELDS
                for ($i=5; $i < $a_rows; ++$i) {
                     //printFieldValues($glayer, $qlayerName, $fldName, $a[$i]);
                     $fldName = pg_field_name($qresult, $i);
                     $this->qStr .= $this->printFieldValues($fldName, $a[$i]);
                }
                $this->qStr .= "]";
                if ($r < ($numrows - 1)) $this->qStr .= ", "; 
                //$this->qStr .= "\n";
                
            }
    
            // Full extent for ALL features
            if ($this->zoomFull) {
                $this->maxExtent = array($mExtMinx, $mExtMiny, $mExtMaxx, $mExtMaxy);
            } else {
                $this->maxExtent = 0;
            }
            
            $this->resultindexes = $oids;
            
        }
    
        pg_Close ($connection);
    
    }
    
   /**
    * Parse DATA tag from PostGIS layer
    */    
    function pgLayerParseData()
    {
        $pg_data = $this->qLayer->data; //"the_geom from images";
        $dl = preg_split('/ from /i', $pg_data);
        $data_list['geocol'] = trim($dl[0]);
        $flds = trim($dl[1]);
        
        if (substr($flds, 0, 1) == '(') {
            // is of type "the_geom from (select the_geom, oid, from mytable) AS foo USING UNIQUE gid USING SRID=4258"
            $tabl = preg_split('/as ([a-z]|_|[A-Z]|[0-9])+ using unique /i', $dl[2]);
            $unique_list = preg_split('/[\s,]+/', $tabl[1]);
            
            $data_list['dbtable'] = $flds . " from " . trim($tabl[0]) . " as foo ";
            $data_list['unique_field'] = trim($unique_list[0]);
        } else {
            $tabl = preg_split('/using unique/i', $dl[1]);
            if (count($tabl) > 1) {
                // is of type "the_geom from mytable USING UNIQUE gid "
                $data_list['dbtable'] = trim($tabl[0]);
                //$data_list['unique_field'] = trim($tabl[1]);
                $unique_list = preg_split('/[\s]+/', trim($tabl[1]));
                $data_list['unique_field'] = trim($unique_list[0]);
            } else {
                // is of type "the_geom from mytable"
                $dbtable = trim($dl[1]);
                $data_list['dbtable'] = $dbtable;
                $data_list['unique_field'] = "oid";
                pm_logDebug(2,"P.MAPPER Warning: no UNIQUE field specified for PostGIS table '$dbtable'. Trying using OID field...");
            }
        }
        return $data_list; 
    }
    
    
    
    function reprojectExtent($inExt)
    {
        $qShpBounds = ms_newRectObj();
        
        // Apply buffer in order to have a correct re-projection of POINT layers
        $pjbuff = ($this->qLayer->type == 0 ? 0.0000001 : 0);    
        $sMinx = $inExt[1] - $pjbuff;
        $sMiny = $inExt[2] - $pjbuff;
        $sMaxx = $inExt[3] + $pjbuff;
        $sMaxy = $inExt[4] + $pjbuff;
        
        $qShpBounds->set("minx", $sMinx);
        $qShpBounds->set("miny", $sMiny);
        $qShpBounds->set("maxx", $sMaxx);
        $qShpBounds->set("maxy", $sMaxy);

        $qShpBounds->project($this->qLayerProjObj, $this->mapProjObj);
        
        $pb['shpMinx'] = $qShpBounds->minx;
        $pb['shpMiny'] = $qShpBounds->miny;
        $pb['shpMaxx'] = $qShpBounds->maxx;
        $pb['shpMaxy'] = $qShpBounds->maxy;
        
        return $pb;
    }  
    
    
    function returnMaxExtent() 
    {
        return $this->maxExtent;
    }
    
    
    
} //END CLASS PGQUERY






?>