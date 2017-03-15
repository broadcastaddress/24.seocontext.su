//BX.CrmKanbanHelper.getInstance().counterTotalPrice();
if (typeof(BX.CrmKanbanHelper) === 'undefined')
{
	BX.CrmKanbanHelper = function(settings)
	{
		this._error = '';
		this._popup_name = 'kanban_dialog';
		this._delayStack = settings.DELAY_BETWEEN_LOADING || 5;//seconds
		this._type = settings.ENTITY_TYPE || '';
		this._ajaxPath = settings.AJAX_PATH || '/bitrix/components/bitrix/crm.kanban/ajax.php';
		this._data = settings.DATA || {};
		this._data.container = settings.CONTAINER || document.body;
		this._currency = settings.CURRENCY || '';
		this._extra = settings.EXTRA || [];
		this._more_fields = settings.MORE_FIELDS || [];
		this._more_fields_new = null;
		this._popup = null;
		this._entityId = 0;
		this.settings = {
			path_column_edit: settings.PATH_COLUMN_EDIT || '/crm/configs/status/',
			show_activity: settings.SHOW_ACTIVITY === 'Y',
			access_config_perms: settings.ACCESS_CONFIG_PERMS === 'Y'
		};

		this._flushStack();

		BX.addCustomEvent('onPullEvent-im', BX.proxy(this._pullEventHandler, this));
		BX.addCustomEvent('onPullEvent-crm', BX.proxy(this._pullEventHandler, this));
		BX.addCustomEvent('onCrmActivityTodoChecked', BX.proxy(this._activityCheckHandler, this));
		BX.addCustomEvent('onPopupClose', BX.proxy(this._onClosePopup, this));
	};

	BX.CrmKanbanHelper.prototype =
	{
		getEntityType: function()
		{
			return this._type;
		},
		getKanban: function()
		{
			return this._kanban;
		},
		getMoreFields: function()
		{
			if (this._more_fields_new === null)
			{
				this._more_fields_new = [];
				for (var i=0; i<this._more_fields.length; i++)
				{
					if (this._more_fields[i].new === 1)
					{
						this._more_fields_new.push(this._more_fields[i]);
					}
				}
			}
			return this._more_fields_new;
		},
		getSettings: function(code)
		{
			return this.settings[code];
		},
		openActivityItems: function(id)
		{
			this._entityId = id;
			this._showPopup(
							'',
							{onAfterPopupShow: BX.proxy(this._loadActivities, this)}
					);
		},
		moveState: function(id, newState, oldState, params)
		{
			var oldColumnItems = this._kanban.getColumn(oldState).items;
			var params = params || {};
			if (oldColumnItems.length > 1)
			{
				params.old_status_lastid = oldColumnItems[oldColumnItems.length-1].id;
			}
			this._loadJSON({
				entity_id: id,
				prev_entity_id: this._kanban.prevItem(id, newState),
				status: newState,
				status_params: params || {},
				action: 'status'
			});
		},
		loadPage: function(column, page)
		{
			this._loadJSON({
				column: column,
				page: page,
				action: 'page'
			});
		},
		_showPopup: function(title, events)
		{
			if (true || this._popup === null)
			{
				this._popup = new BX.PopupWindow(this._popup_name, BX.proxy_context, {
					closeIcon : false,
					autoHide: true,
					overlay: false,
					className: 'crm-kanban-popup-plan',
					autoHide: true,
					closeByEsc : true,
					contentColor: 'white',
					angle: true,
					offsetLeft: 15,
					events: events
				});
			}
			var popupPreLoader =  "<div class=\"crm-kanban-user-loader-item\"><div class=\"crm-kanban-loader\"><svg class=\"crm-kanban-circular\" viewBox=\"25 25 50 50\"><circle class=\"crm-kanban-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-width=\"1\" stroke-miterlimit=\"10\"/></svg></div></div>";
			this._popup.setContent(popupPreLoader);
			this._popup.show();
		},
		_onClosePopup: function(popup, event)
		{
			if (popup.uniquePopupId === this._popup_name)
			{
				BX.cleanNode(popup.contentContainer);
			}
		},
		_flushStackTimeout: function()
		{
			if (this._stack.length > 0)
			{
				this._loadStack();
			}
			else
			{
				this._flushStack();
			}
		},
		_flushStack: function()
		{
			this._startStack = Date.now()/1000;
			this._stack = [];
			if (0 < this._delayStack)
			{
				setTimeout(BX.proxy(this._flushStackTimeout, this), this._delayStack * 1000);
			}
		},
		_loadActivities: function()
		{
			var _this = this;
			var params = {
				entity_id: this._entityId,
				entity_type: this._type,
				action: 'activities'
			};
			BX.ajax.get(this._ajaxPath, params, function(data) {
					_this._popup.setContent(data);
					_this._popup.adjustPosition();
				});
		},
		_loadStack: function()
		{
			var stack = this._stack;
			this._flushStack();
			this._loadJSON({
				entity_id: stack,
				action: 'get'
			});
		},
		_loadJSON: function(params)
		{
			var _this = this;
			params.entity_type = this._type;
			params.sessid = BX.bitrix_sessid();
			params.extra = this._extra;
			BX.ajax.loadJSON(this._ajaxPath, params, function(data){
				if (typeof data !== 'undefined')
				{
					var error = data.error || [];
					var items = data.items || [];
					var columns = data.columns || [];

					if (error.length > 0)
					{
						alert(error + "\n" + BX.message('CRM_KANBAN_RELOAD_PAGE'));
					}
					else
					{
						for (var i=0; i < items.length; i++)
						{
							var itemExist = _this._kanban.getItem(items[i].id);
							if (!itemExist)
							{
								_this._kanban.addItem(items[i]);
							}
							if (typeof params.page === 'undefined' && (typeof params.status_params === 'undefined' || typeof params.status_params.old_status_lastid === 'undefined'))
							{
								var columnItems = _this._kanban.getColumn(items[i].columnId).items;
								_this._kanban.moveItemToColumShow(items[i].id, items[i].columnId, columnItems.length > 0 ? columnItems[0].id : null, items[i].modifyByAvatar);
							}
						}
						_this._kanban.counterTotalPrice(columns);
					}
				}
			});
		},
		_pullEventHandler: function(command, params)
		{
			if (command === 'activity_add' &&
				params.OWNER_TYPE_NAME === this._type &&
				params.COMPLETED !== 'Y'
			)
			{
				BX.CrmKanbanItem.changeActCount(params.OWNER_ID, 1);
			}

			if (command === 'kanban_add' || command === 'kanban_update')
			{
				var item = params;
				var itemExist = this._kanban.getItem(item.id);
				var column = this._kanban.getColumn(item.columnId);
				var columnItems = column.items;
				var beforeItem = columnItems.length > 0 ? columnItems[0].id : null;
				var columns = this._kanban.columns;
				var newColumns = [];

				if (!itemExist)
				{
					item.columnColor = column.color;
					this._kanban.addItem(item, beforeItem);
				}
				else if (itemExist.columnId === item.columnId)
				{
					return;
				}

				for (var key in columns)
				{
					var count = parseInt(columns[key].count);
					var total = parseFloat(columns[key].total);
					if (itemExist && itemExist.columnId === key)
					{
						count--;
						total = total - parseFloat(item.price);
					}
					else if (item.columnId === key)
					{
						count++;
						total = total + parseFloat(item.price);
					}
					columns[key].count = count;
					columns[key].total = total;
					columns[key].total_format = BX.Currency.currencyFormat(total, this._currency, true);
					newColumns.push({
						id: columns[key].id,
						price: columns[key].price,
						columnId: key,
						count: count,
						total: total,
						total_format: columns[key].total_format
					});
				}

				this._kanban.moveItemToColumShow(item.id, item.columnId, beforeItem, item.modifyByAvatar);
				this._kanban.counterTotalPrice(newColumns);
			}

			/*if (command === 'notify' && typeof params.original_tag !== 'undefined')
			{
				var re = params.original_tag.match(new RegExp('CRM\\|' + this._type + '_(PROGRESS|RESPONSIBLE)\\|([\\d]+)', 'i'));
				if (re)
				{
					this._stack.push(re[2]);
				}
				if (0 >= this._delayStack)
				{
					if (this._stack.length > 0 && Date.now()/1000 - this._startStack >= this._delayStack)
					{
						this._loadStack();
					}
				}
			}*/
		},
		_activityCheckHandler: function(activityId, ownerId, ownerTypeId)
		{
			BX.CrmKanbanItem.changeActCount(ownerId, -1);
		}
	};
	BX.CrmKanbanHelper.instance = null;
	BX.CrmKanbanHelper.create = function(settings)
	{
		if (BX.CrmKanbanHelper.instance === null)
		{
			var instance = new BX.CrmKanbanHelper(settings);
			BX.CrmKanbanHelper.instance = instance;
			instance._kanban = new BX.CrmKanbanGrid(instance._data);
			instance._kanban.draw();
		}
		return BX.CrmKanbanHelper.instance;
	};
	BX.CrmKanbanHelper.getInstance = function()
	{
		return BX.CrmKanbanHelper.instance;
	};
}

if (typeof(BX.CrmKanbanGrid) === 'undefined')
{
	BX.CrmKanbanGrid = function(options)
	{
		this.columns = Object.create(null);
		this.columns_sort = {};
		this.items = Object.create(null);
		this.dropzones = Object.create(null);
		this.container = options.container;
		this.dragger = new BX.CrmKanbanDragDrop(this);
		this.loadData(options);
		this.kanban = null;

		this.scrollKanban();
	};

	BX.CrmKanbanGrid.prototype =
	{
		scrollKanban: function ()
		{
			this.kanban = kanban;

			var kanbanWidth = this.kanban.parentNode.offsetWidth;
			var kanbanOffsetTop = this.kanban.getBoundingClientRect().top;
			var kanbanHeight = document.documentElement.clientHeight - kanbanOffsetTop;
			var kanbanParentOffsetTop = this.kanban.parentNode.getBoundingClientRect().top;
			var kanbanOffsetLeft = this.kanban.parentNode.getBoundingClientRect().left;
			var footer = document.getElementById('footer');
			var footerHeight = footer.offsetHeight;
			var footerRectTop = footer.getBoundingClientRect().top;
			var offsetfooter = document.documentElement.clientHeight - footerRectTop;
			var kanbanWrapper = getComputedStyle(document.body.querySelector('.workarea-content-paddings'));
			var kanbanWrapperPadding = +kanbanWrapper.paddingBottom.slice(0, -2);

			function setFirstPosition()
			{
				kanbanWidth = this.kanban.parentNode.offsetWidth;
				kanbanParentOffsetTop = this.kanban.parentNode.getBoundingClientRect().top;
				kanbanOffsetTop = this.kanban.getBoundingClientRect().top;
				kanbanOffsetLeft = this.kanban.parentNode.getBoundingClientRect().left;
				footer = document.getElementById('footer');
				footerRectTop = footer.getBoundingClientRect().top;
				offsetfooter = document.documentElement.clientHeight - footerRectTop;

				this.kanban.style.position = 'relative';
				this.kanban.style.left = '0';
				this.kanban.style.top = '0';
				this.kanban.style.width = kanbanWidth + 'px';
				this.kanban.style.height = kanbanHeight + 'px';
				this.kanban.style.maxHeight = document.documentElement.clientHeight + 'px';

				if(kanbanParentOffsetTop <= 0)
				{
					this.kanban.style.position = 'fixed';
					this.kanban.style.left = kanbanOffsetLeft + 'px';
					this.kanban.style.top = '5px';

					if((footerRectTop - kanbanWrapperPadding) <= (document.documentElement.clientHeight))
					{
						var footerVissibleHeight = document.documentElement.clientHeight - footerRectTop;
						this.kanban.style.height = (kanbanHeight - footerVissibleHeight - kanbanWrapperPadding) + 'px';
					}
				}
			}

			var fix = false;

			BX.bind(this.kanban, 'mouseenter', function ()
			{
				fix = true;
			});

			BX.bind(this.kanban, 'mouseleave', function ()
			{
				fix = false;
				document.body.style.overflow = '';
				kanban.style.width = kanban.parentNode.offsetWidth + 'px';
			});

			function setPosition()
			{
				kanbanWidth = this.kanban.parentNode.offsetWidth;
				kanbanOffsetTop = this.kanban.getBoundingClientRect().top;
				kanbanParentOffsetTop = this.kanban.parentNode.getBoundingClientRect().top;
				kanbanOffsetLeft = this.kanban.parentNode.getBoundingClientRect().left;
				kanbanHeight = document.documentElement.clientHeight - kanbanOffsetTop;
				footer = document.getElementById('footer');
				footerHeight = footer.offsetHeight;
				footerRectTop = footer.getBoundingClientRect().top;
				offsetfooter = document.documentElement.clientHeight - footerRectTop;

				var scrolled = Math.round(window.pageYOffset || document.documentElement.scrollTop);
				var kanbanPosition = BX.pos(this.kanban.parentNode, false);

				this.kanban.style.height = (document.documentElement.clientHeight - kanbanOffsetTop) + 'px';

				if(scrolled < kanbanPosition.top)
				{
					this.kanban.style.position = 'relative';
					this.kanban.style.left = '0';
					this.kanban.style.top = '0';
					this.kanban.style.height = kanbanHeight + 'px';
					this.kanban.parentNode.style.height = (document.documentElement.clientHeight + 300) + 'px';
				}

				if(kanbanParentOffsetTop <= 0)
				{
					this.kanban.style.position = 'fixed';
					this.kanban.style.width = this.kanban.parentNode.offsetWidth + 'px';
					this.kanban.style.left = kanbanOffsetLeft + 'px';
					this.kanban.style.top = '5px';
					this.kanban.style.height = kanbanHeight + 'px';

					if(fix == true)
					{
						document.body.style.overflow = 'hidden';
						kanban.style.width = kanban.parentNode.offsetWidth + 'px';
					}

					if(fix ==false)
					{
						document.body.style.overflow = '';
						kanban.style.width = kanban.parentNode.offsetWidth + 'px';
					}

					if((footerRectTop - kanbanWrapperPadding) <= (document.documentElement.clientHeight))
					{
						var footerVissibleHeight = document.documentElement.clientHeight - footerRectTop;
						this.kanban.style.height = (kanbanHeight - footerVissibleHeight - kanbanWrapperPadding) + 'px';
					}
				}
			}

			// delete this after set new filter
			function setFirstPositionF()
			{
				setTimeout(function ()
				{
					var kanbanWidth = kanban.parentNode.offsetWidth;
					var kanbanParentOffsetTop = kanban.parentNode.getBoundingClientRect().top;
					var kanbanOffsetTop = kanban.getBoundingClientRect().top;
					var kanbanOffsetLeft = kanban.parentNode.getBoundingClientRect().left;
					var kanbanHeight = document.documentElement.clientHeight - kanbanOffsetTop;
					var footer = document.getElementById('footer');
					var footerRectTop = footer.getBoundingClientRect().top;
					var offsetfooter = document.documentElement.clientHeight - footerRectTop;
					var kanbanPosition = BX.pos(kanban.parentNode, false);

					kanban.style.position = 'relative';
					kanban.style.left = '0';
					kanban.style.top = '0';
					kanban.style.width = kanbanWidth + 'px';
					kanban.style.height = kanbanHeight + 'px';
					kanban.style.maxHeight = document.documentElement.clientHeight + 'px';

					if(kanbanParentOffsetTop <= 0)
					{
						kanban.style.position = 'fixed';
						kanban.style.left = kanbanOffsetLeft + 'px';
						kanban.style.top = '0';

						if(offsetfooter >= 0)
						{
							kanban.style.height = (document.documentElement.clientHeight - (offsetfooter + kanbanWrapperPadding)) + 'px';
						}
						else
						{
							kanban.style.height = document.documentElement.clientHeight + 'px';
						}
					}
				}, 500)
			}

			var showFilter = document.querySelector('.bx-filter-switcher-tab'); // delete this after set new filter

			setFirstPosition();
			showFilter.addEventListener('click', setFirstPositionF, false); // delete this after set new filter
			window.addEventListener('resize', setFirstPosition, false);
			window.addEventListener('scroll', setPosition, false);
		},

		prevItem: function(id, newState) {
			var column = this.getColumn(newState);
			if (column)
			{
				var items = column.items || [];
				var c = items.length;
				if (c > 1)
				{
					for (var i=0; i<c-1; i++)
					{
						if (items[i+1].id === id)
						{
							return items[i].id;
						}
					}
				}
			}
			return 0;
		},

		counterTotalPrice: function (newColumns)
		{

			var columns = this.columns;

			if (typeof newColumns === 'undefined')
			{
				for (var key in columns)
				{
					setPrice(columns[key].total, columns[key].layout.summary, columns[key].count, columns[key].layout.total, columns[key].total_format, columns[key]);

					if(columns[key].layout.items.classList.contains('crm-kanban-items-blocked'))
					{
						columns[key].layout.items.classList.remove('crm-kanban-items-blocked')
					}
				}
			}
			else
			{
				for (var key = 0; key < newColumns.length; key++)
				{
					if (columns[newColumns[key].id])
					{
						setPrice(newColumns[key].total, columns[newColumns[key].id].layout.summary, newColumns[key].count, columns[newColumns[key].id].layout.total, newColumns[key].total_format, columns[newColumns[key].id]);

						if(columns[newColumns[key].id].layout.items.classList.contains('crm-kanban-items-blocked'))
						{
							columns[newColumns[key].id].layout.items.classList.remove('crm-kanban-items-blocked')
						}
					}
				}
			};

			function setPrice(priceTotal, priceLayout, countTotal, countLayout, total_format, column)
			{
				var countTotal = countTotal;
				var countLayout = countLayout;
				var priceTotal = priceTotal;
				var priceTotalLayout = priceLayout;
				var priceTotalStep;
				var priceAttr = priceTotalLayout.getAttribute("data-total");
				var priceData = +priceAttr;

				countLayout.innerHTML = countTotal;

				if(column.items.length < +countTotal)
				{
					column.layout.items.appendChild(column.layout.loadMore);
					column.layout.loadMore.classList.remove('crm-kanban-loadmore-show');
				}

				if (priceData !== null)
				{
					if (priceData > priceTotal)
					{
						priceTotalStep = (priceData - priceTotal) / 40;
					}
					else
					{
						priceTotalStep = (priceTotal - priceData) / 40;
					}
				}
				else
				{
					priceTotalStep = priceTotal / 40;
				}

				// animation
				if (priceTotal != 0)
				{
					function scroll(val, el, timeout, step, start)
					{
						val = parseInt(val);
						i = 0;
						if (start != null)
						{
							var i = +start;
						}
						if (i < val)
						{
							(function ()
							{
								if (i <= val)
								{
									setTimeout(arguments.callee, timeout);
									priceTotalLayout.innerHTML = BX.util.number_format(i, 0, ",", " ");
									i = i + step;
								}
								else
								{
									priceTotalLayout.innerHTML = total_format;
									priceTotalLayout.setAttribute("data-total", val);
								}
							})();
						}
						else if (i > val)
						{
							(function ()
							{
								if (i >= val)
								{
									setTimeout(arguments.callee, timeout);
									priceTotalLayout.innerHTML = BX.util.number_format(i, 0, ",", " ");
									i = i - step;
								}
								else
								{
									priceTotalLayout.innerHTML = total_format;
									priceTotalLayout.setAttribute("data-total", val);
								}
							})();
						}
						else
						{
							return false;
						}
					}

					scroll(priceTotal, priceTotalLayout, 10, priceTotalStep, priceData);

				}
				else
				{
					priceTotalLayout.setAttribute("data-total", "0");
					priceTotalLayout.innerHTML = "0";
				}
			}

		},

		addColumn: function(options) {
			options = options || {};

			if (this.getColumn(options.id) !== null)
			{
				return;
			}

			var column = new BX.CrmKanbanColumn(options);
			column.kanban = this;
			this.columns[options.id] = column;
			this.columns_sort[options.sort] = options.id;
		},

		addDrop: function(options) {
			options = options || {};

			if (this.getDrop(options.id) !== null)
			{
				return;
			}

			var drop = new BX.CrmKanbanDrop(options);
			drop.kanban = this;
			this.dropzones[options.id] = drop;
		},

		addItem: function(options) {
			options = options || {};

			var column = this.getColumn(options.columnId);
			if (column)
			{
				var item = new BX.CrmKanbanItem(options);
				item.kanban = this;

				this.items[options.id] = item;
				column.addItem(item);
			}
		},

		moveItemToColumShow: function(item, targetColumn, beforeItem, avatar) {

			item = this.getItem(item);
			targetColumn = this.getColumn(targetColumn);
			beforeItem = this.getItem(beforeItem);

			var currentColumn = this.getColumn(item.columnId);
			var containerStart = document.querySelector('[data-move="' + item.id + '"]');
			var containerStartWidth = containerStart.offsetWidth;
			var containerFinish = document.querySelector('[data-move-column="' + targetColumn.id + '"]');

			if(beforeItem)
			{
				containerFinish = document.querySelector('[data-move="' + beforeItem.id + '"]');
			}
			var top = null;
			var left = null;
			function getPosition(element)
			{
				var box = element.getBoundingClientRect();
				var body = document.body;
				var docElem = document.documentElement;
				var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
				var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
				var clientTop = docElem.clientTop || body.clientTop || 0;
				var clientLeft = docElem.clientLeft || body.clientLeft || 0;
				top  = box.top +  scrollTop - clientTop;
				left = box.left + scrollLeft - clientLeft;
			}
			getPosition(containerStart);

			containerStart.classList.add('crm-kanban-deal-item-block');

			var targetClass = 'crm-kanban-grid-item-pre';
			if(beforeItem)
			{
				targetClass = 'crm-kanban-deal-item-pre';
			}
			containerFinish.classList.add(targetClass);

			var containerMove = containerStart.cloneNode(true);
			if(avatar)
			{
				var userBlock = BX.create("div", {
					attrs: {
						className: "crm-kanban-deal-item-touchuser",
						style: "background-image: url(" + avatar + ")"
					}
				});
				containerMove.appendChild(userBlock);
			}
			containerMove.classList.add('crm-kanban-deal-item-dragshow');
			containerMove.style.width = containerStartWidth + "px";
			containerMove.style.position = "absolute";
			containerMove.style.top = top + "px";
			containerMove.style.left = left + "px";
			containerMove.style.zIndex = "999999";
			document.body.appendChild(containerMove);

			setTimeout(getPosition(containerFinish),1);
			setTimeout(function(){
				containerMove.style.top = (top + 102) + "px";
				if(beforeItem)
				{
					containerMove.style.top = top + "px";
				}
				containerMove.style.left = left + "px";
			},1);

			setTimeout(function(){

				if (currentColumn !== targetColumn)
				{
					currentColumn.removeItem(item);
					targetColumn.addItem(item, beforeItem);
				}

				containerStart.classList.remove('crm-kanban-deal-item-block');
				containerFinish.classList.remove(targetClass);
				containerMove.parentNode.removeChild(containerMove);
			},1000);
		},

		moveItemToColum: function(item, targetColumn, beforeItem) {

			if (BX.type.isNumber(item))
			{
				item = this.getItem(item);
			}

			var border = item.layout.container.querySelector('.crm-kanban-deal-item-wrapper-border');
			var borderColor = targetColumn.color;

			border.style.background = '#' + borderColor;

			if (BX.type.isNumber(targetColumn))
			{
				targetColumn = this.getColumn(targetColumn);
			}

			if (BX.type.isNumber(beforeItem))
			{
				beforeItem = this.getItem(beforeItem);
			}

			var currentColumn = this.getColumn(item.columnId);

			if (currentColumn !== targetColumn)
			{
				currentColumn.removeItem(item);
				targetColumn.addItem(item, beforeItem);
			}
			else if(beforeItem)
			{
				if(beforeItem !== item)
				{
					currentColumn.removeItem(item);
					targetColumn.addItem(item, beforeItem);
				}
			}
			else if(currentColumn && !beforeItem)
			{
				currentColumn.removeItem(item);
				targetColumn.addItem(item, beforeItem);
			}

		},

		removeToColumnItem: function(item) {
			var currentColumn = this.getColumn(item.columnId);
			currentColumn.removeItem(item);
		},

		/**
		 *
		 * @param columnId
		 * @returns {BX.CrmKanbanColumn}
		 */
		getColumn: function(columnId) {
			return this.columns[columnId] ? this.columns[columnId] : null;
		},

		/**
		 *
		 * @param dropId
		 * @returns {BX.CrmKanbanDrop}
		 */

		getDrop: function(dropId) {
			return this.dropzones[dropId] ? this.dropzones[dropId] : null;
		},

		/**
		 *
		 * @param itemId
		 * @returns {BX.CrmKanbanItem}
		 */
		getItem: function(itemId) {
			return this.items[itemId] ? this.items[itemId] : null;
		},

		draw: function() {

			var docFragment = document.createDocumentFragment();

			for (var i in this.columns_sort)
			{
				var columnId = this.columns_sort[i];
				docFragment.appendChild(this.columns[columnId].render());
			}

			var dropZone = BX.create("div", {
				attrs: {
					className: "crm-kanban-dropzone",
					style: "left: " + (BX.pos(this.kanban.parentNode).left - 15) + "px"
				}
			});
			var dropZoneItem = document.createDocumentFragment();

			for (var dropId in this.dropzones)
			{
				dropZoneItem.appendChild(this.dropzones[dropId].render());
			}

			var itemWrapper = BX.create("div", {
				attrs: { className: "crm-kanban-grid-wrapper" }
			});

			dropZone.appendChild(dropZoneItem);
			itemWrapper.appendChild(docFragment);
			this.container.appendChild(itemWrapper);
			document.body.appendChild(dropZone);
			this.counterTotalPrice();
			this.setShadow();
		},

		setShadow: function ()
		{
			function setShadow()
			{
				if(kanban.firstElementChild.offsetWidth > kanban.offsetWidth)
				{
					var scrollWidth = kanban.scrollWidth - kanban.offsetWidth;
					var scrollToX = kanban.scrollLeft;


					if(scrollToX <= 0)
					{
						kanban.parentNode.className = 'crm-kanban crm-kanban-right';
					}
					else if(scrollToX > 0 && scrollToX !== scrollWidth)
					{
						kanban.parentNode.className = 'crm-kanban crm-kanban-right crm-kanban-left';
					}
					else if(scrollToX == scrollWidth)
					{
						kanban.parentNode.className = 'crm-kanban crm-kanban-left';
					}
				}
				else
				{
					kanban.parentNode.className = 'crm-kanban';
				}
			}

			setShadow();

			window.addEventListener('resize', setShadow, false);
			this.kanban.addEventListener('scroll', setShadow, false);
		},

		loadData: function(json) {

			if (BX.type.isArray(json.columns))
			{
				json.columns.forEach(function(column) {
					if (column.type === 'LOOSE')
					{
						this.addDrop(column);
					}
					else
					{
						this.addColumn(column, true);
					}
				}, this);
			}

			if (BX.type.isArray(json.items))
			{
				json.items.forEach(function(item) {
					this.addItem(item);
				}, this);
			}

			if (json.events)
			{
				for (var eventName in json.events)
				{
					if (json.events.hasOwnProperty(eventName))
					{
						BX.addCustomEvent(this, eventName, json.events[eventName]);
					}
				}
			}
		}
	};
}

if (typeof(BX.CrmKanbanDrop) === 'undefined')
{
	BX.CrmKanbanDrop = function(options)
	{
		this.id = options.id;
		this.name = options.name;
		this.color = options.color;
		this.kanban = null;
		this.layout = {
			container: null
		};
	};
	BX.CrmKanbanDrop.prototype =
	{
		render: function() {
			dropContainer = BX.create("div", {
				attrs: {
					className: "crm-kanban-dropzone-item",
					"data-type": "drop",
					"data-id": this.id
				},
				children: [
					BX.create("div", {
						attrs: {
							className: "crm-kanban-dropzone-item-bg",
							style: "background: #" + this.color
						}
					}),
					BX.create("div", {
						attrs: { className: "crm-kanban-dropzone-item-title" },
						html: this.name
					})
				]
			})

			this.kanban.dragger.registerDrop(dropContainer);

			return dropContainer;
		}
	};
}

if (typeof(BX.CrmKanbanColumn) === 'undefined')
{
	BX.CrmKanbanColumn = function(options)
	{
		this.id = options.id;
		this.name = options.name;
		this.color = options.color;
		this.sort = options.sort;
		this.count = options.count;
		this.total = options.total;
		this.total_format = options.total_format;
		this.currency = options.currency;
		this.items = [];
		this.layout = {
			container: null,
			items: null,
			title: null,
			summary: null,
			total: null,
			input: null,
			name: null,
			color: null,
			loadMore: null,
			loadMoreClick: null
		};
		this.kanban = null;
		this.page = 1;
		this.lastId = 0;
	};
	BX.CrmKanbanColumn.prototype = {

		addItem: function(item, beforeItem) {
			if (!item instanceof BX.CrmKanbanItem)
			{
				throw "item must be an instance of BX.CrmKanbanItem";
			}

			item.columnId = this.id;
			this.lastId = item.id;

			var index = BX.util.array_search(beforeItem, this.items);
			if (index >= 0)
			{
				this.items.splice(index, 0, item);
			}
			else
			{
				this.items.push(item);
			}

			if (this.layout.container)
			{
				this.render();
			}
		},

		removeItem: function(itemToRemove) {
			this.items = this.items.filter(function(item) {
				return item !== itemToRemove;
			});

			if (this.layout.container)
			{
				this.render();
			}
		},

		setName: function(name) {
			this.name = name;
		},

		loadMoreClick: function()
		{
			this.page++;
			BX.CrmKanbanHelper.getInstance().loadPage(this.id, this.page);
			this.layout.loadMore.classList.add('crm-kanban-loadmore-show');
		},

		createLayout: function() {

			this.layout.loadMore = BX.create("div", {
				attrs: { className: "crm-kanban-loadmore" },
					children: [
					BX.create("div", {
						attrs: { className: "crm-kanban-user-loader" },
						html:   '<div class="crm-kanban-user-loader-item">' +
									'<div class="crm-kanban-loader">' +
										'<svg class="crm-kanban-circular" viewBox="25 25 50 50">' +
											'<circle class="crm-kanban-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>' +
										'</svg>' +
									'</div>' +
								'</div>'
					}),
					BX.create("span", {
						attrs: { className: "crm-kanban-loadmore-link" },
						text: BX.message("CRM_KANBAN_ACTIVITY_MORE"),
						events: {
							click: BX.proxy(this.loadMoreClick, this)
						}
					})
				]
			});

			if (this.layout.container !== null)
			{
				return this.layout.container;
			}

			var leadOff = 'crm-kanban-header crm-kanban-header-lead';

			BX.CrmKanbanHelper.getInstance().getEntityType() !== 'LEAD' ? leadOff = 'crm-kanban-header' : null

			// color option
			var titleParam = 'crm-kanban-step-title';

			function hexToRgb(hex) {
				var bigint = parseInt(hex, 16);
				var r = (bigint >> 16) & 255;
				var g = (bigint >> 8) & 255;
				var b = bigint & 255;
				var y = 0.21 * r + 0.72 * g + 0.07 * b;
				if(y < 145)
				{
					titleParam = 'crm-kanban-step-title crm-kanban-step-title-dark';
				}
			}

			if(this.color)
			{
				hexToRgb(this.color);
			}

			this.layout.container = BX.create("div", {
				attrs: {
					className: "crm-kanban-grid-item",
					"data-id": this.id,
					"data-move-column": this.id,
					"data-type": "column"
				},
				children: [
					BX.create("div", {
						attrs: {
							className: leadOff
						},
						children: [
							BX.create("div", {
								attrs: { className: titleParam },
								children: [
									(this.layout.color = BX.create("div", {
										attrs: {
											className: "crm-kanban-step-title-bg",
											style: "background: #" + this.color
										}
									})),
									(this.layout.name = BX.create("span", {
										attrs: {
											className: "crm-kanban-step-title-name"
										}
									})),
									(this.layout.total = BX.create("span", {
										attrs: {
											className: "crm-kanban-step-title-total"
										}
									})),
									BX.CrmKanbanHelper.getInstance().getSettings('access_config_perms')
									? BX.create("a", {
										attrs: {
											href: BX.CrmKanbanHelper.getInstance().getSettings('path_column_edit'),
											className: "crm-kanban-step-title-edit"
										}
									})
									: null,
									this.color == "" ?
									BX.create("span", {
										attrs: {
											className: "crm-kanban-step-title-right"
										}
									}) :
									BX.create("span", {
										attrs: {
											className: "crm-kanban-step-title-right",
											style: "background: #fff url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2213%22%20height%3D%2232%22%20viewBox%3D%220%200%2013%2032%22%3E%3Cpath%20fill%3D%22%23" + this.color + "%22%20fill-opacity%3D%221%22%20d%3D%22M0%200h3c2.8%200%204%203%204%203l6%2013-6%2013s-1.06%203-4%203H0V0z%22/%3E%3C/svg%3E) no-repeat"
										}
									})
								]
							}),
							BX.CrmKanbanHelper.getInstance().getEntityType() !== 'LEAD' ?
							BX.create("div", {
								attrs: {
									className: "crm-kanban-total-price"
								},
								children: [
									(this.layout.summary = BX.create("span", {
										attrs: {
											className: "crm-kanban-total-price-total"
										}
									}))
								]
							}) :
							BX.create("div", {
								attrs: {
									className: "crm-kanban-total-price crm-kanban-total-price-off"
								},
								children: [
									(this.layout.summary = BX.create("span", {
										attrs: {
											className: "crm-kanban-total-price-total"
										}
									}))
								]
							})
						]
					}),
					(this.layout.items = BX.create("div", {
							attrs: {
								className: "crm-kanban-items",
								"data-type": "column"
							}
						})
					)
				]
			})

			this.kanban.dragger.registerColumn(this.layout.container);

			return this.layout.container;
		},

		getSummaryPrice: function() {
			var total = 0;
			this.items.forEach(function(item) {
				total += item.price;
			});

			return total;
		},

		render: function() {

			var loadMoreShow = true;
			var columnEmpty = true;

			if (this.layout.container === null)
			{
				this.createLayout();
			}

			this.layout.container.style.maxWidth = (this.kanban.container.offsetWidth / 2) + 'px';

			BX.cleanNode(this.layout.items);

			for (var i = 0; i < this.items.length; i++)
			{
				var item = this.items[i];

				if (this.id === item.columnId)
				{
					columnEmpty = false;
					if (item.page === item.pageCount)
					{
						loadMoreShow = false;
					}
				}
				this.layout.items.appendChild(item.render());
			}

			this.layout.name.innerHTML = this.name;

			return this.layout.container;
		}

	};
}

if (typeof(BX.CrmKanbanItem) === 'undefined')
{
	BX.CrmKanbanItem = function(options)
	{
		this.id = options.id;
		this.name = options.name;
		this.link = options.link;
		this.price = options.price;
		this.price_formatted = options.price_formatted;
		this.date = options.date;
		this.im = options.im;
		this.activityProgress = options.activityProgress;
		this.activityTotal = options.activityTotal;
		this.activityShow = options.activityShow;
		this.contactName = options.contactName;
		this.contactLink = options.contactLink;
		this.contactId = options.contactId;
		this.contactType = options.contactType;
		this.mail = options.email;
		this.phone = options.phone;
		this.columnId = options.columnId;
		this.columnColor = options.columnColor;
		this.page = options.page;
		this.pageCount = options.pageCount;
		this.fields = options.fields;
		this.layout = {
			container: null,
			title: null,
			items: null,
			popup: null
		};
		this.popupTooltip = null;
		this.kanban = null;
		this.helper = BX.CrmKanbanHelper.getInstance();
		this.entityType = this.helper.getEntityType();
		this.moreFields = this.helper.getMoreFields();
		this.activityShow = this.helper.getSettings('show_activity');
	};
	BX.CrmKanbanItem.prototype =
	{

		clickChat: function() {
			BXIM.openMessenger(this.im.value);
		},

		clickContact: function(type) {

			var fields = type === 'mail' ? this.mail : this.phone;
			if (fields.length > 1)
			{
				var id = BX.findParent(BX.proxy_context, {className: 'crm-kanban-deal-item'}).getAttribute('data-id');
				var menuItems = [];

				this.layout.container.classList.add('crm-kanban-deal-item-active');
				document.body.addEventListener('click', BX.proxy(this.onBodyClick, this), true);

				for (var i = 0; i < fields.length; i++)
				{
					if (type === 'mail')
					{
						menuItems.push({
							text: fields[i]['value'] + ' (' + fields[i]['title'] + ')',
							href: 'mailto:' + fields[i]['value']
						});
					}
					else
					{
						menuItems.push({
							phone: fields[i]['value'],
							text: fields[i]['value'] + ' (' + fields[i]['title'] + ')',
							onclick: BX.proxy(this.clickPhoneCall, this)
						});
					}
				}
				BX.PopupMenu.show('kanban-contact-' + type + '-' + id, BX.proxy_context, menuItems,
				{
					autoHide: true,
					zIndex: 1200,
					offsetLeft: 20,
					angle: true,
					closeByEsc : true
				});
			}
			else
			{
				var i = 0;
				if (type === 'mail')
				{
					// top.location.href = 'mailto:' + fields[i]['value'];
				}
				else
				{
					this.clickPhoneCall(i, {phone: fields[i]['value']});
				}
			}
		},

		onBodyClick: function() {
			var itemClassRemove = document.body.querySelector('.crm-kanban-deal-item-active');
			itemClassRemove.classList.remove('crm-kanban-deal-item-active');
			document.body.removeEventListener("click", BX.proxy(this.onBodyClick, this), true);
		},

		clickMail: function() {
			this.clickContact('mail');
		},

		clickPhone: function() {
			this.clickContact('phone');
		},

		clickPhoneCall: function(i, item) {
			if (typeof(BXIM) !== 'undefined') {
				BXIM.phoneTo(item.phone, {ENTITY_TYPE: this.contactType, ENTITY_ID: this.contactId});
			}
		},

		addField: function() {
			var id = BX.findParent(BX.proxy_context, {className: 'crm-kanban-deal-item'}).getAttribute('data-id');
			var menuItems = [];
			for (var i = 0; i < this.moreFields.length; i++)
			{
				menuItems.push({
					code: this.moreFields[i]['code'],
					text: this.moreFields[i]['title'],
					onclick: BX.proxy(this.addFieldClick, this)
				});
			}
			BX.PopupMenu.show('kanban-more-fields-' + id, BX.proxy_context, menuItems);
		},

		addFieldClick: function(i, item) {
			var href = top.location.href;
			top.location.href = href + (href.indexOf('?') === -1 ? '?' : '&') + 'set_field=' + item.code
		},

		delField: function() {
			var href = top.location.href;
			href = href + (href.indexOf('?') === -1 ? '?' : '&') + 'del_field=' + BX.proxy_context.getAttribute('data-code');
			top.location.href = href;
		},

		activityClick: function() {
			BX.CrmKanbanHelper.getInstance().openActivityItems(this.id);
		},

		activityPlanClick: function() {
			var id = BX.findParent(BX.proxy_context, {className: 'crm-kanban-deal-item'}).getAttribute('data-id');
			var menuItems = [
				{
					type: 'call',
					text: BX.message('CRM_KANBAN_ACTIVITY_PLAN_CALL'),
					onclick: BX.proxy(this.activityPlanClickItem, this)
				},
				{
					type: 'meeting',
					text: BX.message('CRM_KANBAN_ACTIVITY_PLAN_MEETING'),
					onclick: BX.proxy(this.activityPlanClickItem, this)
				},
				{
					type: 'task',
					text: BX.message('CRM_KANBAN_ACTIVITY_PLAN_TASK'),
					onclick: BX.proxy(this.activityPlanClickItem, this)
				},
			];
			BX.PopupMenu.show('kanban-plan-' + id, BX.proxy_context, menuItems,
			{
				autoHide: true,
				zIndex: -9,
				offsetLeft: 20,
				angle: true,
				overlay: false
			});
		},

		activityPlanClickItem: function(i, item) {
			if (item.type === 'meeting' || item.type === 'call')
			{
				(new BX.Crm.Activity.Planner()).showEdit({
					TYPE_ID: BX.CrmActivityType[item.type],
					OWNER_TYPE: this.entityType,
					OWNER_ID: this.id
				});
			}
			else if (item.type === 'task')
			{
				if (typeof window['taskIFramePopup'] !== 'undefined')
				{
					var taskData =
						{
							UF_CRM_TASK: [BX.CrmOwnerTypeAbbr.resolve(this.entityType) + '_' + this.id],
							TITLE: 'CRM: ',
							TAGS: 'crm'
						};
					window['taskIFramePopup'].add(taskData);
				}
			}
		},

		renderFields: function() {
			var fields = [];
			for (var i=0; i<this.fields.length; i++)
			{
				fields.push(BX.create("div", {
								attrs: {
								},
								children: [
									BX.create("span", {
										attrs: {
											className: "crm-kanban-deal-item-field"
										},
										html: this.fields[i]["title"] + ": " + this.fields[i]["value"]
									}),
									BX.create("span", {
										attrs: {
											className: "crm-kanban-deal-item-field-del",
											"data-code": this.fields[i]["code"]
										},
										html: "*",
										events: {
											click: BX.proxy(this.delField, this)
										}
									})
								]
				}));
			}
			return fields;
		},

		render: function() {

			if (this.layout.container === null)
			{
				this.layout.container = BX.create("div", {
					attrs: {
						className: "crm-kanban-deal-item",
						"data-id": this.id,
						"data-move": this.id,
						"data-type": "item"
					},
					children: [
						BX.create("div", {
							attrs: {
								className: "crm-kanban-deal-item-wrapper"
							},
							children: [
								BX.create("div", {
									attrs: {
										className: "crm-kanban-deal-item-wrapper-border",
										"data-role": "item-color",
										style: "background: #" + this.columnColor
									}
								}),
								BX.create("div", {
									attrs: {
										className: "crm-kanban-deal-item-wrapper-line"
									}
								}),
								BX.create("a", {
									attrs: {
										className: "crm-kanban-deal-item-title",
										href: this.link,
										title: BX.util.htmlspecialcharsback(this.name)
									},
									html: this.name
								}),
								BX.CrmKanbanHelper.getInstance().getEntityType() !== 'LEAD' ?
								BX.create("div", {
									attrs: { className: "crm-kanban-deal-item-total" },
									children: [
										BX.create("div", {
											attrs: {
												className: "crm-kanban-deal-item-total-price"
											},
											html: this.price_formatted
										})
									]
								}) : null,
								BX.create("a", {
									attrs: {
										className: "crm-kanban-deal-item-user",
										href: this.contactLink
									},
									html: this.contactName
								}),
								BX.create("div", {
									attrs: { className: "crm-kanban-deal-item-date" },
									html: this.date
								}),
								BX.create("div", {
									attrs: { className: "crm-kanban-deal-item-planner" },
									children: [
										(this.activityShow)
										? BX.create("span", {
											attrs: {
												className: "crm-kanban-deal-item-activity",
												id: "crm-kanban-act-count-" + this.id,
												'data-count': this.activityProgress,
												style: this.activityProgress > 0 ? 'display: block' : 'display: none;'
											},
											text: BX.message('CRM_KANBAN_ACTIVITY_PLAN') + ': ' + this.activityProgress,
											events: {
												click: BX.proxy(this.activityClick, this)
											}
										}) : null,

										(this.activityShow)
										? BX.create('div', {
											attrs: {
												className: 'crm-kanban-deal-item-activity-empty',
												id: "crm-kanban-act-icon-" + this.id,
												style: this.activityProgress > 0 ? 'display: none' : 'display: block;'
											},
											events: {
												mouseover: function ()
												{
													this.popupTooltip = new BX.PopupWindow('kanban_plan_tooltip', this, {
														offsetLeft: 8,
														darkMode: true,
														closeByEsc: true,
														angle : true,
														autoHide: true,
														content: BX.message('CRM_KANBAN_ACTIVITY_LETSGO')
													})
													this.popupTooltip.show();
												},
												mouseout: function ()
												{
													var toolip = this.popupTooltip;

													setTimeout(function ()
													{
														toolip.destroy()
													}, 500)
												}
											}
										}) : null,

										this.activityShow
										? BX.create("span", {
											attrs: {
												className: "crm-kanban-deal-item-plan"
											},
											text: BX.message('CRM_KANBAN_ACTIVITY_MY'),
											events: {
												click: BX.proxy(this.activityPlanClick, this)
											}
										})
										: null
									]
								}),
								BX.create("div", {
									attrs: { className: "crm-kanban-deal-item-contact" },
									children: [
										BX.create("a", {
											attrs: {
												className: this.phone ? "crm-kanban-step-contact-phone" : "crm-kanban-step-contact-phone crm-kanban-step-contact-phone-disabled",
												href: "javascript:void(0)"
											},
											events: {
												click: BX.proxy(this.clickPhone, this)
											}
										}),
										BX.create("a", {
											attrs: {
												className: this.mail ? "crm-kanban-step-contact-mail" : "crm-kanban-step-contact-mail crm-kanban-step-contact-mail-disabled",
												href: (!this.mail || this.mail.length > 1) ? "javascript:void(0)" : "mailto:" + this.mail[0]['value']
											},
											events: {
												click: BX.proxy(this.clickMail, this)
											}
										}),
										BX.create("span", {
											attrs: {
												className: this.im ? "crm-kanban-step-contact-message" : "crm-kanban-step-contact-message crm-kanban-step-contact-message-disabled"
											},
											events: {
												click: BX.proxy(this.clickChat, this)
											}
										})
									]
								})
							]
						})
					]
				});

				this.kanban.dragger.registerItem(this.layout.container);
			}
			return this.layout.container;
		}
	};

	BX.CrmKanbanItem.changeActCount = function(id, type)
	{
		var count = Math.max(0, parseInt(BX.data(BX('crm-kanban-act-count-' + id), 'count')) + parseInt(type) * 1);
		BX.data(BX('crm-kanban-act-count-' + id), 'count', count);
		BX('crm-kanban-act-count-' + id).innerText = BX.message('CRM_KANBAN_ACTIVITY_PLAN') + ': ' + count;
		if (count > 0)
		{
			BX.show(BX('crm-kanban-act-count-' + id));
			BX.hide(BX('crm-kanban-act-icon-' + id));
		}
		else
		{
			BX.hide(BX('crm-kanban-act-count-' + id));
			BX.show(BX('crm-kanban-act-icon-' + id));
		}
	};
}

if (typeof(BX.CrmKanbanDragDrop) === 'undefined')
{
	BX.CrmKanbanDragDrop = function(kanban)
	{
		/**
		 * @var {BX.CrmKanbanGrid}
		 */
		this.kanban = kanban;
		this.draggableItem = null;
		this.droppableColumn = null;
		this.stub = null;
		this.droppableZone = null;
		this.dropContainer = null;
	};
	BX.CrmKanbanDragDrop.prototype.registerItem = function(object)
	{
		object.onbxdragstart = BX.proxy(this.onDragStart, this);
		object.onbxdrag = BX.proxy(this.onDrag, this);
		object.onbxdragstop = BX.proxy(this.onDragStop, this);
		object.onbxdraghover = BX.proxy(this.onDragOver, this );
		jsDD.registerObject(object);
		jsDD.registerDest(object, 10);
	};
	BX.CrmKanbanDragDrop.prototype.registerDrop = function(object)
	{
		jsDD.registerDest(object, 1);
	};
	BX.CrmKanbanDragDrop.prototype.registerColumn = function(object)
	{
		jsDD.registerDest(object, 20);
	};
	BX.CrmKanbanDragDrop.prototype.onDragStart = function()
	{
		this.kanban.container.style.overflow = "hidden";
		this.dropContainer = document.querySelector('.crm-kanban-dropzone');
		this.dropContainer.className = "crm-kanban-dropzone crm-kanban-dropzone-show";
		this.dropContainer.style.position = 'fixed';
		this.dropContainer.style.left = (BX.pos(this.kanban.container).left - 15) + 'px';
		this.dropContainer.style.right = 63 + 'px';

		var div = BX.proxy_context;
		var itemId = BX.type.isDomNode(div) ? div.getAttribute("data-id") : null;
		var kanbanOffsetBotom = document.documentElement.clientHeight - this.kanban.container.getBoundingClientRect().bottom;

		if(kanbanOffsetBotom > 0)
		{
			this.dropContainer.style.bottom = (kanbanOffsetBotom - 20) + 'px';
		}
		else
		{
			this.dropContainer.style.bottom = '';
		}

		this.draggableItem = this.kanban.getItem(itemId);
		this.draggableItem.layout.container.classList.add('crm-kanban-deal-item-block')

		if (!this.draggableItem)
		{
			jsDD.stopCurrentDrag();
			return;
		}

		if (!this.stub)
		{
			var widthItem = this.draggableItem.layout.container.offsetWidth;
			this.stub = this.draggableItem.layout.container.cloneNode(true);
			this.stub.style.position = "absolute";
			this.stub.style.width = widthItem + "px";
			this.stub.className = "crm-kanban-deal-item crm-kanban-deal-item-drag";
			document.body.style.height = document.documentElement.clientHeight + 'px';
			document.body.style.overflow = 'hidden';
			document.body.appendChild(this.stub);
		}
	};

	BX.CrmKanbanDragDrop.prototype.onDrag = function(x, y)
	{
		this.stub.style.left = x + "px";
		this.stub.style.top = y + "px";
	};

	BX.CrmKanbanDragDrop.prototype.onDragOver = function(destination, x, y)
	{
		if (this.droppableItem)
		{
			this.droppableItem.layout.container.classList.remove('crm-kanban-deal-item-pre')
		}

		if (this.droppableColumn)
		{
			this.droppableColumn.layout.container.className = "crm-kanban-grid-item";
		}

		if (this.droppableZone)
		{
			this.droppableZone.className = "crm-kanban-dropzone-item";
		}

		var itemId = destination.getAttribute("data-id");
		var type = destination.getAttribute("data-type");

		if (type === "item")
		{
			this.droppableItem = this.kanban.getItem(itemId);
			this.droppableColumn = null;
			this.droppableZone = null;
		}
		else if (type === "column")
		{
			this.droppableColumn = this.kanban.getColumn(itemId);
			this.droppableItem = null;
			this.droppableZone = null;
		}
		else if (type === "drop")
		{
			this.droppableZone = destination;
			this.droppableColumn = null;
			this.droppableItem = null;
		}

		if (this.droppableItem)
		{
			this.droppableItem.layout.container.classList.add('crm-kanban-deal-item-pre')
		}

		if (this.droppableZone)
		{
			this.droppableZone.className = "crm-kanban-dropzone-item crm-kanban-dropzone-item-pre"
		}

		if (this.droppableColumn)
		{
			var parentClass = 'crm-kanban-grid-item-pre';

			if(this.droppableColumn.layout.loadMore.parentNode == null)
			{
				parentClass = 'crm-kanban-grid-item-column-pre';
			}
			this.droppableColumn.layout.container.classList.add(parentClass)
		}

	};
	BX.CrmKanbanDragDrop.prototype.invoicePopupInstance = null;
	BX.CrmKanbanDragDrop.prototype.invoicePopup = function(id, status, oldStatus)
	{
		if (this.invoicePopupInstance === null)
		{
			this.invoicePopupInstance = new BX.PopupWindow('kanban_invoice', window.body, {
					offsetLeft : 0,
					lightShadow : true,
					//closeIcon : true,
					overlay : true,
					titleBar: {content: BX.create('span', {html: ''})},
					draggable: true,
					//closeByEsc : true,
					contentColor: 'white',
					content: BX.create('div', {
								attrs: { className: 'crm-kanban-popup-wrapper' },
								children: [
									BX.create('table', {
										attrs: { className: 'crm-kanban-popup-table' },
										children: [
											BX.create('tr', {
												children: [
													BX.create('td', {
														children: [
															BX.create('SPAN', {
																attrs: { className: 'crm-kanban-popup-text' },
																text: BX.message('CRM_KANBAN_INVOICE_PARAMS_DATE')
															})
														]
													}),
													BX.create('td', {
														children: [
															BX.create('INPUT', {
																attrs: {
																	id: 'crm-kanban-droppopup-date',
																	className: 'crm-kanban-popup-input'
																},
																events: {
																	click: function()
																	{
																		BX.calendar({
																			node: this,
																			field: this
																		});
																	}
																}
															})
														]
													})
												]
											}),
											BX.create('tr', {
												attrs: { id: 'crm-kanban-droppopup-winblock' },
												children: [
													BX.create('td', {
														children: [
															BX.create('SPAN', {
																attrs: { className: 'crm-kanban-popup-text' },
																text: BX.message('CRM_KANBAN_INVOICE_PARAMS_DOCNUM')
															})
														]
													}),
													BX.create('td', {
														children: [
															BX.create('INPUT', {
																attrs: {
																	id: 'crm-kanban-droppopup-docnum',
																	className: 'crm-kanban-popup-input'
																}
															})
														]
													})
												]
											}),
											BX.create('tr', {
												children: [
													BX.create('td', {
														attrs: {
															colspan: '2',
															className: 'crm-kanban-popup-border'
														},
														children: [
															BX.create('SPAN', {
																attrs: { className: 'crm-kanban-popup-text' },
																text: BX.message('CRM_KANBAN_INVOICE_PARAMS_COMMENT')
															}),
															BX.create('TEXTAREA', {
																attrs: {
																	id: 'crm-kanban-droppopup-comment',
																	className: 'crm-kanban-popup-textarea'
																}
															}),
															BX.create('INPUT',{
																attrs: { type: 'hidden', id: 'crm-kanban-droppopup-id' }
															}),
															BX.create('INPUT',{
																attrs: { type: 'hidden', id: 'crm-kanban-droppopup-status' }
															}),
															BX.create('INPUT',{
																attrs: { type: 'hidden', id: 'crm-kanban-droppopup-oldstatus' }
															})
														]
													}),
												]
											})
										]
									})
								]
							}),
					buttons:
							[
								new BX.PopupWindowButton(
									{
										text: BX.message('CRM_KANBAN_INVOICE_PARAMS_SAVE'),
										className: 'popup-window-button-accept',
										events:
										{
											click: function()
											{
												BX.CrmKanbanHelper.getInstance().moveState(
															BX('crm-kanban-droppopup-id').value,
															BX('crm-kanban-droppopup-status').value,
															BX('crm-kanban-droppopup-oldstatus').value,
															{
																comment: BX('crm-kanban-droppopup-comment').value,
																date: BX('crm-kanban-droppopup-date').value,
																docnum: BX('crm-kanban-droppopup-docnum').value
															}
														);
												this.popupWindow.close();
											}
										}
									}
								)/*,
								new BX.PopupWindowButton(
									{
										text: BX.message('CRM_KANBAN_INVOICE_PARAMS_CANCEL'),
										className: 'popup-window-button-cancel',
										events:
										{
											click: function()
											{
												this.popupWindow.close();
											}
										}
									}
								)*/
							]
				});
			this.invoicePopupInstance.setTitleBar(BX.message('CRM_KANBAN_INVOICE_PARAMS'));
		}
		BX('crm-kanban-droppopup-comment').value = '';
		BX('crm-kanban-droppopup-date').value = BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')));
		BX('crm-kanban-droppopup-docnum').value = '';
		BX('crm-kanban-droppopup-id').value = id;
		BX('crm-kanban-droppopup-status').value = status;
		BX('crm-kanban-droppopup-oldstatus').value = oldStatus;

		if (status === 'P')
		{
			BX.show(BX('crm-kanban-droppopup-winblock'));
		}
		else
		{
			BX.hide(BX('crm-kanban-droppopup-winblock'));
		}
		this.invoicePopupInstance.show();
	};

	BX.CrmKanbanDragDrop.prototype.convertId = null;
	BX.CrmKanbanDragDrop.prototype.convertPopupInstance = null;
	BX.CrmKanbanDragDrop.prototype.convertPopup = function(id)
	{
		BX.CrmKanbanDragDrop.prototype.convertId = id;
		if (this.convertPopupInstance === null)
		{
			var targets = [];
			for (var key in BX.CrmLeadConversionScheme.messages)
			{
				targets.push(
					BX.create('DIV', {
						attrs: { 'data-type': key },
						text: BX.CrmLeadConversionScheme.messages[key],
						events: {
							click: BX.proxy(this.convertPopupClick, this)
						}
					})
				);
			}
			targets.push(
						BX.create('DIV', {
							attrs: { 'data-type': 'SELECT' },
							text: BX.message('CRM_KANBAN_CONVERT_SELECT_ENTITY'),
							events: {
								click: BX.proxy(this.convertPopupClick, this)
							}
						})
					);

			this.convertPopupInstance = new BX.PopupWindow('kanban_convert', window.body, {
					className: 'crm-kanban-popup-convert',
					offsetLeft : 0,
					lightShadow : true,
					closeIcon : true,
					overlay : true,
					titleBar: {content: BX.create('span', {html: ''})},
					draggable: true,
					closeByEsc : true,
					contentColor: 'white',
					content: BX.create('div', {
						attrs: { className: 'crm-kanban-popup-convert-list' },
						children: targets
					})
				});
			this.convertPopupInstance.setTitleBar(BX.message('CRM_KANBAN_CONVERT_POPUP_TITLE'));
		}
		this.convertPopupInstance.show();
	};
	BX.CrmKanbanDragDrop.prototype.convertPopupClick = function()
	{
		var scheme = BX.proxy_context.getAttribute('data-type');
		var id = this.convertId;
		if (scheme === 'SELECT')
		{
			BX.CrmLeadConverter.getCurrent().openEntitySelector(function(result){ BX.CrmLeadConverter.getCurrent().convert(id, result.config, '', result.data); });
		}
		else
		{
			BX.CrmLeadConverter.getCurrent().convert(id, BX.CrmLeadConversionScheme.createConfig(scheme), '');
		}
		this.convertPopupInstance.close();
	};

	BX.CrmKanbanDragDrop.prototype.onDragStop = function(x, y, event)
	{

		this.kanban.container.style.overflow = "auto";
		this.draggableItem.layout.container.classList.remove('crm-kanban-deal-item-block');

		var oldColumnId = this.draggableItem.columnId;

		document.body.style.height = '';
		document.body.style.overflow = '';
		document.body.style.width = '';

		if (this.draggableItem)
		{
			if (this.droppableItem)
			{
				this.droppableItem.layout.container.style = " ";
				this.droppableItem.layout.container.className = "crm-kanban-deal-item";
				var targetColumn = this.kanban.getColumn(this.droppableItem.columnId);
				var targetColumnId = this.droppableItem.columnId;
				this.kanban.moveItemToColum(this.draggableItem, targetColumn, this.droppableItem);
				this.droppableItem.layout.container.parentNode.classList.add('crm-kanban-items-blocked');
			}
			else if (this.droppableColumn)
			{
				var targetColumnId = this.droppableColumn.id;
				this.droppableColumn.layout.container.className = "crm-kanban-grid-item";
				this.kanban.moveItemToColum(this.draggableItem, this.droppableColumn);
				this.droppableColumn.layout.items.classList.add('crm-kanban-items-blocked');
			}
			else if (this.droppableZone)
			{
				var targetColumnId = this.droppableZone.getAttribute('data-id');
				this.droppableZone.className = "crm-kanban-dropzone-item";
				this.kanban.removeToColumnItem(this.draggableItem);
			}
			if (
				BX.CrmKanbanHelper.getInstance().getEntityType() === 'INVOICE' &&
				(targetColumnId === 'P' || this.droppableZone) &&
				oldColumnId != targetColumnId
			)
			{
				this.invoicePopup(this.draggableItem.id, targetColumnId, oldColumnId);
			}
			else
			{
				BX.CrmKanbanHelper.getInstance().moveState(this.draggableItem.id, targetColumnId, oldColumnId);
			}
			if (
				BX.CrmKanbanHelper.getInstance().getEntityType() === 'LEAD' &&
				targetColumnId === 'CONVERTED' &&
				oldColumnId != targetColumnId
			)
			{
				this.convertPopup(this.draggableItem.id);
			}
		}

		this.dropContainer.className = "crm-kanban-dropzone";
		this.stub.parentNode.removeChild(this.stub)
		this.stub = null;
		this.draggableItem = null;
		this.droppableColumn = null;
		this.droppableItem = null;
		this.droppableZone = null;
	};
}