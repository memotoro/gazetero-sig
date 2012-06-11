/******************************************************************************
 *
 * Purpose: common js function for pmapper plugins
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

// typewin can be the id of the element like "frame" (with "#")
function openAjaxQueryIn(typewin, url, dlgTitle, dlgwidth, dlgheight) {
	if (typewin == 'window') {
		if (url.pos('?') == 0) {
			url += '?';
		} else {
			url += '&';
		}
		url += 'addjsandcss=true';
		openResultwin(url);
	} else if (typewin == 'dynwin') {
		var myheight = dlgheight ? dlgheight : 400;
		var mywidth = dlgwidth ? dlgwidth : 600;
		createDnRDlg({w:mywidth, h:myheight, l:80, t:100}, {resizeable:true, newsize:true}, 'pmDlgContainer', dlgTitle, url);
	} else {
	    $.ajax({
	        url: url,
	        dataType: "html",
	        success: function(response){
		        if (typewin == 'frame') {
					$("#infoFrame").html(response);
				} else if (typewin[0] == '#') { 
					$(typewin).html(response);
				}
			}
		});  
	} 
}
