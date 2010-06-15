/*
 * jQuery Animate To Class
 * Copyright 2008 Igor Frias Vieira
 * http://igorvieira.com/blog/animate-to-class/
 *
 * Modified by Adam Ballai 
 *  - Added support for direct selector interface and animation toggling
 *
 * Released under the MIT and GPL licenses.
 */

(function($)
{
	$.fn.extend({
		animateToClass : function(to, duration, easing, callback)
		{
			if(!to){ return this; }
			
			var styles = selectStyle(to);
			if(!styles) return this;
			
			$(this).data('animated', true);
			return this.animate(styles, duration, easing, callback);
		},
		toggleAnimateToClass : function(to, from, duration, easing, callback)
		{
			var klass = to;
			if($(this).data('animated'))
			{
				$(this).data('animated', false);
				klass = from;
			}
			else
			{
				$(this).data('animated', true);
			}

			var styles = selectStyle(klass);
			if(!styles) return this;

			return this.animate(styles, duration, easing, callback);
		}
	});
	
	function selectStyle(sel)
	{
		for(var cont = 0; cont < document.styleSheets.length; cont++)
		{
			v = document.styleSheets[cont];
			attrClass = selectAttr(sel, v);
			if(attrClass != false)
			{
				break;	
			}

		}

		if(!attrClass)
		{
			attrClass = Array();
		}
		
		objStyle = {}
		
		if(attrClass == "")
		{
			return false;
		}
		
		if(attrClass.match(";"))
		{
			attrClass = attrClass.split(";");
		}
		else
		{
			attrClass = [attrClass];
		}
		
		$(attrClass).each(function(i,v){
			if(v != ""){
				v = v.split(":");
				v[0] = toCamelCase(v[0]);
				
				objStyle[v[0]] = $.trim(v[1]);	
				
			}
		});
		return objStyle;
	}
	
	function selectAttr(sel, v)
	{
		attrClass = false;	
			
		if($.browser.msie)
		{
			if(v.rules.length > 0)
			{
				$(v.rules).each(function(i2,v2){
					if(sel == v2.selectorText)
					{
						attrClass = v2.style.cssText;
					}
				});
			}
			else if(v.imports.length > 0)
			{
				$(v.imports).each(function(i2,v2){
										   
					if(sel == v2.selectorText)
					{
						attrClass = v2.style.cssText;
					}
					else if(v2 == "[object]" || v2 == "[Object CSSStyleSheet]" || v2 == "[object CSSImportRule]")
					{
						return selectAttr(sel, v2);
					}
				});
			}
		}
		else
		{
			$(v.cssRules).each(function(i2,v2){
				if(sel == v2.selectorText)
				{
					attrClass = v2.style.cssText;
				}
				else if(v2 == "[object CSSImportRule]")
				{
					return selectAttr(sel, v2.styleSheet);
				}
			});
		}
		
		return attrClass;
	}
	
	function toCamelCase(str)
	{

		str = $.trim(str);
		str = str.replace(/-/g, " ");
		str = str.toLowerCase();
		
		strArr = str.split(" ");
		
		var nStr = "";
		$(strArr).each(function(i,v){
			if(i == 0){
				nStr += v;
			}else{
				/*
				v = v.split("");
				v[0] = v[0].toUpperCase();
				nStr += v.join();
				*/
				
				//There was a bug in older version, this correction was sugested by Simon Shepard.
				nStr += v.substr(0,1).toUpperCase();
				nStr += v.substr(1,v.length);
			}
		});
			
		return nStr;
	}
})(jQuery);