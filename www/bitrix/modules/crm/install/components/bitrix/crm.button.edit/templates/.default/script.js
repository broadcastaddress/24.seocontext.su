var CrmButtonEditor = function(params) {

	this.init = function (params)
	{
		this.mess = params.mess;
		this.canRemoveCopyright = params.canRemoveCopyright || false;

		/* init button colors editing */
		this.colorEditor = new CrmButtonEditColors({
			caller: this,
			context: BX('BUTTON_COLOR_CONTAINER')
		});

		/* init widget editing */
		this.widgetManager = new CrmButtonEditWidgetManager({
			caller: this,
			context: BX('WIDGET_CONTAINER'),
			dictionaryPathEdit: params.dictionaryPathEdit
		});

		/* init location changing */
		this.locationManager = new CrmButtonEditLocation({
			caller: this,
			context: BX('LOCATION_CONTAINER')
		});

		/* init button view */
		this.locationManager = new CrmButtonEditButton({
			caller: this,
			context: BX('BUTTON_VIEW_CONTAINER')
		});

		/* init hello */
		this.hello = new CrmButtonEditHello({
			caller: this,
			context: BX('HELLO_CONTAINER')
		});

		this.initCopy();

		this.mainForm = BX('crm_button_main_form');
		this.subForm = BX('crm_button_sub_form');
		BX.bind(this.mainForm, 'submit', BX.proxy(this.copySubFormDataToMainForm, this));

		this.initButtons();
		this.initToolTips();

		if(!this.canRemoveCopyright)
		{
			BX.bind(BX('COPYRIGHT_REMOVED_CONT'), 'click', BX.proxy(function(e){
				if(!B24 || !B24['licenseInfoPopup'])
				{
					return;
				}

				BX.PreventDefault(e);
				B24.licenseInfoPopup.show(
					'crm_webform_copyright',
					this.mess.dlgRemoveCopyrightTitle,
					'<span>' + this.mess.dlgRemoveCopyrightText + '</span>'
				);
			}, this));
		}
	};

	this.initButtons = function()
	{
		var _this = this;
		var buttonCont = BX('BUTTON_COLOR_CONTAINER');
		this.loadedButtonCount = buttonCont.querySelectorAll('[data-b24-crm-button-widget]').length;
		this.blockNode = buttonCont.querySelector('[data-b24-crm-button-block]');
		this.blockInnerNode = buttonCont.querySelector('[data-b24-crm-button-block-inner]');
		BX.bind(this.blockNode, 'mouseover', function (e) {
			_this.showButtons();
		});
		BX.bind(this.blockNode, 'mouseout', function (e) {
			_this.hideButtons();
		});
	};

	this.showButtons = function()
	{
		BX.addClass(this.blockNode, 'b24-crm-button-block-active');
		BX.addClass(this.blockNode, 'b24-crm-button-block-active-' + this.loadedButtonCount);
		BX.removeClass(this.blockInnerNode, 'b24-crm-button-animate-' + this.loadedButtonCount);
	};

	this.hideButtons = function()
	{
		BX.removeClass(this.blockNode, 'b24-crm-button-block-active');
		BX.removeClass(this.blockNode, 'b24-crm-button-block-active-' + this.loadedButtonCount);
		BX.addClass(this.blockInnerNode, 'b24-crm-button-animate-' + this.loadedButtonCount);
	};

	this.copySubFormDataToMainForm = function (e)
	{
		BX.convert.nodeListToArray(this.subForm.elements).forEach(function (element) {

			var name = element.name;
			var value = '';
			var copiedElement = this.mainForm.querySelector('[name="' + name + '"]');
			if(!copiedElement)
			{
				copiedElement = document.createElement('INPUT');
				copiedElement.type = 'hidden';
				copiedElement.name = name;
				this.mainForm.appendChild(copiedElement);
			}

			switch (element.type)
			{
				case 'radio':
					var sourceElement = this.subForm.querySelector('[name="' + name + '"]:checked');
					if(sourceElement)
					{
						value = sourceElement.value;
					}
					break;
				default:
					value = element.value;
			}

			copiedElement.value = value;

		}, this);

		return true;
	};

	this.initToolTips = function(context)
	{
		context = context || document.body;
		this.popupTooltip = {};
		var nodeList = context.querySelectorAll(".crm-button-context-help");
		nodeList = BX.convert.nodeListToArray(nodeList);
		var _this = this;
		for (var i = 0; i < nodeList.length; i++)
		{
			if (nodeList[i].getAttribute('context-help') == 'y')
				continue;

			nodeList[i].setAttribute('data-id', i);
			nodeList[i].setAttribute('context-help', 'y');
			BX.bind(nodeList[i], 'mouseover', function(){
				var id = this.getAttribute('data-id');
				var text = this.getAttribute('data-text');

				_this.showTooltip(id, this, text);
			});
			BX.bind(nodeList[i], 'mouseout', function(){
				var id = this.getAttribute('data-id');
				_this.hideTooltip(id);
			});
		}
	};

	this.showTooltip = function(id, bind, text)
	{
		if (this.popupTooltip[id])
			this.popupTooltip[id].close();

		this.popupTooltip[id] = new BX.PopupWindow('bx-crm-site-button-tooltip', bind, {
			lightShadow: true,
			autoHide: false,
			darkMode: true,
			offsetLeft: 0,
			offsetTop: 2,
			bindOptions: {position: "top"},
			zIndex: 200,
			events : {
				onPopupClose : function() {this.destroy()}
			},
			content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
		});
		this.popupTooltip[id].setAngle({offset:13, position: 'bottom'});
		this.popupTooltip[id].show();

		return true;
	};

	this.hideTooltip = function(id)
	{
		this.popupTooltip[id].close();
		this.popupTooltip[id] = null;
	};

	this.initCopy = function ()
	{
		var context = BX('SCRIPT_CONTAINER');
		if(!context)
		{
			return;
		}

		var buttonAttribute = 'data-bx-webform-script-copy-btn';
		var copyButton = context.querySelector('[' + buttonAttribute + ']');
		var copyButtonText = context.querySelector('[data-bx-webform-script-copy-text]');
		if(!copyButton || !copyButtonText)
		{
			return;
		}

		BX.clipboard.bindCopyClick(copyButton, {text: copyButtonText, offsetLeft: 30});
	};

	this.appendTemplateNode = function(templateId, containerNode, replaceData)
	{
		var templateNode = this.getTemplateNode(templateId, replaceData);
		if (!templateNode)
		{
			return null;
		}
		containerNode.appendChild(templateNode);

		return templateNode;
	};

	this.getTemplateNode = function(templateId, replaceData)
	{
		var templateNode = BX('template-crm-button-' + templateId);
		if (!templateNode)
		{
			return null;
		}

		var addNode = document.createElement('div');
		var templateText = templateNode.innerHTML;
		if (replaceData)
		{
			for (var i in replaceData)
			{
				templateText = templateText.replace(new RegExp('%' + i + '%', 'g'), replaceData[i]);
			}
		}
		addNode.innerHTML = templateText;
		addNode = addNode.children[0];

		return addNode;
	};

	this.init(params);
};

function CrmButtonEditActivationManager(params)
{
	this.attributeItem = 'data-bx-crm-button-item';
	this.attributeActive = 'data-bx-crm-button-item-active';
	this.attributeActiveValue = 'data-bx-crm-button-item-active-val';

	this.context = params.context;

	this.init();
}
CrmButtonEditActivationManager.prototype =
{
	init: function()
	{
		var nodeList = this.context.querySelectorAll('[' + this.attributeActive + ']');
		nodeList = BX.convert.nodeListToArray(nodeList);
		nodeList.forEach(function (node) {
			BX.bind(node, 'click', BX.delegate(function (){
				this.toggleActive(node);
			}, this));
		}, this);
	},

	toggleActive: function(node)
	{
		var itemClass = 'crm-button-edit-channel-lines-container-active';
		var activeClassOn = 'crm-button-edit-channel-lines-title-on';
		var activeClassOff = 'crm-button-edit-channel-lines-title-off';

		var type = node.getAttribute(this.attributeActive);
		var itemNode = this.context.querySelector('[' + this.attributeItem + '="' + type + '"]');
		var valueNode = this.context.querySelector('[' + this.attributeActiveValue + '="' + type + '"]');
		var isActive = BX.hasClass(node, activeClassOn);
		if(isActive)
		{
			BX.addClass(node, activeClassOff);
			BX.removeClass(node, activeClassOn);
			BX.removeClass(itemNode, itemClass);

			valueNode.value ='N';
		}
		else
		{
			BX.addClass(node, activeClassOn);
			BX.removeClass(node, activeClassOff);
			BX.addClass(itemNode, itemClass);

			valueNode.value ='Y';
		}
	}
};

function CrmButtonEditPageManager(params)
{
	this.attributePages = 'data-crm-button-pages';
	this.attributePagesList = 'data-crm-button-pages-list';
	this.attributePagesPage = 'data-crm-button-pages-page';
	this.attributePagesAdd = 'data-crm-button-pages-btn-add';
	this.attributePagesDel = 'data-crm-button-pages-btn-del';

	this.mainObject = params.mainObject;
	this.pageChangeHandler = params.pageChangeHandler;
}
CrmButtonEditPageManager.prototype =
{
	initPages: function(context, replaceData)
	{
		var pageNodeList = context.querySelectorAll('['	+ this.attributePagesList + ']');
		pageNodeList = BX.convert.nodeListToArray(pageNodeList);
		pageNodeList.forEach(function (pageNode) {
			this.initPageButtons(pageNode, replaceData);
		}, this);
	},

	initPageButtons: function(pageNode, replaceData)
	{
		var pagesBtnAddNodeList = pageNode.querySelectorAll('['	+ this.attributePagesAdd + ']');
		pagesBtnAddNodeList = BX.convert.nodeListToArray(pagesBtnAddNodeList);
		pagesBtnAddNodeList.forEach(function (pagesBtnAddNode) {
			BX.bind(pagesBtnAddNode, 'click', BX.delegate(function(){
				this.onClickPageButton(pagesBtnAddNode, true, replaceData);
			}, this));
		}, this);

		var pagesBtnDelNodeList = pageNode.querySelectorAll('['	+ this.attributePagesDel + ']');
		pagesBtnDelNodeList = BX.convert.nodeListToArray(pagesBtnDelNodeList);
		pagesBtnDelNodeList.forEach(function (pagesBtnDelNode) {
			BX.bind(pagesBtnDelNode, 'click', BX.delegate(function(){
				this.onClickPageButton(pagesBtnDelNode, false, replaceData);
			}, this));
		}, this);

		var pagesInputNodeList = pageNode.querySelectorAll('input');
		pagesInputNodeList = BX.convert.nodeListToArray(pagesInputNodeList);
		pagesInputNodeList.forEach(function (pageInputNode) {
			BX.bind(pageInputNode, 'blur', BX.delegate(function(){
				this.onPageChange();
			}, this));
		}, this);
	},

	onPageChange: function()
	{
		if (!this.pageChangeHandler)
		{
			return;
		}

		setTimeout(this.pageChangeHandler, 400);
	},

	onClickPageButton: function(node, isAdd, replaceData)
	{
		if(isAdd)
		{
			//page
			var addNode;
			var pagesNode = BX.findParent(node, {attribute: this.attributePages});
			var templateNode = pagesNode.querySelector('script');
			if (templateNode)
			{
				addNode = document.createElement('div');
				addNode.innerHTML = templateNode.innerHTML;
				addNode = addNode.children[0];
			}
			else
			{
				addNode = this.mainObject.getTemplateNode('page', replaceData);
			}

			this.initPageButtons(addNode, replaceData);

			var listNode = BX.findParent(node, {attribute: this.attributePagesList});
			listNode.appendChild(addNode);
		}
		else
		{
			var delNode = BX.findParent(node, {attribute: this.attributePagesPage});
			BX.remove(delNode);
		}

		this.onPageChange();
	}
};

function CrmButtonEditLocation(params)
{
	this.caller = params.caller;
	this.context = params.context;

	this.attributeItem = 'data-bx-crm-button-loc';
	this.attributeVal = 'data-bx-crm-button-loc-val';
	this.className = 'crm-button-edit-sidebar-button-position-block-active-';

	this.init();
}
CrmButtonEditLocation.prototype =
{
	init: function ()
	{
		this.list = this.context.querySelectorAll('[' + this.attributeItem + ']');
		this.list = BX.convert.nodeListToArray(this.list);

		this.list.forEach(function(locNode){
			BX.bind(locNode, 'click', BX.delegate(function () {
				this.list.forEach(this.deactivate, this);
				this.activate(locNode);
			}, this));
		}, this);
	},

	deactivate: function (locNode)
	{
		this.activate(locNode, true);
	},

	activate: function (locNode, isDeActivate)
	{
		isDeActivate = isDeActivate || false;

		var valNode = locNode.querySelector('[' + this.attributeVal + ']');
		var val = valNode.value;

		if(isDeActivate)
		{
			BX.removeClass(locNode, this.className + val);
		}
		else
		{
			BX.addClass(locNode, this.className + val);
		}
	}
};

function CrmButtonEditColors(params)
{
	this.caller = params.caller;
	this.context = params.context;

	this.attributeBlock = 'data-b24-crm-button-block-button';
	this.attributeBlockBorder = 'data-b24-crm-button-block-border';
	this.attributePulse = 'data-b24-crm-button-pulse';
	this.attributeCont = 'data-b24-crm-button-block-inner';
	this.attributeIconItem = 'data-b24-crm-button-icon';
	this.classNameActive = 'b24-crm-button-inner-item-active';


	this.colorIconNode = BX('ICON_COLOR');
	this.colorBgNode = BX('BACKGROUND_COLOR');

	this.node = this.context.querySelector('[' + this.attributeBlock + ']');
	this.previewNode = this.node.querySelector('[' + this.attributeCont + ']');
	this.borderNode = this.context.querySelector('[' + this.attributeBlockBorder + ']');
	this.pulseNode = this.context.querySelector('[' + this.attributePulse + ']');

	var colorIconNodeList = this.node.querySelectorAll('[' + this.attributeIconItem + ']');
	colorIconNodeList = BX.convert.nodeListToArray(colorIconNodeList);
	this.previewItems = colorIconNodeList.map(function (iconNode) {
		return {
			'type': iconNode.getAttribute(this.attributeIconItem),
			'node': iconNode,
			'iconNode': iconNode.querySelector('path')
		};
	}, this);

	this.init();
}
CrmButtonEditColors.prototype =
{
	init: function()
	{
		this.initColorPicker();
	},

	changeActiveState: function(type, isActive)
	{
		this.previewItems.forEach(function (item) {

			if(item.type != type)
			{
				return;
			}

			if(isActive)
			{
				BX.addClass(item.node, this.classNameActive);
			}
			else
			{
				BX.removeClass(item.node, this.classNameActive);
			}

		}, this);

		this.previewNode.style.background = this.colorBgNode.value;
		this.previewItems.forEach(function (item) {
			item.iconNode.setAttribute('fill', this.colorIconNode.value);
		}, this);
	},

	updateButtonColors: function()
	{
		var bg = this.colorBgNode.value ? this.colorBgNode.value : '#00AEEF';
		this.previewNode.style.background = bg;
		this.borderNode.style.background = bg;
		this.pulseNode.style.border = '1px solid ' + bg;

		this.previewItems.forEach(function (item) {
			item.iconNode.setAttribute(
				'fill',
				this.colorIconNode.value ? this.colorIconNode.value : '#FFFFFF'
			);
		}, this);
	},

	initColorPicker: function()
	{
		this.picker = new window.BXColorPicker({'id': "picker", 'name': 'picker'});
		this.picker.Create();

		var _this = this;

		var clickHandler = function ()
		{
			var element = this;
			element.parentNode.appendChild(_this.picker.pCont);
			_this.picker.oPar.OnSelect = BX.proxy(function (color)
			{
				if(!color)
					color = '';

				element.value = color;
				var colorBox = BX.nextSibling(element);
				if(colorBox)
				{
					colorBox.style.background = color;
				}
				BX.fireEvent(element, 'change');
			}, _this);

			_this.picker.pCont.style.display = '';
			_this.picker.Close();
			_this.picker.Open(element);
			_this.picker.pCont.style.display = 'none';

		};

		var changeHandler = function()
		{
			var colorBox = BX.nextSibling(this);
			if (colorBox)
			{
				colorBox.style.background = this.value;
				_this.updateButtonColors();
			}
		};


		var inputList = this.context.querySelectorAll('[data-web-form-color-picker]');
		inputList = BX.convert.nodeListToArray(inputList);
		for(var i in inputList)
		{
			var inputCtrl = inputList[i];
			var colorBox = BX.nextSibling(inputCtrl);

			BX.bind(colorBox, 'click', BX.proxy(clickHandler, inputCtrl));
			BX.bind(inputCtrl, 'click', clickHandler);
			BX.bind(inputCtrl, "focus", clickHandler);

			BX.bind(inputCtrl, "bxchange", BX.delegate(changeHandler, inputCtrl));
			BX.fireEvent(inputCtrl, 'change');
		}
	}

};

function CrmButtonEditWidgetManager(params)
{
	this.caller = params.caller;
	this.context = params.context;

	this.dictionaryPathEdit = params.dictionaryPathEdit;

	this.attributeSelect = 'data-bx-crm-button-widget-select';
	this.attributeButtonEdit = 'data-bx-crm-button-widget-btn-edit';
	this.attributeSettingsButton = 'data-crm-button-item-settings-btn';
	this.attributeSettings = 'data-crm-button-item-settings';

	this.init();
}
CrmButtonEditWidgetManager.prototype =
{
	init: function()
	{
		this.pageManager = new CrmButtonEditPageManager({'mainObject': this.caller});
		this.pageManager.initPages(this.context);
		this.initSelect();
		this.activationManager = new CrmButtonEditActivationManager({context: this.context});


		var settingsBtnNodeList = this.context.querySelectorAll('[' + this.attributeSettingsButton + ']');
		settingsBtnNodeList = BX.convert.nodeListToArray(settingsBtnNodeList);
		settingsBtnNodeList.forEach(function (settingsBtnNode) {
			BX.bind(settingsBtnNode, 'click', BX.proxy(function() {
				var type = settingsBtnNode.getAttribute(this.attributeSettingsButton);
				var settingsNode = this.context.querySelector('[' + this.attributeSettings + '="' + type + '"]');
				BX.toggleClass(settingsNode, 'crm-button-edit-channel-lines-display-options-open');
			}, this));
		}, this);
	},

	initSelect: function()
	{
		var selectNodeList = this.context.querySelectorAll('[' + this.attributeSelect + ']');
		selectNodeList = BX.convert.nodeListToArray(selectNodeList);
		selectNodeList.forEach(function (selectNode) {

			BX.bind(selectNode, 'change', BX.proxy(function(){
				this.onChangeSelect(selectNode);
			}, this));

			this.onChangeSelect(selectNode);

		}, this);
	},

	onChangeSelect: function(node)
	{
		var type = node.getAttribute(this.attributeSelect);
		var isSelected = !!node.value;
		this.caller.colorEditor.changeActiveState(type, isSelected);

		var buttonNode = this.context.querySelector('[' + this.attributeButtonEdit + '="' + type + '"' + ']');
		if(isSelected)
		{
			var path = this.dictionaryPathEdit[type];
			buttonNode.href = path.path.replace(path.id, node.value);
		}
		buttonNode.style.display = isSelected ? 'inline-block' : 'none';
	}
};

function CrmButtonEditHello(params)
{
	this.caller = params.caller;
	this.context = params.context;

	this.blockCounter = 1000;

	this.init();
}
CrmButtonEditHello.prototype =
{
	init: function()
	{
		this.pageManager = new CrmButtonEditPageManager({
			'mainObject': this.caller,
			'pageChangeHandler': BX.proxy(this.onPagesChange, this)
		});
		this.activationManager = new CrmButtonEditActivationManager({context: this.context});
		this.customHelloContainer = BX('HELLO_MY_CONTAINER');
		this.defaultHelloContainer = BX('HELLO_ALL_CONTAINER');

		// init add block button
		BX.bind(this.context.querySelector('[data-b24-crm-hello-add]'), 'click', BX.proxy(this.addBlock, this));

		// init existed blocks
		this.blockAttribute = 'data-b24-crm-hello-block';
		var existedBlocks = this.customHelloContainer.querySelectorAll('[' + this.blockAttribute + ']');
		existedBlocks = BX.convert.nodeListToArray(existedBlocks);
		existedBlocks.forEach(function (existedBlock) {
			this.initBlock(existedBlock, true, false);
		}, this);

		// init default block
		var defaultBlock = this.defaultHelloContainer.querySelector('[data-b24-crm-hello-block]');
		this.initBlock(defaultBlock, true, true);
		this.onPagesChange();

		// init mode selector
		this.modeSelector = this.context.querySelector('[data-b24-crm-hello-mode]');
		BX.bind(this.modeSelector, 'click', BX.proxy(this.changeMode, this));
	},

	changeMode: function ()
	{
		var isInclude = this.modeSelector.value == 'INCLUDE';
		BX('HELLO_ALL_CONTAINER').style.display = isInclude ? 'none' : '';
	},

	addBlock: function ()
	{
		var replaceData = {
			'id': this.blockCounter++,
			'target': 'HELLO[CONDITIONS]',
			'mode': 'INCLUDE'
		};
		var node = this.caller.appendTemplateNode('hello', this.customHelloContainer, replaceData);
		this.initBlock(node);
	},

	initBlock: function (node, isExisted, isCommon)
	{
		isExisted = isExisted || false;
		isCommon = isCommon || false;
		if (!isExisted)
		{
			this.caller.initToolTips(node);
		}

		var replaceData = {
			'target': 'HELLO[CONDITIONS]',
			'type': node.getAttribute(this.blockAttribute),
			'mode': isCommon ? 'EXCLUDE' : 'INCLUDE'
		};
		this.pageManager.initPageButtons(node, replaceData);
		BX.bind(node.querySelector('[data-b24-hello-btn-remove]'), 'click', BX.delegate(function () {
			this.removeBlock(node);
			this.onPagesChange();
		}, this));

		/* init edit name */
		var nameClassName = 'crm-button-edit-name-state';
		var nodeNameText = node.querySelector('[data-b24-hello-name-text]');
		var nodeNameInput = node.querySelector('[data-b24-hello-name-input]');
		BX.bind(node.querySelector('[data-b24-hello-name-btn-edit]'), 'click', BX.delegate(function () {
			BX.addClass(node, nameClassName);
		}, this));
		BX.bind(node.querySelector('[data-b24-hello-name-btn-apply]'), 'click', BX.delegate(function () {
			nodeNameText.innerText = nodeNameInput.value.trim();
			BX.removeClass(node, nameClassName);
		}, this));

		/* init edit text */
		var textClassName = 'crm-button-edit-description-state';
		var nodeTextText = node.querySelector('[data-b24-hello-text-text]');
		var nodeTextInput = node.querySelector('[data-b24-hello-text-input]');
		BX.bind(node.querySelector('[data-b24-hello-text-btn-edit]'), 'click', BX.delegate(function () {
			BX.addClass(node, textClassName);
		}, this));
		BX.bind(node.querySelector('[data-b24-hello-text-btn-apply]'), 'click', BX.delegate(function () {
			nodeTextText.innerText = nodeTextInput.value.trim();
			BX.removeClass(node, textClassName);
		}, this));

		/* init edit text */
		var iconNode = node.querySelector('[data-b24-hello-icon]');
		var iconButtonNode = node.querySelector('[data-b24-hello-icon-btn]');
		var iconInputNode = node.querySelector('[data-b24-hello-icon-input]');
		var iconClickHandler = function (icon) {
			iconNode.style['background-image'] = "url(" + icon + ")";
			iconInputNode.value = icon;
		};
		BX.bind(iconNode, 'click', BX.delegate(function () {
			this.showAvatarPopup(iconNode, iconClickHandler);
		}, this));
		BX.bind(iconButtonNode, 'click', BX.delegate(function () {
			this.showAvatarPopup(iconNode, iconClickHandler);
		}, this));
	},

	showAvatarPopup: function (bindElement, callback)
	{
		var contentNode = BX('crm_button_edit_avatar_upload');
		this.avatarPopupClickHandler = callback;
		if (!this.avatarPopup)
		{
			this.avatarPopup = BX.PopupWindowManager.create(
				'crm_button_edit_avatar',
				null,
				{
					autoHide: true,
					lightShadow: true,
					closeIcon: true,
					closeByEsc: true,
					angle: true,
					content: contentNode
				}
			);

			var defIconAttribute = 'data-b24-crm-hello-def-icon';
			var iconNodes = contentNode.querySelectorAll('[' + defIconAttribute + ']');
			iconNodes = BX.convert.nodeListToArray(iconNodes);
			iconNodes.forEach(function (iconNode) {
				BX.bind(iconNode, 'click', BX.delegate(function () {
					var icon = iconNode.getAttribute(defIconAttribute);
					this.avatarPopupClickHandler.apply(this, [icon]);
					this.avatarPopup.close();
				}, this));
			}, this);
		}

		this.avatarPopup.setBindElement(bindElement);
		this.avatarPopup.show();
	},

	removeBlock: function (node)
	{
		BX.remove(node);
	},

	onPagesChange: function ()
	{
		var excludedPagesNode = this.defaultHelloContainer.querySelector('[data-b24-hello-excluded-pages]');
		if (!excludedPagesNode)
		{
			return;
		}

		var pageNodes = this.customHelloContainer.querySelectorAll('[data-crm-button-pages-list] input');
		pageNodes = BX.convert.nodeListToArray(pageNodes);
		var pages = pageNodes.map(function (pageNode) {
			return pageNode.value;
		}, this);

		excludedPagesNode.innerText = pages.filter(function (page) {
			return !!page;
		}).join("\n");
	}
};

function CrmButtonEditButton(params)
{
	this.caller = params.caller;
	this.context = params.context;

	this.container = this.context.querySelector('[data-b24-crm-button-cont]');
	this.attributeItem = 'data-bx-crm-button-item';

	this.init();
}
CrmButtonEditButton.prototype =
{
	init: function()
	{
		BX.addClass(
			this.context.querySelector('[data-b24-crm-button-pulse]'),
			'b24-widget-button-pulse-animate'
		);

		/*
		BX.bind(
			this.context.querySelector('[data-b24-crm-button-block-button]'),
			'click',
			BX.proxy(this.toggle, this)
		);

		 this.shadow.init({
		 'caller': this,
		 'shadowNode': this.context.querySelector('[data-b24-crm-button-shadow]')
		 });
		*/

		this.animatedNodes = BX.convert.nodeListToArray(this.context.querySelectorAll('[data-b24-crm-button-icon]'));
		this.animate();
	},
	toggle: function()
	{
		var className = 'b24-widget-button-top';

		if (BX.hasClass(this.container, className))
		{
			BX.removeClass(this.container, className);
			this.shadow.hide();
		}
		else
		{
			BX.addClass(this.container, className);
			this.shadow.show();
		}
	},
	animate: function()
	{
		var className = 'b24-widget-button-icon-animation';
		var curIndex = 0;
		this.animatedNodes.forEach(function (node, index) {
			if (BX.hasClass(node, className)) curIndex = index;
			BX.removeClass(node, className);
		}, this);

		curIndex++;
		curIndex = curIndex < this.animatedNodes.length ? curIndex : 0;
		BX.addClass(this.animatedNodes[curIndex], className);

		if (this.animatedNodes.length > 1)
		{
			var _this = this;
			setTimeout(function () {_this.animate();}, 1500);
		}
	},
	shadow: {
		init: function(params)
		{
			this.c = params.caller;
			this.shadowNode = params.shadowNode;

			var _this = this;
			BX.bind(this.shadowNode, 'click', function (e) {
				_this.hide();
			});
			BX.bind(document, 'keyup', function (e) {
				e = e || window.e;
				if (e.keyCode == 27)
				{
					_this.hide();
				}
			});
		},
		show: function()
		{
			BX.addClass(this.shadowNode, 'b24-widget-button-shadow-show');
		},
		hide: function()
		{
			BX.removeClass(this.shadowNode, 'b24-widget-button-shadow-show');
		}
	}
};