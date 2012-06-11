
/******************************************************************************
 *
 * Purpose: ThemesAndViews plugin
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2007 SIRAP
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This program is distributed in the hope that it will be useful,
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
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

//require_once("../../incphp/group.php");
session_start();

require_once($_SESSION['PM_PLUGIN_REALPATH'] . "/sirapcommon/easyincludes.php");

// url of pmapper directory, for use in AJAX requests
$urlReqDir = getURLReqDir();
$pmapDir = $urlReqDir . "../../";
echo ("var tavUrlPmDir = '" . $pmapDir . "';\n");

// Do we have to insert the corresponding divs for Themes or Views ?
$autoInsertThemes = (($_SESSION["tavThemesBoxType"] == 2) || ($_SESSION["tavThemesBoxType"] == 3)) && $_SESSION["tavThemesBoxContainer"]; 
$autoInsertViews = (($_SESSION["tavViewsBoxType"] == 2) || ($_SESSION["tavViewsBoxType"] == 3)) && $_SESSION["tavViewsBoxContainer"];

// If auto-insertion of divs, is it in the "TOC" div ?
// ie : if yes, the initialisation has to be done after toc loading
$initThemesAfterTOC = $autoInsertThemes && ($_SESSION["tavThemesBoxContainer"] == "toc");
$initViewsAfterTOC = $autoInsertViews && ($_SESSION["tavViewsBoxContainer"] == "toc");

// Selected theme or view have to keep selected in the box ?
$tavThemesKeepSelected = ($_SESSION["tavThemesBoxType"] != 0) && $_SESSION["tavThemesKeepSelected"];
$tavViewsKeepSelected = ($_SESSION["tavViewsBoxType"] != 0) && $_SESSION["tavViewsKeepSelected"];

// Load default theme or view at the begining ?
$tavSetDefaultTheme = ($_SESSION["tavSetDefault"] == "theme");
$tavSetDefaultView = ($_SESSION["tavSetDefault"] == "view");

// writte all js parameters :
echo ("var autoInsertThemes = " . ($autoInsertThemes ? "true" : "false") . ";\n");
echo ("var autoInsertViews = " . ($autoInsertViews ? "true" : "false") . ";\n");
if ($autoInsertThemes) echo ("var tavThemesBoxContainer = '" . $_SESSION["tavThemesBoxContainer"] . "';\n");
if ($autoInsertViews) echo ("var tavViewsBoxContainer = '" . $_SESSION["tavViewsBoxContainer"] . "';\n");
echo ("var initThemesAfterTOC = " . ($initThemesAfterTOC ? "true" : "false") . ";\n");
echo ("var initViewsAfterTOC = " . ($initViewsAfterTOC ? "true" : "false") . ";\n");
echo ("var insertThemeBoxAtFirstPos = " . (($_SESSION["tavThemesBoxType"] == 2) ? "true" : "false") . ";\n");
echo ("var insertViewBoxAtFirstPos = " . (($_SESSION["tavViewsBoxType"] == 2) ? "true" : "false") . ";\n");
echo ("var tavThemesSelBoxStr = '<div id=\"selThemeBox\" class=\"tavSelectBox\" />';\n");
echo ("var tavViewsSelBoxStr = '<div id=\"selViewBox\" class=\"tavSelectBox\" />';\n");
echo ("var tavThemesKeepSelected = " . ($tavThemesKeepSelected ? "true" : "false") . ";\n");
echo ("var tavViewsKeepSelected = " . ($tavViewsKeepSelected ? "true" : "false") . ";\n");
echo ("var tavSetDefaultTheme = " . ($tavSetDefaultTheme ? "true" : "false") . ";\n");
echo ("var tavSetDefaultView = " . ($tavSetDefaultView ? "true" : "false") . ";\n");
echo ("var tavDefaultCodeValue = \"" . $_SESSION["tavDefaultCodeValue"] . "\";\n");

?>	

/**
 * Initialisation of themes or views, but after TOC loading
 */
function tavAfterTocInit() {
	if (initThemesAfterTOC) tavInitThemes();
	if (initViewsAfterTOC) tavInitViews();
	if (initThemesAfterTOC || initViewsAfterTOC) tocResizeUpdate();
}


/**
 * Initialisation of themes or views, but before TOC loading
 */
function tavInit() {
	if (!initThemesAfterTOC) tavInitThemes();
	if (!initViewsAfterTOC) tavInitViews();
}


/**
 * Themes initialisation
 */

function tavInitThemes() {
	if (autoInsertThemes) {
		var jqtavThemesBoxContainer = $('#' + tavThemesBoxContainer);
		if (jqtavThemesBoxContainer.size()) {
			if (!$('#selThemeBox').size()) {
				if (insertThemeBoxAtFirstPos) {
					jqtavThemesBoxContainer.prepend(tavThemesSelBoxStr); 
				} else {
					jqtavThemesBoxContainer.append(tavThemesSelBoxStr); 
				}
				themesboxInit();
			}
		}
	}
}


/**
 * Views initialisation
 */

function tavInitViews() {
	if (autoInsertViews) {
		var jqtavViewsBoxContainer = $('#' + tavViewsBoxContainer);
		if (jqtavViewsBoxContainer.size()) {
			if (!$('#selViewBox').size()) {
				if (insertViewBoxAtFirstPos) {
					jqtavViewsBoxContainer.prepend(tavViewsSelBoxStr); 
				} else {
					jqtavViewsBoxContainer.append(tavViewsSelBoxStr); 
				}
				viewsboxInit();
			}
		}
	}
}

/**
 * Themes box initialisation
 *
 * - AJAX request 
 * - response in #selThemeBox
 * - load specified (by code value, or the first one) default theme
 */

function themesboxInit() {
	if ($("#selThemeBox").size()) {
	   	var strType = "Theme";
	    $.ajax({
	        url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavBox.php?type=' + strType + '&' + SID,
	        dataType: "json",
	        success: function(response){
				var selStr = response.selStr;
				$("#selThemeBox").html(selStr);
				if (tavSetDefaultTheme) {
					if (tavDefaultCodeValue.length > 0) {
						$("#selThemeBox select").val(tavDefaultCodeValue);
						$("#selThemeBox select").change();
					} else {
						var tavCodeTmp = $("#selThemeBox select option:eq(1)").val();
						if (typeof(tavCodeTmp) != 'undefined') {
							if (tavCodeTmp.length > 0) {
								$("#selThemeBox select").val(tavCodeTmp);
								$("#selThemeBox select").change()
							}
						}
					}
				}
	        } 
	    });
	}
}


/**
 * Views box initialisation
 *
 * - AJAX request 
 * - response in #selViewBox
 * - load specified (by code value, or the first one) default view
 */

function viewsboxInit() {
	if ($("#selViewBox").size()) {
	   	var strType = "View";
	    $.ajax({
	        url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavBox.php?type=' + strType + '&' + SID,
	        dataType: "json",
	        success: function(response){
				var selStr = response.selStr;
				$("#selViewBox").html(selStr);
				if (tavSetDefaultView) {
					if (tavDefaultCodeValue.length > 0) {
						$("#selViewBox select").val(tavDefaultCodeValue);
						$("#selViewBox select").change()
					} else {
						var tavCodeTmp = $("#selViewBox select option:eq(1)").val();
						if (typeof(tavCodeTmp) != 'undefined') {
							if (tavCodeTmp.length > 0) {
								$("#selViewBox select").val(tavCodeTmp);
								$("#selViewBox select").change()
							}
						}
					}
				}
	        } 
	    });
	}
}


/**
 * Theme box click
 *
 * Create a div in "mapToolArea", then call "themesboxInit" to init the themes box
 */

function themesbox_click() {
	resetFrames();

	var varform = _$('varform');
	varform.mode.value = 'themebox';
	varform.maction.value = 'click';
	varform.tool.value = 'themebox';
	if (useCustomCursor) {
        setCursor(false, false);
    }	

	$("#mapToolArea").html('<div id="selThemeBox" class="TOOLFRAME"></div>');
   	themesboxInit();
}


/**
 * View box click
 *
 * Create a div in "mapToolArea", then call "viewsboxInit" to init the views box
 */

function viewsbox_click() {
	resetFrames();

	var varform = _$('varform');
	varform.mode.value = 'viewbox';
	varform.maction.value = 'click';
	varform.tool.value = 'viewbox';
	if (useCustomCursor) {
        setCursor(false, false);
    }

	$("#mapToolArea").html('<div id="selViewBox" class="TOOLFRAME"></div>');
   	viewsboxInit();
}


/**
 * Submit the selected theme
 *
 * 1) AJAX call : update server map object (layers, transparencies, ...)
 * 2) call tavUpdateMapAndToc to update interface
 */

function submitSelThemeBox() {
	var tavBoxName = "selThemesBoxForm";
	strType = "Theme";
	var selelem = getSelectedThemeAndBox(tavBoxName);
	if (!tavThemesKeepSelected) {
		var selform = _$(tavBoxName);
		selform.selgroup.selectedIndex = -1;
	}
	if (selelem.length > 0) {
	    $.ajax({
	        url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavApply.php?' + SID + '&type=' + strType + '&selected=' + selelem,
	        dataType: "json",
	        success: function(response) {
	        	tavUpdateMapAndToc(response.transparencies, null, response.reload);
	        } 
		});
	}
}


/**
 * Submit the selected view
 *
 * 1) AJAX call : update server map object (layers, transparencies, ...)
 * 2) call tavUpdateMapAndToc to update interface
 */

function submitSelViewBox() {
	var tavBoxName = "selViewsBoxForm";
	strType = "View";
	var selelem = getSelectedThemeAndBox(tavBoxName);
	if (!tavViewsKeepSelected) {
		var selform = _$(tavBoxName);
		selform.selgroup.selectedIndex = -1;
	}
	if (selelem.length > 0) {
	    $.ajax({
	        url: PM_PLUGIN_LOCATION + '/themesandviews/x_tavApply.php?' + SID + '&type=' + strType + '&selected=' + selelem,
	        dataType: "json",
	        success: function(response) {
	        	tavUpdateMapAndToc(response.transparencies, response.extent, response.reload);
	        } 
		});
	}
}


/**
 * Update interface
 *
 * 1) Map image
 * 2) TOC visible layers (scale)
 * 3) TOC layers transparency and checked state
 * 
 * Be carrefull : this function is also called by the ThemesAndViewsAdmin plugin.
 */

function tavUpdateMapAndToc(transparencies, extent, reload) {
	// Warning : This function can be called by and other window (TAV configurator --> opener.tavUpdateMapAndToc)
	// So the begining of the url is not what it seems...

	// Map update :
/*
	var mapurl = tavPmDirURL + PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=';
*/
/*
	var urlprefix = '';
	if (!jQuery.browser.msie) {
		if (document.URL.indexOf('themesandviews') >=  0) {
			urlprefix = '../../';
		}
	}
	var mapurl = urlprefix + PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=';
*/
	var mapurl = tavUrlPmDir + PM_XAJAX_LOCATION + 'x_load.php?'+SID+'&zoom_type=';


	if (extent) {
		mapurl += 'zoomextent&extent='+extent+'&mode=map';
	} else {
		mapurl += 'zoompoint';
	}
	showloading();
	updateMap(mapurl);

	// TOC update (checked groups, scale) :
	if (reload) {
//		pmToc_init(); // Pb : call treeInit() and so setDefGroups()...
//		var tocurl = urlprefix + PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
		var tocurl = tavUrlPmDir + PM_XAJAX_LOCATION + 'x_toc_update.php?' + SID;
		updateTocScale(tocurl);
	}

	// TOC update (uncheck groups) :
	$("#toc").find('[@id^=\'ginput_\']').attr("checked","");

	// TOC update (transparencies values ans checked groups) :
	if (transparencies && PMap.groupTransparencies) {
		for (transparency in transparencies) {
			PMap.groupTransparencies[transparency] = transparencies[transparency];
			$("#toc #ginput_" + transparency).attr("checked", "true");
		}
		tocPostLoading();
	}
}
