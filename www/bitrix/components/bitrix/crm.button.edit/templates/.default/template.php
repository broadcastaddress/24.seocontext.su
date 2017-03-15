<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

if(!$arResult['BUTTON']['BACKGROUND_COLOR'])
{
	$arResult['BUTTON']['BACKGROUND_COLOR'] = '#00AEEF';
}

if(!$arResult['BUTTON']['ICON_COLOR'])
{
	$arResult['BUTTON']['ICON_COLOR'] = '#FFFFFF';
}

$serverAddress = \Bitrix\Crm\SiteButton\ResourceManager::getServerAddress();
$serverAddress .= $this->GetFolder() . '/images/';
$arResult['HELLO']['ICONS'] = array(
	array('PATH' => $serverAddress . 'upload-girl-mini-1.png'),
	array('PATH' => $serverAddress . 'upload-girl-mini-2.png'),
	array('PATH' => $serverAddress . 'upload-girl-mini-3.png'),
	array('PATH' => $serverAddress . 'upload-girl-mini-4.png'),
	array('PATH' => $serverAddress . 'upload-man-mini-1.png'),
	array('PATH' => $serverAddress . 'upload-man-mini-2.png'),
	array('PATH' => $serverAddress . 'upload-man-mini-3.png'),
	array('PATH' => $serverAddress . 'upload-man-mini-4.png'),
);

$helloDefCond = array(
	'ICON' => $arResult['HELLO']['ICONS'][0]['PATH'],
	'NAME' => Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_DEF_NAME'),
	'TEXT' => Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_DEF_TEXT'),
	'DELAY' => 1,
	'PAGES' => array(),
);
if (count($arResult['HELLO']['CONDITIONS']) == 0)
{
	$arResult['HELLO']['CONDITIONS'][] = $helloDefCond;
}


CJSCore::Init(array('clipboard'));
$APPLICATION->SetPageProperty(
	"BodyClass",
	$APPLICATION->GetPageProperty("BodyClass") . " no-paddings"
);



$getFormattedScript = function ($script)
{
	$script = htmlspecialcharsbx($script);
	$script = str_replace("\t", str_repeat('&nbsp;', 8), $script);
	return nl2br($script);
}
?>

<script>
	BX.ready(function(){
		new CrmButtonEditor(<?=Json::encode(array(
			'dictionaryPathEdit' => $arResult['BUTTON_ITEMS_DICTIONARY_PATH_EDIT'],
			'canRemoveCopyright' => $arResult['CAN_REMOVE_COPYRIGHT'],
			'mess' => array(
				'dlgRemoveCopyrightTitle' => Loc::getMessage('CRM_WEBFORM_EDIT_POPUP_LIMITED_TITLE'),
				'dlgRemoveCopyrightText' => Loc::getMessage('CRM_WEBFORM_EDIT_POPUP_LIMITED_TEXT'),
			)
		));?>);
	});
</script>

<?$this->SetViewTarget('pagetitle', 5);?>
	<a href="<?=htmlspecialcharsbx($arResult['PATH_TO_BUTTON_LIST'])?>" class="crm-button-edit-list-back"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SELECT_BACK_TO_BUTTON_LIST1')?></a>
<?$this->EndViewTarget();?>

<?
if (!empty($arResult['ERRORS']))
{
	?><div class="crm-button-edit-top-block"><?
	foreach ($arResult['ERRORS'] as $error)
	{
		ShowError($error);
	}
	?></div><?
}
?>


<form id="crm_button_main_form" method="post">
	<?=bitrix_sessid_post()?>



<?
function ShowIntranetButtonItemInterface($item)
{
	if(!$item)
	{
		return;
	}

	$type = htmlspecialcharsbx($item['TYPE']);
	$typeName = htmlspecialcharsbx($item['TYPE_NAME']);
	?>
	<div class="crm-button-edit-channel-lines-container <?=($item['ACTIVE'] == 'Y' ? "crm-button-edit-channel-lines-container-active" : "")?>" data-bx-crm-button-item="<?=$type?>">
		<div class="crm-button-edit-channel-lines-title-container">
			<div class="crm-button-edit-channel-lines-title-inner">
				<span class="crm-button-edit-channel-lines-title-icon crm-button-edit-channel-lines-title-icon-default-<?=$type?>"></span>
				<span class="crm-button-edit-channel-lines-title-item"><?=$typeName?>:</span>
			</div>
			<div class="crm-button-edit-channel-lines-title-activate-container <?=($item['ACTIVE'] == 'Y' ? 'crm-button-edit-channel-lines-title-on' : 'crm-button-edit-channel-lines-title-off')?>" data-bx-crm-button-item-active="<?=$type?>">
				<div class="crm-button-edit-channel-lines-title-activate-button-container">
					<span class="crm-button-edit-channel-lines-title-activate-button">
						<span class="crm-button-edit-channel-lines-title-activate-button-text"><?=Loc::getMessage('CRM_WEBFORM_EDIT_ON')?></span>
					</span>
					<span class="crm-button-edit-channel-lines-title-not-activate-button">
						<span class="crm-button-edit-channel-lines-title-activate-button-cursor"></span>
						<span class="crm-button-edit-channel-lines-title-not-activate-button-text"><?=Loc::getMessage('CRM_WEBFORM_EDIT_OFF')?></span>
					</span>
				</div>
			</div>

			<input type="hidden" data-bx-crm-button-item-active-val="<?=$type?>" name="ITEMS[<?=$type?>][ACTIVE]" value="<?=htmlspecialcharsbx($item['ACTIVE'])?>">

		</div><!--crm-button-edit-channel-lines-title-container-->
		<div class="crm-button-edit-channel-lines-inner-wrapper">
			<?if(count($item['LIST']) == 0):?>
				<div class="crm-button-edit-channel-make-line">
					<div class="crm-button-edit-channel-make-line-description">
						<span class="crm-button-edit-channel-make-line-description">
							<?=Loc::getMessage('CRM_WEBFORM_EDIT_CHANNEL_ADD_DESC')?>
						</span>
					</div>
					<div class="crm-button-edit-channel-make-line-button">
						<a href="<?=htmlspecialcharsbx($item['PATH_LIST'])?>" class="crm-button-edit-channel-make-line-button webform-small-button webform-small-button-blue">
							<?=Loc::getMessage('CRM_WEBFORM_EDIT_CHANNEL_SETUP')?>
						</a>
					</div>
				</div><!--crm-button-edit-channel-make-line-->
			<?else:?>
			<div class="crm-button-edit-channel-lines-inner-container">
				<div class="crm-button-edit-channel-lines-inner-create-container">
					<div class="crm-button-edit-channel-lines-inner-create-select-container">
						<select data-bx-crm-button-widget-select="<?=$type?>" id="ITEMS_<?=$type?>" name="ITEMS[<?=$type?>][EXTERNAL_ID]" class="crm-button-edit-channel-lines-inner-create-select-item">
							<?foreach($item['LIST'] as $external):?>
								<option value="<?=htmlspecialcharsbx($external['ID'])?>" <?=($external['SELECTED'] ? 'selected' : '')?>>
									<?=htmlspecialcharsbx($external['NAME'])?>
								</option>
							<?endforeach;?>
						</select>
					</div>
					<div class="crm-button-edit-channel-lines-inner-create-button-container">
						<a data-bx-crm-button-widget-btn-edit="<?=$type?>" href="" class="crm-button-edit-channel-lines-inner-create-button-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_CHANNEL_EDIT')?></a>
						<?if($item['PATH_ADD']):?>
							<a href="<?=htmlspecialcharsbx($item['PATH_ADD'])?>" class="crm-button-edit-channel-lines-inner-create-button-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_CHANNEL_ADD')?></a>
						<?endif;?>
					</div>
				</div><!--crm-button-edit-channel-lines-inner-create-container-->

			</div><!--crm-button-edit-channel-lines-inner-container-->

			<div class="crm-button-edit-channel-lines-display-options-container">
				<div class="crm-button-edit-channel-lines-display-options-inner-container">
					<div class="crm-button-edit-channel-lines-display-options-title-container">
						<span class="crm-button-edit-channel-lines-display-options-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_PAGE_SETTINGS')?>:</span>
					</div>
					<div class="crm-button-edit-channel-lines-display-options-settings-container">
						<div class="crm-button-edit-channel-lines-display-options-settings-button-container">
							<span data-crm-button-item-settings-btn="<?=$type?>" class="crm-button-edit-channel-lines-display-options-settings-button">
								<?=Loc::getMessage('CRM_WEBFORM_EDIT_PAGE_SETTINGS_SETUP')?>
							</span>
							<span class="crm-button-edit-channel-lines-display-options-settings-triangle">
								<span class="crm-button-edit-channel-lines-display-options-settings-triangle-item"></span>
							</span>
						</div>
						<div class="crm-button-edit-channel-lines-display-options-settings-descriptions-container">
							<span class="crm-button-edit-channel-lines-display-options-settings-item">
								<?if($item['PAGES_USES']):?>
									<?=Loc::getMessage('CRM_WEBFORM_EDIT_PAGE_SETTINGS_USER')?>
								<?else:?>
									<?=Loc::getMessage('CRM_WEBFORM_EDIT_PAGE_SETTINGS_DEFAULT')?>
								<?endif;?>
							</span>
						</div>
					</div>
				</div><!--crm-button-edit-channel-lines-display-options-inner-container-->
				<div data-crm-button-item-settings="<?=$type?>" class="crm-button-edit-channel-lines-display-options-links-container">
					<div class="crm-button-edit-channel-lines-display-options-links-for-all-container">

						<label for="ITEMS_<?=$type?>_PAGES_MODE_EXCLUDE" class="crm-button-edit-channel-lines-display-options-links-button-container">
							<input id="ITEMS_<?=$type?>_PAGES_MODE_EXCLUDE" class="crm-button-edit-channel-lines-display-options-links-button-item" type="radio" name="ITEMS[<?=$type?>][PAGES][MODE]" value="EXCLUDE" <?=($item['PAGES']['MODE'] != 'INCLUDE' ? 'checked' : '')?>>
							<span class="crm-button-edit-channel-lines-display-options-links-button-description"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_ON_ALL_PAGES')?>:</span>
						</label>

						<?ShowIntranetButtonItemPageInterface($type, $item['PAGES']['LIST']['EXCLUDE'], 'EXCLUDE');?>
					</div><!--crm-button-edit-channel-lines-display-options-links-for-all-container-->
					<div class="crm-button-edit-channel-lines-display-options-links-specified-container">
						<label for="ITEMS_<?=$type?>_PAGES_MODE_INCLUDE" class="crm-button-edit-channel-lines-display-options-links-button-container">
							<input class="crm-button-edit-channel-lines-display-options-links-button-item" type="radio" id="ITEMS_<?=$type?>_PAGES_MODE_INCLUDE" name="ITEMS[<?=$type?>][PAGES][MODE]" value="INCLUDE" <?=($item['PAGES']['MODE'] == 'INCLUDE' ? 'checked' : '')?>>
							<span class="crm-button-edit-channel-lines-display-options-links-button-description"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_ONLY_PAGES')?>:</span>
						</label>

						<?ShowIntranetButtonItemPageInterface($type, $item['PAGES']['LIST']['INCLUDE'], 'INCLUDE');?>
					</div><!--crm-button-edit-channel-lines-display-options-links-specified-container-->
					<div class="crm-button-edit-channel-lines-display-options-links-description-container">
						<div class="crm-button-edit-channel-lines-display-options-links-description-info">
							<span class="crm-button-edit-channel-lines-display-options-links-description-info-item">&#063;</span>
						</div>
						<span class="crm-button-edit-channel-lines-display-options-links-description-text">
							<?=nl2br(Loc::getMessage('CRM_WEBFORM_EDIT_HINT_ANY'))?>
						</span>
					</div><!--crm-button-edit-channel-lines-display-options-links-description-container-->
				</div><!--crm-button-edit-channel-lines-display-options-links-container-->
			</div><!--crm-button-edit-channel-lines-display-options-container-->
			<?endif;?>
		</div><!--crm-button-edit-channel-lines-inner-wrapper-->
	</div><!--crm-button-edit-channel-lines-container-->
	<?
}

function ShowIntranetButtonItemPage($type, $mode, $page, $target = 'ITEMS')
{
	$type = htmlspecialcharsbx($type);
	$mode = htmlspecialcharsbx($mode);
	$page = htmlspecialcharsbx($page);
	$target = htmlspecialcharsbx($target);
	?>
	<div data-crm-button-pages-page="null" class="crm-button-edit-item-pages-page crm-button-edit-channel-lines-display-options-links-block">
		<input placeholder="http://example.com/dir/page" type="text" name="<?=$target?>[<?=$type?>][PAGES][LIST][<?=$mode?>][]" value="<?=$page?>" class="crm-button-edit-channel-lines-display-options-links-block-item">

		<div class="crm-button-edit-item-pages-btn-add crm-button-edit-channel-lines-display-options-links-block-button">
			<span data-crm-button-pages-btn-add="" class="crm-button-edit-channel-lines-display-options-links-block-button-item crm-button-edit-add-icon"></span>
		</div>
		<div class="crm-button-edit-item-pages-btn-del crm-button-edit-channel-lines-display-options-links-block-button">
			<span data-crm-button-pages-btn-del="" class="crm-button-edit-channel-lines-display-options-links-block-button-item crm-button-edit-close-icon"></span>
		</div>
	</div>
	<?
}

function ShowIntranetButtonItemPageInterface($type, $list, $mode, $target = 'ITEMS')
{
	if(!is_array($list) || count($list) == 0)
	{
		$list = array('');
	}
	?>
	<div data-crm-button-pages="null">
		<script type="text/template">
			<?ShowIntranetButtonItemPage($type, $mode, '');?>
		</script>
		<div data-crm-button-pages-list="null">
		<?
		foreach ($list as $page)
		{
			ShowIntranetButtonItemPage($type, $mode, $page);
		}
		?>
		</div>
	</div>
	<?
}
?>

<div class="crm-button-edit-container">


	<?$this->SetViewTarget('sidebar');?>
	<form id="crm_button_sub_form">
	<div class="crm-button-edit-right-container">
		<div class="crm-button-edit-sidebar-title">
			<span class="crm-button-edit-sidebar-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_VIEW')?>:</span>
		</div><!--crm-button-edit-sidebar-title-->
		<div id="BUTTON_COLOR_CONTAINER">
			<div class="crm-button-edit-sidebar-button-preview-container">
				<div id="BUTTON_VIEW_CONTAINER" class="crm-button-edit-sidebar-button-preview-inner">
					<?
					$APPLICATION->IncludeComponent("bitrix:crm.button.button", ".default", array(
						'PREVIEW' => true,
						'LOCATION' => 1,
						'COLOR_ICON' => $arResult['BUTTON']['ICON_COLOR'],
						'COLOR_BACKGROUND' => $arResult['BUTTON']['BACKGROUND_COLOR'],
					));
					?>
				</div>
			</div><!--crm-button-edit-sidebar-button-preview-container-->
			<div class="crm-button-edit-sidebar-button-colorpicker-container">
				<div class="crm-button-edit-sidebar-button-colorpicker-inner">
					<div class="crm-button-edit-sidebar-button-colorpicker-block">
						<span class="crm-webform-edit-left-field-colorpick-text-container">
							<input size="7" id="BACKGROUND_COLOR" data-web-form-color-picker="" type="hidden" name="BACKGROUND_COLOR" value="<?=htmlspecialcharsbx($arResult['BUTTON']['BACKGROUND_COLOR'])?>">
							<span class="crm-webform-edit-left-field-colorpick-background-circle"></span>
							<span class="crm-web-form-color-picker crm-webform-edit-left-field-colorpick-background"><?=Loc::getMessage('CRM_BUTTON_EDIT_COLOR_BG')?></span>
						</span>

						<span class="crm-webform-edit-left-field-colorpick-text-container">
							<input size="7" id="ICON_COLOR" data-web-form-color-picker="" type="hidden" name="ICON_COLOR" value="<?=$arResult['BUTTON']['ICON_COLOR']?>">
							<span class="crm-webform-edit-left-field-colorpick-text-circle"></span>
							<span class="crm-web-form-color-picker crm-webform-edit-left-field-colorpick-text"><?=Loc::getMessage('CRM_BUTTON_EDIT_COLOR_ICON')?></span>
						</span>

						<?$APPLICATION->IncludeComponent(
							"bitrix:main.colorpicker",
							"",
							Array(
								"COMPONENT_TEMPLATE" => ".default",
								"ID" => "",
								"NAME" => "",
								"ONSELECT" => "",
								"SHOW_BUTTON" => "N"
							)
						);?>
					</div>
				</div>
			</div><!--crm-button-edit-sidebar-button-preview-container-->
		</div>
		<div class="crm-button-edit-sidebar-title">
			<span class="crm-button-edit-sidebar-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_CHOOSE_LOCATION')?>:</span>
		</div><!--crm-button-edit-sidebar-title-->
		<div id="LOCATION_CONTAINER" class="crm-button-edit-sidebar-button-position-container">
			<div class="crm-button-edit-sidebar-button-position-header">
				<div class="crm-button-edit-sidebar-button-position-header-dots">
					<span class="crm-button-edit-sidebar-button-position-header-dots-item"></span>
					<span class="crm-button-edit-sidebar-button-position-header-dots-item"></span>
					<span class="crm-button-edit-sidebar-button-position-header-dots-item"></span>
				</div>
				<div class="crm-button-edit-sidebar-button-position-header-line"></div>
			</div>
			<div class="crm-button-edit-sidebar-button-position-inner">
				<?foreach($arResult['BUTTON_LOCATION'] as $location):
					?>
					<label for="LOCATION_<?=htmlspecialcharsbx($location['ID'])?>" data-bx-crm-button-loc="" class="crm-button-edit-sidebar-button-position-block <?if($location['SELECTED']):?>crm-button-edit-sidebar-button-position-block-active-<?=htmlspecialcharsbx($location['ID'])?><?endif;?>" title="<?=htmlspecialcharsbx($location['NAME'])?>">
						<span class="crm-button-edit-arrow crm-button-edit-sidebar-button-position-arrow-<?=htmlspecialcharsbx($location['ID'])?>"></span>
						<input data-bx-crm-button-loc-val="" id="LOCATION_<?=htmlspecialcharsbx($location['ID'])?>" class="crm-button-edit-sidebar-button-position-block-item" type="radio" name="LOCATION" value="<?=htmlspecialcharsbx($location['ID'])?>" <?=($location['SELECTED'] ? 'checked' : '')?>>
					</label>
				<?endforeach?>
			</div>
		</div><!--crm-button-edit-sidebar-button-position-container-->
		<div class="crm-button-edit-sidebar-title">
			<span class="crm-button-edit-sidebar-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_DELAY')?>:</span>
		</div><!--crm-button-edit-sidebar-title-->
		<div class="crm-button-edit-sidebar-show-container">
			<div class="crm-button-edit-sidebar-show-inner">
				<div class="crm-button-edit-sidebar-show-item">
					<input type="radio" id="DELAY_CHOISE_NONE" name="DELAY_CHOISE" value="N" <?=($arResult['BUTTON']['DELAY'] <= 0 ? 'checked' : '')?>>
					<label for="DELAY_CHOISE_NONE"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_DELAY_AT_ONCE')?></label>
				</div>
				<br>
				<br>
				<span class="crm-button-edit-sidebar-show-item">
					<input type="radio" id="DELAY_CHOISE_TIME" name="DELAY_CHOISE" value="Y" <?=($arResult['BUTTON']['DELAY'] > 0 ? 'checked' : '')?>>
					<label for="DELAY_CHOISE_TIME"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SHOW_DELAY_DELAY')?></label>
				</span>
				<div class="crm-button-edit-sidebar-show-delay-container">
					<select class="crm-button-edit-sidebar-show-delay" name="DELAY">
						<?foreach($arResult['BUTTON_DELAY'] as $delayItem):?>
							<option value="<?=htmlspecialcharsbx($delayItem['ID'])?>" <?=($delayItem['SELECTED'] ? 'selected' : '')?>>
								<?=htmlspecialcharsbx($delayItem['NAME'])?>
							</option>
						<?endforeach?>
					</select>
				</div><!--crm-button-edit-sidebar-show-delay-container-->
			</div>
		</div><!--crm-button-edit-sidebar-show-container-->

		<div class="crm-button-edit-sidebar-title">
			<span class="crm-button-edit-sidebar-title-item"><?//=Loc::getMessage('CRM_WEBFORM_EDIT_REMOVE_LOGO_BX')?><!--:</span>-->
		</div><!--crm-button-edit-sidebar-title-->
		<!--<div class="crm-button-edit-sidebar-show-container">
			<div class="crm-button-edit-sidebar-show-inner">
				<label id="COPYRIGHT_REMOVED_CONT" for="COPYRIGHT_REMOVED" class="crm-button-copyright-disabled">
					<input id="COPYRIGHT_REMOVED" name="COPYRIGHT_REMOVED" <?/*=($arResult['BUTTON']['SETTINGS']['COPYRIGHT_REMOVED'] == 'Y' ? 'checked' : '')*/?> value="Y" type="checkbox" class="">
					<span class="">
						<span class="<?/*=($arResult['CAN_REMOVE_COPYRIGHT'] ? '' : 'crm-button-copyright-disabled')*/?>"><?/*=Loc::getMessage('CRM_WEBFORM_EDIT_REMOVE_LOGO')*/?></span>
					</span>
				</label>
			</div>
		</div>-->

	</div><!--crm-button-edit-right-container-->
	</form>
	<?$this->EndViewTarget();?>

	<div class="crm-button-edit-left-container">
		<div class="crm-button-edit-button-name-container">
			<div class="crm-button-edit-button-name">
				<input type="text" name="NAME" value="<?=htmlspecialcharsbx($arResult['BUTTON']['NAME'])?>" placeholder="<?=Loc::getMessage('CRM_WEBFORM_EDIT_NAME_PLACEHOLDER')?>" class="crm-button-edit-button-item">
			</div>
		</div>
		<div class="crm-button-edit-border"></div><!--crm-button-edit-border-->
		<div class="crm-button-edit-channel-container">
			<?if($arParams['ELEMENT_ID']):?>
				<div class="crm-button-edit-channel-field">
					<div class="crm-button-edit-channel-title">
						<span class="crm-button-edit-channel-title-item">
							<span><?=Loc::getMessage('CRM_WEBFORM_EDIT_SITE_SCRIPT')?></span>
							<span class="crm-button-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("CRM_WEBFORM_EDIT_SITE_SCRIPT_TIP"))?>">?</span>
						</span>
					</div>
					<div class="crm-button-edit-channel-content">
						<div id="SCRIPT_CONTAINER" class="crm-button-edit-insert-code-container">
							<div class="crm-button-edit-insert-code-inner">
								<div data-bx-webform-script-copy-text class="crm-button-edit-insert-code-item"><?=$getFormattedScript($arResult['SCRIPT'])?></div>
							</div>
							<div class="crm-button-edit-insert-code-button">
								<a data-bx-webform-script-copy-btn="" class="crm-button-edit-insert-code-button-item webform-small-button webform-small-button-blue">
									<?=Loc::getMessage('CRM_WEBFORM_EDIT_COPY_TO_CLIPBOARD')?>
								</a>
							</div>
						</div><!--crm-button-edit-sidebar-insert-code-container-->
					</div><!--crm-button-edit-channel-content-->
				</div><!--crm-button-edit-channel-field-->
			<?else:?>
				<div class="crm-button-edit-channel-description-container">
					<div class="crm-button-edit-channel-description-item">
						<?=nl2br(Loc::getMessage('CRM_WEBFORM_EDIT_DESC'))?>
					</div>
				</div><!--"crm-button-edit-channel-description-container-->
			<?endif;?>

			<div class="crm-button-edit-channel-field">
				<div class="crm-button-edit-channel-title">
					<span class="crm-button-edit-channel-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_CHANNELS')?></span>
					<?if($arParams['ELEMENT_ID']):?>
						<span class="crm-button-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("CRM_WEBFORM_EDIT_DESC"))?>">?</span>
					<?endif;?>
				</div>
				<div class="crm-button-edit-channel-content">
					<div id="WIDGET_CONTAINER">
						<?
						ShowIntranetButtonItemInterface($arResult['BUTTON_ITEM_OPEN_LINE']);
						ShowIntranetButtonItemInterface($arResult['BUTTON_ITEM_CRM_FORM']);
						ShowIntranetButtonItemInterface($arResult['BUTTON_ITEM_CALLBACK']);
						?>
					</div>
				</div><!--crm-button-edit-channel-content-->
			</div><!--crm-button-edit-channel-field-->



			<!---------- NEW BLOCK: AUTO HELLO ---------->

<?

function ShowIntranetButtonHelloPageInterface($type, $list, $mode, $target = 'ITEMS')
{
	if(!is_array($list) || count($list) == 0)
	{
		$list = array('');
	}
	?>
	<div data-crm-button-pages="null">
		<div data-crm-button-pages-list="null">
			<?
			foreach ($list as $page)
			{
				ShowIntranetButtonItemPage($type, $mode, $page, $target);
			}
			?>
		</div>
	</div>
	<?
}

function ShowIntranetButtonHelloBlock($params)
{
	$arResult = $params['arResult'];
	$pageList = $params['pageList'];
	$mode = htmlspecialcharsbx($params['mode']);
	$icon = htmlspecialcharsbx($params['icon']);
	$name = htmlspecialcharsbx($params['name']);
	$text = htmlspecialcharsbx($params['text']);
	$delay = intval($params['delay']);

	static $counter = 0;
	$id = isset($params['id']) ? $params['id'] : $counter++;
	$id = htmlspecialcharsbx($id);
	?>
	<div data-b24-crm-hello-block="<?=$id?>" class="crm-button-edit-constructor-block">
		<?if ($mode == 'INCLUDE'):?>
		<div class="crm-button-edit-constructor-close">
			<span data-b24-hello-btn-remove="" class="crm-button-edit-constructor-close-item"></span>
		</div>
		<?endif;?>
		<div id="crm-button-edit-popup-event" class="crm-button-edit-constructor-popup">
			<div data-b24-crm-hello-cont="" class="b24-widget-button-popup" style="border-color: <?=$arResult['COLOR_BACKGROUND']?>;">
				<div class="b24-widget-button-popup-inner">
					<div class="b24-widget-button-popup-image">
						<span data-b24-hello-icon="" class="b24-widget-button-popup-image-item" style="background-image: url(<?=$icon?>);"></span>
						<span data-b24-hello-icon-btn="" class="b24-widget-button-popup-image-edit"><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_CHANGE')?></span>
						<input data-b24-hello-icon-input type="hidden" name="HELLO[CONDITIONS][<?=$id?>][ICON]" value="<?=$icon?>">
					</div>
					<div class="b24-widget-button-popup-content">
						<div data-b24-hello-name="" class="b24-widget-button-popup-name">
							<div class="b24-widget-button-popup-content-block">
								<span data-b24-hello-name-text="" class="b24-widget-button-popup-name-item"><?=$name?></span>
								<span data-b24-hello-name-btn-edit="" class="b24-widget-button-popup-edit" title="<?=Loc::getMessage('CRM_WEBFORM_EDIT_EDIT')?>"></span>
							</div>
							<div class="b24-widget-button-popup-edit-block">
								<input name="HELLO[CONDITIONS][<?=$id?>][NAME]" data-b24-hello-name-input="" type="text" class="b24-widget-button-popup-input" value="<?=$name?>">
								<span data-b24-hello-name-btn-apply="" class="b24-widget-button-popup-edit-confirm" title="<?=Loc::getMessage('CRM_WEBFORM_EDIT_APPLY')?>"></span>
							</div>
						</div>
						<div data-b24-hello-text="" class="b24-widget-button-popup-description">
							<div class="b24-widget-button-popup-content-block">
								<span data-b24-hello-text-text="" class="b24-widget-button-popup-description-item"><?=$text?></span>
								<span data-b24-hello-text-btn-edit="" class="b24-widget-button-popup-edit" title="<?=Loc::getMessage('CRM_WEBFORM_EDIT_EDIT')?>"></span>
							</div>
							<div class="b24-widget-button-popup-edit-block">
								<textarea name="HELLO[CONDITIONS][<?=$id?>][TEXT]" data-b24-hello-text-input="" class="b24-widget-button-popup-textarea" name="" id="" cols="30" rows="10"><?=$text?></textarea>
								<span data-b24-hello-text-btn-apply="" class="b24-widget-button-popup-edit-confirm" title="<?=Loc::getMessage('CRM_WEBFORM_EDIT_APPLY')?>"></span>
							</div>
						</div>
					</div>
				</div>
				<div class="b24-widget-button-popup-triangle"></div>
			</div><!--b24-widget-button-popup-->
		</div><!--crm-button-edit-constructor-popup-->
		<div class="crm-button-edit-hello-select-description">
			<span class="crm-button-edit-hello-select-description-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_TIME_DELAY')?>:</span>
			<span class="crm-button-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("CRM_WEBFORM_EDIT_HELLO_TIME_DELAY_TIP"))?>">?</span>
		</div>
		<div class="crm-button-edit-hello-select crm-button-edit-select-delay">
			<select name="HELLO[CONDITIONS][<?=$id?>][DELAY]" type="text" class="crm-button-edit-hello-select-item">
				<option value=""><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_TIME_DELAY_NO')?></option>
				<?foreach($arResult['BUTTON_DELAY'] as $delayItem):?>
					<option value="<?=htmlspecialcharsbx($delayItem['ID'])?>" <?=($delayItem['ID'] == $delay ? 'selected' : '')?>>
						<?=htmlspecialcharsbx($delayItem['NAME'])?>
					</option>
				<?endforeach?>
			</select>
		</div>
		<div class="crm-button-edit-hello-select-description">
			<span class="crm-button-edit-hello-select-description-item">
				<?if ($mode == 'INCLUDE'):?>
					<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_PAGES_LIST')?>:
				<?else:?>
					<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_PAGES_EXCLUDE')?>:
				<?endif;?>
			</span>
			<span class="crm-button-context-help" data-text="<?=htmlspecialcharsbx(nl2br(Loc::getMessage('CRM_WEBFORM_EDIT_HINT_ANY')))?>">?</span>
		</div>
		<div class="crm-button-edit-hello-input">
			<?
			ShowIntranetButtonHelloPageInterface($id, $pageList, $mode, 'HELLO[CONDITIONS]')
			?>
		</div>
		<?if ($mode != 'INCLUDE'):?>
			<span class="crm-button-edit-hello-select-description-item">
				<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_PAGES_EXCLUDE_ADDITIONAL')?>
			</span>
			<div data-b24-hello-excluded-pages="" class="crm-button-edit-hello-pages"></div>
		<?endif;?>
	</div><!--crm-button-edit-constructor-block-->
	<?
}
?>

<script id="template-crm-button-page" type="text/html">
	<?ShowIntranetButtonItemPage('%type%', '%mode%', '', '%target%');?>
</script>

<script id="template-crm-button-hello" type="text/html">
	<?
	ShowIntranetButtonHelloBlock(array(
		'arResult' => $arResult,
		'pageList' => array(),
		'mode' => 'INCLUDE',
		'id' => '%id%',
		'icon' => $helloDefCond['ICON'],
		'name' => $helloDefCond['NAME'],
		'text' => $helloDefCond['TEXT'],
		'delay' => $helloDefCond['DELAY'],
	));
	?>
</script>

			<div id="HELLO_CONTAINER" class="crm-button-edit-channel-field">
				<div class="crm-button-edit-channel-title">
					<span class="crm-button-edit-channel-title-item">
						<span><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_TITLE')?></span>
					</span>
				</div>
				<div class="crm-button-edit-channel-content">
					<div class="crm-button-edit-channel-description-container">
						<div class="crm-button-edit-channel-description-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_DESC')?></div>
					</div><!--"crm-button-edit-channel-description-container-->

					<div data-bx-crm-button-item="sys-hello" class="crm-button-edit-channel-lines-container <?=($arResult['HELLO']['ACTIVE'] == 'Y' ? 'crm-button-edit-channel-lines-container-active' : '')?>">
						<div class="crm-button-edit-channel-lines-title-container">
							<div class="crm-button-edit-channel-lines-title-inner">
								<span class="crm-button-edit-channel-lines-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_TUNE')?>:</span>
							</div>
							<div class="crm-button-edit-channel-lines-title-activate-container <?=($arResult['HELLO']['ACTIVE'] == 'Y' ? 'crm-button-edit-channel-lines-title-on' : 'crm-button-edit-channel-lines-title-off')?>" data-bx-crm-button-item-active="sys-hello">
								<div class="crm-button-edit-channel-lines-title-activate-button-container">
									<span class="crm-button-edit-channel-lines-title-activate-button">
										<span class="crm-button-edit-channel-lines-title-activate-button-text"><?=Loc::getMessage('CRM_WEBFORM_EDIT_ON')?></span>
									</span>
									<span class="crm-button-edit-channel-lines-title-not-activate-button">
										<span class="crm-button-edit-channel-lines-title-activate-button-cursor"></span>
										<span class="crm-button-edit-channel-lines-title-not-activate-button-text"><?=Loc::getMessage('CRM_WEBFORM_EDIT_OFF')?></span>
									</span>
								</div>
							</div>

							<input type="hidden" data-bx-crm-button-item-active-val="sys-hello" name="HELLO[ACTIVE]" value="<?=htmlspecialcharsbx($arResult['HELLO']['ACTIVE'])?>">

						</div><!--crm-button-edit-channel-lines-title-container-->
						<div class="crm-button-edit-channel-lines-inner-wrapper">
							<div class="crm-button-edit-channel-lines-inner-container">

								<div class="crm-button-edit-hello-container">
									<div class="crm-button-edit-hello-title">
										<span class="crm-button-edit-hello-title-item"><?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_MODE')?></span>
									</div>
									<div class="crm-button-edit-hello-select">
										<select data-b24-crm-hello-mode="" name="HELLO[MODE]" type="text" class="crm-button-edit-hello-select-item">
											<option value="EXCLUDE" <?=($arResult['HELLO']['MODE'] == 'EXCLUDE' ? 'selected' : '')?>>
												<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_MODE_EXCLUDE')?>
											</option>
											<option value="INCLUDE" <?=($arResult['HELLO']['MODE'] == 'INCLUDE' ? 'selected' : '')?>>
												<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_MODE_INCLUDE')?>
											</option>
										</select>
									</div>
								</div><!--crm-button-edit-hello-container-->

								<div id="HELLO_ALL_CONTAINER" class="crm-button-edit-constructor-container" style="<?=($arResult['HELLO']['MODE'] == 'INCLUDE' ? 'display: none' : '')?>">
									<div class="crm-button-edit-hello-description">
										<span class="crm-button-edit-hello-title-item">
											<?=Loc::getMessage('CRM_WEBFORM_EDIT_SECTION_ALL')?>:
										</span>
									</div>
									<div>
										<?
										$helloCommon = $arResult['HELLO']['CONDITIONS'][0];
										ShowIntranetButtonHelloBlock(array(
											'arResult' => $arResult,
											'id' => 0,
											'mode' => 'EXCLUDE', // $condition['PAGES']['MODE']
											'pageList' => $helloCommon['PAGES']['LIST'],
											'icon' => $helloCommon['ICON'],
											'name' => $helloCommon['NAME'],
											'text' => $helloCommon['TEXT'],
											'delay' => $helloCommon['DELAY'],
										));
										?>
									</div>
								</div>
								<div class="crm-button-edit-constructor-container">
									<div class="crm-button-edit-hello-description">
										<span class="crm-button-edit-hello-title-item">
											<?=Loc::getMessage('CRM_WEBFORM_EDIT_SECTION_CUSTOM')?>:
										</span>
									</div>

									<div id="HELLO_MY_CONTAINER">
										<?for($num = 1, $cnt = count($arResult['HELLO']['CONDITIONS']); $num < $cnt; $num++):
											$condition = $arResult['HELLO']['CONDITIONS'][$num];
											ShowIntranetButtonHelloBlock(array(
												'arResult' => $arResult,
												'id' => $num,
												'mode' => 'INCLUDE', // $condition['PAGES']['MODE']
												'pageList' => $condition['PAGES']['LIST'],
												'icon' => $condition['ICON'],
												'name' => $condition['NAME'],
												'text' => $condition['TEXT'],
												'delay' => $condition['DELAY'],
											));
										endfor;?>
									</div>

									<div class="crm-button-edit-hello-add">
										<span data-b24-crm-hello-add="" class="crm-button-edit-hello-link-item">
											<?=Loc::getMessage('CRM_WEBFORM_EDIT_HELLO_ADD')?>
										</span>
									</div>

								</div><!--crm-button-edit-constructor-container-->

							</div><!--crm-button-edit-channel-lines-inner-container-->
						</div><!--crm-button-edit-channel-lines-inner-wrapper-->
					</div><!--crm-button-edit-channel-lines-container-->
				</div><!--crm-button-edit-channel-content-->
			</div><!--crm-button-edit-channel-field-->

			<!----------- END OF NEW BLOCK: AUTO HELLO ----------->

		</div><!--crm-button-edit-channel-container-->
		<div class="crm-button-edit-left-send-container">
			<div class="crm-button-edit-left-send-inner">
				<input type="submit" class="crm-button-edit-left-send-confirm webform-small-button webform-small-button-accept" value="<?=Loc::getMessage('CRM_WEBFORM_EDIT_SAVE')?>">
				<input type="submit" name="submit_apply" class="crm-button-edit-left-send-confirm webform-small-button webform-small-button-transparent" value="<?=Loc::getMessage('CRM_WEBFORM_EDIT_APPLY')?>">
				<a href="<?=htmlspecialcharsbx($arResult['PATH_TO_BUTTON_LIST'])?>" class="crm-button-edit-left-send-back webform-small-button webform-small-button-transparent"><?=Loc::getMessage('CRM_WEBFORM_EDIT_SELECT_BACK_TO_LIST')?></a>
			</div>
		</div><!---crm-button-edit-left-send-container->
	</div><!--crm-button-edit-left-container-->

</div><!--crm-button-edit-container-->


</form>

<div style="display: none;">
	<div id="crm_button_edit_avatar_upload" class="crm-button-edit-photo-upload">
		<div class="crm-button-edit-photo-upload-inner">
			<?foreach($arResult['HELLO']['ICONS'] as $icon):?>
				<span data-b24-crm-hello-def-icon="<?=htmlspecialcharsbx($icon['PATH'])?>" style="background-image: url(<?=htmlspecialcharsbx($icon['PATH'])?>)" class="crm-button-edit-photo-upload-item"></span>
			<?endforeach;?>
		</div>
	</div>
</div>