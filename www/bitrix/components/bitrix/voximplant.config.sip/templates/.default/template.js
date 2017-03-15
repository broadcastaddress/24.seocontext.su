if (!BX.VoxImplant)
	BX.VoxImplant = function() {};

BX.VoxImplant.sip = function() {};

BX.VoxImplant.sip.init = function(params)
{
	BX.VoxImplant.sip.publicFolder = params.publicFolder;
	BX.VoxImplant.sip.number = BX('vi_sip_number');
	BX.VoxImplant.sip.server = BX('vi_sip_server');
	BX.VoxImplant.sip.login = BX('vi_sip_login');
	BX.VoxImplant.sip.password = BX('vi_sip_password');
	BX.VoxImplant.sip.button = BX('vi_sip_add');

	BX.bind(BX.VoxImplant.sip.button, 'click', BX.VoxImplant.sip.attachPhone);

	BX.ready(function(){
		BX.bind(BX('vi_sip_options'), 'click', function(e){
			if (BX('vi_sip_options_div').style.display == 'none')
			{
				BX.removeClass(BX(this), 'webform-button-create');
				BX('vi_sip_options_div').style.display = 'block';
			}
			else
			{
				BX.addClass(BX(this), 'webform-button-create');
				BX('vi_sip_options_div').style.display = 'none';
			}
			BX.PreventDefault(e);
		});
	});
};

BX.VoxImplant.sip.attachPhone = function()
{
	if (BX.VoxImplant.sip.blockAjax)
		return true;
	BX.removeClass(BX.VoxImplant.sip.button, 'webform-button-create');

	BX.showWait();
	BX.VoxImplant.sip.blockAjax = true;
	BX.ajax({
		url: '/bitrix/components/bitrix/voximplant.config.sip/ajax.php?VI_SIP_ATTACH',
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'VI_ADD': 'Y', 'NUMBER': BX.VoxImplant.sip.number.value, 'SERVER': BX.VoxImplant.sip.server.value, 'LOGIN': BX.VoxImplant.sip.login.value, 'PASSWORD': BX.VoxImplant.sip.password.value,  'VI_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (data.ERROR == '')
			{
				location.href = BX.VoxImplant.sip.publicFolder+'edit.php?ID='+data.RESULT;
			}
			else
			{
				BX.closeWait();
				BX.VoxImplant.sip.blockAjax = false;
				BX.addClass(BX.VoxImplant.sip.button, 'webform-button-create');
				alert(data.ERROR.split("<br> ").join("\n"));
			}
		}, this),
		onfailure: function(){
			BX.closeWait();
			BX.addClass(BX.VoxImplant.sip.button, 'webform-button-create');
			BX.VoxImplant.sip.blockAjax = false;
		}
	});
};

BX.VoxImplant.sip.connectModule = function(url)
{
	if (confirm(BX.message('VI_CONFIG_SIP_CONNECT_NOTICE')))
	{
		location.href = url;
	}
};

BX.VoxImplant.sip.unlinkPhone = function(id)
{
	if (BX.VoxImplant.sip.blockAjax)
		return true;

	if (!confirm(BX.message('VI_CONFIG_SIP_DELETE_CONFIRM')))
	{
		return false;
	}
	BX.showWait();

	BX.VoxImplant.sip.blockAjax = true;
	BX.ajax({
		url: '/bitrix/components/bitrix/voximplant.config.sip/ajax.php?VI_SIP_DELETE',
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'VI_DELETE': 'Y', 'CONFIG_ID': id, 'VI_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			BX.closeWait();
			BX.VoxImplant.sip.blockAjax = false;
			if (data.ERROR == '')
			{
				var elements = BX.findChildren(BX('phone-confing-sip-wrap'), {className : "tel-set-num-sip-block"}, false);
				if (elements.length == 1)
				{
					location.reload();
				}
				else
				{
					BX.remove(BX('phone-confing-'+id));
				}
			}
		}, this),
		onfailure: function(){
			BX.closeWait();
			BX.VoxImplant.sip.blockAjax = false;
		}
	});
};