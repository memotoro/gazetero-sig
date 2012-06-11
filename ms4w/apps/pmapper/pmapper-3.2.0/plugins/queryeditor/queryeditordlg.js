/******************************************************************************
 *
 * Purpose: Querty Editor plugin
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

/**
 * Name of the selected group / layer
 */
function getQueryEditorLayerName() {
	var retVal = "";
	if ($("#qeLayerName").length > 0) {
		var layerName = $("#qeLayerName").val();
		if (layerName) {
			if ((layerName.length > 0) && (layerName != "#")) {
				retVal = layerName;
			}
		}
	}
	return retVal;
}

/**
 * Apply the layer that use has chosen
 * 
 * If none selected, reset interface
 * Ask for the available fields for this layer (AJAX request)
 * Refresh interface
 */
function qeLayerNameApply() {
	var layerName = getQueryEditorLayerName();
	$("#qeAttributName").html('');
	$('#qeAttributType').val('');
	qeAttributNameApply();
	qeResetQuery();
	if (layerName.length > 0) {
		var url = qeDirUrl + 'x_queryeditor.php';
		var params = SID + '&operation=getattributs&layername=' + layerName;
	    $.ajax({
	        url: url,
	        data: params,
	        dataType: "json",
	        success: function(response) {
	        	var repfields = response.fields;
	        	var repheaders = response.headers;
	        	if ($('#qeAttributName').length) {
	        		var options = '<option value="#"></option>\n';
	        		var fields = repfields.split(",");
	        		var headers = repheaders.split(",");
	        		if (fields.length > 0) {
			        	for (var iField = 0 ; iField < fields.length ; ++iField) {
			        		var strHeader = (headers.length > iField) ? (headers[iField] ? headers[iField] : fields[iField]) : strHeader = fields[iField];
			        		options += '<option value=\"' + fields[iField] + '\" label=\"' + strHeader + '\">' + strHeader + '</option>\n';
			        	}
			        }
		        	$("#qeAttributName").html(options);
		        	$("#qeAttributName").val("");
		        }
	        } 
		});
	}
}

/**
 * Real name of the selected field
 */
function getQueryEditorAttributRealName() {
	var retVal = "";
	if ($("#qeAttributName").length > 0) {
		var indicatorRealName = $("#qeAttributName").val();
		if (indicatorRealName) {
			if ((indicatorRealName.length > 0) && (indicatorRealName != "#")) {
				retVal = indicatorRealName;
			}
		}
	}
	return retVal;
}

/**
 * Readable name of the selected field (=header)
 */
function getQueryEditorAttributReadName() {
	var retVal = "";
	var elemTmp = document.getElementById("qeAttributName");
	if (typeof(elemTmp) != 'undefined') {
		if (elemTmp.selectedIndex > 0) {
			var indicatorReadName = elemTmp.options[elemTmp.selectedIndex].text;
			if (typeof(indicatorReadName) != 'undefined') {
				retVal = indicatorReadName;
			}
		}
	}
	return retVal;
}

/**
 * Apply the chosen field
 *
 * Refresh interface and call qeAttributTypeApply
 */
function qeAttributNameApply() {
	var attrRealName = getQueryEditorAttributReadName();
	$('#qeAttributType').val('');
	$('#qeAttributType').attr('disabled','disabled');
	qeAttributTypeApply();

	if (attrRealName) {
		if (attrRealName.length > 0) {
		$('#qeAttributType').removeAttr('disabled');
		}
	}
}

/**
 * Apply the field type
 *
 * Refresh interface
 */
function qeAttributTypeApply() {
	$('#qeAttributValue').val('');
	$('#qeAttributValue').attr('disabled','disabled');
	$('#qeAttributCriteriaComparisonNum').parent().hide();
	$('#qeAttributCriteriaComparisonTxt').parent().hide();
	$('#qeAttributCriteriaComparisonNone').parent().show();
	
	var attrType = $('#qeAttributType').val();
	if (attrType) {
		$('#qeAttributCriteriaComparisonNone').parent().hide();
		if (attrType == 'N') {
			$('#qeAttributCriteriaComparisonNum').parent().show();
			$('#qeAttributValue').removeAttr('disabled');
		} else if (attrType == 'S') {
			$('#qeAttributCriteriaComparisonTxt').parent().show();
			$('#qeAttributValue').removeAttr('disabled');
		} else {
			$('#qeAttributCriteriaComparisonNone').parent().show();
		}
	}
}

/**
 * OnKeyPress event
 * 
 * Avoid default ENTER key behaviour:
 * - if ENTER is press, then apply the attribut value (= call qeAttributValueApply).
 * - if an other key is press, the onkeyup will call qeAttributValueChange.
 */
function qeAttributValueKeyPress(e) {
    var key;
    // IE :
    if (window.event) {
        key = window.event.keyCode;
    } else { // Firefox
        key = e.which;
    }

	// ENTER key :
    if (key == 13) {
        qeAttributValueApply();
        return false;
    } else {
        return true;
    }
}

/**
 * OnKypUp event
 *
 * Enable on disable the "add" button
 */
function qeAttributValueChange() {
    var attrval = $('#qeAttributValue').val();
    var btnEnable = false;
    if (attrval) {
    	if (attrval.length > 0) {
    		btnEnable = true;
       	}
	}
	btnEnable ? $('#qeAttributBtnAdd').removeAttr('disabled') : $('#qeAttributBtnAdd').attr('disabled','disabled') ;
}

/**
 * Apply the attribut value
 *
 * Generate the new query part (depending on the field type and comparison operator)
 * Add the query part in the textarea
 * Reset the attribut name and then refresh the interface by calling qeAttributNameApply
 */
function qeAttributValueApply() {
	var bContinue = true;
	var queryPartToAdd = '';

	if (bContinue) {
		bContinue = false;
		var attrName = getQueryEditorAttributReadName();
		if (attrName) {
			if (attrName.length > 0) {
				queryPartToAdd += '[' + attrName + ']';
				bContinue = false;
				var attrVal = $('#qeAttributValue').val();
				if (attrVal) {
					var attrType = $('#qeAttributType').val();
					if (attrType) {
						bContinue = true;
						if (attrType == 'N') {
							var attrOperator = $('#qeAttributCriteriaComparisonNum').val();
							if (attrOperator) {
								if (attrOperator == 'equal') {
									queryPartToAdd += ' = ' + attrVal;
								} else if (attrOperator == 'inferiororequal') {
									queryPartToAdd += ' <= ' + attrVal;
								} else if (attrOperator == 'superiororequal') {
									queryPartToAdd += ' >= ' + attrVal;
								} else if (attrOperator == 'strictlyinferior') {
									queryPartToAdd += ' < ' + attrVal;
								} else if (attrOperator == 'strictlysuperior') {
									queryPartToAdd += ' > ' + attrVal;
								} else if (attrOperator == 'different') {
									queryPartToAdd += ' <> ' + attrVal;
								} else {
									bContinue = false;
								}
							}
						} else if (attrType == 'S') {
							var attrOperator = $('#qeAttributCriteriaComparisonTxt').val();
							if (attrOperator) {
								if (attrOperator == 'equal') {
									queryPartToAdd += ' LIKE \'' + attrVal + '\'';
								} else if (attrOperator == 'different') {
									queryPartToAdd = 'NOT ' + queryPartToAdd;
									queryPartToAdd += ' LIKE \'' + attrVal + '\'';
								} else if (attrOperator == 'contain') {
									queryPartToAdd += ' LIKE \'%' + attrVal + '%\'';
								} else if (attrOperator == 'notcontain') {
									queryPartToAdd = 'NOT ' + queryPartToAdd;
									queryPartToAdd += ' LIKE \'%' + attrVal + '%\'';
								} else if (attrOperator == 'startwith') {
									queryPartToAdd += ' LIKE \'' + attrVal + '%\'';
								} else if (attrOperator == 'endwith') {
									queryPartToAdd += ' LIKE \'%' + attrVal + '\'';
								} else {
									bContinue = false;
								}
							}
						} else {
							bContinue = false;
						}
					}
				}
			}
		}
	}

	if (bContinue) {
		qeAddToQuery(queryPartToAdd);
		$('#qeAttributBtnAdd').attr('disabled','disabled');
		$('#qeAttributName').val('');
		qeAttributNameApply();
	}
}

/**
 * Apply operator choice: add it to query
 */
function qeOperator(id) {
	var op = '';
	switch (id) {
		case 'qeOperatorBtnOpenBracket':
			op = '(';
			break;
		case 'qeOperatorBtnCloseBracket':
			op = ')';
			break;
		case 'qeOperatorBtnNot':
			op = 'NOT';
			break;
		case 'qeOperatorBtnAnd':
			op = 'AND';
			break;
		case 'qeOperatorBtnOr':
			op = 'OR';
			break;
		default:
			break;
	}
	qeAddToQuery(op);
}

/**
 * Add text to the current query
 */
function qeAddToQuery(queryPartToAdd) {
	var currentQuery = $('#qeGeneratedQuery').val();
	if (currentQuery) {
		if (currentQuery.length > 0 ){
			queryPartToAdd = currentQuery + '\n' + queryPartToAdd;
		}
	}
	qeUpdateQuery(queryPartToAdd)
}

/**
 * Delete the urrent query
 */
function qeResetQuery() {
	qeUpdateQuery('');
}

/**
 * Update query
 *
 * Change the query with the parameter value
 * Refresh interface by calling qeQueryHasBeenUpdated
 */
function qeUpdateQuery(query) {
	$('#qeGeneratedQuery').val(query);
	qeQueryHasBeenUpdated();
}

/**
 * Refresh interface depending on the current query content
 */
function qeQueryHasBeenUpdated() {
	$('#qeBtnReset').attr('disabled','disabled');
	$('#qeBtnApply').attr('disabled','disabled');
	$('#qeOperatorGroup2 input').attr('disabled','disabled');

	var currentQuery = $('#qeGeneratedQuery').val();
	if (currentQuery) {
		if (currentQuery.length > 0) {
			$('#qeBtnReset').removeAttr('disabled');
			$('#qeBtnApply').removeAttr('disabled');
			$('#qeOperatorGroup2 input').removeAttr('disabled');
		}
	}
}

/**
 * Reset interface
 */
function qeReset() {
	$('#qeLayerName').val('');
	qeLayerNameApply();
	qeResetQuery();
}

/**
 * Cancel (close the query window)
 */
function qeCancel() {
	if ($('#pmDlgContainer').length > 0) {
		$('#pmDlgContainer .jqmClose').click();
	}
}

/**
 * Execute the current query
 *
 * Use standard getQueryResult function to show result, select and zoom to selected...
 */
function qeApply() {
	var layerName = getQueryEditorLayerName();
	if (layerName.length > 0) {
		var query = $('#qeGeneratedQuery').val();
		query = query.replace('%','%25');
		if (query) {
			if (query.length > 0) {
				var url = qeDirUrl + 'x_queryeditor.php';
				var params = SID + '&operation=query&layername=' + layerName + '&layerType=shape&query=' + query; 
				getQueryResult(url, params);
//	        	qeCancel();
			}
		}
	}
}

/*
function statsApplyInMainWindow() {
	var urltmp = '';

	if (document.URL.indexOf('statsdlg') > 0) {
		if (jQuery.browser.msie) {
			urltmp = 'incphp/xajax/';
		} else {
			urltmp = '../../incphp/xajax/';
		}
	} else {
		urltmp = PM_XAJAX_LOCATION;
	}
	urltmp += 'x_toc.php?' + SID;

	try {
	   	updateToc(urltmp);
	} catch(e) {
		opener.updateToc(urltmp);
	}
}
*/

// Default interface status
$('#qeMain').ready(function() {
	qeReset();
});