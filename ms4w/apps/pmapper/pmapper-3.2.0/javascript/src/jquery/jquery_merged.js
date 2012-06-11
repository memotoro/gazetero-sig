/*
 * jQuery 1.2.1 - New Wave Javascript
 *
 * Copyright (c) 2007 John Resig (jquery.com)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * $Date: 2007-09-16 23:42:06 -0400 (Sun, 16 Sep 2007) $
 * $Rev: 3353 $
 */
(function(){if(typeof jQuery!="undefined")var _jQuery=jQuery;var jQuery=window.jQuery=function(selector,context){return this instanceof jQuery?this.init(selector,context):new jQuery(selector,context);};if(typeof $!="undefined")var _$=$;window.$=jQuery;var quickExpr=/^[^<]*(<(.|\s)+>)[^>]*$|^#(\w+)$/;jQuery.fn=jQuery.prototype={init:function(selector,context){selector=selector||document;if(typeof selector=="string"){var m=quickExpr.exec(selector);if(m&&(m[1]||!context)){if(m[1])selector=jQuery.clean([m[1]],context);else{var tmp=document.getElementById(m[3]);if(tmp)if(tmp.id!=m[3])return jQuery().find(selector);else{this[0]=tmp;this.length=1;return this;}else
selector=[];}}else
return new jQuery(context).find(selector);}else if(jQuery.isFunction(selector))return new jQuery(document)[jQuery.fn.ready?"ready":"load"](selector);return this.setArray(selector.constructor==Array&&selector||(selector.jquery||selector.length&&selector!=window&&!selector.nodeType&&selector[0]!=undefined&&selector[0].nodeType)&&jQuery.makeArray(selector)||[selector]);},jquery:"1.2.1",size:function(){return this.length;},length:0,get:function(num){return num==undefined?jQuery.makeArray(this):this[num];},pushStack:function(a){var ret=jQuery(a);ret.prevObject=this;return ret;},setArray:function(a){this.length=0;Array.prototype.push.apply(this,a);return this;},each:function(fn,args){return jQuery.each(this,fn,args);},index:function(obj){var pos=-1;this.each(function(i){if(this==obj)pos=i;});return pos;},attr:function(key,value,type){var obj=key;if(key.constructor==String)if(value==undefined)return this.length&&jQuery[type||"attr"](this[0],key)||undefined;else{obj={};obj[key]=value;}return this.each(function(index){for(var prop in obj)jQuery.attr(type?this.style:this,prop,jQuery.prop(this,obj[prop],type,index,prop));});},css:function(key,value){return this.attr(key,value,"curCSS");},text:function(e){if(typeof e!="object"&&e!=null)return this.empty().append(document.createTextNode(e));var t="";jQuery.each(e||this,function(){jQuery.each(this.childNodes,function(){if(this.nodeType!=8)t+=this.nodeType!=1?this.nodeValue:jQuery.fn.text([this]);});});return t;},wrapAll:function(html){if(this[0])jQuery(html,this[0].ownerDocument).clone().insertBefore(this[0]).map(function(){var elem=this;while(elem.firstChild)elem=elem.firstChild;return elem;}).append(this);return this;},wrapInner:function(html){return this.each(function(){jQuery(this).contents().wrapAll(html);});},wrap:function(html){return this.each(function(){jQuery(this).wrapAll(html);});},append:function(){return this.domManip(arguments,true,1,function(a){this.appendChild(a);});},prepend:function(){return this.domManip(arguments,true,-1,function(a){this.insertBefore(a,this.firstChild);});},before:function(){return this.domManip(arguments,false,1,function(a){this.parentNode.insertBefore(a,this);});},after:function(){return this.domManip(arguments,false,-1,function(a){this.parentNode.insertBefore(a,this.nextSibling);});},end:function(){return this.prevObject||jQuery([]);},find:function(t){var data=jQuery.map(this,function(a){return jQuery.find(t,a);});return this.pushStack(/[^+>] [^+>]/.test(t)||t.indexOf("..")>-1?jQuery.unique(data):data);},clone:function(events){var ret=this.map(function(){return this.outerHTML?jQuery(this.outerHTML)[0]:this.cloneNode(true);});var clone=ret.find("*").andSelf().each(function(){if(this[expando]!=undefined)this[expando]=null;});if(events===true)this.find("*").andSelf().each(function(i){var events=jQuery.data(this,"events");for(var type in events)for(var handler in events[type])jQuery.event.add(clone[i],type,events[type][handler],events[type][handler].data);});return ret;},filter:function(t){return this.pushStack(jQuery.isFunction(t)&&jQuery.grep(this,function(el,index){return t.apply(el,[index]);})||jQuery.multiFilter(t,this));},not:function(t){return this.pushStack(t.constructor==String&&jQuery.multiFilter(t,this,true)||jQuery.grep(this,function(a){return(t.constructor==Array||t.jquery)?jQuery.inArray(a,t)<0:a!=t;}));},add:function(t){return this.pushStack(jQuery.merge(this.get(),t.constructor==String?jQuery(t).get():t.length!=undefined&&(!t.nodeName||jQuery.nodeName(t,"form"))?t:[t]));},is:function(expr){return expr?jQuery.multiFilter(expr,this).length>0:false;},hasClass:function(expr){return this.is("."+expr);},val:function(val){if(val==undefined){if(this.length){var elem=this[0];if(jQuery.nodeName(elem,"select")){var index=elem.selectedIndex,a=[],options=elem.options,one=elem.type=="select-one";if(index<0)return null;for(var i=one?index:0,max=one?index+1:options.length;i<max;i++){var option=options[i];if(option.selected){var val=jQuery.browser.msie&&!option.attributes["value"].specified?option.text:option.value;if(one)return val;a.push(val);}}return a;}else
return this[0].value.replace(/\r/g,"");}}else
return this.each(function(){if(val.constructor==Array&&/radio|checkbox/.test(this.type))this.checked=(jQuery.inArray(this.value,val)>=0||jQuery.inArray(this.name,val)>=0);else if(jQuery.nodeName(this,"select")){var tmp=val.constructor==Array?val:[val];jQuery("option",this).each(function(){this.selected=(jQuery.inArray(this.value,tmp)>=0||jQuery.inArray(this.text,tmp)>=0);});if(!tmp.length)this.selectedIndex=-1;}else
this.value=val;});},html:function(val){return val==undefined?(this.length?this[0].innerHTML:null):this.empty().append(val);},replaceWith:function(val){return this.after(val).remove();},eq:function(i){return this.slice(i,i+1);},slice:function(){return this.pushStack(Array.prototype.slice.apply(this,arguments));},map:function(fn){return this.pushStack(jQuery.map(this,function(elem,i){return fn.call(elem,i,elem);}));},andSelf:function(){return this.add(this.prevObject);},domManip:function(args,table,dir,fn){var clone=this.length>1,a;return this.each(function(){if(!a){a=jQuery.clean(args,this.ownerDocument);if(dir<0)a.reverse();}var obj=this;if(table&&jQuery.nodeName(this,"table")&&jQuery.nodeName(a[0],"tr"))obj=this.getElementsByTagName("tbody")[0]||this.appendChild(document.createElement("tbody"));jQuery.each(a,function(){var elem=clone?this.cloneNode(true):this;if(!evalScript(0,elem))fn.call(obj,elem);});});}};function evalScript(i,elem){var script=jQuery.nodeName(elem,"script");if(script){if(elem.src)jQuery.ajax({url:elem.src,async:false,dataType:"script"});else
jQuery.globalEval(elem.text||elem.textContent||elem.innerHTML||"");if(elem.parentNode)elem.parentNode.removeChild(elem);}else if(elem.nodeType==1)jQuery("script",elem).each(evalScript);return script;}jQuery.extend=jQuery.fn.extend=function(){var target=arguments[0]||{},a=1,al=arguments.length,deep=false;if(target.constructor==Boolean){deep=target;target=arguments[1]||{};}if(al==1){target=this;a=0;}var prop;for(;a<al;a++)if((prop=arguments[a])!=null)for(var i in prop){if(target==prop[i])continue;if(deep&&typeof prop[i]=='object'&&target[i])jQuery.extend(target[i],prop[i]);else if(prop[i]!=undefined)target[i]=prop[i];}return target;};var expando="jQuery"+(new Date()).getTime(),uuid=0,win={};jQuery.extend({noConflict:function(deep){window.$=_$;if(deep)window.jQuery=_jQuery;return jQuery;},isFunction:function(fn){return!!fn&&typeof fn!="string"&&!fn.nodeName&&fn.constructor!=Array&&/function/i.test(fn+"");},isXMLDoc:function(elem){return elem.documentElement&&!elem.body||elem.tagName&&elem.ownerDocument&&!elem.ownerDocument.body;},globalEval:function(data){data=jQuery.trim(data);if(data){if(window.execScript)window.execScript(data);else if(jQuery.browser.safari)window.setTimeout(data,0);else
eval.call(window,data);}},nodeName:function(elem,name){return elem.nodeName&&elem.nodeName.toUpperCase()==name.toUpperCase();},cache:{},data:function(elem,name,data){elem=elem==window?win:elem;var id=elem[expando];if(!id)id=elem[expando]=++uuid;if(name&&!jQuery.cache[id])jQuery.cache[id]={};if(data!=undefined)jQuery.cache[id][name]=data;return name?jQuery.cache[id][name]:id;},removeData:function(elem,name){elem=elem==window?win:elem;var id=elem[expando];if(name){if(jQuery.cache[id]){delete jQuery.cache[id][name];name="";for(name in jQuery.cache[id])break;if(!name)jQuery.removeData(elem);}}else{try{delete elem[expando];}catch(e){if(elem.removeAttribute)elem.removeAttribute(expando);}delete jQuery.cache[id];}},each:function(obj,fn,args){if(args){if(obj.length==undefined)for(var i in obj)fn.apply(obj[i],args);else
for(var i=0,ol=obj.length;i<ol;i++)if(fn.apply(obj[i],args)===false)break;}else{if(obj.length==undefined)for(var i in obj)fn.call(obj[i],i,obj[i]);else
for(var i=0,ol=obj.length,val=obj[0];i<ol&&fn.call(val,i,val)!==false;val=obj[++i]){}}return obj;},prop:function(elem,value,type,index,prop){if(jQuery.isFunction(value))value=value.call(elem,[index]);var exclude=/z-?index|font-?weight|opacity|zoom|line-?height/i;return value&&value.constructor==Number&&type=="curCSS"&&!exclude.test(prop)?value+"px":value;},className:{add:function(elem,c){jQuery.each((c||"").split(/\s+/),function(i,cur){if(!jQuery.className.has(elem.className,cur))elem.className+=(elem.className?" ":"")+cur;});},remove:function(elem,c){elem.className=c!=undefined?jQuery.grep(elem.className.split(/\s+/),function(cur){return!jQuery.className.has(c,cur);}).join(" "):"";},has:function(t,c){return jQuery.inArray(c,(t.className||t).toString().split(/\s+/))>-1;}},swap:function(e,o,f){for(var i in o){e.style["old"+i]=e.style[i];e.style[i]=o[i];}f.apply(e,[]);for(var i in o)e.style[i]=e.style["old"+i];},css:function(e,p){if(p=="height"||p=="width"){var old={},oHeight,oWidth,d=["Top","Bottom","Right","Left"];jQuery.each(d,function(){old["padding"+this]=0;old["border"+this+"Width"]=0;});jQuery.swap(e,old,function(){if(jQuery(e).is(':visible')){oHeight=e.offsetHeight;oWidth=e.offsetWidth;}else{e=jQuery(e.cloneNode(true)).find(":radio").removeAttr("checked").end().css({visibility:"hidden",position:"absolute",display:"block",right:"0",left:"0"}).appendTo(e.parentNode)[0];var parPos=jQuery.css(e.parentNode,"position")||"static";if(parPos=="static")e.parentNode.style.position="relative";oHeight=e.clientHeight;oWidth=e.clientWidth;if(parPos=="static")e.parentNode.style.position="static";e.parentNode.removeChild(e);}});return p=="height"?oHeight:oWidth;}return jQuery.curCSS(e,p);},curCSS:function(elem,prop,force){var ret,stack=[],swap=[];function color(a){if(!jQuery.browser.safari)return false;var ret=document.defaultView.getComputedStyle(a,null);return!ret||ret.getPropertyValue("color")=="";}if(prop=="opacity"&&jQuery.browser.msie){ret=jQuery.attr(elem.style,"opacity");return ret==""?"1":ret;}if(prop.match(/float/i))prop=styleFloat;if(!force&&elem.style[prop])ret=elem.style[prop];else if(document.defaultView&&document.defaultView.getComputedStyle){if(prop.match(/float/i))prop="float";prop=prop.replace(/([A-Z])/g,"-$1").toLowerCase();var cur=document.defaultView.getComputedStyle(elem,null);if(cur&&!color(elem))ret=cur.getPropertyValue(prop);else{for(var a=elem;a&&color(a);a=a.parentNode)stack.unshift(a);for(a=0;a<stack.length;a++)if(color(stack[a])){swap[a]=stack[a].style.display;stack[a].style.display="block";}ret=prop=="display"&&swap[stack.length-1]!=null?"none":document.defaultView.getComputedStyle(elem,null).getPropertyValue(prop)||"";for(a=0;a<swap.length;a++)if(swap[a]!=null)stack[a].style.display=swap[a];}if(prop=="opacity"&&ret=="")ret="1";}else if(elem.currentStyle){var newProp=prop.replace(/\-(\w)/g,function(m,c){return c.toUpperCase();});ret=elem.currentStyle[prop]||elem.currentStyle[newProp];if(!/^\d+(px)?$/i.test(ret)&&/^\d/.test(ret)){var style=elem.style.left;var runtimeStyle=elem.runtimeStyle.left;elem.runtimeStyle.left=elem.currentStyle.left;elem.style.left=ret||0;ret=elem.style.pixelLeft+"px";elem.style.left=style;elem.runtimeStyle.left=runtimeStyle;}}return ret;},clean:function(a,doc){var r=[];doc=doc||document;jQuery.each(a,function(i,arg){if(!arg)return;if(arg.constructor==Number)arg=arg.toString();if(typeof arg=="string"){arg=arg.replace(/(<(\w+)[^>]*?)\/>/g,function(m,all,tag){return tag.match(/^(abbr|br|col|img|input|link|meta|param|hr|area)$/i)?m:all+"></"+tag+">";});var s=jQuery.trim(arg).toLowerCase(),div=doc.createElement("div"),tb=[];var wrap=!s.indexOf("<opt")&&[1,"<select>","</select>"]||!s.indexOf("<leg")&&[1,"<fieldset>","</fieldset>"]||s.match(/^<(thead|tbody|tfoot|colg|cap)/)&&[1,"<table>","</table>"]||!s.indexOf("<tr")&&[2,"<table><tbody>","</tbody></table>"]||(!s.indexOf("<td")||!s.indexOf("<th"))&&[3,"<table><tbody><tr>","</tr></tbody></table>"]||!s.indexOf("<col")&&[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"]||jQuery.browser.msie&&[1,"div<div>","</div>"]||[0,"",""];div.innerHTML=wrap[1]+arg+wrap[2];while(wrap[0]--)div=div.lastChild;if(jQuery.browser.msie){if(!s.indexOf("<table")&&s.indexOf("<tbody")<0)tb=div.firstChild&&div.firstChild.childNodes;else if(wrap[1]=="<table>"&&s.indexOf("<tbody")<0)tb=div.childNodes;for(var n=tb.length-1;n>=0;--n)if(jQuery.nodeName(tb[n],"tbody")&&!tb[n].childNodes.length)tb[n].parentNode.removeChild(tb[n]);if(/^\s/.test(arg))div.insertBefore(doc.createTextNode(arg.match(/^\s*/)[0]),div.firstChild);}arg=jQuery.makeArray(div.childNodes);}if(0===arg.length&&(!jQuery.nodeName(arg,"form")&&!jQuery.nodeName(arg,"select")))return;if(arg[0]==undefined||jQuery.nodeName(arg,"form")||arg.options)r.push(arg);else
r=jQuery.merge(r,arg);});return r;},attr:function(elem,name,value){var fix=jQuery.isXMLDoc(elem)?{}:jQuery.props;if(name=="selected"&&jQuery.browser.safari)elem.parentNode.selectedIndex;if(fix[name]){if(value!=undefined)elem[fix[name]]=value;return elem[fix[name]];}else if(jQuery.browser.msie&&name=="style")return jQuery.attr(elem.style,"cssText",value);else if(value==undefined&&jQuery.browser.msie&&jQuery.nodeName(elem,"form")&&(name=="action"||name=="method"))return elem.getAttributeNode(name).nodeValue;else if(elem.tagName){if(value!=undefined){if(name=="type"&&jQuery.nodeName(elem,"input")&&elem.parentNode)throw"type property can't be changed";elem.setAttribute(name,value);}if(jQuery.browser.msie&&/href|src/.test(name)&&!jQuery.isXMLDoc(elem))return elem.getAttribute(name,2);return elem.getAttribute(name);}else{if(name=="opacity"&&jQuery.browser.msie){if(value!=undefined){elem.zoom=1;elem.filter=(elem.filter||"").replace(/alpha\([^)]*\)/,"")+(parseFloat(value).toString()=="NaN"?"":"alpha(opacity="+value*100+")");}return elem.filter?(parseFloat(elem.filter.match(/opacity=([^)]*)/)[1])/100).toString():"";}name=name.replace(/-([a-z])/ig,function(z,b){return b.toUpperCase();});if(value!=undefined)elem[name]=value;return elem[name];}},trim:function(t){return(t||"").replace(/^\s+|\s+$/g,"");},makeArray:function(a){var r=[];if(typeof a!="array")for(var i=0,al=a.length;i<al;i++)r.push(a[i]);else
r=a.slice(0);return r;},inArray:function(b,a){for(var i=0,al=a.length;i<al;i++)if(a[i]==b)return i;return-1;},merge:function(first,second){if(jQuery.browser.msie){for(var i=0;second[i];i++)if(second[i].nodeType!=8)first.push(second[i]);}else
for(var i=0;second[i];i++)first.push(second[i]);return first;},unique:function(first){var r=[],done={};try{for(var i=0,fl=first.length;i<fl;i++){var id=jQuery.data(first[i]);if(!done[id]){done[id]=true;r.push(first[i]);}}}catch(e){r=first;}return r;},grep:function(elems,fn,inv){if(typeof fn=="string")fn=eval("false||function(a,i){return "+fn+"}");var result=[];for(var i=0,el=elems.length;i<el;i++)if(!inv&&fn(elems[i],i)||inv&&!fn(elems[i],i))result.push(elems[i]);return result;},map:function(elems,fn){if(typeof fn=="string")fn=eval("false||function(a){return "+fn+"}");var result=[];for(var i=0,el=elems.length;i<el;i++){var val=fn(elems[i],i);if(val!==null&&val!=undefined){if(val.constructor!=Array)val=[val];result=result.concat(val);}}return result;}});var userAgent=navigator.userAgent.toLowerCase();jQuery.browser={version:(userAgent.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/)||[])[1],safari:/webkit/.test(userAgent),opera:/opera/.test(userAgent),msie:/msie/.test(userAgent)&&!/opera/.test(userAgent),mozilla:/mozilla/.test(userAgent)&&!/(compatible|webkit)/.test(userAgent)};var styleFloat=jQuery.browser.msie?"styleFloat":"cssFloat";jQuery.extend({boxModel:!jQuery.browser.msie||document.compatMode=="CSS1Compat",styleFloat:jQuery.browser.msie?"styleFloat":"cssFloat",props:{"for":"htmlFor","class":"className","float":styleFloat,cssFloat:styleFloat,styleFloat:styleFloat,innerHTML:"innerHTML",className:"className",value:"value",disabled:"disabled",checked:"checked",readonly:"readOnly",selected:"selected",maxlength:"maxLength"}});jQuery.each({parent:"a.parentNode",parents:"jQuery.dir(a,'parentNode')",next:"jQuery.nth(a,2,'nextSibling')",prev:"jQuery.nth(a,2,'previousSibling')",nextAll:"jQuery.dir(a,'nextSibling')",prevAll:"jQuery.dir(a,'previousSibling')",siblings:"jQuery.sibling(a.parentNode.firstChild,a)",children:"jQuery.sibling(a.firstChild)",contents:"jQuery.nodeName(a,'iframe')?a.contentDocument||a.contentWindow.document:jQuery.makeArray(a.childNodes)"},function(i,n){jQuery.fn[i]=function(a){var ret=jQuery.map(this,n);if(a&&typeof a=="string")ret=jQuery.multiFilter(a,ret);return this.pushStack(jQuery.unique(ret));};});jQuery.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(i,n){jQuery.fn[i]=function(){var a=arguments;return this.each(function(){for(var j=0,al=a.length;j<al;j++)jQuery(a[j])[n](this);});};});jQuery.each({removeAttr:function(key){jQuery.attr(this,key,"");this.removeAttribute(key);},addClass:function(c){jQuery.className.add(this,c);},removeClass:function(c){jQuery.className.remove(this,c);},toggleClass:function(c){jQuery.className[jQuery.className.has(this,c)?"remove":"add"](this,c);},remove:function(a){if(!a||jQuery.filter(a,[this]).r.length){jQuery.removeData(this);this.parentNode.removeChild(this);}},empty:function(){jQuery("*",this).each(function(){jQuery.removeData(this);});while(this.firstChild)this.removeChild(this.firstChild);}},function(i,n){jQuery.fn[i]=function(){return this.each(n,arguments);};});jQuery.each(["Height","Width"],function(i,name){var n=name.toLowerCase();jQuery.fn[n]=function(h){return this[0]==window?jQuery.browser.safari&&self["inner"+name]||jQuery.boxModel&&Math.max(document.documentElement["client"+name],document.body["client"+name])||document.body["client"+name]:this[0]==document?Math.max(document.body["scroll"+name],document.body["offset"+name]):h==undefined?(this.length?jQuery.css(this[0],n):null):this.css(n,h.constructor==String?h:h+"px");};});var chars=jQuery.browser.safari&&parseInt(jQuery.browser.version)<417?"(?:[\\w*_-]|\\\\.)":"(?:[\\w\u0128-\uFFFF*_-]|\\\\.)",quickChild=new RegExp("^>\\s*("+chars+"+)"),quickID=new RegExp("^("+chars+"+)(#)("+chars+"+)"),quickClass=new RegExp("^([#.]?)("+chars+"*)");jQuery.extend({expr:{"":"m[2]=='*'||jQuery.nodeName(a,m[2])","#":"a.getAttribute('id')==m[2]",":":{lt:"i<m[3]-0",gt:"i>m[3]-0",nth:"m[3]-0==i",eq:"m[3]-0==i",first:"i==0",last:"i==r.length-1",even:"i%2==0",odd:"i%2","first-child":"a.parentNode.getElementsByTagName('*')[0]==a","last-child":"jQuery.nth(a.parentNode.lastChild,1,'previousSibling')==a","only-child":"!jQuery.nth(a.parentNode.lastChild,2,'previousSibling')",parent:"a.firstChild",empty:"!a.firstChild",contains:"(a.textContent||a.innerText||jQuery(a).text()||'').indexOf(m[3])>=0",visible:'"hidden"!=a.type&&jQuery.css(a,"display")!="none"&&jQuery.css(a,"visibility")!="hidden"',hidden:'"hidden"==a.type||jQuery.css(a,"display")=="none"||jQuery.css(a,"visibility")=="hidden"',enabled:"!a.disabled",disabled:"a.disabled",checked:"a.checked",selected:"a.selected||jQuery.attr(a,'selected')",text:"'text'==a.type",radio:"'radio'==a.type",checkbox:"'checkbox'==a.type",file:"'file'==a.type",password:"'password'==a.type",submit:"'submit'==a.type",image:"'image'==a.type",reset:"'reset'==a.type",button:'"button"==a.type||jQuery.nodeName(a,"button")',input:"/input|select|textarea|button/i.test(a.nodeName)",has:"jQuery.find(m[3],a).length",header:"/h\\d/i.test(a.nodeName)",animated:"jQuery.grep(jQuery.timers,function(fn){return a==fn.elem;}).length"}},parse:[/^(\[) *@?([\w-]+) *([!*$^~=]*) *('?"?)(.*?)\4 *\]/,/^(:)([\w-]+)\("?'?(.*?(\(.*?\))?[^(]*?)"?'?\)/,new RegExp("^([:.#]*)("+chars+"+)")],multiFilter:function(expr,elems,not){var old,cur=[];while(expr&&expr!=old){old=expr;var f=jQuery.filter(expr,elems,not);expr=f.t.replace(/^\s*,\s*/,"");cur=not?elems=f.r:jQuery.merge(cur,f.r);}return cur;},find:function(t,context){if(typeof t!="string")return[t];if(context&&!context.nodeType)context=null;context=context||document;var ret=[context],done=[],last;while(t&&last!=t){var r=[];last=t;t=jQuery.trim(t);var foundToken=false;var re=quickChild;var m=re.exec(t);if(m){var nodeName=m[1].toUpperCase();for(var i=0;ret[i];i++)for(var c=ret[i].firstChild;c;c=c.nextSibling)if(c.nodeType==1&&(nodeName=="*"||c.nodeName.toUpperCase()==nodeName.toUpperCase()))r.push(c);ret=r;t=t.replace(re,"");if(t.indexOf(" ")==0)continue;foundToken=true;}else{re=/^([>+~])\s*(\w*)/i;if((m=re.exec(t))!=null){r=[];var nodeName=m[2],merge={};m=m[1];for(var j=0,rl=ret.length;j<rl;j++){var n=m=="~"||m=="+"?ret[j].nextSibling:ret[j].firstChild;for(;n;n=n.nextSibling)if(n.nodeType==1){var id=jQuery.data(n);if(m=="~"&&merge[id])break;if(!nodeName||n.nodeName.toUpperCase()==nodeName.toUpperCase()){if(m=="~")merge[id]=true;r.push(n);}if(m=="+")break;}}ret=r;t=jQuery.trim(t.replace(re,""));foundToken=true;}}if(t&&!foundToken){if(!t.indexOf(",")){if(context==ret[0])ret.shift();done=jQuery.merge(done,ret);r=ret=[context];t=" "+t.substr(1,t.length);}else{var re2=quickID;var m=re2.exec(t);if(m){m=[0,m[2],m[3],m[1]];}else{re2=quickClass;m=re2.exec(t);}m[2]=m[2].replace(/\\/g,"");var elem=ret[ret.length-1];if(m[1]=="#"&&elem&&elem.getElementById&&!jQuery.isXMLDoc(elem)){var oid=elem.getElementById(m[2]);if((jQuery.browser.msie||jQuery.browser.opera)&&oid&&typeof oid.id=="string"&&oid.id!=m[2])oid=jQuery('[@id="'+m[2]+'"]',elem)[0];ret=r=oid&&(!m[3]||jQuery.nodeName(oid,m[3]))?[oid]:[];}else{for(var i=0;ret[i];i++){var tag=m[1]=="#"&&m[3]?m[3]:m[1]!=""||m[0]==""?"*":m[2];if(tag=="*"&&ret[i].nodeName.toLowerCase()=="object")tag="param";r=jQuery.merge(r,ret[i].getElementsByTagName(tag));}if(m[1]==".")r=jQuery.classFilter(r,m[2]);if(m[1]=="#"){var tmp=[];for(var i=0;r[i];i++)if(r[i].getAttribute("id")==m[2]){tmp=[r[i]];break;}r=tmp;}ret=r;}t=t.replace(re2,"");}}if(t){var val=jQuery.filter(t,r);ret=r=val.r;t=jQuery.trim(val.t);}}if(t)ret=[];if(ret&&context==ret[0])ret.shift();done=jQuery.merge(done,ret);return done;},classFilter:function(r,m,not){m=" "+m+" ";var tmp=[];for(var i=0;r[i];i++){var pass=(" "+r[i].className+" ").indexOf(m)>=0;if(!not&&pass||not&&!pass)tmp.push(r[i]);}return tmp;},filter:function(t,r,not){var last;while(t&&t!=last){last=t;var p=jQuery.parse,m;for(var i=0;p[i];i++){m=p[i].exec(t);if(m){t=t.substring(m[0].length);m[2]=m[2].replace(/\\/g,"");break;}}if(!m)break;if(m[1]==":"&&m[2]=="not")r=jQuery.filter(m[3],r,true).r;else if(m[1]==".")r=jQuery.classFilter(r,m[2],not);else if(m[1]=="["){var tmp=[],type=m[3];for(var i=0,rl=r.length;i<rl;i++){var a=r[i],z=a[jQuery.props[m[2]]||m[2]];if(z==null||/href|src|selected/.test(m[2]))z=jQuery.attr(a,m[2])||'';if((type==""&&!!z||type=="="&&z==m[5]||type=="!="&&z!=m[5]||type=="^="&&z&&!z.indexOf(m[5])||type=="$="&&z.substr(z.length-m[5].length)==m[5]||(type=="*="||type=="~=")&&z.indexOf(m[5])>=0)^not)tmp.push(a);}r=tmp;}else if(m[1]==":"&&m[2]=="nth-child"){var merge={},tmp=[],test=/(\d*)n\+?(\d*)/.exec(m[3]=="even"&&"2n"||m[3]=="odd"&&"2n+1"||!/\D/.test(m[3])&&"n+"+m[3]||m[3]),first=(test[1]||1)-0,last=test[2]-0;for(var i=0,rl=r.length;i<rl;i++){var node=r[i],parentNode=node.parentNode,id=jQuery.data(parentNode);if(!merge[id]){var c=1;for(var n=parentNode.firstChild;n;n=n.nextSibling)if(n.nodeType==1)n.nodeIndex=c++;merge[id]=true;}var add=false;if(first==1){if(last==0||node.nodeIndex==last)add=true;}else if((node.nodeIndex+last)%first==0)add=true;if(add^not)tmp.push(node);}r=tmp;}else{var f=jQuery.expr[m[1]];if(typeof f!="string")f=jQuery.expr[m[1]][m[2]];f=eval("false||function(a,i){return "+f+"}");r=jQuery.grep(r,f,not);}}return{r:r,t:t};},dir:function(elem,dir){var matched=[];var cur=elem[dir];while(cur&&cur!=document){if(cur.nodeType==1)matched.push(cur);cur=cur[dir];}return matched;},nth:function(cur,result,dir,elem){result=result||1;var num=0;for(;cur;cur=cur[dir])if(cur.nodeType==1&&++num==result)break;return cur;},sibling:function(n,elem){var r=[];for(;n;n=n.nextSibling){if(n.nodeType==1&&(!elem||n!=elem))r.push(n);}return r;}});jQuery.event={add:function(element,type,handler,data){if(jQuery.browser.msie&&element.setInterval!=undefined)element=window;if(!handler.guid)handler.guid=this.guid++;if(data!=undefined){var fn=handler;handler=function(){return fn.apply(this,arguments);};handler.data=data;handler.guid=fn.guid;}var parts=type.split(".");type=parts[0];handler.type=parts[1];var events=jQuery.data(element,"events")||jQuery.data(element,"events",{});var handle=jQuery.data(element,"handle",function(){var val;if(typeof jQuery=="undefined"||jQuery.event.triggered)return val;val=jQuery.event.handle.apply(element,arguments);return val;});var handlers=events[type];if(!handlers){handlers=events[type]={};if(element.addEventListener)element.addEventListener(type,handle,false);else
element.attachEvent("on"+type,handle);}handlers[handler.guid]=handler;this.global[type]=true;},guid:1,global:{},remove:function(element,type,handler){var events=jQuery.data(element,"events"),ret,index;if(typeof type=="string"){var parts=type.split(".");type=parts[0];}if(events){if(type&&type.type){handler=type.handler;type=type.type;}if(!type){for(type in events)this.remove(element,type);}else if(events[type]){if(handler)delete events[type][handler.guid];else
for(handler in events[type])if(!parts[1]||events[type][handler].type==parts[1])delete events[type][handler];for(ret in events[type])break;if(!ret){if(element.removeEventListener)element.removeEventListener(type,jQuery.data(element,"handle"),false);else
element.detachEvent("on"+type,jQuery.data(element,"handle"));ret=null;delete events[type];}}for(ret in events)break;if(!ret){jQuery.removeData(element,"events");jQuery.removeData(element,"handle");}}},trigger:function(type,data,element,donative,extra){data=jQuery.makeArray(data||[]);if(!element){if(this.global[type])jQuery("*").add([window,document]).trigger(type,data);}else{var val,ret,fn=jQuery.isFunction(element[type]||null),evt=!data[0]||!data[0].preventDefault;if(evt)data.unshift(this.fix({type:type,target:element}));data[0].type=type;if(jQuery.isFunction(jQuery.data(element,"handle")))val=jQuery.data(element,"handle").apply(element,data);if(!fn&&element["on"+type]&&element["on"+type].apply(element,data)===false)val=false;if(evt)data.shift();if(extra&&extra.apply(element,data)===false)val=false;if(fn&&donative!==false&&val!==false&&!(jQuery.nodeName(element,'a')&&type=="click")){this.triggered=true;element[type]();}this.triggered=false;}return val;},handle:function(event){var val;event=jQuery.event.fix(event||window.event||{});var parts=event.type.split(".");event.type=parts[0];var c=jQuery.data(this,"events")&&jQuery.data(this,"events")[event.type],args=Array.prototype.slice.call(arguments,1);args.unshift(event);for(var j in c){args[0].handler=c[j];args[0].data=c[j].data;if(!parts[1]||c[j].type==parts[1]){var tmp=c[j].apply(this,args);if(val!==false)val=tmp;if(tmp===false){event.preventDefault();event.stopPropagation();}}}if(jQuery.browser.msie)event.target=event.preventDefault=event.stopPropagation=event.handler=event.data=null;return val;},fix:function(event){var originalEvent=event;event=jQuery.extend({},originalEvent);event.preventDefault=function(){if(originalEvent.preventDefault)originalEvent.preventDefault();originalEvent.returnValue=false;};event.stopPropagation=function(){if(originalEvent.stopPropagation)originalEvent.stopPropagation();originalEvent.cancelBubble=true;};if(!event.target&&event.srcElement)event.target=event.srcElement;if(jQuery.browser.safari&&event.target.nodeType==3)event.target=originalEvent.target.parentNode;if(!event.relatedTarget&&event.fromElement)event.relatedTarget=event.fromElement==event.target?event.toElement:event.fromElement;if(event.pageX==null&&event.clientX!=null){var e=document.documentElement,b=document.body;event.pageX=event.clientX+(e&&e.scrollLeft||b.scrollLeft||0);event.pageY=event.clientY+(e&&e.scrollTop||b.scrollTop||0);}if(!event.which&&(event.charCode||event.keyCode))event.which=event.charCode||event.keyCode;if(!event.metaKey&&event.ctrlKey)event.metaKey=event.ctrlKey;if(!event.which&&event.button)event.which=(event.button&1?1:(event.button&2?3:(event.button&4?2:0)));return event;}};jQuery.fn.extend({bind:function(type,data,fn){return type=="unload"?this.one(type,data,fn):this.each(function(){jQuery.event.add(this,type,fn||data,fn&&data);});},one:function(type,data,fn){return this.each(function(){jQuery.event.add(this,type,function(event){jQuery(this).unbind(event);return(fn||data).apply(this,arguments);},fn&&data);});},unbind:function(type,fn){return this.each(function(){jQuery.event.remove(this,type,fn);});},trigger:function(type,data,fn){return this.each(function(){jQuery.event.trigger(type,data,this,true,fn);});},triggerHandler:function(type,data,fn){if(this[0])return jQuery.event.trigger(type,data,this[0],false,fn);},toggle:function(){var a=arguments;return this.click(function(e){this.lastToggle=0==this.lastToggle?1:0;e.preventDefault();return a[this.lastToggle].apply(this,[e])||false;});},hover:function(f,g){function handleHover(e){var p=e.relatedTarget;while(p&&p!=this)try{p=p.parentNode;}catch(e){p=this;};if(p==this)return false;return(e.type=="mouseover"?f:g).apply(this,[e]);}return this.mouseover(handleHover).mouseout(handleHover);},ready:function(f){bindReady();if(jQuery.isReady)f.apply(document,[jQuery]);else
jQuery.readyList.push(function(){return f.apply(this,[jQuery]);});return this;}});jQuery.extend({isReady:false,readyList:[],ready:function(){if(!jQuery.isReady){jQuery.isReady=true;if(jQuery.readyList){jQuery.each(jQuery.readyList,function(){this.apply(document);});jQuery.readyList=null;}if(jQuery.browser.mozilla||jQuery.browser.opera)document.removeEventListener("DOMContentLoaded",jQuery.ready,false);if(!window.frames.length)jQuery(window).load(function(){jQuery("#__ie_init").remove();});}}});jQuery.each(("blur,focus,load,resize,scroll,unload,click,dblclick,"+"mousedown,mouseup,mousemove,mouseover,mouseout,change,select,"+"submit,keydown,keypress,keyup,error").split(","),function(i,o){jQuery.fn[o]=function(f){return f?this.bind(o,f):this.trigger(o);};});var readyBound=false;function bindReady(){if(readyBound)return;readyBound=true;if(jQuery.browser.mozilla||jQuery.browser.opera)document.addEventListener("DOMContentLoaded",jQuery.ready,false);else if(jQuery.browser.msie){document.write("<scr"+"ipt id=__ie_init defer=true "+"src=//:><\/script>");var script=document.getElementById("__ie_init");if(script)script.onreadystatechange=function(){if(this.readyState!="complete")return;jQuery.ready();};script=null;}else if(jQuery.browser.safari)jQuery.safariTimer=setInterval(function(){if(document.readyState=="loaded"||document.readyState=="complete"){clearInterval(jQuery.safariTimer);jQuery.safariTimer=null;jQuery.ready();}},10);jQuery.event.add(window,"load",jQuery.ready);}jQuery.fn.extend({load:function(url,params,callback){if(jQuery.isFunction(url))return this.bind("load",url);var off=url.indexOf(" ");if(off>=0){var selector=url.slice(off,url.length);url=url.slice(0,off);}callback=callback||function(){};var type="GET";if(params)if(jQuery.isFunction(params)){callback=params;params=null;}else{params=jQuery.param(params);type="POST";}var self=this;jQuery.ajax({url:url,type:type,data:params,complete:function(res,status){if(status=="success"||status=="notmodified")self.html(selector?jQuery("<div/>").append(res.responseText.replace(/<script(.|\s)*?\/script>/g,"")).find(selector):res.responseText);setTimeout(function(){self.each(callback,[res.responseText,status,res]);},13);}});return this;},serialize:function(){return jQuery.param(this.serializeArray());},serializeArray:function(){return this.map(function(){return jQuery.nodeName(this,"form")?jQuery.makeArray(this.elements):this;}).filter(function(){return this.name&&!this.disabled&&(this.checked||/select|textarea/i.test(this.nodeName)||/text|hidden|password/i.test(this.type));}).map(function(i,elem){var val=jQuery(this).val();return val==null?null:val.constructor==Array?jQuery.map(val,function(val,i){return{name:elem.name,value:val};}):{name:elem.name,value:val};}).get();}});jQuery.each("ajaxStart,ajaxStop,ajaxComplete,ajaxError,ajaxSuccess,ajaxSend".split(","),function(i,o){jQuery.fn[o]=function(f){return this.bind(o,f);};});var jsc=(new Date).getTime();jQuery.extend({get:function(url,data,callback,type){if(jQuery.isFunction(data)){callback=data;data=null;}return jQuery.ajax({type:"GET",url:url,data:data,success:callback,dataType:type});},getScript:function(url,callback){return jQuery.get(url,null,callback,"script");},getJSON:function(url,data,callback){return jQuery.get(url,data,callback,"json");},post:function(url,data,callback,type){if(jQuery.isFunction(data)){callback=data;data={};}return jQuery.ajax({type:"POST",url:url,data:data,success:callback,dataType:type});},ajaxSetup:function(settings){jQuery.extend(jQuery.ajaxSettings,settings);},ajaxSettings:{global:true,type:"GET",timeout:0,contentType:"application/x-www-form-urlencoded",processData:true,async:true,data:null},lastModified:{},ajax:function(s){var jsonp,jsre=/=(\?|%3F)/g,status,data;s=jQuery.extend(true,s,jQuery.extend(true,{},jQuery.ajaxSettings,s));if(s.data&&s.processData&&typeof s.data!="string")s.data=jQuery.param(s.data);if(s.dataType=="jsonp"){if(s.type.toLowerCase()=="get"){if(!s.url.match(jsre))s.url+=(s.url.match(/\?/)?"&":"?")+(s.jsonp||"callback")+"=?";}else if(!s.data||!s.data.match(jsre))s.data=(s.data?s.data+"&":"")+(s.jsonp||"callback")+"=?";s.dataType="json";}if(s.dataType=="json"&&(s.data&&s.data.match(jsre)||s.url.match(jsre))){jsonp="jsonp"+jsc++;if(s.data)s.data=s.data.replace(jsre,"="+jsonp);s.url=s.url.replace(jsre,"="+jsonp);s.dataType="script";window[jsonp]=function(tmp){data=tmp;success();complete();window[jsonp]=undefined;try{delete window[jsonp];}catch(e){}};}if(s.dataType=="script"&&s.cache==null)s.cache=false;if(s.cache===false&&s.type.toLowerCase()=="get")s.url+=(s.url.match(/\?/)?"&":"?")+"_="+(new Date()).getTime();if(s.data&&s.type.toLowerCase()=="get"){s.url+=(s.url.match(/\?/)?"&":"?")+s.data;s.data=null;}if(s.global&&!jQuery.active++)jQuery.event.trigger("ajaxStart");if(!s.url.indexOf("http")&&s.dataType=="script"){var head=document.getElementsByTagName("head")[0];var script=document.createElement("script");script.src=s.url;if(!jsonp&&(s.success||s.complete)){var done=false;script.onload=script.onreadystatechange=function(){if(!done&&(!this.readyState||this.readyState=="loaded"||this.readyState=="complete")){done=true;success();complete();head.removeChild(script);}};}head.appendChild(script);return;}var requestDone=false;var xml=window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP"):new XMLHttpRequest();xml.open(s.type,s.url,s.async);if(s.data)xml.setRequestHeader("Content-Type",s.contentType);if(s.ifModified)xml.setRequestHeader("If-Modified-Since",jQuery.lastModified[s.url]||"Thu, 01 Jan 1970 00:00:00 GMT");xml.setRequestHeader("X-Requested-With","XMLHttpRequest");if(s.beforeSend)s.beforeSend(xml);if(s.global)jQuery.event.trigger("ajaxSend",[xml,s]);var onreadystatechange=function(isTimeout){if(!requestDone&&xml&&(xml.readyState==4||isTimeout=="timeout")){requestDone=true;if(ival){clearInterval(ival);ival=null;}status=isTimeout=="timeout"&&"timeout"||!jQuery.httpSuccess(xml)&&"error"||s.ifModified&&jQuery.httpNotModified(xml,s.url)&&"notmodified"||"success";if(status=="success"){try{data=jQuery.httpData(xml,s.dataType);}catch(e){status="parsererror";}}if(status=="success"){var modRes;try{modRes=xml.getResponseHeader("Last-Modified");}catch(e){}if(s.ifModified&&modRes)jQuery.lastModified[s.url]=modRes;if(!jsonp)success();}else
jQuery.handleError(s,xml,status);complete();if(s.async)xml=null;}};if(s.async){var ival=setInterval(onreadystatechange,13);if(s.timeout>0)setTimeout(function(){if(xml){xml.abort();if(!requestDone)onreadystatechange("timeout");}},s.timeout);}try{xml.send(s.data);}catch(e){jQuery.handleError(s,xml,null,e);}if(!s.async)onreadystatechange();return xml;function success(){if(s.success)s.success(data,status);if(s.global)jQuery.event.trigger("ajaxSuccess",[xml,s]);}function complete(){if(s.complete)s.complete(xml,status);if(s.global)jQuery.event.trigger("ajaxComplete",[xml,s]);if(s.global&&!--jQuery.active)jQuery.event.trigger("ajaxStop");}},handleError:function(s,xml,status,e){if(s.error)s.error(xml,status,e);if(s.global)jQuery.event.trigger("ajaxError",[xml,s,e]);},active:0,httpSuccess:function(r){try{return!r.status&&location.protocol=="file:"||(r.status>=200&&r.status<300)||r.status==304||jQuery.browser.safari&&r.status==undefined;}catch(e){}return false;},httpNotModified:function(xml,url){try{var xmlRes=xml.getResponseHeader("Last-Modified");return xml.status==304||xmlRes==jQuery.lastModified[url]||jQuery.browser.safari&&xml.status==undefined;}catch(e){}return false;},httpData:function(r,type){var ct=r.getResponseHeader("content-type");var xml=type=="xml"||!type&&ct&&ct.indexOf("xml")>=0;var data=xml?r.responseXML:r.responseText;if(xml&&data.documentElement.tagName=="parsererror")throw"parsererror";if(type=="script")jQuery.globalEval(data);if(type=="json")data=eval("("+data+")");return data;},param:function(a){var s=[];if(a.constructor==Array||a.jquery)jQuery.each(a,function(){s.push(encodeURIComponent(this.name)+"="+encodeURIComponent(this.value));});else
for(var j in a)if(a[j]&&a[j].constructor==Array)jQuery.each(a[j],function(){s.push(encodeURIComponent(j)+"="+encodeURIComponent(this));});else
s.push(encodeURIComponent(j)+"="+encodeURIComponent(a[j]));return s.join("&").replace(/%20/g,"+");}});jQuery.fn.extend({show:function(speed,callback){return speed?this.animate({height:"show",width:"show",opacity:"show"},speed,callback):this.filter(":hidden").each(function(){this.style.display=this.oldblock?this.oldblock:"";if(jQuery.css(this,"display")=="none")this.style.display="block";}).end();},hide:function(speed,callback){return speed?this.animate({height:"hide",width:"hide",opacity:"hide"},speed,callback):this.filter(":visible").each(function(){this.oldblock=this.oldblock||jQuery.css(this,"display");if(this.oldblock=="none")this.oldblock="block";this.style.display="none";}).end();},_toggle:jQuery.fn.toggle,toggle:function(fn,fn2){return jQuery.isFunction(fn)&&jQuery.isFunction(fn2)?this._toggle(fn,fn2):fn?this.animate({height:"toggle",width:"toggle",opacity:"toggle"},fn,fn2):this.each(function(){jQuery(this)[jQuery(this).is(":hidden")?"show":"hide"]();});},slideDown:function(speed,callback){return this.animate({height:"show"},speed,callback);},slideUp:function(speed,callback){return this.animate({height:"hide"},speed,callback);},slideToggle:function(speed,callback){return this.animate({height:"toggle"},speed,callback);},fadeIn:function(speed,callback){return this.animate({opacity:"show"},speed,callback);},fadeOut:function(speed,callback){return this.animate({opacity:"hide"},speed,callback);},fadeTo:function(speed,to,callback){return this.animate({opacity:to},speed,callback);},animate:function(prop,speed,easing,callback){var opt=jQuery.speed(speed,easing,callback);return this[opt.queue===false?"each":"queue"](function(){opt=jQuery.extend({},opt);var hidden=jQuery(this).is(":hidden"),self=this;for(var p in prop){if(prop[p]=="hide"&&hidden||prop[p]=="show"&&!hidden)return jQuery.isFunction(opt.complete)&&opt.complete.apply(this);if(p=="height"||p=="width"){opt.display=jQuery.css(this,"display");opt.overflow=this.style.overflow;}}if(opt.overflow!=null)this.style.overflow="hidden";opt.curAnim=jQuery.extend({},prop);jQuery.each(prop,function(name,val){var e=new jQuery.fx(self,opt,name);if(/toggle|show|hide/.test(val))e[val=="toggle"?hidden?"show":"hide":val](prop);else{var parts=val.toString().match(/^([+-]=)?([\d+-.]+)(.*)$/),start=e.cur(true)||0;if(parts){var end=parseFloat(parts[2]),unit=parts[3]||"px";if(unit!="px"){self.style[name]=(end||1)+unit;start=((end||1)/e.cur(true))*start;self.style[name]=start+unit;}if(parts[1])end=((parts[1]=="-="?-1:1)*end)+start;e.custom(start,end,unit);}else
e.custom(start,val,"");}});return true;});},queue:function(type,fn){if(jQuery.isFunction(type)){fn=type;type="fx";}if(!type||(typeof type=="string"&&!fn))return queue(this[0],type);return this.each(function(){if(fn.constructor==Array)queue(this,type,fn);else{queue(this,type).push(fn);if(queue(this,type).length==1)fn.apply(this);}});},stop:function(){var timers=jQuery.timers;return this.each(function(){for(var i=0;i<timers.length;i++)if(timers[i].elem==this)timers.splice(i--,1);}).dequeue();}});var queue=function(elem,type,array){if(!elem)return;var q=jQuery.data(elem,type+"queue");if(!q||array)q=jQuery.data(elem,type+"queue",array?jQuery.makeArray(array):[]);return q;};jQuery.fn.dequeue=function(type){type=type||"fx";return this.each(function(){var q=queue(this,type);q.shift();if(q.length)q[0].apply(this);});};jQuery.extend({speed:function(speed,easing,fn){var opt=speed&&speed.constructor==Object?speed:{complete:fn||!fn&&easing||jQuery.isFunction(speed)&&speed,duration:speed,easing:fn&&easing||easing&&easing.constructor!=Function&&easing};opt.duration=(opt.duration&&opt.duration.constructor==Number?opt.duration:{slow:600,fast:200}[opt.duration])||400;opt.old=opt.complete;opt.complete=function(){jQuery(this).dequeue();if(jQuery.isFunction(opt.old))opt.old.apply(this);};return opt;},easing:{linear:function(p,n,firstNum,diff){return firstNum+diff*p;},swing:function(p,n,firstNum,diff){return((-Math.cos(p*Math.PI)/2)+0.5)*diff+firstNum;}},timers:[],fx:function(elem,options,prop){this.options=options;this.elem=elem;this.prop=prop;if(!options.orig)options.orig={};}});jQuery.fx.prototype={update:function(){if(this.options.step)this.options.step.apply(this.elem,[this.now,this]);(jQuery.fx.step[this.prop]||jQuery.fx.step._default)(this);if(this.prop=="height"||this.prop=="width")this.elem.style.display="block";},cur:function(force){if(this.elem[this.prop]!=null&&this.elem.style[this.prop]==null)return this.elem[this.prop];var r=parseFloat(jQuery.curCSS(this.elem,this.prop,force));return r&&r>-10000?r:parseFloat(jQuery.css(this.elem,this.prop))||0;},custom:function(from,to,unit){this.startTime=(new Date()).getTime();this.start=from;this.end=to;this.unit=unit||this.unit||"px";this.now=this.start;this.pos=this.state=0;this.update();var self=this;function t(){return self.step();}t.elem=this.elem;jQuery.timers.push(t);if(jQuery.timers.length==1){var timer=setInterval(function(){var timers=jQuery.timers;for(var i=0;i<timers.length;i++)if(!timers[i]())timers.splice(i--,1);if(!timers.length)clearInterval(timer);},13);}},show:function(){this.options.orig[this.prop]=jQuery.attr(this.elem.style,this.prop);this.options.show=true;this.custom(0,this.cur());if(this.prop=="width"||this.prop=="height")this.elem.style[this.prop]="1px";jQuery(this.elem).show();},hide:function(){this.options.orig[this.prop]=jQuery.attr(this.elem.style,this.prop);this.options.hide=true;this.custom(this.cur(),0);},step:function(){var t=(new Date()).getTime();if(t>this.options.duration+this.startTime){this.now=this.end;this.pos=this.state=1;this.update();this.options.curAnim[this.prop]=true;var done=true;for(var i in this.options.curAnim)if(this.options.curAnim[i]!==true)done=false;if(done){if(this.options.display!=null){this.elem.style.overflow=this.options.overflow;this.elem.style.display=this.options.display;if(jQuery.css(this.elem,"display")=="none")this.elem.style.display="block";}if(this.options.hide)this.elem.style.display="none";if(this.options.hide||this.options.show)for(var p in this.options.curAnim)jQuery.attr(this.elem.style,p,this.options.orig[p]);}if(done&&jQuery.isFunction(this.options.complete))this.options.complete.apply(this.elem);return false;}else{var n=t-this.startTime;this.state=n/this.options.duration;this.pos=jQuery.easing[this.options.easing||(jQuery.easing.swing?"swing":"linear")](this.state,n,0,1,this.options.duration);this.now=this.start+((this.end-this.start)*this.pos);this.update();}return true;}};jQuery.fx.step={scrollLeft:function(fx){fx.elem.scrollLeft=fx.now;},scrollTop:function(fx){fx.elem.scrollTop=fx.now;},opacity:function(fx){jQuery.attr(fx.elem.style,"opacity",fx.now);},_default:function(fx){fx.elem.style[fx.prop]=fx.now+fx.unit;}};jQuery.fn.offset=function(){var left=0,top=0,elem=this[0],results;if(elem)with(jQuery.browser){var absolute=jQuery.css(elem,"position")=="absolute",parent=elem.parentNode,offsetParent=elem.offsetParent,doc=elem.ownerDocument,safari2=safari&&parseInt(version)<522;if(elem.getBoundingClientRect){box=elem.getBoundingClientRect();add(box.left+Math.max(doc.documentElement.scrollLeft,doc.body.scrollLeft),box.top+Math.max(doc.documentElement.scrollTop,doc.body.scrollTop));if(msie){var border=jQuery("html").css("borderWidth");border=(border=="medium"||jQuery.boxModel&&parseInt(version)>=7)&&2||border;add(-border,-border);}}else{add(elem.offsetLeft,elem.offsetTop);while(offsetParent){add(offsetParent.offsetLeft,offsetParent.offsetTop);if(mozilla&&/^t[d|h]$/i.test(parent.tagName)||!safari2)border(offsetParent);if(safari2&&!absolute&&jQuery.css(offsetParent,"position")=="absolute")absolute=true;offsetParent=offsetParent.offsetParent;}while(parent.tagName&&!/^body|html$/i.test(parent.tagName)){if(!/^inline|table-row.*$/i.test(jQuery.css(parent,"display")))add(-parent.scrollLeft,-parent.scrollTop);if(mozilla&&jQuery.css(parent,"overflow")!="visible")border(parent);parent=parent.parentNode;}if(safari2&&absolute)add(-doc.body.offsetLeft,-doc.body.offsetTop);}results={top:top,left:left};}return results;function border(elem){add(jQuery.css(elem,"borderLeftWidth"),jQuery.css(elem,"borderTopWidth"));}function add(l,t){left+=parseInt(l)||0;top+=parseInt(t)||0;}};})();// UPGRADE: The following attribute helpers should now be used as:
// .attr("title") or .attr("title","new title")
jQuery.each(["id","title","name","href","src","rel"], function(i,n){
	jQuery.fn[ n ] = function(h) {
		return h == undefined ?
			this.length ? this[0][n] : null :
			this.attr( n, h );
	};
});

// UPGRADE: The following css helpers should now be used as:
// .css("top") or .css("top","30px")
jQuery.each("top,left,position,float,overflow,color,background".split(","), function(i,n){
	jQuery.fn[ n ] = function(h) {
		return h == undefined ?
			( this.length ? jQuery.css( this[0], n ) : null ) :
			this.css( n, h );
	};
});

// UPGRADE: The following event helpers should now be used as such:
// .oneblur(fn) -> .one("blur",fn)
// .unblur(fn) -> .unbind("blur",fn)
var e = ("blur,focus,load,resize,scroll,unload,click,dblclick," +
	"mousedown,mouseup,mousemove,mouseover,mouseout,change,reset,select," + 
	"submit,keydown,keypress,keyup,error").split(",");

// Go through all the event names, but make sure that
// it is enclosed properly
for ( var i = 0; i < e.length; i++ ) new function(){
			
	var o = e[i];
		
	// Handle event unbinding
	jQuery.fn["un"+o] = function(f){ return this.unbind(o, f); };
		
	// Finally, handle events that only fire once
	jQuery.fn["one"+o] = function(f){
		// save cloned reference to this
		var element = jQuery(this);
		var handler = function() {
			// unbind itself when executed
			element.unbind(o, handler);
			element = null;
			// apply original handler with the same arguments
			return f.apply(this, arguments);
		};
		return this.bind(o, handler);
	};
			
};

// UPGRADE: .ancestors() was removed in favor of .parents()
jQuery.fn.ancestors = jQuery.fn.parents;

// UPGRADE: The CSS selector :nth-child() now starts at 1, instead of 0
jQuery.expr[":"]["nth-child"] = "jQuery.nth(a.parentNode.firstChild,parseInt(m[3])+1,'nextSibling')==a";

// UPGRADE: .filter(["div", "span"]) now becomes .filter("div, span")
jQuery.fn._filter = jQuery.fn.filter;
jQuery.fn.filter = function(arr){
	return this._filter( arr.constructor == Array ? arr.join(",") : arr );
};
/*
 * ContextMenu - jQuery plugin for right-click context menus
 *
 * Author: Chris Domigan
 * Contributors: Dan G. Switzer, II
 * Parts of this plugin are inspired by Joern Zaefferer's Tooltip plugin
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Version: r2
 * Date: 16 July 2007
 *
 * For documentation visit http://www.trendskitchens.co.nz/jquery/contextmenu/
 *
 */
(function($){var menu,shadow,trigger,content,hash,currentTarget;var defaults={menuStyle:{listStyle:'none',padding:'1px',margin:'0px',backgroundColor:'#fff',border:'1px solid #999',width:'100px'},itemStyle:{margin:'0px',color:'#000',display:'block',cursor:'default',padding:'3px',border:'1px solid #fff',backgroundColor:'transparent'},itemHoverStyle:{border:'1px solid #0a246a',backgroundColor:'#b6bdd2'},eventPosX:'pageX',eventPosY:'pageY',shadow:true,onContextMenu:null,onShowMenu:null};$.fn.contextMenu=function(id,options){if(!menu){menu=$('<div id="jqContextMenu"></div>').hide().css({position:'absolute',zIndex:'500'}).appendTo('body').bind('click',function(e){e.stopPropagation()})}if(!shadow){shadow=$('<div></div>').css({backgroundColor:'#000',position:'absolute',opacity:0.2,zIndex:499}).appendTo('body').hide()}hash=hash||[];hash.push({id:id,menuStyle:$.extend({},defaults.menuStyle,options.menuStyle||{}),itemStyle:$.extend({},defaults.itemStyle,options.itemStyle||{}),itemHoverStyle:$.extend({},defaults.itemHoverStyle,options.itemHoverStyle||{}),bindings:options.bindings||{},shadow:options.shadow||options.shadow===false?options.shadow:defaults.shadow,onContextMenu:options.onContextMenu||defaults.onContextMenu,onShowMenu:options.onShowMenu||defaults.onShowMenu,eventPosX:options.eventPosX||defaults.eventPosX,eventPosY:options.eventPosY||defaults.eventPosY});var index=hash.length-1;$(this).bind('contextmenu',function(e){var bShowContext=(!!hash[index].onContextMenu)?hash[index].onContextMenu(e):true;if(bShowContext)display(index,this,e,options);return false});return this};function display(index,trigger,e,options){var cur=hash[index];content=$('#'+cur.id).find('ul:first').clone(true);content.css(cur.menuStyle).find('li').css(cur.itemStyle).hover(function(){$(this).css(cur.itemHoverStyle)},function(){$(this).css(cur.itemStyle)}).find('img').css({verticalAlign:'middle',paddingRight:'2px'});menu.html(content);if(!!cur.onShowMenu)menu=cur.onShowMenu(e,menu);$.each(cur.bindings,function(id,func){$('#'+id,menu).bind('click',function(e){hide();func(trigger,currentTarget)})});menu.css({'left':e[cur.eventPosX],'top':e[cur.eventPosY]}).show();if(cur.shadow)shadow.css({width:menu.width(),height:menu.height(),left:e.pageX+2,top:e.pageY+2}).show();$(document).one('click',hide)}function hide(){menu.hide();shadow.hide()}$.contextMenu={defaults:function(userDefaults){$.each(userDefaults,function(i,val){if(typeof val=='object'&&defaults[i]){$.extend(defaults[i],val)}else defaults[i]=val})}}})(jQuery);$(function(){$('div.contextMenu').hide()});/**
 * @cat Plugins/Dimensions
 * @author Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 */
 
 jQuery.fn.height=function(){if(this.get(0)==window)return self.innerHeight||jQuery.boxModel&&document.documentElement.clientHeight||document.body.clientHeight;if(this.get(0)==document)return Math.max(document.body.scrollHeight,document.body.offsetHeight);return this.css("height",arguments[0]);};jQuery.fn.width=function(){if(this.get(0)==window)return self.innerWidth||jQuery.boxModel&&document.documentElement.clientWidth||document.body.clientWidth;if(this.get(0)==document)return Math.max(document.body.scrollWidth,document.body.offsetWidth);return this.css("width",arguments[0]);};jQuery.fn.innerHeight=function(){return this.get(0)==window||this.get(0)==document?this.height():this.get(0).offsetHeight-parseInt(this.css("borderTop")||0)-parseInt(this.css("borderBottom")||0);};jQuery.fn.innerWidth=function(){return this.get(0)==window||this.get(0)==document?this.width():this.get(0).offsetWidth-parseInt(this.css("borderLeft")||0)-parseInt(this.css("borderRight")||0);};jQuery.fn.outerHeight=function(){return this.get(0)==window||this.get(0)==document?this.height():this.get(0).offsetHeight;};jQuery.fn.outerWidth=function(){return this.get(0)==window||this.get(0)==document?this.width():this.get(0).offsetWidth;};jQuery.fn.scrollLeft=function(){if(this.get(0)==window||this.get(0)==document)return self.pageXOffset||jQuery.boxModel&&document.documentElement.scrollLeft||document.body.scrollLeft;return this.get(0).scrollLeft;};jQuery.fn.scrollTop=function(){if(this.get(0)==window||this.get(0)==document)return self.pageYOffset||jQuery.boxModel&&document.documentElement.scrollTop||document.body.scrollTop;return this.get(0).scrollTop;};jQuery.fn.offset=function(refElem){if(!this[0])throw 'jQuery.fn.offset requires an element.';refElem=(refElem)?jQuery(refElem)[0]:null;var x=0,y=0,elem=this[0],parent=this[0],sl=0,st=0;do{if(parent.tagName=='BODY'||parent.tagName=='HTML'){if((jQuery.browser.safari||jQuery.browser.msie)&&jQuery.css(parent,'position')!='absolute'){x+=parseInt(jQuery.css(parent,'marginLeft'))||0;y+=parseInt(jQuery.css(parent,'marginTop'))||0;};break;};x+=parent.offsetLeft||0;y+=parent.offsetTop||0;if(jQuery.browser.mozilla||jQuery.browser.msie){x+=parseInt(jQuery.css(parent,'borderLeftWidth'))||0;y+=parseInt(jQuery.css(parent,'borderTopWidth'))||0;};if(jQuery.browser.mozilla&&jQuery.css(parent,'overflow')=='hidden'){x+=parseInt(jQuery.css(parent,'borderLeftWidth'))||0;y+=parseInt(jQuery.css(parent,'borderTopWidth'))||0;};var op=parent.offsetParent;do{sl+=parent.scrollLeft||0;st+=parent.scrollTop||0;parent=parent.parentNode;}while(parent !=op);}while(parent);if(refElem){var offset=jQuery(refElem).offset();x=x-offset.left;y=y-offset.top;sl=sl-offset.scrollLeft;st=st-offset.scrollTop;};if(jQuery.browser.safari||jQuery.browser.opera){x+=parseInt(jQuery.css(elem,'borderLeftWidth'))||0;y+=parseInt(jQuery.css(elem,'borderTopWidth'))||0;};return{top:y-st,left:x-sl,width:elem.offsetWidth,height:elem.offsetHeight,borderTop:parseInt(jQuery.css(elem,'borderTopWidth'))||0,borderLeft:parseInt(jQuery.css(elem,'borderLeftWidth'))||0,marginTop:parseInt(jQuery.css(elem,'marginTopWidth'))||0,marginLeft:parseInt(jQuery.css(elem,'marginLeftWidth'))||0,scrollTop:st,scrollLeft:sl,pageYOffset:window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0,pageXOffset:window.pageXOffset||document.documentElement.scrollLeft||document.body.scrollLeft||0};};/*
 * jqModal - Minimalist Modaling with jQuery
 *
 * Copyright (c) 2007 Brice Burgess <bhb@iceburg.net>, http://www.iceburg.net
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * $Version: 2007.02.07 +r6???
 */
(function($) {
$.fn.jqm=function(o,x,y){
var _o = {
zIndex: 3000,
overlay: 50,
overlayClass: 'jqmOverlay',
wrapClass: 'jqmWrap',
trigger: '.jqModal',
ajax: false,
target: false,
autofire: false,
focus: false
};
$.jqm.serial++; s = $.jqm.serial;
hash[s] = {c:$.extend(_o, o),active:false,w:this,o:false,u:$('input,select,button',this)[0],cb:[($.isFunction(x))?x:false,($.isFunction(y))?y:false]};
$(_o.trigger).bind("click",{'s':s},function(e) { hash[e.data.s]['t']=this;
	return (!hash[e.data.s]['active'])?$.jqm.open(e.data.s):false;});
if(_o.autofire) $(_o.trigger).click();
return this;
}
$.fn.jqmClose=function(){var p=this.parent();
	this.hide().insertBefore(p); p.remove(); return this;}
$.jqm = {
open:function(s){
	var h=hash[s];
	var c=h.c;
	var z=c.zIndex; if (c.focus) z+=10; if (c.overlay == 0) z-=5;
	if(!$.isFunction(h.q)) h.q=function(){return $.jqm.close(s)};
	h['active']=true;

	var f=$('<iframe></iframe>').css({'z-index':z-2,opacity:0});
	var o=$('<div></div>').css({'z-index':z-1,opacity:c.overlay/100}).addClass(c.overlayClass);
	$([f[0],o[0]]).css({height:$.jqm.pageHeight(),width:'100%',position:'absolute',left:0,top:0});

	if (c.focus) { if($.jqm.x.length == 0) $.jqm.ffunc('bind'); $.jqm.x.push(s);
		o=f.add(o[0]).css('cursor','wait');}
	else if (c.overlay > 0){o.bind('click', h.q); o=($.jqm.ie6)?f.add(o[0]):o;}
	else o=($.jqm.ie6)?f.css('height','100%').prependTo(h.w):false;
	if (o) h.o=o.appendTo('body');

	h.w.wrap('<div class="'+c.wrapClass+'" id="jqmID'+s+'" style="z-index:'+z+'"></div>');
	if (c.ajax) { var t = (c.target) ?
		(typeof(c.target) == 'string')?$(c.target,h.w):$(c.target):h.w;
		c.ajax=(c.ajax.substr(0,1) == '@')?$(h.t).attr(c.ajax.substring(1)):c.ajax;
		t.load(c.ajax, function() {$('.jqmClose',h.w).bind('click',h.q);}); }
	else h.w.find('.jqmClose').bind('click',h.q);

	(h.cb[0])?h.cb[0](h):h.w.show(); h.u.focus();
	return false;
},
close:function(s){var h=hash[s]; h['active'] = false;
	$('.jqmClose',h.w[0]).unbind('click',h.q);
	var x=$.jqm.x; if(x.length != 0) { x.pop(); 
		if (x.length == 0) $.jqm.ffunc('unbind'); }
	(h.cb[1])?h.cb[1](h):h.w.jqmClose(); if(h.o) h.o.remove();
	return false;
},
pageHeight:function(){var d=document.documentElement;
	return Math.max(document.body.scrollHeight,d.offsetHeight,d.clientHeight || 0,window.innerHeight || 0);
},
hash: {},
serial: 0,
x: [],
f:function(e) { var s=$.jqm.x[$.jqm.x.length-1];
	if($(e.target).parents('#jqmID'+s).length == 0) { hash[s].u.focus(); return false;} return true;
},
ffunc:function(t) {$()[t]("keypress",$.jqm.f)[t]("keydown",$.jqm.f)[t]("mousedown",$.jqm.f);},
ie6:$.browser.msie && typeof XMLHttpRequest == 'function'
};
var hash=$.jqm.hash;
})(jQuery);

/*
 * jqDnR - Minimalistic Drag'n'Resize for jQuery.
 *
 * Copyright (c) 2007 Brice Burgess <bhb@iceburg.net>, http://www.iceburg.net
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * $Version: 2007.02.09 +r1
 */
(function($){
$.fn.jqDrag=function(r){$.jqDnR.init(this,r,'d'); return this;}
$.fn.jqResize=function(r){$.jqDnR.init(this,r,'r'); return this;}
$.jqDnR={
init:function(w,r,t){ r=(r)?$(r,w):w;
	r.bind('mousedown',{w:w,t:t},function(e){ var h=e.data; var w=h.w;
	hash=$.extend({oX:f(w,'left'),oY:f(w,'top'),oW:f(w,'width'),oH:f(w,'height'),pX:e.pageX,pY:e.pageY},h);
	//hash=$.extend({oX:f(w,'left'),oY:f(w,'top'),oW:f(w,'width'),oH:f(w,'height'),pX:e.pageX,pY:e.pageY,o:w.css('opacity')},h);
    //h.w.css('opacity',0.95);    uncommented because of problems with IE6 and treeview
    $().mousemove($.jqDnR.drag).mouseup($.jqDnR.stop);
	return false;});
},
drag:function(e) {var h=hash; var w=h.w[0];
    if(h.t == 'd') h.w.css({left:h.oX + e.pageX - h.pX,top:h.oY + e.pageY - h.pY}); 
	else h.w.css({width:Math.max(e.pageX - h.pX + h.oW,0),height:Math.max(e.pageY - h.pY + h.oH,0)});
    adaptDWin(h.w);
	return false;},
stop:function(){var j=$.jqDnR; hash.w.css('opacity',hash.o); $().unbind('mousemove',j.drag).unbind('mouseup',j.stop);},
h:false};
var hash=$.jqDnR.h;
var f=function(w,t){return parseInt(w.css(t)) || 0};
})(jQuery);
/**
 * Return integer values of top, left, width, height
 */
 
jQuery.fn.itop = function() {
    return parseInt(this.top());
};

jQuery.fn.ileft = function() {
    return parseInt(this.left());
};

jQuery.fn.iwidth = function() {
    return parseInt(this.width());
};

jQuery.fn.iheight = function() {
    return parseInt(this.height());
};


/**
 * Set visibility of jQuery object to 'hidden' or 'visible'
 */
jQuery.fn.showv = function() {
    this.css('visibility', 'visible');
};

jQuery.fn.hidev = function() {
    this.css('visibility', 'hidden');
};


/**
 * Set checked status of checkboxes globablly
 */
jQuery.fn.check = function(mode) {
    var mode = mode || 'on'; // if mode is undefined, use 'on' as default
    return this.each(function() {
        switch(mode) {
        case 'on':
            this.checked = true;
            break;
        case 'off':
            this.checked = false;
            break;
        case 'toggle':
            this.checked = !this.checked;
            break;
        }
    });
};


/**
 * Swap image src
 */
jQuery.fn.imgSwap = function(from, to) {
    this.src(this.src().replace(from,to));
    return this;
};


/**
 * Default method for AJAX requests 
 */
$.ajaxSetup( { type: "POST", data:"dummy=dummy" } )
//$.ajaxSetup( { type: "POST" } )


/**
 * Create jqDnR Dialog (jquery.jqmodal_full.js)
 */
function createDnRDlg(win, size, container, title, url) {
    var dlg = '<div style="height: 100%">';
    dlg += '<div id="' + container + '_TC" class="jqmdTC dragHandle">' + title + '</div>';
    dlg += '<div id="' + container + '_MSG" class="jqmdMSG"></div>';
    dlg += '<div id="' + container + '_BC" class="jqmdBC" ';
    if (size.resizeable) {
        dlg += '><img src="templates/dialog/resize.gif" alt="resize" class="resizeHandle" />';
    } else {
        dlg += 'style="height:0px; border:none">';
    }
    dlg += '</div>';
    dlg += '<input type="image" src="templates/dialog/close.gif" onclick="$(this).parent().parent().hide(); $(\'#' + container + '_MSG\').html(\'\')" class="jqmdClose jqmClose" />';
    dlg += '</div>';

    var dynwin = $('#' + container);
    dynwin.html(dlg)
          .jqm({autofire: false, overlay: 0})
          .jqDrag('div.dragHandle');
          
        
    if (size.newsize) dynwin.height(win.h).width(win.w);
    if (win.l) dynwin.css({left:win.l, top:win.t});
    if (size.resizeable) dynwin.jqResize('img.resizeHandle');
    if (url) $('#' + container + '_MSG').load(url);
    
    dynwin.show();
    adaptDWin(dynwin);
}


function adaptDWin(container) {
    var cn = container.id();
    var newMSGh = parseInt($('#' + cn).css('height')) - parseInt($('#' + cn +'_TC').css('height')) - parseInt($('#' + cn + '_BC').css('height')); 
    $('#' + cn + '_MSG').css({height: newMSGh});
}
	
	/*
	 *	jquery.suggest 1.1 - 2007-08-06
	 *	
	 *	Uses code and techniques from following libraries:
	 *	1. http://www.dyve.net/jquery/?autocomplete
	 *	2. http://dev.jquery.com/browser/trunk/plugins/interface/iautocompleter.js	
	 *
	 *	All the new stuff written by Peter Vulgaris (www.vulgarisoip.com)	
	 *	Feel free to do whatever you want with this file
	 *
	 */
	var cache = [];
	(function($) {

		$.suggest = function(input, options) {
	
			// ------ added for p.mapper -----------
            // check for existing <ul> for suggest results
            if ($('ul.'+options.resultsClass).is('ul')) {
                var $results = $('ul.'+options.resultsClass);
            } else {
                var $results = $(document.createElement("ul"));
                $results.addClass(options.resultsClass).appendTo('body');
            }
            
            var $input = $(input).attr("autocomplete", "off");

			var timeout = false;		// hold timeout ID for suggestion results to appear	
			var prevLength = 0;			// last recorded length of $input.val()
			//var cache = [];				// cache MRU list
			var cacheSize = 0;			// size of cache in chars (bytes?)
			

			resetPosition();
			$(window)
				.load(resetPosition)		
				.resize(resetPosition);

			$input.blur(function() {
                setTimeout(function() { clearResults() }, 200);
			});
            
            // --- for p.mapper: empty on double click ---
            $input.dblclick( function() { $input.val('')} );
			
			
			// help IE users if possible
			try {
				$results.bgiframe();
			} catch(e) { }


			// I really hate browser detection, but I don't see any other way
			if ($.browser.mozilla) {
				$input.keypress(processKey);	// onkeypress repeats arrow keys in Mozilla/Opera
			} else {
				$input.keydown(processKey);		// onkeydown repeats arrow keys in IE/Safari
			}



			function resetPosition() {
				// requires jquery.dimension plugin
				var offset = $input.offset();
				$results.css({
					top: (offset.top + input.offsetHeight) + 'px',
					left: offset.left + 'px'
				});
			};
			
			
			function processKey(e) {
				
				// handling up/down/escape requires results to be visible
				// handling enter/tab requires that AND a result to be selected
				if ((/27$|38$|40$/.test(e.keyCode) && $results.is(':visible')) ||
					(/^13$|^9$/.test(e.keyCode) && getCurrentResult())) {
		            
		            if (e.preventDefault)
		                e.preventDefault();
					if (e.stopPropagation)
		                e.stopPropagation();

					e.cancelBubble = true;
					e.returnValue = false;
				
					switch(e.keyCode) {
	
						case 38: // up
							prevResult();
							break;
				
						case 40: // down
							nextResult();
							break;
	
						case 9:  // tab
						case 13: // return
							selectCurrentResult();
							break;
							
						case 27: //	escape
							$results.hide();
							break;
	
					}
					
				} else if ($input.val().length != prevLength) {

					if (timeout) 
						clearTimeout(timeout);
					timeout = setTimeout(suggest, options.delay);
					prevLength = $input.val().length;
					
				}			
					
				
			};
			
			
			function suggest() {
                        
				var q = $.trim($input.val());

				if (q.length >= options.minchars) {
					
					cached = checkCache(q);
					
					if (cached) {

						displayItems(cached['items']);
						
					} else {
                        // -------- mofified for p.mapper ---------------------------
						// $.get(options.source, {q: q}, function(txt) {
                        var suggesturl = options.source + '&q=' + q;
                        if (options.addToUrl) suggesturl += '&' + eval(options.addToUrl);
                        
                        $.get(suggesturl, {}, function(txt) {

							$results.hide();
							
							var items = parseTxt(txt, q);
							
							displayItems(items);
							addToCache(q, items, txt.length);
							
						});
						
					}
					
				} else {
				
					$results.hide();
					
				}
					
			};
			
			
			function checkCache(q) {

				for (var i = 0; i < cache.length; i++)
					if (cache[i]['q'] == q) {
						cache.unshift(cache.splice(i, 1)[0]);
						return cache[0];
					}
				
				return false;
			
			};
			
			function addToCache(q, items, size) {

				while (cache.length && (cacheSize + size > options.maxCacheSize)) {
					var cached = cache.pop();
					cacheSize -= cached['size'];
				}
				
				cache.push({
					q: q,
					size: size,
					items: items
					});
					
				cacheSize += size;
			
			};
			
			function displayItems(items) {
				
				if (!items)
					return;
					
				if (!items.length) {
					$results.hide();
					return;
				}
				
				var html = '';
				for (var i = 0; i < items.length; i++)
					html += '<li>' + items[i] + '</li>';

				$results.html(html).show();
				
				$results
					.children('li')
					.mouseover(function() {
						$results.children('li').removeClass(options.selectClass);
						$(this).addClass(options.selectClass);
					})
					.click(function(e) {
						e.preventDefault(); 
						e.stopPropagation();
						selectCurrentResult();
					});
							
			};
			
			function parseTxt(txt, q) {
				
				var items = [];
				var tokens = txt.split(options.delimiter);
				
				// parse returned data for non-empty items
				for (var i = 0; i < tokens.length; i++) {
					var token = $.trim(tokens[i]);
					if (token) {
						token = token.replace(
							new RegExp(q, 'ig'), 
							function(q) { return '<span class="' + options.matchClass + '">' + q + '</span>' }
							);
						items[items.length] = token;
					}
				}
				
				return items;
			};
			
			function getCurrentResult() {
			
				if (!$results.is(':visible'))
					return false;
			
				var $currentResult = $results.children('li.' + options.selectClass);
				
				if (!$currentResult.length)
					$currentResult = false;
					
				return $currentResult;

			};
			
			function selectCurrentResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult) {
					$input.val($currentResult.text());
					$results.hide();
					
					if (options.onSelect)
						options.onSelect.apply($input[0]);
						
				}
			
			};
			
			function nextResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult) {
					$currentResult
						.removeClass(options.selectClass)
						.next()
							.addClass(options.selectClass);
				} else {
					$results.children('li:first-child').addClass(options.selectClass);
                }
			
			};
			
			function prevResult() {
			
				$currentResult = getCurrentResult();
			
				if ($currentResult) {
					$currentResult
						.removeClass(options.selectClass)
						.prev()
							.addClass(options.selectClass);
				} else {
					$results.children('li:last-child').addClass(options.selectClass);
                }
			
			};
            
            // ------ added for p.mapper -----------
            function clearResults() {
                $results.hide();
                $('ul.'+options.resultsClass).html('');
            };
	
		};
		
		$.fn.suggest = function(source, options) {
		
			if (!source)
				return;
		
			options = options || {};
			options.source = source;
			options.delay = options.delay || 100;
			options.resultsClass = options.resultsClass || 'ac_results';
			options.selectClass = options.selectClass || 'ac_over';
			options.matchClass = options.matchClass || 'ac_match';
			options.minchars = options.minchars || 2;
			options.delimiter = options.delimiter || '\n';
			options.onSelect = options.onSelect || false;
			options.maxCacheSize = options.maxCacheSize || 65536;
            options.onSuggest = options.onSuggest || false;
	
			this.each(function() {
				new $.suggest(this, options);
			});
	
			return this;
			
		};
        
        $.clearSuggestCache = function() {
            //alert(this.cache.size);
            //this.cache = [];
        }
		
	})(jQuery);
	
/*
 * Treeview 1.3 - jQuery plugin to hide and show branches of a tree
 * 
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 *
 * Copyright (c) 2006 Jörn Zaefferer, Myles Angell
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.treeview.js 3506 2007-10-02 18:15:21Z joern.zaefferer $
 *
 */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(4($){7 k={U:"U",O:"O",s:"s",w:"w",u:"u",t:"t",q:"q",C:"C"};$.13($.I,{G:4(b,c){6 3.v(4(){7 a=$(3);5($.L.9(3,b))a.o(b).l(c);11 5($.L.9(3,c))a.o(c).l(b)})},y:4(b,c){6 3.v(4(){7 a=$(3);5($.L.9(3,b))a.o(b).l(c)})},Z:4(a){a=a||"Y";6 3.Y(4(){$(3).l(a)},4(){$(3).o(a)})},X:4(b,a){b?3.W({1f:"n"},b,a):3.v(4(){V(3)[V(3).1c(":R")?"Q":"z"]();5(a)a.B(3,P)})},19:4(b,a){5(b){3.W({1f:"z"},b,a)}11{3.z();5(a)3.v(a)}},N:4(a){3.p(":q-1t").l(k.q);3.p((a.1q?"":"."+k.O)+":K(."+k.U+")").m(">8").z();6 3.p(":9(>8)")},S:4(b,c){3.p(":9(>8):K(:9(>a))").m(">1m").A(4(a){5(3==a.1j){c.B($(3).10())}}).r($("a",3)).Z();3.p(":9(>8:R)").l(k.s).y(k.q,k.t);3.K(":9(>8:R)").l(k.w).y(k.q,k.u);3.1i("<H 1h=\\""+k.C+"\\"/>").m("H."+k.C).A(c)},x:4(g){g=$.13({},g);5(g.r){6 3.1g("r",[g.r])}5(g.n){7 d=g.n;g.n=4(){6 d.B($(3).D()[0],P)}}4 1e(b,c){4 F(a){6 4(){E.B($("H."+k.C,b).p(4(){6 a?$(3).D("."+a).1d:1D}));6 1C}}$(":T(0)",c).A(F(k.w));$(":T(1)",c).A(F(k.s));$(":T(2)",c).A(F())}4 E(){$(3).D().G(k.w,k.s).G(k.u,k.t).m(">8").X(g.1b,g.n);5(g.1A){$(3).D().1z().y(k.w,k.s).y(k.u,k.t).m(">8").19(g.1b,g.n)}}4 1a(){4 1y(a){6 a?1:0}7 b=[];j.v(4(i,e){b[i]=$(e).1c(":9(>8:1x)")?1:0});$.J("x",b.1w(""))}4 18(){7 b=$.J("x");5(b){7 a=b.1v("");j.v(4(i,e){$(e).m(">8")[1u(a[i])?"Q":"z"]()})}}3.l("x");7 j=3.m("M").N(g);1s(g.1r){17"J":7 h=g.n;g.n=4(){1a();5(h){h.B(3,P)}};18();16;17"15":7 f=3.m("a").p(4(){6 3.14==15.14});5(f.1d){f.l("1p").1o("8, M").r(f.10()).Q()}16}j.S(g,E);5(g.12)1e(3,g.12);6 3.1n("r",4(a,b){$(b).1l().o(k.q).o(k.u).o(k.t);$(b).m("M").1B().N(g).S(g,E)})}});$.I.1k=$.I.x})(V);',62,102,'|||this|function|if|return|var|ul|has||||||||||||addClass|find|toggle|removeClass|filter|last|add|expandable|lastExpandable|lastCollapsable|each|collapsable|treeview|replaceClass|hide|click|apply|hitarea|parent|toggler|handler|swapClass|div|fn|cookie|not|className|li|prepareBranches|closed|arguments|show|hidden|applyClasses|eq|open|jQuery|animate|heightToggle|hover|hoverClass|next|else|control|extend|href|location|break|case|deserialize|heightHide|serialize|animated|is|length|treeController|height|trigger|class|prepend|target|Treeview|prev|span|bind|parents|selected|collapsed|persist|switch|child|parseInt|split|join|visible|binary|siblings|unique|andSelf|false|true'.split('|'),0,{}))