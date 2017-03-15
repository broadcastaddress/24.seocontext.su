if(typeof(BX.CrmInterfaceGridManager) == 'undefined')
{
	BX.CrmInterfaceGridManager = function()
	{
		this._id = '';
		this._settings = {};
		this._messages = {};
		this._enableIterativeDeletion = false;
		this._toolbarMenu = null;
		this._applyButtonClickHandler = BX.delegate(this._handleFormApplyButtonClick, this);
		this._setFilterFieldsHandler = BX.delegate(this._onSetFilterFields, this);
		this._getFilterFieldsHandler = BX.delegate(this._onGetFilterFields, this);
		this._deletionProcessDialog = null;
	};

	BX.CrmInterfaceGridManager.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};

			this._makeBindings();
			BX.ready(BX.delegate(this._bindOnGridReload, this));

			BX.addCustomEvent(
				window,
				"CrmInterfaceToolbarMenuShow",
				BX.delegate(this._onToolbarMenuShow, this)
			);
			BX.addCustomEvent(
				window,
				"CrmInterfaceToolbarMenuClose",
				BX.delegate(this._onToolbarMenuClose, this)
			);

			BX.addCustomEvent(
				window,
				"BXInterfaceGridCheckColumn",
				BX.delegate(this._onGridColumnCheck, this)
			);

			this._messages = this.getSetting("messages", {});

			this._enableIterativeDeletion = !!this.getSetting("enableIterativeDeletion", false);
			if(this._enableIterativeDeletion)
			{
				BX.addCustomEvent(
					window,
					"BXInterfaceGridDeleteRow",
					BX.delegate(this._onGridRowDelete, this)
				);
			}
		},
		_onGridColumnCheck: function(sender, eventArgs)
		{
			if(this._toolbarMenu)
			{
				eventArgs["columnMenu"] = this._toolbarMenu.GetMenuByItemId(eventArgs["targetElement"].id);
			}
		},
		_onGridRowDelete: function(sender, eventArgs)
		{
			var gridId = BX.type.isNotEmptyString(eventArgs["gridId"]) ? eventArgs["gridId"] : "";
			if(gridId === "" || gridId !== this.getGridId())
			{
				return;
			}

			eventArgs["cancel"] = true;
			BX.defer(BX.delegate(this.openDeletionDialog, this))(
				{
					gridId: gridId,
					ids: eventArgs["selectedIds"],
					processAll: eventArgs["forAll"]
				}
			);
		},
		_onToolbarMenuShow: function(sender, eventArgs)
		{
			this._toolbarMenu = eventArgs["menu"];
			eventArgs["items"] = this.getGridJsObject().settingsMenu;
		},
		_onToolbarMenuClose: function(sender, eventArgs)
		{
			if(eventArgs["menu"] === this._toolbarMenu)
			{
				this._toolbarMenu = null;
				this.getGridJsObject().SaveColumns();
			}
		},
		getId: function()
		{
			return this._id;
		},
		reinitialize: function()
		{
			this._makeBindings();
			BX.onCustomEvent(window, 'BXInterfaceGridManagerReinitialize', [this]);
		},
		_makeBindings: function()
		{
			var form = this.getForm();
			if(form)
			{
				BX.unbind(form['apply'], 'click', this._applyButtonClickHandler);
				BX.bind(form['apply'], 'click', this._applyButtonClickHandler);
			}

			BX.ready(BX.delegate(this._bindOnSetFilterFields, this));
		},
		_bindOnGridReload: function()
		{
			BX.addCustomEvent(
				window,
				'BXInterfaceGridAfterReload',
				BX.delegate(this._makeBindings, this)
			);
		},
		_bindOnSetFilterFields: function()
		{
			var grid = this.getGridJsObject();

			BX.removeCustomEvent(grid, 'AFTER_SET_FILTER_FIELDS', this._setFilterFieldsHandler);
			BX.addCustomEvent(grid, 'AFTER_SET_FILTER_FIELDS', this._setFilterFieldsHandler);

			BX.removeCustomEvent(grid, 'AFTER_GET_FILTER_FIELDS', this._getFilterFieldsHandler);
			BX.addCustomEvent(grid, 'AFTER_GET_FILTER_FIELDS', this._getFilterFieldsHandler);
		},
		registerFilter: function(filter)
		{
			BX.addCustomEvent(
				filter,
				'AFTER_SET_FILTER_FIELDS',
				BX.delegate(this._onSetFilterFields, this)
			);

			BX.addCustomEvent(
				filter,
				'AFTER_GET_FILTER_FIELDS',
				BX.delegate(this._onGetFilterFields, this)
			);
		},
		_onSetFilterFields: function(sender, form, fields)
		{
			var infos = this.getSetting('filterFields', null);
			if(!BX.type.isArray(infos))
			{
				return;
			}

			var isSettingsContext = form.name.indexOf('flt_settings') === 0;

			var count = infos.length;
			var element = null;
			var paramName = '';
			for(var i = 0; i < count; i++)
			{
				var info = infos[i];
				var id = BX.type.isNotEmptyString(info['id']) ? info['id'] : '';
				var type = BX.type.isNotEmptyString(info['typeName']) ? info['typeName'].toUpperCase() : '';
				var params = info['params'] ? info['params'] : {};

				if(type === 'USER')
				{
					var data = params['data'] ? params['data'] : {};
					this._setElementByFilter(
						data[isSettingsContext ? 'settingsElementId' : 'elementId'],
						data['paramName'],
						fields
					);

					var search = params['search'] ? params['search'] : {};
					this._setElementByFilter(
						search[isSettingsContext ? 'settingsElementId' : 'elementId'],
						search['paramName'],
						fields
					);
				}
			}
		},
		_setElementByFilter: function(elementId, paramName, filter)
		{
			var element = BX.type.isNotEmptyString(elementId) ? BX(elementId) : null;
			if(BX.type.isElementNode(element))
			{
				element.value = BX.type.isNotEmptyString(paramName) && filter[paramName] ? filter[paramName] : '';
			}
		},
		_onGetFilterFields: function(sender, form, fields)
		{
			var infos = this.getSetting('filterFields', null);
			if(!BX.type.isArray(infos))
			{
				return;
			}

			var isSettingsContext = form.name.indexOf('flt_settings') === 0;
			var count = infos.length;
			for(var i = 0; i < count; i++)
			{
				var info = infos[i];
				var id = BX.type.isNotEmptyString(info['id']) ? info['id'] : '';
				var type = BX.type.isNotEmptyString(info['typeName']) ? info['typeName'].toUpperCase() : '';
				var params = info['params'] ? info['params'] : {};

				if(type === 'USER')
				{
					var data = params['data'] ? params['data'] : {};
					this._setFilterByElement(
						data[isSettingsContext ? 'settingsElementId' : 'elementId'],
						data['paramName'],
						fields
					);

					var search = params['search'] ? params['search'] : {};
					this._setFilterByElement(
						search[isSettingsContext ? 'settingsElementId' : 'elementId'],
						search['paramName'],
						fields
					);
				}
			}
		},
		_setFilterByElement: function(elementId, paramName, filter)
		{
			var element = BX.type.isNotEmptyString(elementId) ? BX(elementId) : null;
			if(BX.type.isElementNode(element) && BX.type.isNotEmptyString(paramName))
			{
				filter[paramName] = element.value;
			}
		},
		getSetting: function (name, defaultval)
		{
			return typeof(this._settings[name]) != 'undefined' ? this._settings[name] : defaultval;
		},
		getMessage: function(name)
		{
			return this._messages.hasOwnProperty(name) ? this._messages[name] : name;
		},
		getOwnerType: function()
		{
			return this.getSetting('ownerType', '');
		},
		getForm: function()
		{
			return document.forms[this.getSetting('formName', '')];
		},
		getGridId: function()
		{
			return this.getSetting('gridId', '');
		},
		getGrid: function()
		{
			return BX(this.getSetting('gridId', ''));
		},
		getGridJsObject: function()
		{
			var gridId = this.getSetting('gridId', '');
			return BX.type.isNotEmptyString(gridId) ? window['bxGrid_' + gridId] : null;
		},
		getAllRowsCheckBox: function()
		{
			return BX(this.getSetting('allRowsCheckBoxId', ''));
		},
		getEditor: function()
		{
			var editorId = this.getSetting('activityEditorId', '');
			return BX.CrmActivityEditor.items[editorId] ? BX.CrmActivityEditor.items[editorId] : null;
		},
		reload: function()
		{
			var gridId = this.getSetting("gridId");
			if(!BX.type.isNotEmptyString(gridId))
			{
				return false;
			}

			var grid = window['bxGrid_' + gridId];
			if(!grid || !BX.type.isFunction(grid.Reload))
			{
				return false;
			}
			grid.Reload();
			return true;
		},
		getServiceUrl: function()
		{
			return this.getSetting('serviceUrl', '/bitrix/components/bitrix/crm.activity.editor/ajax.php');
		},
		getListServiceUrl: function()
		{
			return this.getSetting('listServiceUrl', '');
		},
		_loadCommunications: function(commType, ids, callback)
		{
			BX.ajax(
				{
					'url': this.getServiceUrl(),
					'method': 'POST',
					'dataType': 'json',
					'data':
					{
						'ACTION' : 'GET_ENTITIES_DEFAULT_COMMUNICATIONS',
						'COMMUNICATION_TYPE': commType,
						'ENTITY_TYPE': this.getOwnerType(),
						'ENTITY_IDS': ids,
						'GRID_ID': this.getSetting('gridId', '')
					},
					onsuccess: function(data)
					{
						if(data && data['DATA'] && callback)
						{
							callback(data['DATA']);
						}
					},
					onfailure: function(data)
					{
					}
				}
			);
		},
		_onEmailDataLoaded: function(data)
		{
			var settings = {};
			if(data)
			{
				var items = BX.type.isArray(data['ITEMS']) ? data['ITEMS'] : [];
				if(items.length > 0)
				{
					var entityType = data['ENTITY_TYPE'] ? data['ENTITY_TYPE'] : '';
					var comms = settings['communications'] = [];
					for(var i = 0; i < items.length; i++)
					{
						var item = items[i];
						comms.push(
							{
								'type': 'EMAIL',
								'entityTitle': '',
								'entityType': entityType,
								'entityId': item['entityId'],
								'value': item['value']
							}
						);
					}
				}
			}

			this.addEmail(settings);
		},
		_onCallDataLoaded: function(data)
		{
			var settings = {};
			if(data)
			{
				var items = BX.type.isArray(data['ITEMS']) ? data['ITEMS'] : [];
				if(items.length > 0)
				{
					var entityType = data['ENTITY_TYPE'] ? data['ENTITY_TYPE'] : '';
					var comms = settings['communications'] = [];
					var item = items[0];
					comms.push(
						{
							'type': 'PHONE',
							'entityTitle': '',
							'entityType': entityType,
							'entityId': item['entityId'],
							'value': item['value']
						}
					);
					settings['ownerType'] = entityType;
					settings['ownerID'] = item['entityId'];
				}
			}

			this.addCall(settings);
		},
		_onMeetingDataLoaded: function(data)
		{
			var settings = {};
			if(data)
			{
				var items = BX.type.isArray(data['ITEMS']) ? data['ITEMS'] : [];
				if(items.length > 0)
				{
					var entityType = data['ENTITY_TYPE'] ? data['ENTITY_TYPE'] : '';
					var comms = settings['communications'] = [];
					var item = items[0];
					comms.push(
						{
							'type': '',
							'entityTitle': '',
							'entityType': entityType,
							'entityId': item['entityId'],
							'value': item['value']
						}
					);
					settings['ownerType'] = entityType;
					settings['ownerID'] = item['entityId'];
				}
			}

			this.addMeeting(settings);
		},
		_onDeletionProcessStateChange: function(sender)
		{
			if(sender !== this._deletionProcessDialog || sender.getState() !== BX.CrmLongRunningProcessState.completed)
			{
				return;
			}

			this._deletionProcessDialog.close();
			this.reload();
		},
		_handleFormApplyButtonClick: function(e)
		{
			var form = this.getForm();
			if(!form)
			{
				return true;
			}

			var selected = form.elements['action_button_' + this.getSetting('gridId', '')];
			if(!selected)
			{
				return;
			}
			
			var value = selected.value;
			if (value === 'subscribe')
			{
				var allRowsCheckBox = this.getAllRowsCheckBox();
				var ids = [];
				if(!(allRowsCheckBox && allRowsCheckBox.checked))
				{
					var checkboxes = BX.findChildren(
						this.getGrid(),
						{
							'tagName': 'INPUT',
							'attribute': { 'type': 'checkbox' }
						},
						true
					);

					if(checkboxes)
					{
						for(var i = 0; i < checkboxes.length; i++)
						{
							var checkbox = checkboxes[i];
							if(checkbox.id.indexOf('ID') == 0 && checkbox.checked)
							{
								ids.push(checkbox.value);
							}
						}
					}
				}
				this._loadCommunications('EMAIL', ids, BX.delegate(this._onEmailDataLoaded, this));
				return BX.PreventDefault(e);
			}

			return true;
		},
		openDeletionDialog: function(params)
		{
			var contextId = BX.util.getRandomString(12);
			var processParams =
			{
				"CONTEXT_ID" : contextId,
				"GRID_ID": params["gridId"],
				"ENTITY_TYPE_NAME": this.getOwnerType(),
				"USER_FILTER_HASH": this.getSetting("userFilterHash", "")
			};

			var processAll = params["processAll"];
			var ids = params["ids"];
			if(processAll)
			{
				processParams["PROCESS_ALL"] = "Y";
			}
			else
			{
				processParams["ENTITY_IDS"] = ids;
			}

			this._deletionProcessDialog = BX.CrmLongRunningProcessDialog.create(
				contextId,
				{
					serviceUrl: this.getListServiceUrl(),
					action: "DELETE",
					params: processParams,
					title: this.getMessage("deletionDialogTitle"),
					summary: this.getMessage("deletionDialogSummary")
				}
			);
			BX.addCustomEvent(
				this._deletionProcessDialog,
				"ON_STATE_CHANGE",
				BX.delegate(this._onDeletionProcessStateChange, this)
			);
			this._deletionProcessDialog.show();
			this._deletionProcessDialog.start();
		},
		addEmail: function(settings)
		{
			var editor = this.getEditor();
			if(!editor)
			{
				return;
			}

			settings = settings ? settings : {};
			if(typeof(settings['ownerID']) !== 'undefined')
			{
				settings['ownerType'] = this.getOwnerType();
			}

			editor.addEmail(settings);
		},
		addCall: function(settings)
		{
			var editor = this.getEditor();
			if(!editor)
			{
				return;
			}

			settings = settings ? settings : {};
			if(typeof(settings['ownerID']) !== 'undefined')
			{
				settings['ownerType'] = this.getOwnerType();
			}
			//TODO: temporary
			BX.namespace('BX.Crm.Activity');
			if(typeof BX.Crm.Activity.Planner !== 'undefined')
			{
				(new BX.Crm.Activity.Planner()).showEdit({
					TYPE_ID: BX.CrmActivityType.call,
					OWNER_TYPE: settings['ownerType'],
					OWNER_ID: settings['ownerID']
				});
				return;
			}

			editor.addCall(settings);
		},
		addMeeting: function(settings)
		{
			var editor = this.getEditor();
			if(!editor)
			{
				return;
			}

			settings = settings ? settings : {};
			if(typeof(settings['ownerID']) !== 'undefined')
			{
				settings['ownerType'] = this.getOwnerType();
			}
			//TODO: temporary
			BX.namespace('BX.Crm.Activity');
			if(typeof BX.Crm.Activity.Planner !== 'undefined')
			{
				(new BX.Crm.Activity.Planner()).showEdit({
					TYPE_ID: BX.CrmActivityType.meeting,
					OWNER_TYPE: settings['ownerType'],
					OWNER_ID: settings['ownerID']
				});
				return;
			}

			editor.addMeeting(settings);
		},
		addTask: function(settings)
		{
			var editor = this.getEditor();
			if(!editor)
			{
				return;
			}

			settings = settings ? settings : {};
			if(typeof(settings['ownerID']) !== 'undefined')
			{
				settings['ownerType'] = this.getOwnerType();
			}

			editor.addTask(settings);
		},
		viewActivity: function(id, optopns)
		{
			var editor = this.getEditor();
			if(editor)
			{
				editor.viewActivity(id, optopns);
			}
		}
	};

	BX.CrmInterfaceGridManager.items = {};
	BX.CrmInterfaceGridManager.create = function(id, settings)
	{
		var self = new BX.CrmInterfaceGridManager();
		self.initialize(id, settings);
		this.items[id] = self;

		BX.onCustomEvent(
			this,
			'CREATED',
			[self]
		);

		return self;
	};
	BX.CrmInterfaceGridManager.addEmail = function(managerId, settings)
	{
		if(typeof(this.items[managerId]) !== 'undefined')
		{
			this.items[managerId].addEmail(settings);
		}
	};
	BX.CrmInterfaceGridManager.addCall = function(managerId, settings)
	{
		if(typeof(this.items[managerId]) !== 'undefined')
		{
			this.items[managerId].addCall(settings);
		}
	};
	BX.CrmInterfaceGridManager.addMeeting = function(managerId, settings)
	{
		if(typeof(this.items[managerId]) !== 'undefined')
		{
			this.items[managerId].addMeeting(settings);
		}
	};
	BX.CrmInterfaceGridManager.addTask = function(managerId, settings)
	{
		if(typeof(this.items[managerId]) !== 'undefined')
		{
			this.items[managerId].addTask(settings);
		}
	};
	BX.CrmInterfaceGridManager.viewActivity = function(managerId, id, optopns)
	{
		if(typeof(this.items[managerId]) !== 'undefined')
		{
			this.items[managerId].viewActivity(id, optopns);
		}
	};
	BX.CrmInterfaceGridManager.showPopup = function(id, anchor, items)
	{
		BX.PopupMenu.show(
			id,
			anchor,
			items,
			{
				offsetTop:0,
				offsetLeft:-30
			});
	};
	BX.CrmInterfaceGridManager.reloadGrid = function(gridId)
	{
		var grid = window['bxGrid_' + gridId];
		if(!grid || !BX.type.isFunction(grid.Reload))
		{
			return false;
		}
		grid.Reload();
		return true;
	};
	BX.CrmInterfaceGridManager.applyFilter = function(gridId, filterName)
	{
		var grid = window['bxGrid_' + gridId];
		if(!grid || !BX.type.isFunction(grid.Reload))
		{
			return false;
		}

		grid.ApplyFilter(filterName);
		return true;
	};
	BX.CrmInterfaceGridManager.clearFilter = function(gridId)
	{
		var grid = window['bxGrid_' + gridId];
		if(!grid || !BX.type.isFunction(grid.ClearFilter))
		{
			return false;
		}

		grid.ClearFilter();
		return true;
	};
	BX.CrmInterfaceGridManager.menus = {};
	BX.CrmInterfaceGridManager.createMenu = function(menuId, items, zIndex)
	{
		zIndex = parseInt(zIndex);
		var menu = new PopupMenu(menuId, !isNaN(zIndex) ? zIndex : 1010);
		if(BX.type.isArray(items))
		{
			menu.settingsMenu = items;
		}
		this.menus[menuId] = menu;
	};
	BX.CrmInterfaceGridManager.showMenu = function(menuId, anchor)
	{
		var menu = this.menus[menuId];
		if(typeof(menu) !== 'undefined')
		{
			menu.ShowMenu(anchor, menu.settingsMenu, false, false);
		}
	};
	BX.CrmInterfaceGridManager.expandEllipsis = function(ellepsis)
	{
		if(!BX.type.isDomNode(ellepsis))
		{
			return false;
		}

	    var cut = BX.findNextSibling(ellepsis, { 'class': 'bx-crm-text-cut-on' });
		if(cut)
		{
			BX.removeClass(cut, 'bx-crm-text-cut-on');
			BX.addClass(cut, 'bx-crm-text-cut-off');
			cut.style.display = '';
		}

		ellepsis.style.display = 'none';
		return true;
	};
}

//region BX.CrmUIGridExtension
//Created for BX.Main.grid
if(typeof(BX.CrmUIGridExtension) == "undefined")
{
	BX.CrmUIGridExtension = function()
	{
		this._id = "";
		this._settings = {};
	};
	BX.CrmUIGridExtension.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function (name, defaultval)
		{
			return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
		},
		getOwnerTypeName: function()
		{
			return this.getSetting('ownerTypeName', '');
		},
		getActivityEditor: function()
		{
			var editorId = this.getSetting("activityEditorId", "");
			return BX.CrmActivityEditor.items[editorId] ? BX.CrmActivityEditor.items[editorId] : null;
		},
		createActivity: function(typeId, settings)
		{
			BX.namespace("BX.Crm.Activity");

			typeId = parseInt(typeId);
			if(isNaN(typeId))
			{
				typeId = BX.CrmActivityType.undefined;
			}

			settings = settings ? settings : {};
			if(BX.type.isNumber(settings["ownerID"]))
			{
				settings["ownerType"] = this.getOwnerTypeName();
			}

			if(typeId === BX.CrmActivityType.call || typeId === BX.CrmActivityType.meeting)
			{
				if(typeof BX.Crm.Activity.Planner !== "undefined")
				{
					var planner = new BX.Crm.Activity.Planner();
					planner.showEdit(
						{
							TYPE_ID: typeId,
							OWNER_TYPE: settings["ownerType"],
							OWNER_ID: settings["ownerID"]
						}
					);
				}
			}
			else
			{
				var editor = this.getActivityEditor();
				if(editor)
				{
					if(typeId === BX.CrmActivityType.email)
					{
						editor.addEmail(settings);
					}
					else if(typeId === BX.CrmActivityType.task)
					{
						editor.addTask(settings);
					}
				}
			}
		},
		viewActivity: function(id, optopns)
		{
			var editor = this.getActivityEditor();
			if(editor)
			{
				editor.viewActivity(id, optopns);
			}
		}
	};
	//region Activity
	BX.CrmUIGridExtension.createActivity = function(extensionId, typeId, settings)
	{
		if(this.items.hasOwnProperty(extensionId))
		{
			this.items[extensionId].createActivity(typeId, settings);
		}
	};
	BX.CrmUIGridExtension.viewActivity = function(extensionId, activityId, options)
	{
		if(this.items.hasOwnProperty(extensionId))
		{
			this.items[extensionId].viewActivity(activityId, options);
		}
	};
	//endregion
	//region Context Menu
	BX.CrmUIGridExtension.contextMenus = {};
	BX.CrmUIGridExtension.createContextMenu = function(menuId, items, zIndex)
	{
		zIndex = parseInt(zIndex);
		var menu = new PopupMenu(menuId, !isNaN(zIndex) ? zIndex : 1010);
		if(BX.type.isArray(items))
		{
			menu.settingsMenu = items;
		}
		this.contextMenus[menuId] = menu;
	};
	BX.CrmUIGridExtension.showContextMenu = function(menuId, anchor)
	{
		if(this.contextMenus.hasOwnProperty(menuId))
		{
			var menu = this.contextMenus[menuId];
			menu.ShowMenu(anchor, menu.settingsMenu, false, false);
		}
	};
	//endregion
	//region Constructor & Items
	BX.CrmUIGridExtension.items = {};
	BX.CrmUIGridExtension.create = function(id, settings)
	{
		var self = new BX.CrmUIGridExtension();
		self.initialize(id, settings);
		this.items[id] = self;
		//BX.onCustomEvent(this, 'CREATED', [self]);
		return self;
	};
	//endregion
}
//endregion