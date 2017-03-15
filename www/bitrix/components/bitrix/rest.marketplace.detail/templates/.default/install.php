<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}


/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

?>
<form method="POST" action="<?echo POST_FORM_ACTION_URI;?>" id="APP_INSTALL_FORM">
	<?=bitrix_sessid_post()?>
</form>

<div style="width: 450px; padding: 5px; /*max-height: 400px;*/ overflow-y: auto; margin: 5px;">
	<div class="mp_dt_title_icon">
		<span class="mp_sc_ls_img">
<?
if($arResult["APP"]["ICON"]):
?>
			<span><img src="<?=htmlspecialcharsbx($arResult["APP"]["ICON"])?>" alt=""></span>
<?
else:
?>
			<span class="mp_empty_icon"></span>
<?
endif;
?>
		</span>
		<span class="mp_sc_ls_shadow"></span>
	</div>
	<h2 class="mp_dt_pp_title_section"><?=htmlspecialcharsbx($arResult["APP"]["NAME"]);?></h2>

	<p style="margin: 20px 0 0 125px;"><?=GetMessage("B24_APP_INSTALL_VERSION")?> <?=htmlspecialcharsbx($arResult["APP"]["VER"])?></p>

	<div style="clear:both"></div>

	<div class="mp_notify_message" style="display: none;" id="mp_error"></div>

	<hr class="mp_pp_hr" />
<?
if(is_array($arResult["APP"]["RIGHTS"])):
?>
	<div class="mp_pp_content">
		<p><?=GetMessage("BX24_APP_INSTALL_RIGHTS")?></p>
<?
	if(!empty($arResult["SCOPE_DENIED"])):
		$b24 = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
?>
		<div class="mp_notify_message"><?
			echo (\Bitrix\Main\Loader::includeModule("bitrix24") ? GetMessage("BX24_APP_INSTALL_MODULE_UNINSTALL_BITRIX24", array("#PATH_CONFIGS#" => CBitrix24::PATH_CONFIGS)) : GetMessage("BX24_APP_INSTALL_MODULE_UNINSTALL"))
		?></div>
<?
	endif;
?>
		<ul class="mp_pp_ul">
<?
	foreach($arResult["APP"]["RIGHTS"] as $key => $scope):
?>
			<li<?=(array_key_exists($key, $arResult['SCOPE_DENIED']) ? ' style="color:#d83e3e"' : '');?>><?=htmlspecialcharsbx($scope)?></li>
<?
	endforeach;
?>
		</ul>
	</div>
	<hr class="mp_pp_hr" />
<?
endif;
?>
</div>
<?
/*
?>

<script type="text/javascript">
	var myBX = (window.BX ? window.BX : (window.top.BX ? window.top.BX : null));
	if(myBX)
	{
		<?if($ID):?>
		window.top.Bitrix24AppInstallPopup.popup.setButtons([
			new myBX.PopupWindowButtonLink({
				text: '<?=GetMessage("B24_APP_INSTALL_CANCEL")?>',
				className: "popup-window-button-link-cancel",
				events: {
					click: function()
					{
						this.popupWindow.close();
					}
				}
			})
		]);

		//document.location.href = document.location.href;
		<? endif?>
	}
</script>
*/