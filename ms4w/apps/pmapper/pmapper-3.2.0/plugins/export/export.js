/******************************************************************************
 *
 * Purpose: functions for query result export
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
 
 
/**
 * Export query results in various file formats
 */
 
/** 
 * Export as CSV 
 */ 
function export2CSV() {
    exportQueryResult('ExportCSV');
}

/** 
 * Export as XLS (Excel 5) 
 */ 
function export2XLS() {
    exportQueryResult('ExportXLS');
}

/** 
 * Export as PDF 
 */ 
function export2PDF() {
    exportQueryResult('ExportPDF');
}

/** 
 * run PHP export functions via AJAX 
 */ 
function exportQueryResult(format) {
    $('#exportLinkDL').hide();
    var target = (format == 'ExportPDF' ? ' target="_blank"' : '');
    $.ajax({
        url: PM_PLUGIN_LOCATION + '/export/x_export.php?' + SID + '&format=' + format,
        dataType: "json",
        success: function(response){
            $('#exportLinkDL').html('<a href="' + response.expFileLocation + '" ' + target + '>' + localeList['Download']+ '</a>').show();
        } 
    });  
}


/** 
 * Add controls to result display (called from pmjson.js) 
 */ 
function returnCustomQueryHtml(zp, infoWin) {
    var html = "";
    html += '<div id="selectexport">';
    html += '<div style="display:block; padding-bottom:4px">' + localeList['Export_Result'] + '</div>';
    html += '<div class="exportFormat"><input type="radio" name="exportformat" onclick="export2XLS()" /><img src="plugins/export/images/xls.gif" title="XLS" alt="XLS"/></div>';
    html += '<div class="exportFormat"><input type="radio" name="exportformat" onclick="export2CSV()" /><img src="plugins/export/images/csv.gif" title="CSV" alt="CSV"/></div>';
    html += '<div class="exportFormat"><input type="radio" name="exportformat" onclick="export2PDF()" /><img src="plugins/export/images/pdf.gif" title="PDF" alt="PDF"/></div>';
    html += '<div style="height:30px"><div id="exportLinkDL"></div></div>';
    html += '</div>';
    return html;
}