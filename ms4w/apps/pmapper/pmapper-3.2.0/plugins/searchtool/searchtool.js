/******************************************************************************
 *
 * Purpose: SearchTool plugin
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
 
function searchtool_click() {
	resetFrames();

	var varform = _$('varform');
	varform.mode.value = 'searchtool';
	varform.maction.value = 'click';
	varform.tool.value = 'searchtool';
	if (useCustomCursor) {
        setCursor(false, false);
    }	

	searchtoolgetform();
}

function searchtoolshowform() {
//	resetFrames();
	$('#mapToolArea').html('');

	searchtoolgetform();
}

function searchtoolgetform() {
	$("#searchForm").remove();
	var url = PM_PLUGIN_LOCATION + '/searchtool/x_searchtool.php?' + SID;
	$.ajax({
    	type: "POST",
        url: url,
        dataType: "html",
        success: function(response){
			$('#mapToolArea').html(response);
			setSearchOptions();
        }
    });
}
