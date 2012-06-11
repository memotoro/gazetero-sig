/******************************************************************************
 *
 * Purpose: Extension of transparency plugins
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

// Array to keep names of layergroups, and the associated slider
// (slider array is used to call the setPosition method)
var transp2sliders = new Array();
var transp2groupnames = new Array();


/**
 * re-define the function of transparency plugin
 * It just made the same as the original one, and then call initGroupTransparencies2
 */

function initGroupTransparencies() {
	if (!PMap.groupTransparencies) {
	    var url = PM_PLUGIN_LOCATION + '/transparency/x_get-transparencies.php?' + SID;
	    $.ajax({
	        type: "POST",
	        url: url,
	        dataType: "json",
	        success: function(response){
	            PMap.groupTransparencies = response.transparencies;
	            initGroupTransparencies2();
	        }
	    });
	} else {
		initGroupTransparencies2();
	}
}


/**
 * Init the sliders in TOC (create and update position)
 */

function initGroupTransparencies2() {
	transp2initArrays();
	// for each groups
	$('#toc .grp').each(function() {
		var grpparent = $(this).parent();
// Be carrefull : in IE and Firefox, slit function return different arrays
// if test is on the begining of string...
		var gnames = $(this).find('[@id^=\'spxg_\']').id().split(/spxg_/);
		if (gnames.length > 0) {
			var gname = gnames[gnames.length - 1];
			if (gname.length > 0) {
				var sliderDivID = 'toc_transp2_' + gname;
				var sliderDiv = $('#' + sliderDivID);
				// Add transparency (slider, etc...) only if not ever done
				if (sliderDiv.size() == 0) {
					grpparent.append('<td><div id="' + sliderDivID + '" class="transparency2Slider"></div></td>');
					createTransparency2Slider(sliderDivID, gname);
				}
				// update slider position :
				sliderDiv = $('#' + sliderDivID);
				if (sliderDiv.size() > 0) {
					for (iSlider = 0 ; iSlider < transp2groupnames.length ; iSlider++) {
						if (transp2groupnames[iSlider] == gname) {
							if (typeof(transp2sliders[iSlider]) != 'undefined') {
								if (transp2sliders[iSlider] != 'null') {
									var sliderPos = PMap.groupTransparencies[gname]/100;
									sliderPos = transp2UseOpacity ? 1 - sliderPos : sliderPos;
									transp2sliders[iSlider].setPosition(sliderPos);
								}
							}
							break;
						}
					}
				}
			}
		}
	});
}


/**
 * Create slider for transparency setting
 */

function createTransparency2Slider(sliderDivID, gname) {
	for (iPos = 0 ; iPos < transp2groupnames.length ; iPos++) {
		if (transp2groupnames[iPos] == gname) {
			transp2sliders[iPos] = new slider(
		        sliderDivID,
		        3, 40, '#666666',
				1, '#000000',
				2, '#666666',
				8, 3, '#999999', 1,
		        '', true,     
		        false, 'setTransparencyFor_' + gname,
		        null
			);
			break;
		}
	}
}


/**
 * Post the Transparency value to PHP GROUP object
 *
 * re-write the transparency plugin function
 */
/*
function setGroupTransparency2(groupname, transparency) {
    var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        success: function(response){
            PMap.groupTransparencies[groupname] = transparency;
            if (response.reload && (PMap.layerAutoRefresh == '1')) {
                showloading();
                reloadMap(false);
            }
        }
    });
}
*/
function setGroupTransparency(pos, groupname) {
	var transparency;

	// for classical transparency plugin 
	if (groupname == undefined) {
	    groupname = $('#transpdlg_groupsel option:selected').val();
	    if (typeof(groupname)=='undefined') {
	        var groupname = $('#layerSliderCont').attr('name');
	        var cmenu = 1;
	    }
	    if (groupname == '#') return false;
	    if (cmenu) $('#layerSliderContTab').remove();

		for (iSlider = 0 ; iSlider < transp2groupnames.length ; iSlider++) {
			if (transp2groupnames[iSlider] == groupname) {
				if (typeof(transp2sliders[iSlider]) != 'undefined') {
					if (transp2sliders[iSlider] != 'null') {
						transp2sliders[iSlider].setPosition(pos);
					}
				}
				break;
			}
		}
		transparency = Math.round(pos  * 100);
	}
	// for extended transparency plugin (Transparency2)
	else {
		var sliderPos100 = Math.round(pos  * 100);
		transparency = transp2UseOpacity ? 100 - sliderPos100 : sliderPos100;
	}
    	
    var url = PM_PLUGIN_LOCATION + '/transparency/x_set-transparency.php?' + SID + '&transparency=' + transparency + '&groupname=' + groupname;
    $.ajax({
        type: "POST",
        url: url,
        dataType: "json",
        success: function(response){
            PMap.groupTransparencies[groupname] = transparency;
            if (response.reload && (PMap.layerAutoRefresh == '1')) {
                showloading();
                reloadMap(false);
            }
        }
    });
}
