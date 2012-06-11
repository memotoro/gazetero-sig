/******************************************************************************
 *
 * Purpose: JS functions for XML based search definition
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
 * Disable ENTER input for search form
 * Patch provided by Walter Lorenzetti
 */
function disableEnterKey(e)
{
    var key;
    if (window.event) {
        key = window.event.keyCode;     //IE
    } else {
        key = e.which;     //firefox
    }
    if (key == 13) {
        submitSearch();
        return false;
    } else {
        return true;
    }
}

/**
 * Launch AJAX request to parse search.xml and get optionlist for serach
 */
function setSearchOptions() {
    var url = PM_XAJAX_LOCATION + 'x_search.php?' + SID +'&action=optionlist';
    createSearchItems(url);
}

/**
 * Launch AJAX request to parse search.xml and get params for chosen searchitem
 */
function setSearchInput() {
    var searchForm = _$('searchForm');
    var searchitem = searchForm.findlist.options[searchForm.findlist.selectedIndex].value;
    var url = PM_XAJAX_LOCATION + 'x_search.php?' + SID +'&action=searchitem&searchitem=' + searchitem;
    _$('searchForm').findlist.options[0].selected = true;  // reset parent select box to "Search for..."
    //alert(url);
    createSearchItems(url);
}

/**
 * Convert a JSON string to HTML <select><option> list
 */
function json2Select(jsonObj, fo) {
    var html = '<select name="' + jsonObj.selectname + '" ';
    var events = jsonObj.events;
    var size = jsonObj.size;
    
    if (size > 0) html += ' size="' + size +'" multiple="multiple" ';
    
    if (events) {
        /*for (var e in events) {
            html += e + '="' + events[e] + '" '; 
        }*/
        html += events;
    }

    html += '>';
    
    var options = jsonObj.options;
    if (fo != "0") html += '<option value=\"#\">' + fo + '</option>';
    for (var o in options) {
        html += '<option value=\"' + o + '\">' + options[o] + '</option>';
    }
    html += '</select>';
    
    return html;
}


/**
 * Create the input tag for every field of the attribute search
 */
function createSearchInput(jsonObj) {

    var searchitemsElem = $('#searchitems');
    var itemLayout = searchitemsElem.attr('class').replace(/pm_search_/, '');
    
    var searchitem = jsonObj.searchitem;
    var fields     = jsonObj.fields;

    var hc = '<table id="searchitems_container1" class="searchitem" border="0" cellspacing="0" cellpadding="0">';
    if (itemLayout == 'inline') {
        hc += '<tr id="searchitems_container2"></tr>';
        var itemsAppendTo = 'searchitems_container2';
    } else {
        var itemsAppendTo = 'searchitems_container1';
    }
    hc += '</table>';
    
    searchitemsElem.html('');
    $(hc).appendTo(searchitemsElem);
    
    var html = '';
    var htmlend = '';
    for (var i=0; i<fields.length; i++) {
        var description = fields[i].description;
        var fldname     = fields[i].fldname;
        var fldsize     = fields[i].fldsize;
        var fldsizedesc = fields[i].fldsizedesc;
        var fldinline   = fields[i].fldinline;
        var definition  = fields[i].definition;
        
        var inputsize = fldsize ? ' size="' + fldsize + '" ' : '';
        var sizedesc = fldsizedesc ? ' style="position:absolute; left:' + fldsizedesc + 'em"' : '';
        
        if (!definition) {
            var hi = ' <td class="searchdesc">' + description + '</td>';
            hi += ' <td' + sizedesc + '>' + '<input type="text" class="search_textinput" id="pmsfld_' + fldname + '" name="' + fldname + '"' + inputsize + '></td>';
            if (itemLayout != "inline") hi = '<tr>' + hi + '</tr>';
            $(hi).appendTo('#'+itemsAppendTo);
            
        } else {
            if (definition.type == 'options') {
                var ho = ' <td class="searchdesc">' + description + '</td>';
                ho += ' <td>' + json2Select(definition, definition.firstoption) + '</td>';
                if (itemLayout != "inline") ho = '<tr>' + ho + '</tr>';
                $(ho).appendTo('#'+itemsAppendTo);
                
            } else if (definition.type == 'suggest') {
                var hs = '<td class="searchdesc">' + description + '</td>';
                hs += '<td><input type="text" id="pmsfld_' + fldname + '" name="' + fldname + '" alt="Search Criteria"' + inputsize + ' /></td>';
                if (itemLayout != "inline") hs = '<tr>' + hs + '</tr>';
                $(hs).appendTo('#'+itemsAppendTo);
                
                resetSuggestCache();
                var searchitem  = definition.searchitem;
                var minlength   = definition.minlength;
                var suggesturl = PM_XAJAX_LOCATION + 'x_suggest.php?' + SID + '&searchitem=' + searchitem + '&fldname=' + fldname;
                $('#pmsfld_' + fldname).suggest(suggesturl,
                    { delimiter: '||', 
                      minchars: minlength, 
                      delay:250, 
                      addToUrl: "getFormKVP('searchForm')", 
                      onSelect: function() {launchSearchFromSuggest()}
                });
            
            } else if (definition.type == 'checkbox') {
                var value      = definition.value;
                var defchecked = ''; //(definition.checked == 1) ? ' checked ' : '' ; //" checked="checked" ' : '' ;                
                var hcb = '<td class="searchdesc">' + description + '</td>';
                hcb += '<td><input type="checkbox" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + '" value="' + value + '" ' + defchecked + ' /></td>';
                if (itemLayout != "inline") hcb = '<tr>' + hcb + '</tr>';
                $(hcb).appendTo('#'+itemsAppendTo);
                
            // Radio Button
            } else if (definition.type == 'radio') {
                var inputlist  = definition.inputlist;

                for (var ipt in inputlist) {
                    var defchecked = (definition.checked == 1) ? ' checked ' : '' ; //" checked="checked" ' : '' ;                
                    var hra = '<td>' + inputlist[ipt]+ '</td>';
                    hra += '<td><input type="radio" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + '" value="' + ipt + '" ' + defchecked + ' /></td>';
                }
            
            } else if (definition.type == 'operator') {
                //if (fldinline) html += '<div class="search_inline">';
                var hop = '<td class="searchdesc">' + description + '</td>';
                hop += ' <td' + sizedesc +'>' + json2Select(definition, false);
                hop += ' <input type="text" class="search_textinput_compare" id="pmsfld_' + fldname + '" name="' + fldname + '" ' + inputsize + '></td>';
                if (itemLayout != "inline") hop = '<tr>' + hop + '</tr>';
                $(hop).appendTo('#'+itemsAppendTo);
            
            } else if (definition.type == 'hidden') {
                htmlend += '<input type="hidden" id="pmsfld_' + fldname + '" name="' + fldname + '" value="' + definition.value + '">';
            }
        }
        
    }
    
    html += '<td colspan="2" class="searchitem">';
    html += '<div><input type="button" value="' + _p('Search') + '" size="20" ';
    html += 'onclick="submitSearch()" onmouseover="changeButtonClr(this, \'over\')" onmouseout="changeButtonClr (this, \'out\')"></div>';
    html += '<div><img src="images/close.gif" alt="" onclick="$(\'#searchitems\').html(\'\')" /></div>';
    html += '</td>';
    
    htmlend += '<input type="hidden"  name="searchitem" value="' + searchitem + '" />';
    html += htmlend;
    if (itemLayout != "inline") html = '<tr>' + html + '</tr>';
    $(html).appendTo('#'+itemsAppendTo);
}



/**
 * Return form values in key=value pair notation
 */
function getFormKVP(formid) {
    var htmlform = document.getElementById(formid);
    //alert(searchForm.elements);
    var el = htmlform.elements;
    var s = '';
    for (var i=0; i < el.length; i++) {
        var e = el[i]; 
        var ename = e.name;
        var evalue = e.value;
        var etype = e.type;
        var delim = (i>0 ? '&' : '');
        
        if (evalue.length > 0 && evalue != '#') {
            //alert(etype + ' - ' + evalue);
            switch (etype) {
                //case 'text':
                case 'select-one':
                    s += delim + ename + '=' + e.options[e.selectedIndex].value;
                    break;
            
                case 'select-multiple':
                    var ol = e.options;
                    var opttxt = '';
                    for (var o=0; o < ol.length; o++) {
                        if (ol[o].selected) {
                            opttxt += ol[o].value + ',';
                        }
                    }
                    s += delim + ename + '=' + opttxt.substr(0, opttxt.length - 1); 
                    break;
                    
                case 'checkbox':
                    if (e.checked) {
                        s += delim + ename  + '=' + evalue;
                    }
                    break;
                    
                case 'radio':
                    if (e.checked) {
                        s += delim + ename  + '=' + evalue;
                    }
                    break;
                    
                default:
                    s += delim + ename  + '=' + evalue;
                    break;
            }
        }
    }
    //alert(s);
    return s;
}


/**
 * Reset suggest cache when select box or suggest field changes
 */
function resetSuggestCache() {
    //$.getSuggestCache();
    cache = [];
}





/***
// sample function for executing attribute searches with external search parameter definitions
function submitSearchExt() {
    var searchForm = _$('searchForm');
    if (PMap.infoWin != 'window') {
        searchForm.target='infoZone';
    } else {
        var resultwin = openResultwin('blank.html');
        searchForm.target='resultwin';
    }
    //var qStr = '(([POPULATION]<12000))';
    var qStr = '(  ( "[NAME]" =~ /(B|b)(E|e)(R|r)(L|l)(I|i)/ ) )';
    var queryurl = PM_XAJAX_LOCATION + 'x_info.php';
    var params = SID + '&externalSearchDefinition=y&mode=search&layerName=cities10000eu&layerType=shape&fldName=POPULATION&qStr=' + qStr ; 
    getQueryResult(queryurl, params);
}
***/

