/******************************************************************************
 *
 * Purpose: Rounded boxes plugin
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

/*
 * This is a pmapper plugin. It permit to draw rounded corners, by using the 
 * jQuery corner plugin.
 */


// for Armin : 
// the following function doesn't work correctly in IE6 (and 7 too I think !)
/*
$(document).ready(function() {
	roundedBoxesInit();
});
*/


/*
 * Add divs to main elements of pmapper interface. Those divs draw rounded corners.
 *
 * TODO :
 * customisable size and style of corner
 *
 * BUGS :
 * in IE6 and 7 (maybe 5 too), the top right corner of mapZone element doesn't work !
 */

function roundedBoxesInit() {
	var borderType = "round ";
	var borderSize = 10;
	var borderSizeTxt = " " + borderSize + "px";

	// W and E full height of mapZone 
	if (Layout.MapWestEastFull) {

		// Left corners :

		// use corners of MapWest element :
		if (Layout.MapWestWidth > 0) {
			if (Layout.MapWestWidth >= borderSize) {
				$("#mapWest").corner(borderType + " tl bl" + borderSizeTxt);
			}
		// use corners of MapNorth and MapSouth elements :
		} else {
			if ((Layout.MapNorthHeight >= borderSize) && (Layout.MapSouthHeight >= borderSize)) {
				$("#mapNorth").corner(borderType + " tl" + borderSizeTxt);
				$("#mapSouth").corner(borderType + " bl" + borderSizeTxt);
			}
		}

		// Right corners :

		// use corners of MapEast element :
		if (Layout.MapEastWidth >= 0) {
			if (Layout.MapEastWidth >= borderSize) {
				$("#mapEast").corner(borderType + " tr br" + borderSizeTxt);
			}
		// use corners of MapNorth and MapSouth elements :
		} else {
			if ((Layout.MapNorthHeight >= borderSize) && (Layout.MapSouthHeight >= borderSize)) {
				$("#mapNorth").corner(borderType + " tr" + borderSizeTxt);
				$("#mapSouth").corner(borderType + " br" + borderSizeTxt);
			}
		}
	// N and S full width of mapZone
	} else {

		// Top corners :

		// use corners of MapNorth element :
		if (Layout.MapNorthHeight > 0) {
			if (Layout.MapNorthHeight >= borderSize) {
				$("#mapNorth").corner(borderType + " tl tr" + borderSizeTxt);
			}
		// use corners of MapWest and MapEast elements :
		} else {
			if ((Layout.MapWestWidth >= borderSize) && (Layout.MapEastWidth >= borderSize)) {
				$("#mapWest").corner(borderType + " tl" + borderSizeTxt);
				$("#mapEast").corner(borderType + " tr" + borderSizeTxt);
			}
		}

		// Bottom corners :

		// use corners of MapSouth element :
		if (Layout.MapSouthHeight > 0) {
			if (Layout.MapSouthHeight >= borderSize) {
				$("#mapSouth").corner(borderType + " bl br" + borderSizeTxt);
			}
		// use corners of MapWest and MapEast elements :
		} else {
			if ((Layout.MapWestWidth >= borderSize) && (Layout.MapEastWidth >= borderSize)) {
				$("#mapWest").corner(borderType + " bl" + borderSizeTxt);
				$("#mapEast").corner(borderType + " br" + borderSizeTxt);
			}
		}
	}

	if ($("#refZone").parent().id() != "map") {
		$("#refZone").corner(borderType + borderSizeTxt);
	}
	$("#west").corner(borderType + borderSizeTxt);
	$("#east").corner(borderType + borderSizeTxt);
	
	$("#north").corner(borderType + " bottom" + borderSizeTxt);
	$("#south").corner(borderType + " top" + borderSizeTxt);
}


