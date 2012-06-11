<?php
/******************************************************************************
 *
 * Purpose: class for printing based on XML template
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
 * class for printing based on XML template
 */
class PrintXML
{
    var $xml;
    var $printScale;
    var $printTitle;
    var $printLegend;
    var $printUrlList;
    
    
    /**
     * Init function
     */
    function __construct($xmlstr)
    {   
        if ($xmlstr) {
            $this->xml = simplexml_load_string($xmlstr);
        } else {
            $xmlFN = $_SESSION['PM_PRINT_CONFIGFILE'];
            $this->xml = simplexml_load_file($xmlFN);
        }
    }
    
    /**
     * Get the title for printing, either from REQUEST or from XML file
     */
    function getPrintTitle()
    {
        global $pmPrintTitle;
        if (isset($_REQUEST['printtitle'])) {
            return $_REQUEST['printtitle'];
        } elseif (isset($pmPrintTitle)) {
            return $pmPrintTitle;
        } else {
            $printTitle = (string)$this->xml->settings->printtitle;
            return $this->parseContent($printTitle); 
        }
    }
    
    /**
     * eval string to PHP variable for $$strings
     */
    function evalString($str)
    {
        $printUrlList = $this->printUrlList;
        $printScale   = $this->printScale;
        $printTitle   = $this->printTitle;
        $printLegend  = $this->printLegend;
        if (substr(trim($str), 0, 2) == "$$") {
            $str = substr(trim($str), 1);
            eval("\$str = \"$str\";");
            return $str;
        } elseif (substr(trim($str), 0, 2) == "@@") {
            return _p(substr(trim($str), 2));

        } else {
            return $str;
        }
    }
    
    /**
     * Parse content of string accoring to string prefix
     */
    function parseContent($str) 
    {
        if (substr($str, 0, 2) == "##") {
            return substr($str, 2);
        } else {
            $pattern = "/@\[([\d\w\s\.\;\,\:\-\(\)\[\]\@\&\*\+\=\%\?\/\\\]*)\]@/";
            $strList = preg_split($pattern, $str, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY );
            if (count($strList) > 0) {
                $ret = "";
                foreach ($strList as $s) {
                    if (strpos($s, "$$") !== false) {
                        $strListVars = preg_split("/\s/", $s);
                        foreach ($strListVars as $v) {
                            if (substr(trim($v), 0, 2) == "$$") {
                                $ret .= $this->evalString(trim($v)) . " ";
                            } else {
                                $ret .= $v . " ";
                            }
                        }
                    } else {
                        $ret .= _p(trim($s)) . " ";
                    }   
                }
                return $ret;
            }
        }
    }
    
}
        


/**
 * Class for creating HTML output for printing
 * uses print.xml as template
 */
class PrintXML_HTML extends PrintXML
{
    
    function __construct($xpath, $printScale=false, $xmlstr=false)
    {
        parent::__construct($xmlstr);
        $this->printScale = $printScale;
        $this->printTitle = $this->getPrintTitle();
        $this->xpath = $xpath;
    }
    
    /**
     * create print HTML based on XML structure 
     */
    function xmlToHtml($printUrlList, $prefmap, $printLegend)
    {
        $this->printUrlList = $printUrlList;
        $this->prefmap = $prefmap;
        $this->printLegend = $printLegend;
        $this->xmlHtml = $this->xml->xpath($this->xpath);
        $this->html = "";
        $this->indent = "";
        
        $this->parseToHTML($this->xmlHtml);
        
        return $this->html;
    }
    
    /**
     * parse print XML to HTML output
     */
    function parseToHTML($element)
    {
        $this->indent .= "  ";
        
        foreach ($element as $el) {            
            $elName = $el->getName();
            $elAttr = $el->attributes();
            
            // Break loop and continue if refmap not added to map
            $attr = "";
            $refmapbreak = 0;
            foreach ($el->attributes() as $k=>$v) {
                if (!$this->prefmap && $v == "print_map_refimage") $refmapbreak = 1;
                $attr .= " $k=\"" . $this->evalString($v) . "\" ";
            }
            
            if ($refmapbreak) continue;
            
            $this->html .= $this->indent;
            $this->html .= "<";
            $this->html .= $elName;
            $this->html .= $attr;
            
            $elStr = trim((string)$el);
            
            $this->html .= ">" . $this->parseContent($elStr) ."\n";
            
            $this->parseToHTML($el);
            $this->html .= $this->indent;
            $this->html .= "</$elName>\n";
            
        }
        $this->indent = substr($this->indent, 0, -2);
    
    }
    
    
    /**
     * Return print settings for given paper size and orientation
     */
    function getPrintParams($papersize, $orientation, $maptype)
    {
        $ret['printtitle'] = $this->getPrintTitle();
        
        $xmlSettings = $this->xml->xpath("/print/settings/html/format[@papersize=\"$papersize\" and @orientation=\"$orientation\"]/map[@type=\"$maptype\"]");        
        foreach ($xmlSettings[0]->attributes() as $k=>$v) {
            $ret[$k] = (string)$v;
        }
        
        //print_r($ret);
        return $ret; 
    }
    
    
}


/**
 * Class for gettings parameters for PDF printing
 * uses print.xml as template
 */
class PrintXML_PDF extends PrintXML
{
    function __construct($xmlstr=false)
    {
        parent::__construct($xmlstr);
        $this->printTitle = $this->getPrintTitle();
    }
    
    /**
     * Return print settings for given paper size and orientation
     */
    function getPrintParams($papersize, $orientation, $maptype)
    {
        $ret = (array)$this->xml->settings->pdf;
        $ret['printtitle'] = $this->getPrintTitle();
        
        $xmlSettings = $this->xml->xpath("/print/settings/pdf/format[@papersize=\"$papersize\" and @orientation=\"$orientation\"]/map[@type=\"$maptype\"]");        
        foreach ($xmlSettings[0]->attributes() as $k=>$v) {
            $ret[$k] = (string)$v;
        }
        
        pm_logDebug(3, $ret, "PDF printing settings");
        
        return $ret; 
    }
    
}

?>