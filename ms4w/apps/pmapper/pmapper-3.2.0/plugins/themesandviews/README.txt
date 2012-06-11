The ThemesAndViews plugin can extend the layers selection.

Few definitions :
A "theme" is a list of layer to select, possibily with opacity.
A "view" is a theme but with extent.

What does this plugins can do ?
- auto insert a selectbox to chose a theme
- auto insert a selectbox to chose a view
- use a button tool to show theme box in "mapToolArea"
- use a button tool to show view box in "mapToolArea"
- if use with "legendonly" plugin, TOC behavior become very simple (but with less functionalities !) for GIS beginner

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- tav.css: boxes styles
- tav.js.php: dynamic part (js variables depending on ini configuration file) for concerning initialisation functions (before or after TOC upadate, auto-insert) and static js functions
- tavCommon.js: functions used by this plugin and ThemesAndViewsAdmin plugin
- tav.php: server part (XML loading, boxes code generation, ...)
- x_tavBox.php: call in AJAX to generate boxes for the interface
- x_tavApply.php: call in AJAX to apply a theme or a view

Dependancies :
pmapper/plugins/sirapcommon/easyincludes.php

How to use:
- add the string "themesandviews" to the plugins list in the pmapper config file.
- add parameters  to your pmapper ini config file
- configure your themes and views (either with ThemesAndViewsAdmin plugin, either directly in a XML file)
- if you want, you add new tools in your button bar. To do that, modify your "$buttons" variable in "php_config.php" (in your config directory) like this:
'"themesbox" => array(_p("Apply theme"), "0")' for themes
'"viewsbox" => array(_p("Apply vue"), "0")' for views

Parameters:
- tavFile: XML file path and name describing your themes and views
- tavThemesBoxType: how to insert themesbox
- tavThemesBoxContainer: container for themesbox
- tavThemesKeepSelected: when a theme is selected in the selectbox, should the box keep it selected ?
- tavViewsBoxType: see tavThemesBoxType
- tavViewsBoxContainer: see tavThemesBoxContainer
- tavViewsKeepSelected: see tavThemesKeepSelected
- tavSetDefault: indicate if a theme or a view will be automaticaly applied
- tavDefaultCodeValue: code of the theme or view auto-loaded

Complementary plugin : ThemesAndViewsAdmin plugin (soon available)