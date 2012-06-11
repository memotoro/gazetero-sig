This is a small plugin for pmapper framework.
It just allow to use a button in user interface witch show the search form layer.

It contains:
- config.inc: include the other files in pmapper
- searchtool.js: the function to execute when button is clicked
- x_searchtool.php: generate the HTML code for the "searchForm".

Dependancies :
No dependancy

How to use:
Add the string "searchtool" to the plugins list in the pmapper config file.
Add the search tool button in the interface ( '"searchtool"  => array(_p("Search"), "0")' in the "$buttons" variable of "php_config.php" file).
Add an image to use for the button in the corresponding theme directory.

Parameters:
No parameters.

BE CARREFULL :
Maybe you will need to modify your CSS properties, like for instance :
#mapToolArea {
	height: 25px;
	text-align: left;
}
.pm_searchcont {
	position: relative;
}

TO DO :
add image for button