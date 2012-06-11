<?php
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

session_start();

?>

/******************************************************************************
 *
 * Purpose: Additionnal buttons for each groups / layer in TOC plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * The software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

<?php

$abtgArray = $_SESSION["abtgArray"];

// write the different elements in order to use them after TOC loading in js 

$js = "var abtgArray = new Array(" . count($abtgArray) . ");\n";

$iBtn = 0;
foreach ($abtgArray as $abtgElement) {
	$js .= "abtgArray[" . $iBtn . "] = new Array(4);\n";
	$js .= "abtgArray[" . $iBtn . "][0] = '" . $abtgElement["prefix"] . "';\n";
	$js .= "abtgArray[" . $iBtn . "][1] = '" . $abtgElement["hrefjsfunction"] . "';\n";
	$js .= "abtgArray[" . $iBtn . "][2] = '" . $abtgElement["titleandimgalttext"] . "';\n";
	$js .= "abtgArray[" . $iBtn . "][3] = '" . $abtgElement["imgsrc"] . "';\n";
	$iBtn++;
}

echo $js;

?>

/**
 * Init function called after TOC loading
 *
 * It will call "createLinkButtonToGroup" function for each couple groups - button to add
 */
function abtgAfterTocInit() {
	$('#toc .grp').each(function() {
		var grpparent = $(this).parent();
// Be carrefull : in IE and Firefox, split function return different arrays
// if test is on the begining of string...
		var gnames = $(this).find('[@id^=\'spxg_\']').id().split(/spxg_/);
		if (gnames.length > 0) {
			var gname = gnames[gnames.length - 1];
			if (gname.length > 0) {
				for (var iBtn = 0 ; iBtn < abtgArray.length ; iBtn++) {
					var abtgBtn = abtgArray[iBtn];
					if (typeof(abtgBtn) != 'undefined') {
						if (abtgBtn) {
							if (abtgBtn.length == 4) {
								createLinkButtonToGroup(gname, abtgBtn[0], abtgBtn[1], abtgBtn[2], abtgBtn[3]);
							}
						}
					}
				}
			}
		}
	});
}

/**
 * Create a link with an image as last element of the group in TOC
 */
function createLinkButtonToGroup(grpname, idprefix, jsfunction, titleandimgalttext, imgsrc) {
	var grpDivId = 'tgrp_' + grpname;
	var grpDiv = $('#' + grpDivId);
	if (grpDiv.size() > 0) {
		var btnDivId = 'abtg_' + idprefix + '_' + grpname;
		var btnDiv = $('#' + btnDivId);
		if (btnDiv.size() == 0) {
			grpDiv.append('<a href="javascript:' + jsfunction + '(\'tgrp_' + grpname + '\')" title="' + titleandimgalttext + '"><img alt="' + titleandimgalttext + '" src="' + imgsrc + '" /></a>');
		}
	}
}
