<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/**
 * @var $arParams array
 * @var $arResult array
 * @var $arResult['NAV_OBJECT'] CAllDBResult
 * @var $APPLICATION CMain
 * @var $USER CUser
 */
$numbersC = CVoxImplantConfig::GetPortalNumbers();
$portalNumber = CVoxImplantConfig::GetPortalNumber();
$numbers = array('' => GetMessage("VI_NUMBERS_DEFAULT")) + $numbersC;
?>
<div class="bx-vi-block bx-vi-options adm-workarea">
	<table cellpadding="0" cellspacing="0" border="0" class="bx-edit-tab-title" style="width: 100%; ">
		<tr>
			<td class="bx-form-title">
				<?=GetMessage('VI_NUMBERS_CONFIG')?>
			</td>
		</tr>
	</table>
	<form id="option_form">
		<input type="hidden" name="act" value="save">
		<dl>
			<dt><?=GetMessage("VI_NUMBERS_CONFIG_BACKPHONE")?></dt>
			<dd>
				<select name="portalNumber">
					<?foreach ($numbersC as $k => $v): ?>
					<option value="<?=$k?>" <? if ($portalNumber == $k): ?> selected <? endif; ?>><?=$v?></option>
					<?endforeach;?>
				</select>
				<span><?=GetMessage("VI_NUMBERS_CONFIG_BACKPHONE_TITLE")?></span>
			</dd>
		</dl>
		<a id="option_btn" href="javascript:void(0);" class="webform-small-button">
			<span class="webform-small-button-left"></span>
			<span class="webform-small-button-text"><?=GetMessage('VI_NUMBERS_SAVE'); ?></span>
			<span class="webform-small-button-right"></span>
		</a>
	</form>
</div>
<div class="bx-vi-block bx-vi-filter adm-workarea">
	<form id="search_form" action="<?=POST_FORM_ACTION_URI?>" method="GET">
		<input type="hidden" name="act" value="search">
		<span class="filter-field">
			<input name="FILTER" type="text" value="<?=htmlspecialcharsbx($arResult['FILTER'])?>" placeholder="<?=GetMessage('VI_NUMBERS_SEARCH')?>" />
			&nbsp;
			<a id="search_btn" href="#" class="webform-small-button">
				<span class="webform-small-button-left"></span>
				<span class="webform-small-button-text"><?=GetMessage('VI_NUMBERS_SEARCH_BTN'); ?></span>
				<span class="webform-small-button-right"></span>
			</a>
			<? if (!empty($arResult['FILTER'])): ?>
			<a id="clear_btn" href="#" class="webform-small-button">
				<span class="webform-small-button-left"></span>
				<span class="webform-small-button-text"><?=GetMessage('VI_NUMBERS_SEARCH_CANCEL'); ?></span>
				<span class="webform-small-button-right"></span>
			</a>
			<? endif; ?>
		</span>
		<input type="submit" style="visibility: hidden;" />
	</form>
</div>
<?

CJSCore::Init(array('admin_interface'));
$arResult['ROWS_COUNT'] = $arResult['NAV_OBJECT']->selectedRowsCount();
$arRows = array();
foreach ($arResult["USERS"] as $user)
{
	$arCols = array(
		'NAME' => '<table id="user_'.$user['ID'].'" style="border-collapse: collapse; border: none; ">
			<tr>
				<td style="border: none !important; padding: 0px !important; ">
					<div style="width: 32px; height: 32px; margin:2px; padding: 2px; box-shadow:0 0 2px 1px rgba(0, 0, 0, 0.1);">
						<a href="'.$user['DETAIL_URL'].'">'.$user['PHOTO_THUMB'].'</a>
					</div>
				</td>
				<td style="border: none !important; padding: 0px 0px 0px 7px !important; vertical-align: top; ">
					<a href="'.$user['DETAIL_URL'].'"><b>'.CUser::formatName(CSite::getNameFormat(), $user, true, true).'</b></a><br>
					'.htmlspecialcharsbx($user['WORK_POSITION']).'
				</td>
			</tr>
		</table>',
		'UF_PHONE_INNER' => '<span id="innerphone_'.$user['ID'].'">'.$user["UF_PHONE_INNER"].'</span>',
		'UF_VI_BACKPHONE' => '<span id="backphone_'.$user['ID'].'">'.(
				array_key_exists($user["UF_VI_BACKPHONE"], $numbers) ? $numbers[$user["UF_VI_BACKPHONE"]] : $user["UF_VI_BACKPHONE"]).'</span>'.
				'<span id="backphone_'.$user['ID'].'_value" style="display:none;">'.$user["UF_VI_BACKPHONE"].'</span>',
		'EDIT' => '<span id="create_'.$user['ID'].'">'.
				'<a href="#" onclick="window.BX.VoxImplantNumbers.edit('.$user['ID'].'); return false; ">'.GetMessage('VI_NUMBERS_EDIT').'</a>'.
			'</span>'
	);
	$arRows[$user['ID']] = array('data' => $user, 'columns' => $arCols);
}
$arResult['ROWS'] = $arRows;

$APPLICATION->IncludeComponent(
	'bitrix:main.interface.grid',
	'',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => array(
			array('id' => 'NAME', 'name' => GetMessage('VI_NUMBERS_GRID_NAME'), 'sort' => 'name', 'default' => true, 'editable' => false),
			array('id' => 'UF_PHONE_INNER', 'name' => GetMessage('VI_NUMBERS_GRID_CODE'), 'default' => true, 'editable' => false),
			array('id' => 'UF_VI_BACKPHONE', 'name' => GetMessage('VI_NUMBERS_GRID_PHONE'), 'default' => true, 'editable' => false),
			array('id' => 'EDIT', 'name' => '', 'default' => true, 'editable' => false),
		),
		'ROWS' => $arResult['ROWS'],
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
	)
);
?>
<script type="text/javascript">
BX.message({
	VI_NUMBERS_CREATE_TITLE : '<?=GetMessage("VI_NUMBERS_CREATE_TITLE")?>',
	VI_NUMBERS_ERR_AJAX : '<?=GetMessage("VI_NUMBERS_ERR_AJAX")?>',
	VI_NUMBERS_GRID_CODE : '<?=GetMessage("VI_NUMBERS_GRID_CODE")?>',
	VI_NUMBERS_GRID_PHONE : '<?=GetMessage("VI_NUMBERS_GRID_PHONE")?>',
	VI_NUMBERS_URL : '<?=$this->__component->GetPath()?>/ajax.php?act='
});

window.BX.VoxImplantNumbers.init();

BX.ready(function(){
	window.BX.VoxImplantNumbers = (!!window.BX.VoxImplantNumbers ? window.BX.VoxImplantNumbers : {});
	window.BX.VoxImplantNumbers.numbers = <?=CUtil::PhpToJSObject($numbers)?>;
});
</script>
