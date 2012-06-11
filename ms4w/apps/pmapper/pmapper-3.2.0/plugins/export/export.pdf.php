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
class ExportPDF extends ExportQuery
{
    
    
    /**
     * Init function
     */
    function __construct($json)
    {
        require('mc_table.php');
        parent::__construct($json);
        
        
        $pdf=new PDF_MC_Table();
        $pdf->Open();
            
        $groups = (array)$this->jsonList[0];

        foreach ($groups as $grp) {

            $ret = $this->prepareData4PDF($grp);
            $colsPerc = $ret[0];
            $data = $ret[1];
            
            $pdfW = 180;
            $cols = array();
            foreach ($colsPerc as $cp) {
                $cols[] = $cp * $pdfW;
            }
            
            $pdf->AddPage();
            $pdf->SetFont('FreeSans','',9);
            $pdf->SetWidths($cols); 

            foreach ($data as $row) {
                $pdf->Row($row);
            }
        }
        
        $pdfFilePath = $_SESSION['web_imagepath'] . session_id() . ".pdf";
        $this->tempFileLocation = $_SESSION['web_imageurl'] . session_id() . ".pdf" ;
        $pdf->Output($pdfFilePath, "F");
        

    }
    
    
    function prepareData4PDF($grp)
    {
        $data = array();
        
        $headerList = $grp->header; 
        $cols = array();
        
        $withShpLink = 0;
        $headerLine = array();
        foreach ($headerList as $h) {
            if ($h != "@") {
                $cols[] = 0;
                $headerLine[] = $h;
            } else {
                $withShpLink = 1;
            }
        }
        $data[] = $headerLine;
        
        // Values
        $values = $grp->values; 

        foreach ($values as $vList) {
            $valLine = array();
            $vL = count($vList);
            $start = $withShpLink ? 1 : 0;
            for ($i=$start; $i<$vL; $i++) {
                $ii = $withShpLink ? $i-1 : $i;
                // Links
                $v = $vList[$i];
                if (is_object($v)) {
                    $shplink = $v->shplink;
                    if ($shplink) {
                    }
                    
                    $hyperlink = $v->hyperlink;
                    if ($hyperlink) {
                        $cols[$ii] = max ($cols[$ii], strlen($hyperlink[2]));   
                        $valLine[] = $hyperlink[2];
                    }
                } else {
                    $cols[$ii] = max ($cols[$ii], strlen($v)); 
                    $valLine[] = $v;
                }
            }
            $data[] = $valLine;
        }
        
        $csum = array_sum($cols);
        $colsPerc = array();
        foreach ($cols as $c) {
            $colsPerc[] = round($c/$csum, 2);
        }
        
        return array($colsPerc, $data);
    }
    
        

   
}

?>