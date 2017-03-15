(function(widow){
	window.BX.VoxImplantNumbers = (!!window.BX.VoxImplantNumbers ? window.BX.VoxImplantNumbers : {} );
	if (!!window.BX.VoxImplantNumbers["edit"])
		return;

	var mb = {
		dialog: new BX.CDialog({
			'content': '',
			'title': '',
			'height': -1,
			'width': 420,
			'resizable': false
		}),
		edit : function(id) {
			mb.dialog.hideNotify();
			mb.dialog.ClearButtons();
			mb.dialog.SetTitle(BX.message("VI_NUMBERS_CREATE_TITLE"));
			var select = [];
			window.BX.VoxImplantNumbers.numbers = (!!window.BX.VoxImplantNumbers.numbers ? window.BX.VoxImplantNumbers.numbers : {});
			for (var ii in window.BX.VoxImplantNumbers.numbers)
			{
				if (window.BX.VoxImplantNumbers.numbers.hasOwnProperty(ii))
				{
					select.push([
						'<option value="', ii ,'" ', (BX('backphone_' + id + '_value').innerHTML == ii ? 'selected' : ''),'>',
							window.BX.VoxImplantNumbers.numbers[ii],
						'</option>'
					].join(''));
				}
			}
			mb.dialog.SetContent([
			'<form>',
				'<table id="bitrixvoximplantnumbers" class="bx-interface-grid" cellspacing="0">',
					'<tr><td colspan="2">',
						'<input type="hidden" name="sessid" value="', BX.bitrix_sessid(), '" />',
						'<input type="hidden" name="USER_ID" value="', id, '" />',
						BX('user_'+id).parentNode.innerHTML,
					'</td></tr>',
					'<tr><td>',
						'<label for="innerphone_', id, '" />', BX.message('VI_NUMBERS_GRID_CODE'), ':</label>',
					'</td>',
					'<td>',
						'<input name="UF_PHONE_INNER" value="', BX('innerphone_'+id).innerHTML,'" style="width:150px;" />',
					'</td></tr>',
					'<tr><td>',
						'<label for="s_backphone_', id, '" />', BX.message('VI_NUMBERS_GRID_PHONE'), ':</label>',
					'</td>',
					'<td>',
						'<select name="UF_VI_BACKPHONE" id="s_backphone_', id, '"  style="width:157px;"/>',
							select.join(''),
						'</select>',
					'</td></tr>',
				'</table>',
			'</form>'
			].join(''));
			mb.dialog.SetButtons([
				{
					title: BX.CDialog.btnSave.title,
					id: BX.CDialog.btnSave.id,
					name: BX.CDialog.btnSave.name,
					className: BX.CDialog.btnSave.className,
					action: function () {
						var btn = this;

						mb.dialog.hideNotify();
						btn.disable();

						BX.ajax({
							method: 'POST',
							url: (BX.message("VI_NUMBERS_URL") + 'edit&USER_ID=' + id),
							data: mb.dialog.GetParameters(),
							dataType: 'json',
							onsuccess: function(json)
							{
								if (json.result == 'error')
								{
									mb.dialog.ShowError(json.error);
								}
								else
								{
									BX('innerphone_' + id).innerHTML = json.UF_PHONE_INNER;
									var res = (!!json.UF_VI_BACKPHONE && !!window.BX.VoxImplantNumbers.numbers[json.UF_VI_BACKPHONE] ? json.UF_VI_BACKPHONE : '');
									BX('backphone_' + id).innerHTML = window.BX.VoxImplantNumbers.numbers[res];
									BX('backphone_' + id + '_value').innerHTML = res;
									mb.dialog.Close();
								}
							},
							onfailure: function()
							{
								mb.dialog.ShowError(BX.message('VI_NUMBERS_ERR_AJAX'));
							}
						});
					}
				},
				BX.CDialog.btnCancel
			]);
			mb.dialog.Show();
		}
	};

	window.BX.VoxImplantNumbers.edit = mb.edit;
	window.BX.VoxImplantNumbers.init = function() {
		BX.ready(function(){
			BX.bind(BX('search_btn'), 'click', function() {
				BX.submit(BX('search_form'));
				return false;
			});
			BX.bind(BX('clear_btn'), 'click', function() {
				BX('search_form').elements.FILTER.value = '';
				BX.submit(BX('search_form'));
				return false;
			});
			BX.bind(BX('option_btn'), 'click', function() {
				var node = BX.create('SPAN', {props : {className : "wait"}});
				BX.addClass(BX('option_btn'), "webform-small-button-wait webform-small-button-active");
				this.appendChild(node);
				BX.ajax({
					method: 'POST',
					url: (BX.message("VI_NUMBERS_URL") + 'option'),
					data: {sessid : BX.bitrix_sessid(), portalNumber : BX('option_form').elements.portalNumber.value},
					dataType: 'json',
					onsuccess: function()
					{
						BX.removeClass(BX('option_btn'), "webform-small-button-wait webform-small-button-active");
						BX.remove(node);
					},
					onfailure: function()
					{
						BX.removeClass(BX('option_btn'), "webform-small-button-wait webform-small-button-active");
						BX.remove(node);
					}
				});
				return false;
			});
		});
	};

})(window);