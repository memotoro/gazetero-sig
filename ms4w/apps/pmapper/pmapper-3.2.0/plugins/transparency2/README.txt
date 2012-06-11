Transparency2 plugin add buttons for each groups / layer in TOC.

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- transparency2.css: styles for sliders
- transparency2.js: redefine "initGroupTransparencies" and setGroupTransparency functions to add a small part of code concerning sliders and contains functions dedicated to sliders
- transparency2DynFunctions.js.php: dynamic js part for each layers

Dependancies :
No dependancy

How to use:
- add the string "transparency" to the plugins list in the pmapper config file.
- modify configuration in your ini config file to indicate if you want to use opacity or transparency ("transp2UseOpacity")

TO DO:
add tooltip
