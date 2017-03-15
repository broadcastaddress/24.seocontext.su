(function(){
	if (!BX.Tasks)
		BX.Tasks = {};

	if (BX.Tasks.ListControlsNS)
		return;

	BX.Tasks.ListControlsNS = {
		ready : false,
		params: {
			appendUrlParams: {
				SW_FF: 'FOR'
			}
		},
		init  : function() {
			this.ready = true;
		},
		menu : {
			menus : {},
			create : function(menuId){
				this.menus[menuId] = {
					items : []
				}
			},
			show : function(menuId, anchor, parameters)
			{
				if ( ! self.ready )
					return;

				if ( ! this.menus[menuId] )
					return;

				if ( ! this.menus[menuId].items.length )
					return;

				if(typeof parameters == 'undefined')
					parameters = {};

				var anchorPos = BX.pos(anchor);

				var items = BX.clone(this.menus[menuId].items);

				var params = [];
				if(parameters.useAppendParams)
				{
					for(var i in self.params.appendUrlParams)
					{
						params.push(i+'='+self.params.appendUrlParams[i]);
					}

					for(var k = 0; k < items.length; k++)
					{
						items[k].href = items[k].href+'&'+params.join('&');
					}
				}

				BX.PopupMenu.show(
					'task-top-panel-menu' + menuId + params.join('_'),
					anchor,
					items,
					{
						autoHide : true,
						//"offsetLeft": -1 * anchorPos["width"],
						"offsetTop": 4,
						"events":
						{
							"onPopupClose" : function(ind){
							}
						}
					}
				);
			},
			addItem : function (menuId, title, className, href)
			{
				this.menus[menuId].items.push({
					text      : title,
					className : className,
					href      : href
				});
			},
			addDelimiter : function(menuId)
			{
				this.menus[menuId].items.push({
					delimiter : true
				});
			}
		}
	}

	var self = BX.Tasks.ListControlsNS;
})();
