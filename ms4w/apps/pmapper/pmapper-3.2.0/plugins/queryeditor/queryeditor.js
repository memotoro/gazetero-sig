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

function openQueryEditorDlg() {
	var varform = _$('varform');
	varform.mode.value = 'queryeditor';
	varform.maction.value = 'click';
	varform.tool.value = 'queryeditor';

	var url = PM_PLUGIN_LOCATION + '/queryeditor/queryeditordlg.php?' + SID;
	var title = localeList['QueryEditor'] ? localeList['QueryEditor'] : 'Query Editor';
	openAjaxQueryIn('dynwin', url, title, 350, 455);
}
