<?php

/******************************************************************************
 *
 * Purpose: XML based search definition functions
 * Author:  Armin Burger
 *
 ******************************************************************************
 *
 * Copyright (c) 2003-2007 Armin Burger
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


/**
 * XML based definition and requests for attribute query (Search)
 */
class XML_search
{
    protected $xmlFN;
    protected $xml;
    protected $xmlRoot;
    protected $dataRoot;
    
   
    /**
     * initialize XML based search
     */
    public function __construct($xmlFN)
    {
        $this->xmlFN = $xmlFN;
        $this->xml = simplexml_load_file($xmlFN);
        $this->xmlRoot = "searchlist";
        $this->dataRoot = $this->getDataRoot(trim((string)$this->xml->dataroot));
        //error_log($this->dataRoot);
    }
    
    
    /**
     * Validate XML file with an XML schema 
     * called in x_searc.php
     */
    public function validateSearchXML()
    {
        if (class_exists('DOMDocument')) {
            ob_start();        
            $dom = new DOMDocument();
            $dom->load($this->xmlFN);
            $xsd = "../query/search.xsd";
            if ($dom->schemaValidate($xsd)) {
                pm_logDebug(2, $searchArray, "Validation of search.xml file succeeded");
            } else {
                $output = ob_get_contents();
                pm_logDebug(0, $searchArray, "Validation of search.xml file FAILED: \n$output");
                ob_end_clean();
            }
        }
    }

    
    /**
     * Return data root for files used in search
     */
    protected function getDataRoot($dataRoot)
    {
        if ($dataRoot{0} == "$") {
            return $_SESSION['datapath'] . substr($dataRoot, 1);
        } else {
            return $dataRoot;
        }
    }
    
    
    
    /**
     * Compile the search string for queryByAttribute
     * called from query.php
     */
    public function getSearchParameters($map, $searchitem, $searchArray)
    {
        pm_logDebug(3, $searchArray, "Searcharray in search.php->getSearchParameters() ");
        
        $searchitems = $this->xml->xpath("/$this->xmlRoot/searchitem[@name=\"$searchitem\"]");
        foreach ($searchitems as $si) {
            $layer = $si->layer;
            $fields = $layer->field;
            
            $layerType = (string)$layer['type'];
            $layerName = (string)$layer['name'];
            $exFilters = (int)$layer['existingfilters'];
            $firstFld  = (string)$fields[0]['name'];
            $sql_from  = (string)$layer->sql_from;
            $sql_where = (string)$layer->sql_where;
            
            $mapLayer = $map->getLayerByName($layerName);
            
            
            /**** SQL string explicitely defined ****/
            if (strlen($sql_where) > 1) {
                foreach ($fields as $fo) {
                    $f = (string)$fo['name'];
                    $wildcard = (int)$fo['wildcard'];
                    $val = $val = $this->getEncodedVal($mapLayer, trim($searchArray[$f]));
                    
                    // Explicitly use wildcards
                    if ($wildcard) {
                        if ($val{0} == "*") {
                            $wc1 = "";
                            $val = substr($val, 1);
                        } else {
                            $wc1 = "^";
                        }
                        if (substr($val, -1) == "*") {
                            $wc2 = "";
                            $val = substr($val, 0, -1);
                        } else {
                            $wc2 = "$";
                        }
                    }
                    $replSearchStr = "$wc1$val$wc2";
                    $sql_where = str_replace("[$f]", $replSearchStr, $sql_where);
                }
                
                //$sql['from']  = $sql_from;
                //$sql['where'] = $sql_where;                
                
                return $sql_where;
            
            
            /**** Create search string from fields ****/
            } else {
                $qs = "";
                $fc = 0;
                foreach ($fields as $fo) {
                    $f = (string)$fo['name'];
                    $fa = !$fo['alias'] ?  $f : (string)$fo['alias'];
                    
                    // add only if user entered value
                    if (strlen(trim($searchArray[$fa])) > 0) { 
                        
                        $type = trim((string)$fo['type']);
                        $wildcard = (int)$fo['wildcard'];
                        $operator = trim((string)$fo['operator']);
                        $compare  = trim((string)$fo['compare']);                        
                        $deftype  = (string)$fo->definition['type'];
                        $val = $this->getEncodedVal($mapLayer, $searchArray[$fa]);
                        
                        $valoperator = $fc < 1 ? "" : (strlen($operator) > 0 ? $operator : "AND");
                        
                        //=== PostGIS layers 
                        if ($layerType == "postgis") {
                            $qs .= $this->getSearchParamsPG($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);
                        
                        //=== XY layers 
                        } elseif ($layerType == "xy" || $layerType == "oracle") {
                            $qs .= $this->getSearchParamsXY_DB($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);
                        
                        //=== Shape etc. layers 
                        } else {
                            $qs .= " " . $valoperator . $this->getSearchParamsShp($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator);   
                            $filter = "";
                            if ($exFilters) {
                                if ($layerFilter = $mapLayer->getFilter()) {
                                    $filter = $layerFilter . " AND ";
                                }
                            }
                            $qs = "(" . $qs . ")";
                        }
                        $fc++;
                    }
                }
            }
        }
        
        $searchParams['layerName'] = $layerName;
        $searchParams['layerType'] = $layerType;
        $searchParams['firstFld']  = $firstFld;
        $searchParams['qStr']      = $qs;
        return $searchParams;
    
    }
    
    /**
     * De/Encode search string depending on settings for Layer and defCharset
     * if no METADATA LAYER_ENCODING specified for layer, ISO-8859-1 is assumed
     */
    protected function getEncodedVal($mapLayer, $inVal) 
    {
        if ($layerEncoding = $mapLayer->getMetaData("LAYER_ENCODING")) {
            $outVal = iconv($_SESSION['defCharset'], $layerEncoding, $inVal);
        } else {
            $outVal = iconv($_SESSION['defCharset'], "ISO-8859-1", $inVal);
        }   
        return $outVal;
    }
    
    
    /**
     * Return parameters for Shapefile layers
     */
    protected function getSearchParamsShp($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        //--- String ---
        if ($type == "s") {
            // Create regex for case insensitivity of value if not from OPTION or SUGGEST
            $val = ($wildcard != 2 ? preg_replace ("/\w/ie", "'('. strtoupper('$0') . '|' . strtolower('$0') .')'", $val) : $val);
            
            // Explicitly use wildcards
            if ($wildcard == 1) {
                if ($val{0} == "*") {
                    $wc1 = "";
                    $val = substr($val, 1);
                } else {
                    $wc1 = "^";
                }
                
                if (substr($val, -1) == "*") {
                    $wc2 = "";
                    $val = substr($val, 0, -1);
                } else {
                    $wc2 = "$";
                }
                
                $qs .=  " \"[$f]\" =~ /$wc1$val$wc2/" ;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                // add slashes if not automatically added via php.ini "magic_quotes"
                if (!ini_get('magic_quotes_gpc')) {
                    $val = addslashes($val);
                }
                
                if (preg_match("/,/", $val)) {
                    $vList = explode(",", $val);
                    $os =  "(";
                    foreach ($vList as $v) {
                        $os .=  " ($valoperator \"[$f]\" = \"$v\") OR" ;
                    }
                    $qs .= substr($os, 0, -2) . ")";                                     
                
                } else {
                    //$qs .=  " $valoperator \"[$f]\" = \"$val\"" ;  //was before
                    $qs .=  " \"[$f]\" = \"$val\"" ;
                }
            
            // Use wildcard-like search
            } else {
                $qs .=  " \"[$f]\" =~ /" . $val . "/ " ;
            }
        
        //--- Number ---    
        } else {
            // Check for select-multiple
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                    $vList = explode(",", $val);
                    $os =  "(";
                    foreach ($vList as $v) {
                        $os .=  " ($valoperator \"[$f]\" = $v) OR" ;
                    }
                    $qs .= substr($os, 0, -2) . ")";    
            } else {
                // Check if there is another comparison operator than '=' defined
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " [$f] $valcompare $val ";
            }
        }
        
        $qs = " (" . $qs . ") ";
        return $qs;        
    }
    
    
    
    /**
     * For PostGIS layers
     */
    protected function getSearchParamsPG($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        $val = trim(addslashes($val));
        
        //--- String ---
        if ($type == "s") {
            // Explicitly use wildcards
            if ($wildcard == 1) {
                if ($val{0} == "*") {
                    $wc1 = "";
                    $val = substr($val, 1);
                } else {
                    $wc1 = "^";
                }
                
                if (substr($val, -1) == "*") {
                    $wc2 = "";
                    $val = substr($val, 0, -1);
                } else {
                    $wc2 = "$";
                }
                
                $qs .=  " $valoperator $f ~* '$wc1$val$wc2' " ;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                if (preg_match("/,/", $val)) {
                    $qs .= " $valoperator $f IN ('" . str_replace(",", "','", $val) . "')";
                } else {
                    $qs .=  " $valoperator $f = '$val' " ;
                }
            
            // Make normal search
            } else {
                $qs .=  " $valoperator $f ~* '$val' " ;
            }
        
        //--- Number ---
        } else {
            // Check for select-multiple
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                $qs .= " $valoperator $f IN ($val)";
            } else {
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " $valoperator $f $valcompare $val ";
            }
        }
        
        return $qs;
    }
    
    /**
     * For XY and Oracle layers
     */
    protected function getSearchParamsXY_DB($searchArray, $f, $fa, $type, $wildcard, $operator, $compare, $deftype, $val, $valoperator)
    {
        $qs = "";
        $val = trim(str_replace("\'", "''", $val));
        
        //--- String ---
        if ($type == "s") {
            // Explicitly use wildcards
            if ($wildcard == 1) {
                if ($val{0} == "*") {
                    $wc1 = "%";
                    $val = substr($val, 1);
                } else {
                    $wc1 = "";
                }
                
                if (substr($val, -1) == "*") {
                    $wc2 = "%";
                    $val = substr($val, 0, -1);
                } else {
                    $wc2 = "";
                }
                $val = strtoupper($val);
                $qs .=  " $valoperator UPPER($f) LIKE '$wc1$val$wc2' " ;
            
            // Use exact search because full value from OPTION or SUGGEST
            } elseif ($wildcard == 2) {
                if (preg_match("/,/", $val)) {
                    $qs .= " $valoperator $f IN ('" . str_replace(",", "','", $val) . "')";
                } else {
                    $qs .=  " $valoperator $f = '$val' " ;
                }
            
            } else {
                $val = strtoupper($val);
                $qs .=  " $valoperator UPPER($f) LIKE '%$val%' " ;
            }
        
        //--- Number ---
        } else {
            if ($wildcard == 2 && preg_match("/,/", $val)) {
                $qs .= " $valoperator $f IN ($val)";
            } else {
                $valcompare = (strlen($compare) > 0 ? $compare : ($searchArray["_fldoperator_$fa"] ? $searchArray["_fldoperator_$fa"] : "=") );
                $qs .=  " $valoperator $f $valcompare $val ";
            }
        }
        
        return $qs;
    }
    
    /**
     * Create OPTION list for selecting the serach items available
     * called from x_search.php
     */
    public function createSearchOptions()
    {
        $searchitems = $this->xml->xpath("/$this->xmlRoot/searchitem");

        $json  = '{"selectname": "findlist", "events": "onchange=\"setSearchInput()\"", ' ; 
        $json .= '"options": {"0": "' . _p("Search for") . '"';
        foreach ($searchitems as $si) {
            $description = _p((string)$si['description']);
            $optvalue = (string)$si['name'];
            $json .= ", \"$optvalue\": \"$description\"";
        }
        $json .= "}}"; 
            
        return $json;
    }
    
    
    /**
     * Create single search item, can be simple input, OPTION list, suggest field, checkbox, radio
     * called from x_search.php
     */
    public function createSearchItem($searchitem)
    {
        $searchitems = $this->xml->xpath("/$this->xmlRoot/searchitem[@name=\"$searchitem\"]");
        
        foreach ($searchitems as $si) {
            //$description = _p($si['description']);
            $searchitem = (string)$si['name'];
            
            $json  = "{\"searchitem\": \"$searchitem\", ";
            
            $layer = $si->layer;
            $fields = $layer->field;
            
            $json  .= "\"fields\": [";
            $fc = 0;
            
            foreach ($fields as $f) {
                $fjson = "";
                $description = addslashes(_p((string)$f['description']));
                $fldname = $f['alias'] ? (string)$f['alias'] : (string)$f['name'];
                $fldsize = $f['size'] ? (int)$f['size'] : "false";
                $fldsizedesc = $f['sizedesc'] ? (int)$f['sizedesc'] : "false";
                $fldinline = $f['inline'] ? (bool)$f['inline'] : "false";
                
                $sep = $fc < 1 ? "" : ",";
                $fjson .= "$sep{\"description\": \"$description\", ";
                $fjson .= "\"fldname\": \"$fldname\", ";
                $fjson .= "\"fldsize\": $fldsize, ";
                $fjson .= "\"fldsizedesc\": $fldsizedesc, ";
                $fjson .= "\"fldinline\": $fldinline";

                if ($f->definition['type'] == true) {
                    $retList = $this->getFieldDefinition((string)$layer['name'], $f, $searchitem, $fldname);
                    // Suggest things
                    if ($retList['newSuggest']) {
                        $fieldSuggest[$fldname] = $retList['newSuggest'];
                        $_SESSION['suggestList'][$searchitem] = $fieldSuggest;
                    }
                    
                    $fjson .= ", \"definition\": ";
                    $fjson .= $retList['json'];
                    //error_log($retList['json']);
                } else {
                    // do nothing, for the time being
                }
                $fjson .= "}";
                $fc++;
                
                $json  .= $fjson;
                //error_log($fjson);
                unset($fjson);
                
            }
            
            
            $json  .= "]}";
            
        }
        //error_log($json);
        
        return $json;
    
    }
    
    /**
     * Get the <definitions> for a <field> in the XML
     * called from createSearchItem()
     */
    protected function getFieldDefinition($layername, $field, $searchitem, $fldname)
    {
        $definition = $field->definition;
        $def_type = (string)$definition['type'];
        $def_connectiontype = (string)$definition['connectiontype'];
        $sort = (string)$definition['sort'];
        
        pm_logDebug(3, $definition, "XML->//definition");

        $events = '"events":' . ($definition->events ? '"' . addslashes(trim((string)$definition->events)) . '"' : 'false');
        
        // *** OPTIONS *** //
        if ($def_type == "options") {
            $firstoption = $definition['firstoption'] ? _p((string)$definition['firstoption']) : "*";
            
            // Database
            if ($def_connectiontype == "db") {
                $dsn      = (string)$definition->dsn;
                $encoding = (string)$definition->dsn['encoding'];
                $sql      = (string)$definition->sql;
                $optjson = $this->getOptionsFromDb($dsn, $sql, $encoding);
            
            // CSV file            
            } elseif ($def_connectiontype == "csv") {
                $csvfile   = $this->getDataFilePath((string)$definition->csvfile);
                $separator = (string)$definition->csvfile['separator'];
                $encoding  = (string)$definition->csvfile['encoding'];
                $optjson = $this->getOptionsFromCSV($csvfile, $separator, $encoding, $sort);
            
            // dBase file
            } elseif ($def_connectiontype == "dbase") {
                $dbasefile = $this->getDataFilePath((string)$definition->dbasefile);
                $encoding  = (string)$definition->dbasefile['encoding'];
                $keyfield  = (string)$definition->dbasefile['keyfield'];
                $showfield = (string)$definition->dbasefile['showfield'];
                $optjson = $this->getOptionsFromDbase($dbasefile, $encoding, $keyfield, $showfield, $sort);
                
            // MS layer
            } elseif ($def_connectiontype == "ms") {
                $mslayer   = $layername; 
                $encoding  = (string)$definition->mslayer['encoding'];
                $keyfield  = (string)$definition->mslayer['keyfield'];
                $showfield = (string)$definition->mslayer['showfield'];
                $optjson = $this->getOptionsFromMS($mslayer, $encoding, $keyfield, $showfield, $sort);
            
            // Inline definition as <option> tag
            } elseif ($def_connectiontype == "inline") {
                $optionlist = $definition->option;
                foreach ($optionlist as $o) {
                    $oarray[(string)$o['value']] = (string)$o['name'];
                }
                $optjson = $this->options_array2json($oarray, false);
            }
            
            $size = "\"size\": " . ($definition['size'] == true ?  (int)$definition['size']  : "0");
            $json .= "{\"type\":\"$def_type\", \"selectname\":\"$fldname\", \"firstoption\":\"$firstoption\", $size, $events, \"options\": "; 
            $json .= $optjson;
            $json .= "}";
            
            $retList['newSuggest'] = false;
        
        // *** SUGGEST *** //
        } elseif ($def_type == "suggest") {
            $minlength = (int)$definition['minlength'];
            $regexleft = (string)$definition['regexleft'];
            $startleft = (int)$definition['startleft'];
            $dependfld = (string)$definition['dependfld'];
            
            $newSuggest['type']      = $def_connectiontype;
            $newSuggest['sort']      = $sort;
            $newSuggest['minlength'] = $minlength;
            $newSuggest['regexleft'] = $regexleft;
            $newSuggest['startleft'] = $startleft;
            $newSuggest['dependfld'] = $dependfld;
            
            // Database 
            if ($def_connectiontype == "db") {
                $newSuggest['dsn']      = (string)$definition->dsn;
                $newSuggest['encoding'] = (string)$definition->dsn['encoding'];
                $newSuggest['sql']      = (string)$definition->sql;
                
            // TXT file            
            } elseif ($def_connectiontype == "txt") {
                $newSuggest['txtfile']   = $this->getDataFilePath((string)$definition->txtfile);
                $newSuggest['separator'] = (string)$definition->txtfile['separator'];
                $newSuggest['encoding']  = (string)$definition->txtfile['encoding'];
            
            // dBase file
            } elseif ($def_connectiontype == "dbase") {
                $newSuggest['dbasefile']   = $this->getDataFilePath((string)$definition->dbasefile);
                $newSuggest['encoding']    = (string)$definition->dbasefile['encoding'];
                $newSuggest['searchfield'] = (string)$definition->dbasefile['searchfield'];
        
            // MS layer
            } elseif ($def_connectiontype == "ms") {
                $newSuggest['mslayer']     = $layername; 
                $newSuggest['encoding']    = (string)$definition->mslayer['encoding'];
                $newSuggest['searchfield'] = (string)$field['name'];
                $newSuggest['fieldtype']   = (string)$field['type'];
            }
            
            $retList['newSuggest'] = $newSuggest;
            
            $json .= "{\"type\": \"$def_type\", \"searchitem\": \"$searchitem\", $events, \"minlength\": $minlength"; 
            $json .= "}";
            
        // Checkbox and radio inputs
        } elseif ($def_type == "checkbox") {
            $value     = addslashes((string)$definition['value']);
            $checked   = (int)$definition['checked'];
            $json = "{\"type\": \"$def_type\", \"value\": '$value', \"checked\": $checked }";
        
        } elseif ($def_type == "radio") {
            $iList = $definition->input;
            $json = "{\"type\": \"$def_type\", \"inputlist\": {";
            $ic = 0;
            foreach ($iList as $i) {
                $sep = $ic > 0 ? "," : "";
                $name    = (string)$i['name'];
                $value   = (string)$i['value'];
                $checked = (int)$definition['checked'];
                $json .= "$sep \"$value\": \"$name\"";
                $ic++;
            }
            $json .= "} }";
            //error_log($json);
        
        } elseif ($def_type == "operator") { 
                $optionlist = $definition->option;
                foreach ($optionlist as $o) {
                    $oarray[(string)$o['value']] = (string)$o['name'];
                }
                $optjson = $this->options_array2json($oarray, false);
                $json .= "{\"type\": \"$def_type\", \"selectname\": \"_fldoperator_" . $fldname . "\", \"options\": $optjson }"; 
        
        // Checkbox and radio inputs
        } elseif ($def_type == "hidden") {
            $keyval = (string)$definition['value'];
            $value = $_SESSION[$keyval];
            $json = "{\"type\": \"$def_type\", \"value\": '$value'}";
        
        }
        
        $retList['json'] = $json;
        //error_log($json);
        return $retList;
    }
    
    
    /**
     * Get the option values from a DBMS via PEAR
     */
    protected function getOptionsFromDb($dsn, $sql)
    {
        pm_logDebug(3, $sql, "search.php->getOptionsFromDb()");
        
        // Query DB
        $options = array (
                    'persistent'=>0,
                    'portability' => DB_PORTABILITY_ALL
        );
        
        // Load PEAR class
        $pearDbClass = $_SESSION['pearDbClass'];    
        require_once ("$pearDbClass.php");
        
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
        
        
        // Create output JSON
        $json = "{";
        
        $rc = 0;
        while ($row =& $res->fetchRow()) {
            $sep = $rc > 0 ? "," : "";
            $k = $row[0];
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[1]) : $row[1];
            $json .= "$sep \"$k\":" . "\"". addslashes(trim($v)) . "\"";
            $rc++;
        }
        
        $json .=  "}";
        
        //error_log($json);
        pm_logDebug(3, $json, "search.php->getOptionsFromDb()->json");
        
        return $json;
    }
    
    /**
     * Get the option values from a CVS file
     */
    protected function getOptionsFromCSV($csvfile, $separator, $encoding, $sort)
    {
        if (!is_file($csvfile)) {
            error_log("P.MAPPER ERROR: File $csvfile not existing.");
            return false;
        } 
        
        $fh = fopen($csvfile, "r");
        while (($row = fgetcsv($fh, 0, $separator)) !== FALSE) {
            $k = trim($row[0]);
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[1]) : $row[1];
            $ol[$k] = addslashes(trim($v));
        }
        //print_r($ol);
        fclose($fh);
        
        $json = $this->options_array2json($ol, $sort);
        //error_log($json);
        return $json;
    }
    
    /**
     * Get the option values from a dBase file
     */
    protected function getOptionsFromDbase($dbasefile, $encoding, $keyfield, $showfield, $sort)
    {
        // Load dbf extension on Windows if needed
        if (PHP_OS == "WINNT" || PHP_OS == "WIN32") {
            if (!extension_loaded('dBase')) {
                dl("php_dbase.dll");
            }
        }
        
        if (!is_file($dbasefile)) {
            error_log("P.MAPPER ERROR: File $dbasefile not existing.");
            return false;
        }
        
        $dbf = dbase_open($dbasefile, 0);
        if (!$dbf) error_log("P.MAPPER ERROR: dBase file $dbf_file could not be opened");
        
        $record_numbers = dbase_numrecords($dbf);
        
        for ($i = 1; $i <= $record_numbers; $i++) {
            $row = dbase_get_record_with_names($dbf, $i);
            $k = trim($row[$keyfield]);
            $v = $encoding != "UTF-8" ? iconv($encoding, "UTF-8", $row[$showfield]) : $row[$showfield];

            //if (strlen(trim($k)) > 0) 
            $ol[$k] = trim($v); //addslashes($v);
        }
        dbase_close($dbf);
        
        $json = $this->options_array2json($ol, $sort);
        
        //error_log($json);
        return $json;
    }
    
    
    /**
     * Get the option values from a MapServer layer
     */
    protected function getOptionsFromMS($mslayer, $encoding, $keyfield, $showfield, $sort)
    {
        $msVersion = $_SESSION['msVersion']; 
        if (!extension_loaded('MapScript')) {
            dl("php_mapscript$msVersion." . PHP_SHLIB_SUFFIX);
        }
        
        $PM_MAP_FILE = $_SESSION['PM_MAP_FILE'];
        $map = ms_newMapObj($PM_MAP_FILE);
        
        $qLayer = $map->getLayerByName($mslayer);
        
        $query = $qLayer->queryByAttributes($keyfield, "/()/", MS_MULTIPLE);
        if ($query == MS_SUCCESS) {
            $qLayer->open();
            $numResults = $qLayer->getNumResults();
            //error_log($numResults);
            for ($iRes=0; $iRes < $numResults; $iRes++) {
                $qRes = $qLayer->getResult($iRes);
                $qShape = $qLayer->getShape($qRes->tileindex,$qRes->shapeindex);
                $k = $qShape->values[$keyfield];
                $v = $qShape->values[$showfield];
                if ($encoding != "UTF-8") {
                    $fldValue = iconv($encoding, "UTF-8", $fldValue);
                }
                $ol[$k] = trim($v);
                $qShape->free();
            }
            $json = $this->options_array2json($ol, $sort);
            return $json;
        }
        
    }
  
  
    protected function options_array2json($array, $sort)
    {
        $uarray = array_unique($array);
        
        if ($sort == "asc") {
            natsort($uarray);
        } elseif ($sort == "desc") {
            arsort($uarray);
        }
        
        $json = '{';
        $rc = 0;
        foreach ($uarray as $k=>$v) {
            $sep = $rc > 0 ? "," : "";
            $json .= "$sep \"" . $k . "\":" . "\"". addslashes($v) . "\"";
            $rc++;
        }
        $json .= '}';

        return $json;
    }
    

    protected function getDataFilePath($inpath)
    {
        $config_dir = $_SESSION['PM_CONFIG_DIR'];
        
        if ($inpath{0} == "/" || $inpath{1} == ":") {
            $retPath = $inpath;
        } else {
            if ($inpath{0} == "$") {
                if ($this->dataRoot{0} == "/" || $this->dataRoot{1} == ":") {
                    $retPath = $this->dataRoot . substr($inpath, 1);
                } else {
                    $retPath = str_replace('\\', '/', realpath($config_dir . "/" . $this->dataRoot)) . substr($inpath, 1);
                }
                
            } else {
                $retPath = str_replace('\\', '/', realpath($config_dir . "/" . $inpath));
            }
        }
        //error_log($retPath);
        pm_logDebug(3, $retPath, "Data path for XML search \nfile: " . __FILE__ . "\nfunction: " . __FUNCTION__);
        
        return $retPath;
    }
    
}



?>
