<?php

/******************************************************************************
 *
 * Purpose: PDF printing functions
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


require_once('tcpdf.php');
require_once('print.php');


class Pdf extends TCPDF
{

    function Pdf($map, $printScale, $orientation, $units, $format, $pdfSettings, $prefmap=true)
    {
        $this->TCPDF($orientation,$units,$format,true);
        
        $mapW = $pdfSettings['width']; 
        $mapH = $pdfSettings['height'];
        $this->pdfSettings = $pdfSettings;
        $this->prefmap = $prefmap;
        
        $printMap = new PRINTMAP($map, $mapW, $mapH, $printScale, "pdf", 144);
        $printUrlList = $printMap->returnImgUrlList();
        
        $this->initPDF($pdfSettings['author'], $pdfSettings['pdftitle'], $pdfSettings['defFont'], $pdfSettings['defFontSize']);
        $this->printPDF($map, $mapW, $mapH, $printUrlList, $printScale);
        $this->printScale(_p("Scale"), $printScale);
        $this->printLegendPDF($map, $printScale, 30, 500);
    }
    
    
    function initPDF($author, $title, $defFontType, $defFontSize)
    {
        $this->SetFont($defFontType, "B", $defFontSize);
        $this->setAuthor($author);
        $this->setTitle($title);
        $this->Open();
        $this->SetLineWidth(1.5);
        $this->AddPage();
        $this->defaultFontType = $defFontType;
        $this->defaultFontSize = $defFontSize;
    }
    
    
    // FONTS 
    function resetDefaultFont()
    {
        $this->SetFont($this->defaultFontType, "", $this->defaultFontSize); 
        $rgb = preg_split("/[\s,]/", $this->pdfSettings['defFontColor']); 
        $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
    }
    
    
    
    /*
     * PRINT FUNCTIONS
     ***********************/
    
    // MAIN PDF PAGE PTINTING
    function printPDF($map, $pdfMapW, $pdfMapH, $printUrlList, $prScale)
    {
        $printmapUrl  = $printUrlList[0];
        $printrefUrl  = $printUrlList[1];
        $printsbarUrl = $printUrlList[2];
    
        // Dimensions: A4 Portrait
        $pageWidth = 595;
        $pageHeight = 842;
    
        // Reduction factor, calculated from PDF resolution (72 dpi)
        // reolution from map file and factor for increased map size for PDF output
        //$redFactor = 72/(($map->resolution) / $_SESSION["pdfres"]);
        $redFactor = 72 / 96; 
    
        $imgWidth = $pdfMapW * $redFactor;
        $imgHeight = $pdfMapH * $redFactor;
    
        // Margin lines around page
        $this->margin = round(($pageWidth - ($pdfMapW * $redFactor)) / 2);
        $this->xminM = $this->margin;                                     //   ____________
        $this->yminM = $this->margin;                                     //  |             |topLineDelta
        $this->xmaxM = $pageWidth - $this->margin;                        //  |------------  topLineY
        $this->ymaxM = $pageHeight - $this->margin;                       //  |   IMG
                                                                          //  |
        $this->topLineDelta = $this->pdfSettings['top_height'];           //  |------------  botLineY
        $this->topLineY = $this->yminM + $this->topLineDelta;             //  |   LEG
        $this->botLineY = $this->topLineY + $imgHeight;                   //  |------------
    
    
        // Draw Map Image
        $web = $map->web;
        $basePath = $web->imagepath;
        $mapImgBaseName = basename($printmapUrl);
        $mapImgFullName = $basePath . $mapImgBaseName;
        $this->Image($mapImgFullName, $this->xminM, $this->topLineY , $imgWidth, $imgHeight);
    
        //Draw Reference Image
        if ($this->prefmap) {
            $refImgBaseName = basename($printrefUrl);
            $refImgFullName = $basePath . $refImgBaseName;
            $refmap = $map->reference;
            $this->refmapwidth = ($refmap->width) * $redFactor ;
            $this->refmapheight = ($refmap->height) * $redFactor ;
            $this->Image($refImgFullName, $this->xminM, $this->topLineY, $this->refmapwidth, $this->refmapheight);
        }
        
        //Draw Scalebar Image
        $sbarImgBaseName = basename($printsbarUrl);
        $sbarImgFullName = $basePath . $sbarImgBaseName;
        $sbar = $map->scalebar;
        $sbarwidth = ($sbar->width) * $redFactor ;
        $sbarheight = ($sbar->height);
        $this->Image($sbarImgFullName, $this->xminM, $this->botLineY - 20, $sbarwidth, $sbarheight + 15);
        
        // Print title bar with logo
        $this->printTitle($this->pdfSettings['printtitle']);
        
        // Print frame lines (margins, inner frames)
        $this->printFrameLines(1);
        
        $this->redFactor = $redFactor;
    }
    
    
    // PRINT OUTER AND INNER FRAME LINES AROUND IMAGE AND LEGEND
    function printFrameLines($firstPage)
    {
        $this->printMargins();
    
        // Inner frame lines
        $this->SetLineWidth(1);
        $this->Line($this->xminM, $this->topLineY, $this->xmaxM, $this->topLineY);
        
        if ($firstPage) { 
            // Bottom line for map image
            $this->Line($this->xminM, $this->botLineY, $this->xmaxM, $this->botLineY);
        
            // Frame around ref map
            if ($this->prefmap) {
                $this->Line($this->xminM, $this->topLineY + $this->refmapheight, $this->xminM + $this->refmapwidth, $this->topLineY + $this->refmapheight);
                $this->Line($this->xminM + $this->refmapwidth, $this->topLineY + $this->refmapheight, $this->xminM + $this->refmapwidth, $this->topLineY);
            }
        }
    }
    
    // OUTER (MARGIN) LINES
    function printMargins()
    {
        // Outer margin
        $this->SetLineWidth(1.5);
        $this->Line($this->xminM, $this->yminM, $this->xminM, $this->ymaxM);
        $this->Line($this->xminM, $this->ymaxM, $this->xmaxM, $this->ymaxM);
        $this->Line($this->xmaxM, $this->ymaxM, $this->xmaxM, $this->yminM);
        $this->Line($this->xmaxM, $this->yminM, $this->xminM, $this->yminM);
    }
    
    
    // TITLE IN TITLE BAR
    function printTitle($prTitle)
    {
        // Draw background in image color
        $rgb = preg_split("/[\s,]/", $this->pdfSettings['top_bgcolor']); 
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $this->Rect($this->xminM, $this->yminM, $this->xmaxM - $this->xminM, $this->topLineDelta , "F");
        
        // Print logo image
        
        if ($this->pdfSettings['top_logo']) $this->Image($this->pdfSettings['top_logo'], $this->xminM, $this->yminM);
        
        if (strlen($prTitle) > 0) {
            // Print title
            $trgb = preg_split("/[\s,]/", $this->pdfSettings['top_color']); 
            $this->SetTextColor($trgb[0], $trgb[1], $trgb[2]);
            $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize + 5);
            $this->SetXY($this->xminM + 120, $this->yminM + (0.5 * $this->topLineDelta));
            $this->Cell(0, 0, $prTitle);
        }    	
    }
    
    // SCALE ABOVE SCALEBAR
    function printScale($prString, $prScale)
    {
        $prString = $prString;    
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize);
        $scaleStr = $prString . " 1: $prScale";
        $this->SetXY($this->xminM + 6, $this->botLineY - 25);
        $this->Cell(50, 0, $scaleStr);
    }
    
    
    // 2-COLUMN LEGEND
    function printLegendPDF($map, $scale)
    {
        $grouplist = $_SESSION["grouplist"];
        $defGroups = $_SESSION["defGroups"];
        $icoW      = $_SESSION["icoW"] * $this->redFactor;  // Width in pixels
        $icoH      = $_SESSION["icoH"] * $this->redFactor;  // Height in pixels
        $imgExt    = $_SESSION["imgFormatExt"];
        
        $this->resetDefaultFont();
        
        // Vertical differerence between lines (for new groups and classes)
        /*$dy_grp = $icoH + 4;
        $dy_cls = $icoH + 2; */
    
        // GET LAYERS FOR DRAWING AND IDENTIFY
        if (isset ($_SESSION["groups"]) && count($_SESSION["groups"]) > 0){
            $groups = $_SESSION["groups"];
        }else{
            $groups = $defGroups;
        }
    
        $legPath = "images/legend/";
    
        $x0 = $this->xminM + 10;
        $x = $x0;
        $y = $this->botLineY + 10;
    
        $xr = (($this->xmaxM - $this->xminM) + (2 * $this->margin)) / 2 + 5;
        $mcellW = (($this->xmaxM - $this->xminM) / 2) - $icoW - 28;
    
        // Text Color for legend annotations
        //$this->SetTextColor(0, 0, 0);
    
        foreach ($grouplist as $grp){
            if (in_array($grp->getGroupName(), $groups, TRUE)) {
                $glayerList = $grp->getLayers();
                $grpClassList = array();
                $grpcnt = -1;  // used to identify if layers are still in the same group
    
                // Get number of classes for each group
                // write group classes to array
                $numcls = 0;
                foreach ($glayerList as $glayer) {
                    $legendLayer = $map->getLayer($glayer->getLayerIdx());
                    $numClasses = count($glayer->getClasses());
                    $skipLegend = $glayer->getSkipLegend();
                    //error_log($legendLayer->name . " numcl " . $numClasses); 
                    
                    //if ($legendLayer->type < 3 && checkScale($map, $legendLayer, $scale) == 1) {
                    if (($legendLayer->type < 3 || $legIconPath || $numClasses > 0) && checkScale($map, $legendLayer, $scale) == 1 && $skipLegend < 2) {
    
                        $leglayers[] = $legendLayer;
                        $numcls += $legendLayer->numclasses;
                        
                        $legLayerName = $glayer->getLayerName();
                        $layClasses = $glayer->getClasses();
                        $clsno = 0;
                        foreach ($layClasses as $cl) {
                            $legIconPath = $legendLayer->getClass($clsno)->keyimage;
                            $icoUrl = $legIconPath ? $legIconPath : $legPath.$legLayerName.'_i'.$clsno.'.'.$imgExt;
                            //error_log($icoUrl);
                            $grpClassList[] = array($cl, $icoUrl);
                            $clsno++;
                        }
                    }
                }
    
                //error_log("$numcls  \n");
                // Only 1 class for all Layers -> 1 Symbol for Group
                if ($numcls == 1) {
                    $legLayer = $leglayers[0];
                    $legLayerName = $legLayer->name;
                    //$icoUrl = $legPath.$legLayerName.'_i0.'.$imgExt;
                    $icoUrl = $grpClassList[0][1];
                    //error_log($icoUrl);
    
                    // Putput PDF
                    $this->Image($icoUrl, $x, $y, $icoW, $icoH);
                    $this->SetXY($x + $icoW + 5, $y + 6);
                    $this->SetFont($this->defaultFontType, "B", $this->defaultFontSize);
                    
                    $grpDescription = $grp->getDescription();
                    $this->Cell(0, 0, $grpDescription);
    
                    $y += 18;   // y-difference between GROUPS
    
                // More than 2 classes for Group  -> symbol for *every* class
                } elseif ($numcls > 1) {
                    $this->SetXY($x - 2, $y + 6);
                    //$this->SetFont($this->defaultFontType, "B", $this->defaultFontSized);
                    
                    $grpDescription = $grp->getDescription();
                    $this->Cell(0, 0, $grpDescription);
                    $y += 14;  // y-difference between GROUP NAME and first class element
    
                    $allc = 0;
                    $clscnt = 0;
    
                    #if ($clscnt < $numcls) {
                    foreach ($grpClassList as $cls) {
                        $clsStr = $cls[0];
                        $icoUrl = $cls[1];
                        $clno = 0;
                        
                        // Output PDF
                        $this->Image($icoUrl, $x, $y, $icoW, $icoH);
                        $this->SetFont($this->defaultFontType, "", $this->defaultFontSized);
    
                        // What to do if CLASS string is too large for cell box
                        if ($this->GetStringWidth($clsStr) >= $mcellW) {   // test if string is wider than cell box
                            $mcellH = 10;
                            $ydiff = 0;
                            $yadd = 1;
                        } else {
                            $mcellH = 0;
                            $ydiff = 6;
                        }
                        $this->SetXY($x + $icoW + 5, $y + $ydiff);
                        $this->MultiCell($mcellW, $mcellH, $clsStr, 0, "L", 0);
    
                        // change x and y coordinates for img and cell placement
                        if ($clscnt % 2) {   // after printing RIGHT column
                            $y += 16;
                            $y += ($clscnt == ($numcls - 1) ? 2 : 0);  // Begin new group when number of printed classes equals total class number
                            $x = $x0;
                            if ($yadd) $y += 8;
                            $yadd = 0;
                        } else {           // after printing LEFT column
                            if ($clscnt == ($numcls - 1)) {    // Begin new group when number of printed classes equals total class number
                                $y += 18;
                                $x = $x0;
                            } else {
                                $x = $xr;     // Continue in same group, add only new class item
                            }
                        }
                        
                        $allc++;
                        $clscnt++;
    
                        // if Y too big add new PDF page and reset Y to beginning of document
                        if ($y > (($this->ymaxM) - 30)) {
                            $this->AddPage("P");
                            $this->printTitle("");
                            $this->printFrameLines(0);
                            $this->resetDefaultFont();
                            $y = $this->yminM + 35;
                        }
                    }
                }
            }
            unset($leglayers);
            unset($grpClassList);
        }
    }


}  // END CLASS


?>