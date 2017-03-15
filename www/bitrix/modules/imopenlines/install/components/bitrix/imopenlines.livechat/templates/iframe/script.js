function LiveChatBackend(params)
{
	this.init = function(params)
	{
		this.initParams = params;

		this.timeout = {};

		// Post message
		this.postMessageDomain = null;
		this.postMessageOrigin = null;
		this.postMessageSource = null;

		// Process parameters from top window
		this.initFrameParameters();

		// Start listener of resize events
		this.initEvent();
	};

	this.initFrameParameters = function()
	{
		if(!this.isFrame())
		{
			return;
		}

		if(!window.location.hash)
		{
			return;
		}

		var frameParameters = {};
		try
		{
			frameParameters = JSON.parse(decodeURIComponent(window.location.hash.substring(1)));
		}
		catch (err){}

		if(frameParameters.domain)
		{
			this.postMessageDomain = frameParameters.domain;
		}
	};

	this.isFrame = function()
	{
		return window != window.top;
	};

	this.initEvent = function()
	{
		if(!this.isFrame())
		{
			return;
		}

		if(typeof window.postMessage === 'function')
		{
			BX.bind(window, 'message', BX.proxy(function(event){
				if(event && event.origin == this.postMessageDomain)
				{
					var data = {};
					try { data = JSON.parse(event.data); } catch (err){}
					if (data.action == 'init')
					{
						this.uniqueLoadId = data.uniqueLoadId;
						this.postMessageSource = event.source;
						this.postMessageOrigin = event.origin;
						this.postMessageStartShowed = data.showed;

						var initMessage = {};

						initMessage['uniqueLoadId'] = this.uniqueLoadId;
						initMessage['action'] = 'init';
						initMessage['title'] = this.initParams.LINE_NAME;
						initMessage['connectors'] = this.initParams.CONNECTORS;
						initMessage['queue'] = this.initParams.QUEUE;
						initMessage['showedDialog'] = this.showedDialog? 'Y':'N';
						if (this.initParams.ERROR_CODE)
						{
							initMessage['errorCode'] = this.initParams.ERROR_CODE;
						}
						this.sendDataToFrameHolder(initMessage);

						if (typeof(BXIM) != 'undefined')
						{
							BXIM.messenger.popupMessengerTextarea.value = data.textarea;
							BXIM.messenger.textareaCheckText();
							if (this.postMessageStartShowed)
							{
								setTimeout(function(){
									BXIM.messenger.popupMessengerTextarea.focus();
								}, 200);
							}
						}
						else
						{
							BX.addCustomEvent("onImInit", BX.delegate(function(BxImObject) {
								BxImObject.messenger.popupMessengerTextarea.value = data.textarea;
								BxImObject.messenger.textareaCheckText();
								if (this.postMessageStartShowed)
								{
									setTimeout(function(){
										BxImObject.messenger.popupMessengerTextarea.focus();
									}, 200);
								}
							}, this));
						}
					}
					else if (data.action == 'message')
					{
						BXIM.messenger.popupMessengerTextarea.value = data.text;
						BXIM.messenger.sendMessage(BXIM.messenger.currentTab);
					}
					else if (data.action == 'textareaFocus')
					{
						setTimeout(function(){
							if (typeof(BXIM) != 'undefined')
							{
								BXIM.messenger.popupMessengerTextarea.focus();
							}
						}, 200);
					}
				}
			}, this));
		}

		BX.addCustomEvent("onImTextareaFocus", BX.delegate(function(focus)
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = focus? 'textareaFocused': 'textareaBlured';
			this.sendDataToFrameHolder(initMessage);

		}, this));
		BX.addCustomEvent("onImDrawTab", BX.delegate(function(params)
		{
			if (params.hasMessage)
			{
				this.showedDialog = true;
				var initMessage = {};
				initMessage['uniqueLoadId'] = this.uniqueLoadId;
				initMessage['action'] = 'showDialog';
				this.sendDataToFrameHolder(initMessage);
			}
		}, this));
		BX.addCustomEvent("onImBeforeMessageSend", BX.delegate(function()
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = 'sendMessage';
			this.sendDataToFrameHolder(initMessage);
		}, this));
		BX.addCustomEvent("onImMessageRead", BX.delegate(function()
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = 'readMessage';
			this.sendDataToFrameHolder(initMessage);
		}, this));
		BX.addCustomEvent("onImMessageReceive", BX.delegate(function()
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = 'receiveMessage';
			this.sendDataToFrameHolder(initMessage);
		}, this));
		BX.addCustomEvent("onImResizeTextarea", BX.delegate(function()
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = 'resizeTextarea';
			this.sendDataToFrameHolder(initMessage);
		}, this));
		BX.addCustomEvent("onImOpenSmileMenu", BX.delegate(function()
		{
			var initMessage = {};
			initMessage['uniqueLoadId'] = this.uniqueLoadId;
			initMessage['action'] = 'openSmileMenu';
			this.sendDataToFrameHolder(initMessage);
		}, this));
	};

	this.sendDataToFrameHolder = function(data)
	{
		var encodedData = JSON.stringify(data);
		if (!this.postMessageOrigin)
		{
			clearTimeout(this.timeout[encodedData]);
			this.timeout[encodedData] = setTimeout(BX.delegate(function(){
				this.sendDataToFrameHolder(data);
			}, this), 10);
			return true;
		}
		if(typeof window.postMessage === 'function')
		{
			if(this.postMessageSource)
			{
				this.postMessageSource.postMessage(
					encodedData,
					this.postMessageOrigin
				);
			}
		}

		var ie = 0 /*@cc_on + @_jscript_version @*/;
		if(ie)
		{
			var url = window.location.hash.substring(1);
			top.location = url.substring(0, url.indexOf('#')) + '#' + encodedData;
		}
	};

	this.init(params);
}