var PMSE=PMSE||{};PMSE.Validator=function(options,parent){PMSE.Base.call(this,options);this.parent=null;this.criteria=null;this.validated=false;this.valid=null;this.errorMessage=null;PMSE.Validator.prototype.initObject.call(this,options,parent);};PMSE.Validator.prototype=new PMSE.Base();PMSE.Validator.prototype.type='PMSE.Validator';PMSE.Validator.prototype.family='PMSE.Validator';PMSE.Validator.prototype.initObject=function(options,parent){var defaults={criteria:null,errorMessage:'the validation has failed'};$.extend(true,defaults,options);this.setCriteria(defaults.criteria).setParent(parent).setErrorMessage(defaults.errorMessage);};PMSE.Validator.prototype.setErrorMessage=function(errorMessage){this.errorMessage=errorMessage;return this;};PMSE.Validator.prototype.getErrorMessage=function(){return this.errorMessage;};PMSE.Validator.prototype.setCriteria=function(criteria){this.criteria=criteria;return this;};PMSE.Validator.prototype.setParent=function(parent){this.parent=parent;return this;};PMSE.Validator.prototype.validate=function(){this.valid=true;};PMSE.Validator.prototype.isValid=function(){this.validate();return this.valid;};