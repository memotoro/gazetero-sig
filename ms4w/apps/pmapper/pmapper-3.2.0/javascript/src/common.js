
/******************************************************************************
 *
 * Purpose: common JS util functions
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
 * inArray function
 * Returns true if the passed value is found in the
 * array.  Returns false if it is not.
 * Usage: if (myList.inArray('search term')) {
 */
Array.prototype.inArray = function (value)
{
    var i;
    for (i=0; i < this.length; i++) {
        // Matches identical (===), not just similar (==).
        if (this[i] === value) {
            return true;
        }
    }
    return false;
};


/*  Prototype JavaScript framework, version 1.4.0
 *  (c) 2005 Sam Stephenson <sam@conio.net>
 *
 *  Prototype is freely distributable under the terms of an MIT-style license.
 *  For details, see the Prototype web site: http://prototype.conio.net/
/*--------------------------------------------------------------------------*/
function _$() {
    var elements = new Array();

    for (var i = 0; i < arguments.length; i++) {
        var element = arguments[i];
        if (typeof element == 'string')
            element = document.getElementById(element);

        if (arguments.length == 1)
            return element;

        elements.push(element);
    }

    return elements;
}


/**
 * DOM generic functions
 */
function objL(obj) {	
    return parseInt(obj.style.left || obj.offsetLeft);
}

function objT(obj) {
    return parseInt(obj.style.top || obj.offsetTop);
}

function objW(obj) {
	return parseInt( obj.style.width || obj.clientWidth );
}

function objH(obj) {		
    return parseInt( obj.style.height || obj.clientHeight);    
}

function hideObj(obj) {
    obj.style.visibility = 'hidden';
}

function showObj(obj) {
    obj.style.visibility = 'visible';
}


/**
 * Substitution for .innerHTML = ...
 */
function setInnerHTML(elementId , html){
    var el = _$('toc');
    el.innerHTML = html; 
    evalInnerJS(el);
}

function evalInnerJS(element) {
    var scripts = element.getElementsByTagName('script');
    var code;
    for (var i = 0; i < scripts.length; i++) {
        code =  scripts[i].innerHTML ? scripts[i].innerHTML : 
            scripts[i].text ? scripts[i].text : 
            scripts[i].textContent;
        try {
            eval(code);
        } catch(e) {
            alert(e);
        }
    }
}

