(function(app){app.events.on('app:init',function(){app.plugins.register('ListRemoveLinks',['view'],{onAttach:function(component,plugin){var removeLinks=function(){component.$('a:not(.rowaction, .dropdown-toggle, .dropdown-menu *)').contents().unwrap();};component.on('render',removeLinks,null,component);app.events.on('list:preview:decorate',removeLinks,this);},onDetach:function(){app.events.off('list:preview:decorate',null,this);}});});})(SUGAR.App);