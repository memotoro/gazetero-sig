<?php

/******************************************************************************
 *
 * Purpose: export query results as XLS document
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
 * Export results to CSV files
 * 1 file per result group
 */
class ExportCSV extends ExportQuery
{
    
    /**
     * Init function
     */
    function __construct($json)
    {
        parent::__construct($json);
        
        // Delimiter and quote characters
        $del = ",";
        $enc = '"';

        $groups = (array)$this->jsonList[0];
        $fileList = array();
        foreach ($groups as $grp) {
            $this->tempFilePath = $_SESSION['web_imagepath'] . $grp->name . "_" . session_id(). ".csv";
            $this->tempFileLocation = $_SESSION['web_imageurl'] . $grp->name . "_" . session_id(). ".csv";
            $fileList[] = $this->tempFilePath;
            $fp = fopen($this->tempFilePath, "w");
            
            // Header
            $headerList = $grp->header; 
            $csv_header = array();
            foreach ($headerList as $h) {
                $headline = $headerList[$hi];
                if ($h == "@") {
                    //$col--;
                } else {
                    $csv_header[] = $h;
                }
            }
            $this->fwritecsv($fp, $csv_header, $del, $enc);

            // Values
            $values = $grp->values; 
            $csv_val = "";
            foreach ($values as $vList) {
                $csv_row = array();
                foreach ($vList as $v) {
                    // Links
                    if (is_object($v)) {
                        $shplink = $v->shplink;
                        if ($shplink) {
                        }
                        
                        $hyperlink = $v->hyperlink;
                        if ($hyperlink) {
                            $csv_row[] = $hyperlink[2]; //str_replace($enc, "@@@@", $hyperlink[3]); //           
                        }
                    } else {
                        $csv_row[] = $v; //str_replace($enc, "@@@@", $v); 
                                                 
                    }
                }
                $this->fwritecsv($fp, $csv_row, $del, $enc);
            }
            
            fclose($fp);
            unset($fp);
        }
        
        // Write all csv files to zip
        $this->tempFileLocation = $_SESSION['web_imageurl'] . session_id() . ".zip" ;
        $zipFilePath = $_SESSION['web_imagepath'] . session_id() . ".zip";
        packFilesZip($zipFilePath, $fileList, true, true);

    }
    
    /**
     * Write string to CSV file pointer
     */
    function fwritecsv($filePointer, $dataArray, $delimiter, $enclosure)
    {
        // Write a line to a file
        // $filePointer = the file resource to write to
        // $dataArray = the data to write out
        // $delimeter = the field separator

        // Build the string
        $string = "";

        // No leading delimiter
        $writeDelimiter = FALSE;
        foreach($dataArray as $dataElement) {
            // Replaces a double quote with two double quotes
            $dataElement=str_replace("\"", "\"\"", $dataElement);

            // Adds a delimiter before each field (except the first)
            if($writeDelimiter) $string .= $delimiter;

            // Encloses each field with $enclosure and adds it to the string
            $string .= $enclosure . $dataElement . $enclosure;

            // Delimiters are used every time except the first.
            $writeDelimiter = TRUE;
        } // end foreach($dataArray as $dataElement)

        // Append new line
        $string .= "\n";

        // Write the string to the file
        fwrite($filePointer,$string);
    }

    

   
}

?>