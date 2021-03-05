(function(app){app.events.on('app:init',function(){app.plugins.register('FilterSharing',['layout','view'],{predefinedFilters:null,isDashboard:function(){return this.name==='dashboard'||(this.type==='record'&&this.module==='Dashboards');},isFilteredListViewDashlet:function(dashlet){return dashlet.view&&dashlet.view.type==='dashablelist'&&dashlet.view.filter_id&&this.isCustomFilter(dashlet.view.filter_id);},getMetadataFilterIds:function(){var allFilters=_.flatten(_.compact(_.map(app.metadata.getModules(),function(module){return module.filters&&module.filters.basic&&module.filters.basic.meta&&module.filters.basic.meta.filters;})));this.predefinedFilters=_.unique(_.pluck(allFilters,'id'));return this.predefinedFilters;},isCustomFilter:function(filterId){var filters=this.predefinedFilters?this.predefinedFilters:this.getMetadataFilterIds();return!_.contains(filters,filterId);},getListViewFilterIds:function(){var ids=[];var metadata=this.model.get('metadata');if(metadata&&metadata.components){_.each(metadata.components,function(section){_.each(section.rows,function(row){_.each(row,function(dashlet){if(this.isFilteredListViewDashlet(dashlet)){ids.push(dashlet.view.filter_id);}},this);},this);},this);}
return ids;},getPrivateFilter:function(filterData){var filterModel=app.data.createBean('Filters',filterData);var filterTeams=filterModel.get('team_name');var globalTeam=_.findWhere(filterTeams,{id:'1'});filterModel.set('team_name',_.without(filterTeams,globalTeam));return filterModel;},updateListViewFilters:function(filterUsersData,filter){var filterModel=this.getPrivateFilter(filter);var isFilterTeamsUpdated=this.updateFilterTeams(filterModel,filterUsersData.dashboardTeams);if(isFilterTeamsUpdated){filterModel.save();}},updateFilterTeams:function(filterModel,dashboardTeams){var filterTeams=filterModel.get('team_name');var hasNewFilterTeam=_.some(filterTeams,function(team){return!_.findWhere(dashboardTeams,{id:team.id});});var hasNewDashboardTeam=_.some(dashboardTeams,function(team){return!_.findWhere(filterTeams,{id:team.id});});var isFilterTeamsChanged=hasNewDashboardTeam||hasNewFilterTeam;if(isFilterTeamsChanged){filterModel.set('team_name',dashboardTeams);}
return isFilterTeamsChanged;},triggerListviewFilterUpdate:function(){if(this.isDashboard()){var filterIds=this.getListViewFilterIds();var dashboardTeams=this.model.get('team_name');var assignedUserId=this.model.get('assigned_user_id');_.each(filterIds,function(filterId){var url=app.api.buildURL('Filters/'+filterId,null,null);var filterUsersData={assignedUserId:assignedUserId,dashboardTeams:dashboardTeams};app.api.call('GET',url,null,{success:_.bind(this.updateListViewFilters,this,filterUsersData),error:function(){app.logger.error('Filter can not be read, thus is not shared. Filter id: '+filterId);}});},this);}}});});})(SUGAR.App);