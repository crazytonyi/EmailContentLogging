var CommandConnectionCondition=function(receiver,condition){jCore.Command.call(this,receiver);this.before=null;this.after=null;CommandConnectionCondition.prototype.initObject.call(this,condition);};CommandConnectionCondition.prototype=new jCore.Command();CommandConnectionCondition.prototype.type="CommandConnectionCondition";CommandConnectionCondition.prototype.initObject=function(condition){condition=((typeof condition)==='string')&&!_.isEmpty(condition)?condition.trim():'';this.before={condition:this.receiver.getFlowCondition(),type:this.receiver.flo_type};this.after={condition:condition,type:condition?"CONDITIONAL":"SEQUENCE"};};CommandConnectionCondition.prototype.updateConditionMarker=function(){if(this.receiver.getFlowCondition()&&this.receiver.getSrcPort().parent.type!=='AdamGateway'){this.receiver.setFlowType('CONDITIONAL');this.receiver.changeFlowType('conditional');}else{this.receiver.setFlowType('SEQUENCE');this.receiver.changeFlowType('sequence');}};CommandConnectionCondition.prototype.fireTrigger=function(undo){var fields,v,n;fields=[{field:'condition',oldVal:undo?this.after.condition:this.before.condition,newVal:this.receiver.getFlowCondition()}];v=undo?this.after.type:this.before.type;n=this.receiver.getFlowType();if(n!==v){fields.push({field:'type',oldVal:v,newVal:n});}
this.receiver.canvas.triggerConnectionConditionChangeEvent(this.receiver,fields);};CommandConnectionCondition.prototype.execute=function(){this.receiver.setFlowCondition(this.after.condition);this.updateConditionMarker();this.fireTrigger();};CommandConnectionCondition.prototype.undo=function(){this.receiver.setFlowCondition(this.before.condition);this.updateConditionMarker();this.fireTrigger(true);};CommandConnectionCondition.prototype.redo=function(){this.execute();};