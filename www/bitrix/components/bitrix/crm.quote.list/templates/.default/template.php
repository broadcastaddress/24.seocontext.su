<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

use \Bitrix\Crm\Category\DealCategory;
use \Bitrix\Crm\Conversion\EntityConverter;

$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/crm-entity-show.css");
if(SITE_TEMPLATE_ID === 'bitrix24')
{
	$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/bitrix24/crm-entity-show.css");
}

CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/interface_grid.js');

if($arResult['NEED_FOR_REBUILD_QUOTE_ATTRS']):
	?><div id="rebuildQuoteAttrsMsg" class="crm-view-message">
		<?=GetMessage('CRM_QUOTE_REBUILD_ACCESS_ATTRS', array('#ID#' => 'rebuildQuoteAttrsLink', '#URL#' => $arResult['PATH_TO_PRM_LIST']))?>
	</div><?
endif;

if($arResult['NEED_FOR_TRANSFER_PS_REQUISITES']):
	?><div id="transferPSRequisitesMsg" class="crm-view-message">
	<?=Bitrix\Crm\Requisite\Conversion\PSRequisiteConverter::getIntroMessage(
	array(
		'EXEC_ID' => 'transferPSRequisitesLink', 'EXEC_URL' => '#',
		'SKIP_ID' => 'skipTransferPSRequisitesLink', 'SKIP_URL' => '#'
	)
)?>
	</div><?
endif;

$currentUserID = $arResult['CURRENT_USER_ID'];
$isInternal = $arResult['INTERNAL'];

$gridManagerID = $arResult['GRID_ID'].'_MANAGER';
$gridManagerCfg = array(
	'ownerType' => 'QUOTE',
	'gridId' => $arResult['GRID_ID'],
	'formName' => "form_{$arResult['GRID_ID']}",
	'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
	/*'activityEditorId' => $activityEditorID,
	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),*/
	'filterFields' => array()
);
$prefix = $arResult['GRID_ID'];
?>
<script type="text/javascript">
function crm_quote_delete_grid(title, message, btnTitle, path)
{
	var d;
	d = new BX.CDialog({
		title: title,
		head: '',
		content: message,
		resizable: false,
		draggable: true,
		height: 70,
		width: 300
	});

	var _BTN = [
		{
			title: btnTitle,
			id: 'crmOk',
			'action': function ()
			{
				window.location.href = path;
				BX.WindowManager.Get().Close();
			}
		},
		BX.CDialog.btnCancel
	];
	d.ClearButtons();
	d.SetButtons(_BTN);
	d.Show();
}
BX.ready(
	function()
	{
		if (BX('actallrows_<?=$arResult['GRID_ID']?>')) {
			BX.bind(BX('actallrows_<?=$arResult['GRID_ID']?>'), 'click', function () {
				var el_t = BX.findParent(this, {tagName : 'table'});
				var el_s = BX.findChild(el_t, {tagName : 'select'}, true, false);
				for (i = 0; i < el_s.options.length; i++)
				{
					if (el_s.options[i].value == 'tasks' || el_s.options[i].value == 'calendar')
						el_s.options[i].disabled = this.checked;
				}
				if (this.checked && (el_s.options[el_s.selectedIndex].value == 'tasks' || el_s.options[el_s.selectedIndex].value == 'calendar'))
					el_s.selectedIndex = 0;
			});
		}
	}
);
</script>
<?
	echo CCrmViewHelper::RenderQuoteStatusSettings();
	for ($i=0, $ic=sizeof($arResult['FILTER']); $i < $ic; $i++)
	{
		$filterField = $arResult['FILTER'][$i];
		$filterID = $filterField['id'];
		$filterType = $filterField['type'];
		$enable_settings = $filterField['enable_settings'];

		if($filterID === 'PRODUCT_ROW_PRODUCT_ID')
		{
			$productID = isset($arResult['DB_FILTER'][$filterID])
				? $arResult['DB_FILTER'][$filterID] : 0;

			ob_start();
			$APPLICATION->IncludeComponent('bitrix:crm.entity.selector',
				'',
				array(
					'ENTITY_TYPE' => 'PRODUCT',
					'INPUT_NAME' => $filterID,
					'INPUT_VALUE' => $productID,
					'FORM_NAME' => $arResult['GRID_ID'],
					'MULTIPLE' => 'N',
					'FILTER' => true
				),
				false,
				array('HIDE_ICONS' => 'Y')
			);
			$val = ob_get_contents();
			ob_end_clean();

			$arResult['FILTER'][$i]['type'] = 'custom';
			$arResult['FILTER'][$i]['value'] = $val;

			continue;
		}

		if ($filterType !== 'user')
		{
			continue;
		}

		$userID = isset($arResult['DB_FILTER'][$filterID])
			? (intval(is_array($arResult['DB_FILTER'][$filterID])
				? $arResult['DB_FILTER'][$filterID][0]
				: $arResult['DB_FILTER'][$filterID]))
			: 0;
		$userName = $userID > 0 ? CCrmViewHelper::GetFormattedUserName($userID) : '';

		ob_start();
		CCrmViewHelper::RenderUserCustomSearch(
			array(
				'ID' => "{$prefix}_{$filterID}_SEARCH",
				'SEARCH_INPUT_ID' => "{$prefix}_{$filterID}_NAME",
				'SEARCH_INPUT_NAME' => "{$filterID}_name",
				'DATA_INPUT_ID' => "{$prefix}_{$filterID}",
				'DATA_INPUT_NAME' => $filterID,
				'COMPONENT_NAME' => "{$prefix}_{$filterID}_SEARCH",
				'SITE_ID' => SITE_ID,
				'NAME_FORMAT' => $arParams['NAME_TEMPLATE'],
				'USER' => array('ID' => $userID, 'NAME' => $userName),
				'DELAY' => 100
			)
		);
		$val = ob_get_clean();

		$arResult['FILTER'][$i]['type'] = 'custom';
		$arResult['FILTER'][$i]['value'] = $val;

		$filterFieldInfo = array(
			'typeName' => 'USER',
			'id' => $filterID,
			'params' => array(
				'data' => array(
					'paramName' => "{$filterID}",
					'elementId' => "{$prefix}_{$filterID}"
				),
				'search' => array(
					'paramName' => "{$filterID}_name",
					'elementId' => "{$prefix}_{$filterID}_NAME"
				)
			)
		);

		if($enable_settings)
		{
			ob_start();
			CCrmViewHelper::RenderUserCustomSearch(
				array(
					'ID' => "FILTER_SETTINGS_{$prefix}_{$filterID}_SEARCH",
					'SEARCH_INPUT_ID' => "FILTER_SETTINGS_{$prefix}_{$filterID}_NAME",
					'SEARCH_INPUT_NAME' => "{$filterID}_name",
					'DATA_INPUT_ID' => "FILTER_SETTINGS_{$prefix}_{$filterID}",
					'DATA_INPUT_NAME' => $filterID,
					'COMPONENT_NAME' => "FILTER_SETTINGS_{$prefix}_{$filterID}_SEARCH",
					'SITE_ID' => SITE_ID,
					'NAME_FORMAT' => $arParams['NAME_TEMPLATE'],
					'USER' => array('ID' => $userID, 'NAME' => $userName),
					'ZINDEX' => 4000,
					'DELAY' => 100
				)
			);
			$arResult['FILTER'][$i]['settingsHtml'] = ob_get_clean();

			$filterFieldInfo['params']['data']['settingsElementId'] = "FILTER_SETTINGS_{$prefix}_{$filterID}";
			$filterFieldInfo['params']['search']['settingsElementId'] = "FILTER_SETTINGS_{$prefix}_{$filterID}_NAME";
		}

		$gridManagerCfg['filterFields'][] = $filterFieldInfo;
	}

	$arResult['GRID_DATA'] = array();
	$arColumns = array();
	foreach ($arResult['HEADERS'] as $arHead)
		$arColumns[$arHead['id']] = false;
	foreach($arResult['QUOTE'] as $sKey =>  $arQuote)
	{
		$jsTitle = isset($arQuote['~TITLE']) ? CUtil::JSEscape($arQuote['~TITLE']) : '';
		$jsShowUrl = isset($arQuote['PATH_TO_QUOTE_SHOW']) ? CUtil::JSEscape($arQuote['PATH_TO_QUOTE_SHOW']) : '';

		$arActions = array();
		$arActions[] =  array(
			'ICONCLASS' => 'view',
			'TITLE' => GetMessage('CRM_QUOTE_SHOW_TITLE'),
			'TEXT' => GetMessage('CRM_QUOTE_SHOW'),
			'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arQuote['PATH_TO_QUOTE_SHOW'])."');",
			'DEFAULT' => true
		);
		if ($arQuote['EDIT']):
			$arActions[] =  array(
				'ICONCLASS' => 'edit',
				'TITLE' => GetMessage('CRM_QUOTE_EDIT_TITLE'),
				'TEXT' => GetMessage('CRM_QUOTE_EDIT'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arQuote['PATH_TO_QUOTE_EDIT'])."');"
			);
			$arActions[] =  array(
				'ICONCLASS' => 'copy',
				'TITLE' => GetMessage('CRM_QUOTE_COPY_TITLE'),
				'TEXT' => GetMessage('CRM_QUOTE_COPY'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arQuote['PATH_TO_QUOTE_COPY'])."');"
			);
		endif;

		if($arResult['CAN_CONVERT']):
			if($arResult['CONVERSION_PERMITTED']):
				$arSchemeDescriptions = \Bitrix\Crm\Conversion\QuoteConversionScheme::getJavaScriptDescriptions(true);
				$arSchemeList = array();
				foreach($arSchemeDescriptions as $name => $description)
				{
					$arSchemeList[] = array(
						'ICONCLASS' => 'invoice',
						'TITLE' => $description,
						'TEXT' => $description,
						'ONCLICK' => "BX.CrmQuoteConverter.getCurrent().convert({$arQuote['ID']}, BX.CrmQuoteConversionScheme.createConfig('{$name}'), '".CUtil::JSEscape($APPLICATION->GetCurPage())."');"
					);
				}
				if(!empty($arSchemeList)):
					$bSep = true;
					$arActions[] = array('SEPARATOR' => true);
					$arActions[] =  array(
						'ICONCLASS' => 'invoice',
						'TITLE' => GetMessage('CRM_QUOTE_CREATE_ON_BASIS_TITLE'),
						'TEXT' => GetMessage('CRM_QUOTE_CREATE_ON_BASIS'),
						'MENU' => $arSchemeList
					);
				endif;
			else:
				$bSep = true;
				$arActions[] = array('SEPARATOR' => true);
				$arActions[] =  array(
					'ICONCLASS' => 'grid-lock',
					'TITLE' => GetMessage('CRM_QUOTE_CREATE_ON_BASIS_TITLE'),
					'TEXT' => GetMessage('CRM_QUOTE_CREATE_ON_BASIS'),
					'ONCLICK' => isset($arResult['CONVERSION_LOCK_SCRIPT']) ? $arResult['CONVERSION_LOCK_SCRIPT'] : ''
				);
			endif;
		endif;

		if ($arQuote['DELETE'] && !$arResult['INTERNAL']):
			$arActions[] = array('SEPARATOR' => true);
			$arActions[] =  array(
				'ICONCLASS' => 'delete',
				'TITLE' => GetMessage('CRM_QUOTE_DELETE_TITLE'),
				'TEXT' => GetMessage('CRM_QUOTE_DELETE'),
				'ONCLICK' => "crm_quote_delete_grid('".CUtil::JSEscape(GetMessage('CRM_QUOTE_DELETE_TITLE'))."', '".CUtil::JSEscape(GetMessage('CRM_QUOTE_DELETE_CONFIRM'))."', '".CUtil::JSEscape(GetMessage('CRM_QUOTE_DELETE'))."', '".CUtil::JSEscape($arQuote['PATH_TO_QUOTE_DELETE'])."')"
			);
		endif;

		$contactID = isset($arQuote['~CONTACT_ID']) ? intval($arQuote['~CONTACT_ID']) : 0;
		$companyID = isset($arQuote['~COMPANY_ID']) ? intval($arQuote['~COMPANY_ID']) : 0;
		$leadID = isset($arQuote['~LEAD_ID']) ? intval($arQuote['~LEAD_ID']) : 0;
		$dealID = isset($arQuote['~DEAL_ID']) ? intval($arQuote['~DEAL_ID']) : 0;
		$myCompanyID = isset($arQuote['~MYCOMPANY_ID']) ? intval($arQuote['~MYCOMPANY_ID']) : 0;

		$resultItem = array(
			'id' => $arQuote['ID'],
			'actions' => $arActions,
			'data' => $arQuote,
			'editable' => !$arQuote['EDIT'] ? ($arResult['INTERNAL'] ? 'N' : $arColumns) : 'Y',
			'columns' => array(
				'QUOTE_NUMBER' => '<a target="_self" href="'.$arQuote['PATH_TO_QUOTE_SHOW'].'">'.$arQuote['QUOTE_NUMBER'].'</a>',
				'QUOTE_SUMMARY' => CCrmViewHelper::RenderInfo1($arQuote['PATH_TO_QUOTE_SHOW'], isset($arQuote['QUOTE_NUMBER']) ? $arQuote['QUOTE_NUMBER'] : ('['.$arQuote['ID'].']'), $arQuote['TITLE'], '_self'),
				'QUOTE_CLIENT' => $contactID > 0
					? CCrmViewHelper::PrepareClientInfo(
						array(
							'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
							'ENTITY_ID' => $contactID,
							'TITLE' => isset($arQuote['~CONTACT_FORMATTED_NAME']) ? $arQuote['~CONTACT_FORMATTED_NAME'] : ('['.$contactID.']'),
							'PREFIX' => "QUOTE_{$arQuote['~ID']}",
							'DESCRIPTION' => isset($arQuote['~COMPANY_TITLE']) ? $arQuote['~COMPANY_TITLE'] : ''
						)
					) : ($companyID > 0
						? CCrmViewHelper::PrepareClientInfo(
							array(
								'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
								'ENTITY_ID' => $companyID,
								'TITLE' => isset($arQuote['~COMPANY_TITLE']) ? $arQuote['~COMPANY_TITLE'] : ('['.$companyID.']'),
								'PREFIX' => "QUOTE_{$arQuote['~ID']}"
							)
						) : ''),
				'COMPANY_ID' => isset($arQuote['COMPANY_INFO']) ? CCrmViewHelper::PrepareClientInfo($arQuote['COMPANY_INFO']) : '',
				'LEAD_ID' => isset($arQuote['LEAD_INFO']) ? CCrmViewHelper::PrepareClientInfo($arQuote['LEAD_INFO']) : '',
				'DEAL_ID' => isset($arQuote['DEAL_INFO']) ? CCrmViewHelper::PrepareClientInfo($arQuote['DEAL_INFO']) : '',
				'CONTACT_ID' => isset($arQuote['CONTACT_INFO']) ? CCrmViewHelper::PrepareClientInfo($arQuote['CONTACT_INFO']) : '',
				'MYCOMPANY_ID' => isset($arQuote['MY_COMPANY_INFO']) ? CCrmViewHelper::PrepareClientInfo($arQuote['MY_COMPANY_INFO']) : '',
				'WEBFORM_ID' => isset($arResult['WEBFORM_LIST'][$arQuote['WEBFORM_ID']]) ? $arResult['WEBFORM_LIST'][$arQuote['WEBFORM_ID']] : $arQuote['WEBFORM_ID'],
				'TITLE' => '<a target="_self" href="'.$arQuote['PATH_TO_QUOTE_SHOW'].'">'.$arQuote['TITLE'].'</a>',
				'CLOSED' => $arQuote['CLOSED'] == 'Y' ? GetMessage('MAIN_YES') : GetMessage('MAIN_NO'),
				'ASSIGNED_BY' => $arQuote['~ASSIGNED_BY_ID'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "QUOTE_{$arQuote['~ID']}_RESPONSIBLE",
							'USER_ID' => $arQuote['~ASSIGNED_BY_ID'],
							'USER_NAME'=> $arQuote['ASSIGNED_BY'],
							'USER_PROFILE_URL' => $arQuote['PATH_TO_USER_PROFILE']
						)
					) : '',
				'COMMENTS' => htmlspecialcharsback($arQuote['COMMENTS']),
				'SUM' => '<nobr>'.$arQuote['FORMATTED_OPPORTUNITY'].'</nobr>',
				'OPPORTUNITY' => '<nobr>'.$arQuote['OPPORTUNITY'].'</nobr>',
				'DATE_CREATE' => '<nobr>'.FormatDate('SHORT', MakeTimeStamp($arQuote['DATE_CREATE'])).'</nobr>',
				'DATE_MODIFY' => '<nobr>'.FormatDate('SHORT', MakeTimeStamp($arQuote['DATE_MODIFY'])).'</nobr>',
				'CURRENCY_ID' => CCrmCurrency::GetCurrencyName($arQuote['CURRENCY_ID']),
				'PRODUCT_ID' => isset($arQuote['PRODUCT_ROWS']) ? htmlspecialcharsbx(CCrmProductRow::RowsToString($arQuote['PRODUCT_ROWS'])) : '',
				'STATUS_ID' => CCrmViewHelper::RenderQuoteStatusControl(
					array(
						'PREFIX' => "{$arResult['GRID_ID']}_PROGRESS_BAR_",
						'ENTITY_ID' => $arQuote['~ID'],
						'CURRENT_ID' => $arQuote['~STATUS_ID'],
						'SERVICE_URL' => '/bitrix/components/bitrix/crm.quote.list/list.ajax.php'
					)
				),
				'CREATED_BY' => $arQuote['~CREATED_BY'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "QUOTE_{$arQuote['~ID']}_CREATOR",
							'USER_ID' => $arQuote['~CREATED_BY'],
							'USER_NAME'=> $arQuote['CREATED_BY_FORMATTED_NAME'],
							'USER_PROFILE_URL' => $arQuote['PATH_TO_USER_CREATOR']
						)
					) : '',
				'MODIFY_BY' => $arQuote['~MODIFY_BY'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "QUOTE_{$arQuote['~ID']}_MODIFIER",
							'USER_ID' => $arQuote['~MODIFY_BY'],
							'USER_NAME'=> $arQuote['MODIFY_BY_FORMATTED_NAME'],
							'USER_PROFILE_URL' => $arQuote['PATH_TO_USER_MODIFIER']
						)
					) : '',
				'ENTITIES_LINKS' => $arQuote['FORMATTED_ENTITIES_LINKS'],
				'CLOSEDATE' => empty($arQuote['CLOSEDATE']) ? '' : '<nobr>'.$arQuote['CLOSEDATE'].'</nobr>'
			) + $arResult['QUOTE_UF'][$sKey]
		);
		if ($arQuote['IN_COUNTER_FLAG'] === true)
		{
			if ($resultItem['columnClasses']['CLOSEDATE'] != '')
				$resultItem['columnClasses']['CLOSEDATE'] .= ' ';
			else
				$resultItem['columnClasses']['CLOSEDATE'] = '';
			$resultItem['columnClasses']['CLOSEDATE'] .= 'crm-list-quote-today';
		}
		if ($arQuote['EXPIRED_FLAG'] === true)
		{
			if ($resultItem['columnClasses']['CLOSEDATE'] != '')
				$resultItem['columnClasses']['CLOSEDATE'] .= ' ';
			else
				$resultItem['columnClasses']['CLOSEDATE'] = '';
			$resultItem['columnClasses']['CLOSEDATE'] .= 'crm-list-quote-time-expired';
		}

		$arResult['GRID_DATA'][] = &$resultItem;
		unset($resultItem);
	}
	$APPLICATION->IncludeComponent('bitrix:main.user.link',
		'',
		array(
			'AJAX_ONLY' => 'Y',
		),
		false,
		array('HIDE_ICONS' => 'Y')
	);

$isEditable = $arResult['PERMS']['WRITE'] && !$arResult['INTERNAL'];
$actionHtml = '';
if($isEditable)
{
	// Setup STATUS_ID -->
	$statuses = '<div id="ACTION_STATUS_WRAPPER" style="display:none;"><select name="ACTION_STATUS_ID" size="1">';
	$statuses .= '<option value="" title="'.GetMessage('CRM_STATUS_INIT').'" selected="selected">'.GetMessage('CRM_STATUS_INIT').'</option>';
	foreach($arResult['STATUS_LIST_WRITE'] as $id => $name):
		$name = htmlspecialcharsbx($name);
		$statuses .= '<option value="'.$id.'" title="'.$name.'">'.$name.'</option>';
	endforeach;
	$statuses .= '</select></div>';

	$actionHtml .= $statuses;
	// Setup STATUS_ID -->

	// Setup ASSIGNED_BY_ID -->
	ob_start();
	CCrmViewHelper::RenderUserSearch(
		"{$prefix}_ACTION_ASSIGNED_BY",
		"ACTION_ASSIGNED_BY_SEARCH",
		"ACTION_ASSIGNED_BY_ID",
		"{$prefix}_ACTION_ASSIGNED_BY",
		SITE_ID,
		$arParams['~NAME_TEMPLATE'],
		500
	);
	$actionHtml .= '<div id="ACTION_ASSIGNED_BY_WRAPPER" style="display:none;">'.ob_get_clean().'</div>';
	// <-- Setup ASSIGNED_BY_ID

	// Setup OPENED -->
	$opened = '<div id="ACTION_OPENED_WRAPPER" style="display:none;"><select name="ACTION_OPENED" size="1">';
	$opened .= '<option value="Y">'.GetMessage("CRM_QUOTE_MARK_AS_OPENED_YES").'</option>';
	$opened .= '<option value="N">'.GetMessage("CRM_QUOTE_MARK_AS_OPENED_NO").'</option>';
	$opened .= '</select></div>';
	$actionHtml .= $opened;
	// Setup OPENED -->

	$actionHtml .= '
		<script type="text/javascript">
			BX.ready(
				function(){
				var select = BX.findChild(BX.findPreviousSibling(BX.findParent(BX("ACTION_ASSIGNED_BY_WRAPPER"), { "tagName":"td" })), { "tagName":"select" });
				BX.bind(
					select,
					"change",
					function(e){
						BX("ACTION_STATUS_WRAPPER").style.display = select.value === "set_status" ? "" : "none";
						BX("ACTION_ASSIGNED_BY_WRAPPER").style.display = select.value === "assign_to" ? "" : "none";
						BX("ACTION_OPENED_WRAPPER").style.display = select.value === "mark_as_opened" ? "" : "none";
					}
				)
			}
		);
		</script>';
}

$arActionList = array();
if($isEditable)
{
	if($arResult['PERMS']['WRITE'])
	{
		$arActionList['set_status'] = GetMessage('CRM_QUOTE_SET_STATUS');
		$arActionList['assign_to'] = GetMessage('CRM_QUOTE_ASSIGN_TO');
		$arActionList['mark_as_opened'] = GetMessage('CRM_QUOTE_MARK_AS_OPENED');
	}
}

if($arResult['ENABLE_TOOLBAR'])
{
	$addButton =array(
		'TEXT' => GetMessage('CRM_QUOTE_LIST_ADD_SHORT'),
		'TITLE' => GetMessage('CRM_QUOTE_LIST_ADD'),
		'LINK' => $arResult['PATH_TO_QUOTE_ADD'],
		'ICON' => 'btn-new'
	);

	if($arResult['ADD_EVENT_NAME'] !== '')
	{
		$addButton['ONCLICK'] = "BX.onCustomEvent(window, '{$arResult['ADD_EVENT_NAME']}')";
	}

	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.toolbar',
		'',
		array(
			'TOOLBAR_ID' => strtolower($arResult['GRID_ID']).'_toolbar',
			'BUTTONS' => array($addButton)
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}

//region Navigation
$navigationHtml = '';
if(isset($arResult['PAGINATION']) && is_array($arResult['PAGINATION']))
{
	ob_start();
	$APPLICATION->IncludeComponent(
		'bitrix:crm.pagenavigation',
		'',
		isset($arResult['PAGINATION']) ? $arResult['PAGINATION'] : array(),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
	$navigationHtml = ob_get_contents();
	ob_end_clean();
}
//endregion

$APPLICATION->IncludeComponent(
	'bitrix:crm.interface.grid',
	'',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'HEADERS' => $arResult['HEADERS'],
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ROWS' => $arResult['GRID_DATA'],
		'FOOTER' => array(
			array(
				'type' => 'row_count',
				'title' => GetMessage('CRM_ALL'),
				'show_row_count' => GetMessage('CRM_SHOW_ROW_COUNT'),
				'service_url' => '/bitrix/components/bitrix/crm.quote.list/list.ajax.php?'.bitrix_sessid_get()
			),
			array('custom_html' => '<td>'.$navigationHtml.'</td>')
		),
		'EDITABLE' =>  $isEditable ? 'Y' : 'N',
		'ACTIONS' => array(
			'delete' => $arResult['PERMS']['DELETE'],
			'custom_html' => $actionHtml,
			'list' => $arActionList
		),
		'ACTION_ALL_ROWS' => true,
		'NAV_OBJECT' => $arResult['DB_LIST'],
		'FORM_ID' => $arResult['FORM_ID'],
		'TAB_ID' => $arResult['TAB_ID'],
		'AJAX_MODE' => $arResult['AJAX_MODE'],
		'AJAX_ID' => $arResult['AJAX_ID'],
		'AJAX_OPTION_JUMP' => $arResult['AJAX_OPTION_JUMP'],
		'AJAX_OPTION_HISTORY' => $arResult['AJAX_OPTION_HISTORY'],
		'AJAX_LOADER' => isset($arParams['AJAX_LOADER']) ? $arParams['AJAX_LOADER'] : null,
		'FILTER' => $arResult['FILTER'],
		'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
		'FILTER_TEMPLATE' => 'flat',
		'FILTER_NAVIGATION_BAR' => array(
			'ITEMS' => array(
				array(
					'icon' => 'table',
					'id' => 'list',
					'name' => GetMessage('CRM_QUOTE_LIST_FILTER_NAV_BUTTON_LIST'),
					'active' => true,
					'url' => $arResult['PATH_TO_QUOTE_LIST']
				),
				array(
					'icon' => 'kanban',
					'id' => 'kanban',
					'name' => GetMessage('CRM_QUOTE_LIST_FILTER_NAV_BUTTON_KANBAN'),
					'active' => false,
					'url' => $arParams['PATH_TO_QUOTE_KANBAN']
				)
			),
			'BINDING' => array(
				'category' => 'crm.navigation',
				'name' => 'index',
				'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
			)
		),
		'MANAGER' => array(
			'ID' => $gridManagerID,
			'CONFIG' => $gridManagerCfg
		)
	),
	$component
);
?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmEntityType.captions =
			{
				"<?=CCrmOwnerType::LeadName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Lead)?>",
				"<?=CCrmOwnerType::ContactName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Contact)?>",
				"<?=CCrmOwnerType::CompanyName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Company)?>",
				"<?=CCrmOwnerType::DealName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Deal)?>",
				"<?=CCrmOwnerType::InvoiceName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Invoice)?>",
				"<?=CCrmOwnerType::QuoteName?>": "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Quote)?>"
			};
		}
	);
</script>
<?if($arResult['CONVERSION_PERMITTED'] && $arResult['CAN_CONVERT'] && isset($arResult['CONVERSION_CONFIG'])):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmQuoteConversionScheme.messages =
					<?=CUtil::PhpToJSObject(\Bitrix\Crm\Conversion\QuoteConversionScheme::getJavaScriptDescriptions(false))?>;

				BX.CrmQuoteConverter.messages =
				{
					accessDenied: "<?=GetMessageJS("CRM_QUOTE_CONV_ACCESS_DENIED")?>",
					generalError: "<?=GetMessageJS("CRM_QUOTE_CONV_GENERAL_ERROR")?>",
					dialogTitle: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_TITLE")?>",
					syncEditorLegend: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_SYNC_LEGEND")?>",
					syncEditorFieldListTitle: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_SYNC_FILED_LIST_TITLE")?>",
					syncEditorEntityListTitle: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_SYNC_ENTITY_LIST_TITLE")?>",
					continueButton: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_CONTINUE_BTN")?>",
					cancelButton: "<?=GetMessageJS("CRM_QUOTE_CONV_DIALOG_CANCEL_BTN")?>"
				};
				BX.CrmQuoteConverter.permissions =
				{
					deal: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_DEAL'])?>,
					invoice: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_INVOICE'])?>
				};
				BX.CrmQuoteConverter.settings =
				{
					serviceUrl: "<?='/bitrix/components/bitrix/crm.quote.show/ajax.php?action=convert&'.bitrix_sessid_get()?>",
					config: <?=CUtil::PhpToJSObject($arResult['CONVERSION_CONFIG']->toJavaScript())?>
				};
				BX.CrmDealCategory.infos = <?=CUtil::PhpToJSObject(
					DealCategory::getJavaScriptInfos(EntityConverter::getPermittedDealCategoryIDs())
				)?>;
				BX.CrmDealCategorySelectDialog.messages =
				{
					title: "<?=GetMessageJS('CRM_QUOTE_LIST_CONV_DEAL_CATEGORY_DLG_TITLE')?>",
					field: "<?=GetMessageJS('CRM_QUOTE_LIST_CONV_DEAL_CATEGORY_DLG_FIELD')?>",
					saveButton: "<?=GetMessageJS('CRM_QUOTE_LIST_BUTTON_SAVE')?>",
					cancelButton: "<?=GetMessageJS('CRM_QUOTE_LIST_BUTTON_CANCEL')?>"
				};
			}
		);
	</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_QUOTE_ATTRS']):?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			var link = BX("rebuildQuoteAttrsLink");
			if(link)
			{
				BX.bind(
					link,
					"click",
					function(e)
					{
						var msg = BX("rebuildQuoteAttrsMsg");
						if(msg)
						{
							msg.style.display = "none";
						}
					}
				);
			}
		}
	);
</script>
<?endif;?>
<?if($arResult['NEED_FOR_TRANSFER_PS_REQUISITES']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmLongRunningProcessDialog.messages =
				{
					startButton: "<?=GetMessageJS('CRM_PSRQ_LRP_DLG_BTN_START')?>",
					stopButton: "<?=GetMessageJS('CRM_PSRQ_LRP_DLG_BTN_STOP')?>",
					closeButton: "<?=GetMessageJS('CRM_PSRQ_LRP_DLG_BTN_CLOSE')?>",
					requestError: "<?=GetMessageJS('CRM_PSRQ_LRP_DLG_REQUEST_ERR')?>"
				};

				BX.CrmPSRequisiteConverter.messages =
				{
					processDialogTitle: "<?=GetMessageJS('CRM_PS_RQ_TX_PROC_DLG_TITLE')?>",
					processDialogSummary: "<?=GetMessageJS('CRM_PS_RQ_TX_PROC_DLG_DLG_SUMMARY')?>"
				};

				var converter = BX.CrmPSRequisiteConverter.create(
					"psRqConverter",
					{
						serviceUrl: "<?=SITE_DIR?>bitrix/components/bitrix/crm.config.ps.list/list.ajax.php?&<?=bitrix_sessid_get()?>"
					}
				);

				BX.addCustomEvent(
					converter,
					'ON_PS_REQUISITE_TRANFER_COMPLETE',
					function()
					{
						var msg = BX("transferPSRequisitesMsg");
						if(msg)
						{
							msg.style.display = "none";
						}
					}
				);

				var transferLink = BX("transferPSRequisitesLink");
				if(transferLink)
				{
					BX.bind(
						transferLink,
						"click",
						function(e)
						{
							converter.convert();
							return BX.PreventDefault(e);
						}
					);
				}

				var skipTransferLink = BX("skipTransferPSRequisitesLink");
				if(skipTransferLink)
				{
					BX.bind(
						skipTransferLink,
						"click",
						function(e)
						{
							converter.skip();

							var msg = BX("transferPSRequisitesMsg");
							if(msg)
							{
								msg.style.display = "none";
							}

							return BX.PreventDefault(e);
						}
					);
				}
			}
		);
	</script>
<?endif;?>
