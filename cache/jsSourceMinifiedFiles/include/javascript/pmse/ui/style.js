var PMSE=PMSE||{};PMSE.Style=function(options){this.cssProperties=null;this.cssClasses=null;this.belongsTo=null;PMSE.Style.prototype.initObject.call(this,options);};PMSE.Style.prototype.type='PMSE.Style';PMSE.Style.MAX_ZINDEX=100;PMSE.Style.prototype.initObject=function(options){var defaults={cssClasses:[],cssProperties:{},belongsTo:null};$.extend(true,defaults,options);this.cssClasses=defaults.cssClasses;this.cssProperties=defaults.cssProperties;this.belongsTo=defaults.belongsTo;};PMSE.Style.prototype.applyStyle=function(){if(!this.belongsTo.html){throw new Error("applyStyle(): can't apply style to an"+" object with no html");}
var i,class_i;$(this.belongsTo.html).css(this.cssProperties);for(i=0;i<this.cssClasses.length;i+=1){class_i=this.cssClasses[i];if(!$(this.belongsTo.html).hasClass(class_i)){$(this.belongsTo.html).addClass(class_i);}}
return this;};PMSE.Style.prototype.addProperties=function(properties){$.extend(true,this.cssProperties,properties);$(this.belongsTo.html).css(properties);return this;};PMSE.Style.prototype.getProperty=function(property){return this.cssProperties[property]||$(this.belongsTo.html).css(property)||window.getComputedStyle(this.belongsTo.html,null).getPropertyValue(property);};PMSE.Style.prototype.removeProperties=function(properties){var property,i;for(i=0;i<properties.length;i+=1){property=properties[i];if(this.cssProperties.hasOwnProperty(property)){$(this.belongsTo.html).css(property,"");delete this.cssProperties[property];}}
return this;};PMSE.Style.prototype.addClasses=function(cssClasses){var i,cssClass;if(cssClasses&&cssClasses instanceof Array){for(i=0;i<cssClasses.length;i+=1){cssClass=cssClasses[i];if(typeof cssClass==="string"){if(this.cssClasses.indexOf(cssClass)===-1){this.cssClasses.push(cssClass);$(this.belongsTo.html).addClass(cssClass);}}else{throw new Error("addClasses(): array element is not of type string");}}}else{throw new Error("addClasses(): parameter must be of type Array");}
return this;};PMSE.Style.prototype.removeClasses=function(cssClasses){var i,index,cssClass;if(cssClasses&&cssClasses instanceof Array){for(i=0;i<cssClasses.length;i+=1){cssClass=cssClasses[i];if(typeof cssClass==="string"){index=this.cssClasses.indexOf(cssClass);if(index!==-1){$(this.belongsTo.html).removeClass(this.cssClasses[index]);this.cssClasses.splice(index,1);}}else{throw new Error("removeClasses(): array element is not of "+"type string");}}}else{throw new Error("removeClasses(): parameter must be of type Array");}
return this;};PMSE.Style.prototype.removeAllClasses=function(){this.cssClasses=[];$(this.belongsTo.html).removeClass();return this;};PMSE.Style.prototype.containsClass=function(cssClass){return this.cssClasses.indexOf(cssClass)!==-1;};PMSE.Style.prototype.getClasses=function(){return this.cssClasses;};PMSE.Style.prototype.stringify=function(){return{cssClasses:this.cssClasses};};