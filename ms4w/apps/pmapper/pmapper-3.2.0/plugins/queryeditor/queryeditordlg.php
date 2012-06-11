<?php

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
// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once("../../incphp/group.php");
session_start();
require_once($_SESSION['PM_INCPHP'] . "/common.php");
require_once($_SESSION['PM_INCPHP'] . "/globals.php");
require_once($_SESSION['PM_PLUGIN_REALPATH'] . "/common/groupsAndLayers.php");
require_once($_SESSION['PM_PLUGIN_REALPATH'] . "/sirapcommon/easyincludes.php");

header("Content-type: text/html; charset=$defCharset");

$urlReqDir = getURLReqDir();

$groups = Array();
$groupsToTransform = Array();
$bTransformGroupsArray = true;
switch($_SESSION["queryEditorLayersChoice"]) {
	// checked and visible non raster layers only :
	case 4:
		$groupsToTransform = getAvailableGroups($map, true, true, true);
		break;
	// checked non raster layers only :
	case 3 :
		$groupsToTransform = getAvailableGroups($map, true, true, false);
		break;
	// pre-defined list :
	case 2 :
		if (isset($_SESSION["queryEditorLayersList"])) {
			$tmparray = $_SESSION["queryEditorLayersList"];
			foreach ($tmparray as $grpName => $grpDescription) {
				if ($grpName && $grpDescription) {
					$groups[$grpName] = $grpDescription;
				}
			}
			$bTransformGroupsArray = false;
		}
		break;
	// all non raster layers :
	case 1 :
	default:
		$groupsToTransform = getAvailableGroups($map, false, true, false);
		break;
}
if ($bTransformGroupsArray && $groupsToTransform) {
	foreach ($groupsToTransform as $groupToTransform) {
		if ($groupToTransform->groupName && $groupToTransform->description) {
			$groups[$groupToTransform->groupName] = $groupToTransform->description;
		}
	}
}
//echo "count groups = " . count($groups) . "<br />";

?>
<html>
<head>
<title><?php echo (_p("Query editor")) ?></title>
<?php
// "addjsandcss" mean the the result page will be opened in a new window.
// That's mean we have to add jQuery and css files.
	if ($_REQUEST["addjsandcss"]) {
		$jsReference = getJQueryFiles();
	}
	$jsReference .= "<script type=\"text/javascript\" src=\"" . $urlReqDir . "queryeditordlg.js\"></script>\n";
	if (strlen($jsReference) > 0) {
		echo $jsReference;
	}
	
	if ($_REQUEST["addjsandcss"]) {
		$css = getCSSReference($pmapDir);
	}
	$css .= "<link rel=\"stylesheet\" href=\"" . $urlReqDir . "../common/commonforms.css\" type=\"text/css\" />";
// custom CSS for this window:
	$css .= "<link rel=\"stylesheet\" href=\"" . $urlReqDir . "queryeditor.css\" type=\"text/css\" />";
	if (strlen($css) > 0 ) {
		echo $css;
	}
?>
</head>
<body>
<?php
	$strTmp = "";
	$strTmp .= "var SID = '" . SID . "';\n"; 
	$strTmp .= "var qeDirUrl = '" . $urlReqDir . "';\n";
	if ($strTmp) {
		echo ("<script type=\"text/javascript\">\n");
		echo $strTmp;
		echo ("</script>\n");
	}
?>	

<div id="qeMain">
	<form action="" target="_blank" id="qeform" class="commonform" method="get">
		<fieldset id="qeLayer">
			<legend><?php echo (_p("Spatial datas")) ?></legend>
			<ol>
				<li class="lastli">
					<label for="qeLayerName"><?php echo (_p("Layer name"))?></label>
					<select id="qeLayerName" name="qeLayerName" onchange="javascript:qeLayerNameApply()">
						<option value="#" />
<?php
foreach ($groups as $grpName => $grpDescription) {
echo 
("						<option value=\"$grpName\" label=\"$grpDescription\" >$grpDescription</option>
");
}
?>
					</select>
				</li>
			</ol>
		</fieldset>

		<fieldset id="qeAttribut">
			<legend><?php echo (_p("Attribute")) ?></legend>
			<ol>
				<li>
					<label for="qeAttributName"><?php echo (_p("Name"))?></label>
					<select id="qeAttributName" name="qeAttributName" onchange="javascript:qeAttributNameApply()" />
				</li>
				<li>
					<label for="qeAttributType"><?php echo (_p("Type"))?></label>
					<select id="qeAttributType" name="qeAttributType" onchange="javascript:qeAttributTypeApply()">
						<option name="none" value="#" selected="selected" />
						<option name="S" value="S"><?php echo (_p("Text"))?></option>
						<option name="N" value="N"><?php echo (_p("Numeric")) ?></option>
					</select>
				</li>
				<li>
					<label for="qeAttributCriteriaComparisonNone"><?php echo (_p("Comparison"))?></label>
					<select id="qeAttributCriteriaComparisonNone" name="qeAttributCriteriaComparisonNone" disabled="disabled">
						<option label="none" value="#" />
					</select>
				</li>
				<li>
					<label for="qeAttributCriteriaComparisonNum"><?php echo (_p("Comparison"))?></label>
					<select id="qeAttributCriteriaComparisonNum" name="qeAttributCriteriaComparisonNum">
						<option label="none" value="#" />
						<option label="equal" value="equal">=</option>
						<option label="inferiororequal" value="inferiororequal">&lt;=</option>
						<option label="superiororequal" value="superiororequal">&gt;=</option>
						<option label="strictlyinferior" value="strictlyinferior">&lt;</option>
						<option label="strictlysuperior" value="strictlysuperior">&gt;</option>
						<option label="different" value="different">!=</option>
					</select>
				</li>
				<li>
					<label for="qeAttributCriteriaComparisonTxt"><?php echo (_p("Comparison"))?></label>
					<select id="qeAttributCriteriaComparisonTxt" name="qeAttributCriteriaComparisonTxt">
						<option label="none" value="#" />
						<option label="equal" value="equal"><?php echo (_p("equal")) ?></option>
						<option label="different" value="different"><?php echo (_p("different")) ?></option>
						<option label="contain" value="contain"><?php echo (_p("contain")) ?></option>
						<option label="notcontain" value="notcontain"><?php echo (_p("doesn't contain")) ?></option>
						<option label="startwith" value="startwith"><?php echo (_p("start with")) ?></option>
						<option label="endwith" value="endwith"><?php echo (_p("end with")) ?></option>
					</select>
					<input id="qeAttributCriteriaComparisonTxt" type="checkbox" value="qeAttributCriteriaComparisonTxt" name="qeAttributCriteriaComparisonTxt" <?php /* checked="checked" */ echo ('disabled="disabled"') ?> />
					<label for="qeAttributCriteriaComparisonTxt"><?php echo (_p("case sensitive"))?></label>
				</li>
				<li class="lastli">
					<label for="qeAttributValue"><?php echo (_p("Value")) ?></label>
					<input type="text" id="qeAttributValue" name="qeAttributValue" onkeypress="return qeAttributValueKeyPress(event)" onkeyup="javascript:qeAttributValueChange()" />
					<input id="qeAttributBtnAdd" class="qeBtn" type="button"  value="<?php echo (_p("Add")) ?>"  onclick="javascript:qeAttributValueApply()" />
				</li>
			</ol>
		</fieldset>

		<fieldset id="qeOperatorGroup">
			<legend><?php echo (_p("Operator")) ?></legend>
			<ol>
				<li id="qeOperatorGroup1">
					<input id="qeOperatorBtnOpenBracket" class="qeBtn qeBtnOperator" type="button"  value="<?php echo (_p("(")) ?>"  onclick="javascript:qeOperator(this.id)" />
					<input id="qeOperatorBtnCloseBracket" class="qeBtn qeBtnOperator" type="button"  value="<?php echo (_p(")")) ?>"  onclick="javascript:qeOperator(this.id)" />
					<input id="qeOperatorBtnNot" class="qeBtn qeBtnOperator" type="button"  value="<?php echo (_p("NOT")) ?>"  onclick="javascript:qeOperator(this.id)" />
				</li>
				<li id="qeOperatorGroup2" class="lastli">
					<input id="qeOperatorBtnAnd" class="qeBtn qeBtnOperator" type="button"  value="<?php echo (_p("AND")) ?>"  onclick="javascript:qeOperator(this.id)" />
					<input id="qeOperatorBtnOr" class="qeBtn qeBtnOperator" type="button"  value="<?php echo (_p("OR")) ?>"  onclick="javascript:qeOperator(this.id)" />
				</li>
			</ol>
		</fieldset>
					

		<fieldset id="qeRequest">
			<legend><?php echo (_p("Generated query")) ?></legend>
			<ol>
				<li>
					<textarea id="qeGeneratedQuery" name="qeGeneratedQuery" rows="5" cols="35" onkeyup="javascript:qeQueryHasBeenUpdated()"></textarea>
				</li>
				<li class="lastli">
					<input id="qeBtnReset" class="qeBtn" type="button"  value="<?php echo (_p("Reset")) ?>"  onclick="javascript:qeReset()" />
					<input id="qeBtnApply"  class="qeBtn" type="button"  value="<?php echo (_p("Apply")) ?>"  onclick="javascript:qeApply()" />
					<input id="qeBtnCancel"  class="qeBtn" type="button"  value="<?php echo (_p("Cancel")) ?>"  onclick="javascript:qeCancel()" />
				</li>
			</ol>
		</fieldset>

		<input type="hidden" name="<?php echo ini_get("session.name") ?>" value="<?php session_id() ?>" />
    	<input type="hidden" name="config" value="<?php echo $_SESSION['config'] ?>" />
	</form>
</div>
</body>
</html>