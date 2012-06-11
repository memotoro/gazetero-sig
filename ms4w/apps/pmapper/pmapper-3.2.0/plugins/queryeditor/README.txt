The QueryEditor plugin is a form witch permit to construct attribute query (syntax is near SQL).

IMPORTANT:
Wait for a bug correction in core code. If bug not corrected, see instructions at http://svn.pmapper.net/trac/ticket/5

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- queryeditor.css: styles for form (search "// custom CSS for this window"  in queryeditor.php to un/activated it)
- queryeditor.js: only the "click" function event for button
- queryeditordlg.php: the html page containing the form for query generation 
- queryeditordlg.js: the js applicative code for the window
- x_queryeditor.php: load fields headers for the selected layer, and execute query
- install directory: images for buttons

Dependancies :
- plugins/common/commonforms.css (and gif associated files if needed) : inspired of cmxform, but no js associated
- plugins/common/common.js for openning the window in dynwin, etc...
- plugins/sirapcommon/easyincludes.php

Parameters:
- queryEditorLayersChoice: want kind of layers are allowed in the query editor
- queryEditorLayersList: pre-defined list of layers

How to use:
- add the string "queryeditor" to the plugins list in the pmapper config file.
- modify the "queryEditorLayersChoice" (and "queryEditorLayersList" if needed) parameters in your ini configuration file.
- add new tool in your button bar. To do that, modify your "$buttons" variable in "php_config.php" (in your config directory) like this:
'"queryeditor" => array(_p("Query editor"), "0")'
And of course, add the image for buttons... (available in the install dir

TO DO :
- add images for buttons
- try to find a generic way to show the first 5 différent values for the chosen attribute field
- change operator organisation in the window
- try to see with Armin if/how it is possible to re-use part of search.php (getSearchParameters functions)
- if "commonforms" could re-use in the core code, share it in "templates" directory
- maybe use the custom styles (search "// custom CSS for this window" in queryeditor.php)
- allow to use in a different navigator window