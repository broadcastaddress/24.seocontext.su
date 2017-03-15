<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/activity.js');
CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/interface_grid.js');

use \Bitrix\Crm\Conversion\LeadConversionScheme;
use \Bitrix\Crm\Category\DealCategory;
use \Bitrix\Crm\Conversion\EntityConverter;

?><div id="rebuildMessageWrapper"><?
if($arResult['NEED_FOR_REBUILD_DUP_INDEX']):
	?><div id="rebuildLeadDupIndexMsg" class="crm-view-message">
		<?=GetMessage('CRM_LEAD_REBUILD_DUP_INDEX', array('#ID#' => 'rebuildLeadDupIndexLink', '#URL#' => '#'))?>
	</div><?
endif;
if($arResult['NEED_FOR_REBUILD_LEAD_ATTRS']):
	?><div id="rebuildLeadAttrsMsg" class="crm-view-message">
	<?=GetMessage('CRM_LEAD_REBUILD_ACCESS_ATTRS', array('#ID#' => 'rebuildLeadAttrsLink', '#URL#' => $arResult['PATH_TO_PRM_LIST']))?>
	</div><?
endif;
?></div><?

if(isset($arResult['ERROR_HTML'])):
	ShowError($arResult['ERROR_HTML']);
endif;

$currentUserID = $arResult['CURRENT_USER_ID'];
$isInternal = $arResult['INTERNAL'];
$activityEditorID = '';
if(!$isInternal):
	$activityEditorID = "{$arResult['GRID_ID']}_activity_editor";
	$APPLICATION->IncludeComponent(
		'bitrix:crm.activity.editor',
		'',
		array(
			'EDITOR_ID' => $activityEditorID,
			'PREFIX' => $arResult['GRID_ID'],
			'OWNER_TYPE' => 'LEAD',
			'OWNER_ID' => 0,
			'READ_ONLY' => false,
			'ENABLE_UI' => false,
			'ENABLE_TOOLBAR' => false
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
endif;

$gridManagerID = $arResult['GRID_ID'].'_MANAGER';
$gridManagerCfg = array(
	'ownerType' => 'LEAD',
	'gridId' => $arResult['GRID_ID'],
	'formName' => "form_{$arResult['GRID_ID']}",
	'allRowsCheckBoxId' => "actallrows_{$arResult['GRID_ID']}",
	'activityEditorId' => $activityEditorID,
	'serviceUrl' => '/bitrix/components/bitrix/crm.activity.editor/ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
	'listServiceUrl' => '/bitrix/components/bitrix/crm.lead.list/list.ajax.php?siteID='.SITE_ID.'&'.bitrix_sessid_get(),
	'filterFields' => array(),
	'userFilterHash' => $arResult['DB_FILTER_HASH'],
	'enableIterativeDeletion' => true,
	'messages' => array(
		'deletionDialogTitle' => GetMessage('CRM_LEAD_LIST_DEL_PROC_DLG_TITLE'),
		'deletionDialogSummary' => GetMessage('CRM_LEAD_LIST_DEL_PROC_DLG_SUMMARY')
	)
);
$prefix = $arResult['GRID_ID'];
?>
<script type="text/javascript">
function crm_lead_delete_grid(title, message, btnTitle, path)
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
BX.ready(function() {
	if (BX('actallrows_<?=$arResult['GRID_ID']?>'))
	{
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
});
</script>
<?
echo CCrmViewHelper::RenderLeadStatusSettings();
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
		$GLOBALS['APPLICATION']->IncludeComponent('bitrix:crm.entity.selector',
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

	foreach($arResult['LEAD'] as $sKey => $arLead)
	{
		$arActivityMenuItems = array();
		$arActions = array();
		$arActions[] =  array(
			'ICONCLASS' => 'view',
			'TITLE' => GetMessage('CRM_LEAD_SHOW_TITLE'),
			'TEXT' => GetMessage('CRM_LEAD_SHOW'),
			'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arLead['PATH_TO_LEAD_SHOW'])."');",
			'DEFAULT' => true
		);
		if ($arLead['EDIT'])
		{
			$arActions[] = array(
				'ICONCLASS' => 'edit',
				'TITLE' => GetMessage('CRM_LEAD_EDIT_TITLE'),
				'TEXT' => GetMessage('CRM_LEAD_EDIT'), 'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arLead['PATH_TO_LEAD_EDIT']) . "');"
			);
			$arActions[] = array(
				'ICONCLASS' => 'copy',
				'TITLE' => GetMessage('CRM_LEAD_COPY_TITLE'),
				'TEXT' => GetMessage('CRM_LEAD_COPY'),
				'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arLead['PATH_TO_LEAD_COPY']) . "');"
			);
		}

		if(!$isInternal):
			$arActions[] = array('SEPARATOR' => true);

			$arActions[] = $arActivityMenuItems[] = array(
				'ICONCLASS' => 'event',
				'TITLE' => GetMessage('CRM_LEAD_EVENT_TITLE'),
				'TEXT' => GetMessage('CRM_LEAD_EVENT'),
				'ONCLICK' => "javascript:(new BX.CDialog({'content_url':'/bitrix/components/bitrix/crm.event.add/box.php?FORM_TYPE=LIST&ENTITY_TYPE=LEAD&ENTITY_ID=".$arLead['ID']."', 'width':'498', 'height':'275', 'resizable':false })).Show();"
			);

			if ($arLead['EDIT'] && IsModuleInstalled('tasks')):
				$arActions[] = $arActivityMenuItems[] = array(
					'ICONCLASS' => 'task',
					'TITLE' => GetMessage('CRM_LEAD_TASK_TITLE'),
					'TEXT' => GetMessage('CRM_LEAD_TASK'),
					'ONCLICK' => 'BX.CrmInterfaceGridManager.addTask("'.CUtil::JSEscape($gridManagerID).'", { "ownerID":'.$arLead['ID'].' })'
				);
			endif;
			if ($arLead['EDIT'] && IsModuleInstalled('subscribe')):
				$arActions[] = $arActivityMenuItems[] = array(
					'ICONCLASS' => 'subscribe',
					'TITLE' => GetMessage('CRM_LEAD_ADD_EMAIL_TITLE'),
					'TEXT' => GetMessage('CRM_LEAD_ADD_EMAIL'),
					'ONCLICK' => 'BX.CrmInterfaceGridManager.addEmail("'.CUtil::JSEscape($gridManagerID).'", { "ownerID":'.$arLead['ID'].' })'
				);
			endif;
			if ($arLead['EDIT'] && IsModuleInstalled(CRM_MODULE_CALENDAR_ID)):
				$arActions[] = $arActivityMenuItems[] = array(
					'ICONCLASS' => 'calendar',
					'TITLE' => GetMessage('CRM_LEAD_ADD_CALL_TITLE'),
					'TEXT' => GetMessage('CRM_LEAD_ADD_CALL'),
					'ONCLICK' => 'BX.CrmInterfaceGridManager.addCall("'.CUtil::JSEscape($gridManagerID).'", { "ownerID":'.$arLead['ID'].' })'
				);

				$arActions[] = $arActivityMenuItems[] = array(
					'ICONCLASS' => 'calendar',
					'TITLE' => GetMessage('CRM_LEAD_ADD_MEETING_TITLE'),
					'TEXT' => GetMessage('CRM_LEAD_ADD_MEETING'),
					'ONCLICK' => 'BX.CrmInterfaceGridManager.addMeeting("'.CUtil::JSEscape($gridManagerID).'", { "ownerID":'.$arLead['ID'].' })'
				);
			endif;
			if ($arLead['EDIT'] && IsModuleInstalled('sale')):
				$arActions[] = array(
					'ICONCLASS' => 'quote',
					'TITLE' => GetMessage('CRM_LEAD_ADD_QUOTE_TITLE'),
					'TEXT' => GetMessage('CRM_LEAD_ADD_QUOTE'),
					'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arLead['PATH_TO_QUOTE_ADD'])."');"
				);
			endif;

		endif;

		if ($arLead['EDIT']):
			if ($arResult['IS_BIZPROC_AVAILABLE']):
				$arActions[] = array('SEPARATOR' => true);
				if(isset($arContact['PATH_TO_BIZPROC_LIST']) && $arContact['PATH_TO_BIZPROC_LIST'] !== '')
					$arActions[] =  array(
						'ICONCLASS' => 'bizproc',
						'TITLE' => GetMessage('CRM_LEAD_BIZPROC_TITLE'),
						'TEXT' => GetMessage('CRM_LEAD_BIZPROC'),
						'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arLead['PATH_TO_BIZPROC_LIST'])."');"
					);
				if (!empty($arLead['BIZPROC_LIST'])):
					$arBizprocList = array();
					foreach ($arLead['BIZPROC_LIST'] as $arBizproc) :
						$arBizprocList[] = array(
							'ICONCLASS' => 'bizproc',
							'TITLE' => $arBizproc['DESCRIPTION'],
							'TEXT' => $arBizproc['NAME'],
							'ONCLICK' => "jsUtils.Redirect([], '".CUtil::JSEscape($arBizproc['PATH_TO_BIZPROC_START'])."');"
						);
					endforeach;
					$arActions[] =  array(
						'ICONCLASS' => 'bizproc',
						'TITLE' => GetMessage('CRM_LEAD_BIZPROC_LIST_TITLE'),
						'TEXT' => GetMessage('CRM_LEAD_BIZPROC_LIST'),
						'MENU' => $arBizprocList
					);
				endif;
			endif;

			$bSep = false;
			if ($arResult['CAN_CONVERT']):
				$arSchemeDescriptions = \Bitrix\Crm\Conversion\LeadConversionScheme::getJavaScriptDescriptions(true);
				$arSchemeList = array();
				foreach($arSchemeDescriptions as $name => $description)
				{
					$arSchemeList[] = array(
						'ICONCLASS' => 'convert',
						'TITLE' => $description,
						'TEXT' => $description,
						'ONCLICK' => "BX.CrmLeadConverter.getCurrent().convert({$arLead['ID']}, BX.CrmLeadConversionScheme.createConfig('{$name}'), '".CUtil::JSEscape($APPLICATION->GetCurPage())."');"
					);
				}
				if(!empty($arSchemeList)):
					$arSchemeList[] = array(
						'ICONCLASS' => 'convert',
						'TITLE' => GetMessage('CRM_LEAD_CONV_OPEN_ENTITY_SEL'),
						'TEXT' => GetMessage('CRM_LEAD_CONV_OPEN_ENTITY_SEL'),
						'ONCLICK' => "BX.CrmLeadConverter.getCurrent().openEntitySelector(function(result){ BX.CrmLeadConverter.getCurrent().convert({$arLead['ID']}, result.config, '".CUtil::JSEscape($APPLICATION->GetCurPage())."', result.data); });"
					);
					$bSep = true;
					$arActions[] = array('SEPARATOR' => true);
					$arActions[] =  array(
						'ICONCLASS' => 'convert',
						'TITLE' => GetMessage('CRM_LEAD_CREATE_ON_BASIS_TITLE'),
						'TEXT' => GetMessage('CRM_LEAD_CREATE_ON_BASIS'),
						'MENU' => $arSchemeList
					);
				endif;
			endif;
		endif;
		if ($arLead['DELETE']):
			if (!$bSep)
				$arActions[] = array('SEPARATOR' => true);
			$arActions[] =  array(
				'ICONCLASS' => 'delete',
				'TITLE' => GetMessage('CRM_LEAD_DELETE_TITLE'),
				'TEXT' => GetMessage('CRM_LEAD_DELETE'),
				'ONCLICK' => "crm_lead_delete_grid('".CUtil::JSEscape(GetMessage('CRM_LEAD_DELETE_TITLE'))."', '".CUtil::JSEscape(GetMessage('CRM_LEAD_DELETE_CONFIRM'))."', '".CUtil::JSEscape(GetMessage('CRM_LEAD_DELETE'))."', '".CUtil::JSEscape($arLead['PATH_TO_LEAD_DELETE'])."')"
			);
		endif;

		$resultItem = array(
			'id' => $arLead['ID'],
			'actions' => $arActions,
			'data' => $arLead,
			'editable' => !$arLead['EDIT'] ? $arColumns : true,
			'columns' => array(
				'LEAD_SUMMARY' => CCrmViewHelper::RenderInfo($arLead['PATH_TO_LEAD_SHOW'], isset($arLead['TITLE']) ? $arLead['TITLE'] : ('['.$arLead['ID'].']'), $arLead['LEAD_SOURCE_NAME'], '_self'),
				'ACTIVITY_ID' => CCrmViewHelper::RenderNearestActivity(
					array(
						'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Lead),
						'ENTITY_ID' => $arLead['ID'],
						'GRID_MANAGER_ID' => $gridManagerID,
						'ACTIVITY_ID' => isset($arLead['~ACTIVITY_ID']) ? intval($arLead['~ACTIVITY_ID']) : 0,
						'ACTIVITY_SUBJECT' => isset($arLead['~ACTIVITY_SUBJECT']) ? $arLead['~ACTIVITY_SUBJECT'] : '',
						'ACTIVITY_TIME' => isset($arLead['~ACTIVITY_TIME']) ? $arLead['~ACTIVITY_TIME'] : '',
						'ACTIVITY_EXPIRED' => isset($arLead['~ACTIVITY_EXPIRED']) ? $arLead['~ACTIVITY_EXPIRED'] : false,
						'MENU_ITEMS' => $arActivityMenuItems
					)
				),
				'COMMENTS' => htmlspecialcharsback($arLead['COMMENTS']),
				'ADDRESS' => nl2br($arLead['ADDRESS']),
				'ASSIGNED_BY' => $arLead['~ASSIGNED_BY_ID'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "LEAD_{$arLead['~ID']}_RESPONSIBLE",
							'USER_ID' => $arLead['~ASSIGNED_BY_ID'],
							'USER_NAME'=> $arLead['ASSIGNED_BY'],
							'USER_PROFILE_URL' => $arLead['PATH_TO_USER_PROFILE']
						)
					) : '',
				'STATUS_DESCRIPTION' => nl2br($arLead['STATUS_DESCRIPTION']),
				'SOURCE_DESCRIPTION' => nl2br($arLead['SOURCE_DESCRIPTION']),
				'DATE_CREATE' => '<nobr>'.FormatDate('SHORT', MakeTimeStamp($arLead['DATE_CREATE'])).'</nobr>',
				'DATE_MODIFY' => '<nobr>'.FormatDate('SHORT', MakeTimeStamp($arLead['DATE_MODIFY'])).'</nobr>',
				'SUM' => '<nobr>'.$arLead['FORMATTED_OPPORTUNITY'].'</nobr>',
				'OPPORTUNITY' => '<nobr>'.$arLead['~OPPORTUNITY'].'</nobr>',
				'CURRENCY_ID' => CCrmCurrency::GetCurrencyName($arLead['~CURRENCY_ID']),
				'PRODUCT_ID' => isset($arLead['PRODUCT_ROWS']) ? htmlspecialcharsbx(CCrmProductRow::RowsToString($arLead['PRODUCT_ROWS'])) : '',
				'STATUS_ID' => CCrmViewHelper::RenderLeadStatusControl(
					array(
						'PREFIX' => "{$arResult['GRID_ID']}_PROGRESS_BAR_",
						'ENTITY_ID' => $arLead['~ID'],
						'CURRENT_ID' => $arLead['~STATUS_ID'],
						'SERVICE_URL' => '/bitrix/components/bitrix/crm.lead.list/list.ajax.php',
						'CONVERSION_SCHEME' => $arResult['CONVERSION_SCHEME'],
						'READ_ONLY' => !(isset($arLead['EDIT']) && $arLead['EDIT'] === true)
					)
				),
				'SOURCE_ID' => $arLead['LEAD_SOURCE_NAME'],
				'CREATED_BY' => $arLead['~CREATED_BY'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "LEAD_{$arLead['~ID']}_CREATOR",
							'USER_ID' => $arLead['~CREATED_BY'],
							'USER_NAME'=> $arLead['CREATED_BY_FORMATTED_NAME'],
							'USER_PROFILE_URL' => $arLead['PATH_TO_USER_CREATOR'],
						)
					) : '',
				'MODIFY_BY' => $arLead['~MODIFY_BY'] > 0
					? CCrmViewHelper::PrepareUserBaloonHtml(
						array(
							'PREFIX' => "LEAD_{$arLead['~ID']}_MODIFIER",
							'USER_ID' => $arLead['~MODIFY_BY'],
							'USER_NAME'=> $arLead['MODIFY_BY_FORMATTED_NAME'],
							'USER_PROFILE_URL' => $arLead['PATH_TO_USER_MODIFIER']
						)
					) : '',
			) + CCrmViewHelper::RenderListMultiFields($arLead, "LEAD_{$arLead['ID']}_", array('ENABLE_SIP' => true, 'SIP_PARAMS' => array('ENTITY_TYPE' => 'CRM_'.CCrmOwnerType::LeadName, 'ENTITY_ID' => $arLead['ID']))) + $arResult['LEAD_UF'][$sKey]
		);

		if(isset($arLead['~BIRTHDATE']))
		{
			$resultItem['columns']['BIRTHDATE'] = '<nobr>'.FormatDate('SHORT', MakeTimeStamp($arLead['~BIRTHDATE'])).'</nobr>';
		}

		$userActivityID = isset($arLead['~ACTIVITY_ID']) ? intval($arLead['~ACTIVITY_ID']) : 0;
		$commonActivityID = isset($arLead['~C_ACTIVITY_ID']) ? intval($arLead['~C_ACTIVITY_ID']) : 0;
		if($userActivityID > 0)
		{
			$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
				array(
					'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Lead),
					'ENTITY_ID' => $arLead['~ID'],
					'ENTITY_RESPONSIBLE_ID' => $arLead['~ASSIGNED_BY'],
					'GRID_MANAGER_ID' => $gridManagerID,
					'ACTIVITY_ID' => $userActivityID,
					'ACTIVITY_SUBJECT' => isset($arLead['~ACTIVITY_SUBJECT']) ? $arLead['~ACTIVITY_SUBJECT'] : '',
					'ACTIVITY_TIME' => isset($arLead['~ACTIVITY_TIME']) ? $arLead['~ACTIVITY_TIME'] : '',
					'ACTIVITY_EXPIRED' => isset($arLead['~ACTIVITY_EXPIRED']) ? $arLead['~ACTIVITY_EXPIRED'] : '',
					'ALLOW_EDIT' => $arLead['EDIT'],
					'MENU_ITEMS' => $arActivityMenuItems,
				)
			);

			$counterData = array(
				'CURRENT_USER_ID' => $currentUserID,
				'ENTITY' => $arLead,
				'ACTIVITY' => array(
					'RESPONSIBLE_ID' => $currentUserID,
					'TIME' => isset($arLead['~ACTIVITY_TIME']) ? $arLead['~ACTIVITY_TIME'] : '',
					'IS_CURRENT_DAY' => isset($arLead['~ACTIVITY_IS_CURRENT_DAY']) ? $arLead['~ACTIVITY_IS_CURRENT_DAY'] : false
				)
			);

			if(CCrmUserCounter::IsReckoned(CCrmUserCounter::CurrentLeadActivies, $counterData))
			{
				$resultItem['columnClasses'] = array('ACTIVITY_ID' => 'crm-list-deal-today');
			}
		}
		elseif($commonActivityID > 0)
		{
			$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
				array(
					'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Lead),
					'ENTITY_ID' => $arLead['~ID'],
					'ENTITY_RESPONSIBLE_ID' => $arLead['~ASSIGNED_BY'],
					'GRID_MANAGER_ID' => $gridManagerID,
					'ACTIVITY_ID' => $commonActivityID,
					'ACTIVITY_SUBJECT' => isset($arLead['~C_ACTIVITY_SUBJECT']) ? $arLead['~C_ACTIVITY_SUBJECT'] : '',
					'ACTIVITY_TIME' => isset($arLead['~C_ACTIVITY_TIME']) ? $arLead['~C_ACTIVITY_TIME'] : '',
					'ACTIVITY_RESPONSIBLE_ID' => isset($arLead['~C_ACTIVITY_RESP_ID']) ? intval($arLead['~C_ACTIVITY_RESP_ID']) : 0,
					'ACTIVITY_RESPONSIBLE_LOGIN' => isset($arLead['~C_ACTIVITY_RESP_LOGIN']) ? $arLead['~C_ACTIVITY_RESP_LOGIN'] : '',
					'ACTIVITY_RESPONSIBLE_NAME' => isset($arLead['~C_ACTIVITY_RESP_NAME']) ? $arLead['~C_ACTIVITY_RESP_NAME'] : '',
					'ACTIVITY_RESPONSIBLE_LAST_NAME' => isset($arLead['~C_ACTIVITY_RESP_LAST_NAME']) ? $arLead['~C_ACTIVITY_RESP_LAST_NAME'] : '',
					'ACTIVITY_RESPONSIBLE_SECOND_NAME' => isset($arLead['~C_ACTIVITY_RESP_SECOND_NAME']) ? $arLead['~C_ACTIVITY_RESP_SECOND_NAME'] : '',
					'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
					'PATH_TO_USER_PROFILE' => $arParams['PATH_TO_USER_PROFILE'],
					'ALLOW_EDIT' => $arLead['EDIT'],
					'MENU_ITEMS' => $arActivityMenuItems
				)
			);
		}
		else
		{
			$resultItem['columns']['ACTIVITY_ID'] = CCrmViewHelper::RenderNearestActivity(
				array(
					'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName(CCrmOwnerType::Lead),
					'ENTITY_ID' => $arLead['~ID'],
					'ENTITY_RESPONSIBLE_ID' => $arLead['~ASSIGNED_BY'],
					'GRID_MANAGER_ID' => $gridManagerID,
					'ALLOW_EDIT' => $arLead['EDIT'],
					'MENU_ITEMS' => $arActivityMenuItems
				)
			);

			$counterData = array(
				'CURRENT_USER_ID' => $currentUserID,
				'ENTITY' => $arLead
			);

			if(CCrmUserCounter::IsReckoned(CCrmUserCounter::CurrentLeadActivies, $counterData))
			{
				$resultItem['columnClasses'] = array('ACTIVITY_ID' => 'crm-list-enitity-action-need');
			}
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
		$prefix = $arResult['GRID_ID'];
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
		$opened .= '<option value="Y">'.GetMessage("CRM_LEAD_MARK_AS_OPENED_YES").'</option>';
		$opened .= '<option value="N">'.GetMessage("CRM_LEAD_MARK_AS_OPENED_NO").'</option>';
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
		if (IsModuleInstalled('tasks'))
		{
			$arActionList['tasks'] = GetMessage('CRM_LEAD_TASK');
		}
		if (IsModuleInstalled('subscribe'))
		{
			$arActionList['subscribe'] = GetMessage('CRM_LEAD_SUBSCRIBE');
		}
		//if (IsModuleInstalled(CRM_MODULE_CALENDAR_ID))
		//	$arActionList['calendar'] = GetMessage('CRM_LEAD_CALENDAR');
		if($arResult['PERMS']['WRITE'])
		{
			$arActionList['set_status'] = GetMessage('CRM_LEAD_SET_STATUS');
			$arActionList['assign_to'] = GetMessage('CRM_LEAD_ASSIGN_TO');
			$arActionList['mark_as_opened'] = GetMessage('CRM_LEAD_MARK_AS_OPENED');
		}
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
					'service_url' => '/bitrix/components/bitrix/crm.lead.list/list.ajax.php?'.bitrix_sessid_get()
				),
				array('custom_html' => '<td>'.$navigationHtml.'</td>')
			),
			'EDITABLE' => $isEditable ? 'Y' : 'N',
			'ACTIONS' => array(
				'delete' => $arResult['PERMS']['DELETE'],
				'custom_html' => $actionHtml,
				'list' => $arActionList
			),
			'ACTION_ALL_ROWS' => true,
			'NAV_OBJECT' => $arResult['DB_LIST'],
			'FORM_ID' => $arResult['FORM_ID'],
			'TAB_ID' => $arResult['TAB_ID'],
			'AJAX_MODE' => $arResult['INTERNAL'] ? 'N' : 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			'FILTER' => $arResult['FILTER'],
			'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
			'FILTER_TEMPLATE' => 'flat',
			'FILTER_NAVIGATION_BAR' => array(
				'ITEMS' => array(
					array(
						'icon' => 'table',
						'id' => 'list',
						'name' => GetMessage('CRM_LEAD_LIST_FILTER_NAV_BUTTON_LIST'),
						'active' => true,
						'url' => $arParams['PATH_TO_LEAD_LIST']
					),
					array(
						'icon' => 'chart',
						'id' => 'widget',
						'name' => GetMessage('CRM_LEAD_LIST_FILTER_NAV_BUTTON_WIDGET'),
						'active' => false,
						'url' => $arParams['PATH_TO_LEAD_WIDGET']
					),
					array(
						'icon' => 'kanban',
						'id' => 'kanban',
						'name' => GetMessage('CRM_LEAD_LIST_FILTER_NAV_BUTTON_KANBAN'),
						'active' => false,
						'url' => $arParams['PATH_TO_LEAD_KANBAN']
					)
				),
				'BINDING' => array(
					'category' => 'crm.navigation',
					'name' => 'index',
					'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
				)
			),
			'IS_EXTERNAL_FILTER' => $arResult['IS_EXTERNAL_FILTER'],
			'MANAGER' => array(
				'ID' => $gridManagerID,
				'CONFIG' => $gridManagerCfg
			)
		),
		$component
	);
?><script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmLongRunningProcessDialog.messages =
			{
				startButton: "<?=GetMessageJS('CRM_LEAD_LRP_DLG_BTN_START')?>",
				stopButton: "<?=GetMessageJS('CRM_LEAD_LRP_DLG_BTN_STOP')?>",
				closeButton: "<?=GetMessageJS('CRM_LEAD_LRP_DLG_BTN_CLOSE')?>",
				wait: "<?=GetMessageJS('CRM_LEAD_LRP_DLG_WAIT')?>",
				requestError: "<?=GetMessageJS('CRM_LEAD_LRP_DLG_REQUEST_ERR')?>"
			};
		}
	);
</script>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmSipManager.getCurrent().setServiceUrl(
				"CRM_<?=CUtil::JSEscape(CCrmOwnerType::LeadName)?>",
				"/bitrix/components/bitrix/crm.lead.show/ajax.php?<?=bitrix_sessid_get()?>"
			);

			if(typeof(BX.CrmSipManager.messages) === 'undefined')
			{
				BX.CrmSipManager.messages =
				{
					"unknownRecipient": "<?= GetMessageJS('CRM_SIP_MGR_UNKNOWN_RECIPIENT')?>",
					"makeCall": "<?= GetMessageJS('CRM_SIP_MGR_MAKE_CALL')?>"
				};
			}
		}
	);
</script>
<?if(!$isInternal):?>
<script type="text/javascript">
	BX.ready(
			function()
			{
				BX.CrmActivityEditor.items['<?= CUtil::JSEscape($activityEditorID)?>'].addActivityChangeHandler(
						function()
						{
							BX.CrmInterfaceGridManager.reloadGrid('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
						}
				);
				BX.namespace('BX.Crm.Activity');
				if(typeof BX.Crm.Activity.Planner !== 'undefined')
				{
					BX.Crm.Activity.Planner.Manager.setCallback('onAfterActivitySave', function()
					{
						BX.CrmInterfaceGridManager.reloadGrid('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
					});
				}
			}
	);
</script>
<?endif;?>
<?if($arResult['CAN_CONVERT'] && isset($arResult['CONVERSION_CONFIG'])):?>
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
			BX.CrmLeadConversionScheme.messages =
				<?=CUtil::PhpToJSObject(LeadConversionScheme::getJavaScriptDescriptions(false))?>;
			BX.CrmLeadConverter.messages =
			{
				accessDenied: "<?=GetMessageJS("CRM_LEAD_CONV_ACCESS_DENIED")?>",
				generalError: "<?=GetMessageJS("CRM_LEAD_CONV_GENERAL_ERROR")?>",
				dialogTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_TITLE")?>",
				syncEditorLegend: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_LEGEND")?>",
				syncEditorFieldListTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_FILED_LIST_TITLE")?>",
				syncEditorEntityListTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_ENTITY_LIST_TITLE")?>",
				continueButton: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_CONTINUE_BTN")?>",
				cancelButton: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_CANCEL_BTN")?>",
				selectButton: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_BTN")?>",
				openEntitySelector: "<?=GetMessageJS("CRM_LEAD_CONV_OPEN_ENTITY_SEL")?>",
				entitySelectorTitle: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_TITLE")?>",
				contact: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_CONTACT")?>",
				company: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_COMPANY")?>",
				noresult: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_SEARCH_NO_RESULT")?>",
				search : "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_SEARCH")?>",
				last : "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_LAST")?>"
			};
			BX.CrmLeadConverter.permissions =
			{
				contact: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_CONTACT'])?>,
				company: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_COMPANY'])?>,
				deal: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_DEAL'])?>
			};
			BX.CrmLeadConverter.settings =
			{
				serviceUrl: "<?='/bitrix/components/bitrix/crm.lead.show/ajax.php?action=convert&'.bitrix_sessid_get()?>",
				config: <?=CUtil::PhpToJSObject($arResult['CONVERSION_CONFIG']->toJavaScript())?>
			};
			BX.CrmDealCategory.infos = <?=CUtil::PhpToJSObject(
				DealCategory::getJavaScriptInfos(EntityConverter::getPermittedDealCategoryIDs())
			)?>;
			BX.CrmDealCategorySelectDialog.messages =
			{
				title: "<?=GetMessageJS('CRM_LEAD_LIST_CONV_DEAL_CATEGORY_DLG_TITLE')?>",
				field: "<?=GetMessageJS('CRM_LEAD_LIST_CONV_DEAL_CATEGORY_DLG_FIELD')?>",
				saveButton: "<?=GetMessageJS('CRM_LEAD_LIST_BUTTON_SAVE')?>",
				cancelButton: "<?=GetMessageJS('CRM_LEAD_LIST_BUTTON_CANCEL')?>"
			};
		}
	);
</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_DUP_INDEX']):?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmDuplicateManager.messages =
			{
				rebuildLeadIndexDlgTitle: "<?=GetMessageJS('CRM_LEAD_REBUILD_DUP_INDEX_DLG_TITLE')?>",
				rebuildLeadIndexDlgSummary: "<?=GetMessageJS('CRM_LEAD_REBUILD_DUP_INDEX_DLG_SUMMARY')?>"
			};

			var mgr = BX.CrmDuplicateManager.create("mgr", { entityTypeName: "<?=CUtil::JSEscape(CCrmOwnerType::LeadName)?>", serviceUrl: "<?=SITE_DIR?>bitrix/components/bitrix/crm.lead.list/list.ajax.php?&<?=bitrix_sessid_get()?>" });
			BX.addCustomEvent(
				mgr,
				'ON_LEAD_INDEX_REBUILD_COMPLETE',
				function()
				{
					var msg = BX("rebuildLeadDupIndexMsg");
					if(msg)
					{
						msg.style.display = "none";
					}
				}
			);

			var link = BX("rebuildLeadDupIndexLink");
			if(link)
			{
				BX.bind(
					link,
					"click",
					function(e)
					{
						mgr.rebuildIndex();
						return BX.PreventDefault(e);
					}
				);
			}
		}
	);
</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_LEAD_SEMANTICS']):?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				var builderPanel = BX.CrmLongRunningProcessPanel.create(
					"rebuildLeadSemantics",
					{
						"containerId": "rebuildMessageWrapper",
						"prefix": "",
						"active": true,
						"message": "<?=GetMessageJS('CRM_LEAD_REBUILD_SEMANTICS')?>",
						"manager":
						{
							dialogTitle: "<?=GetMessageJS("CRM_LEAD_REBUILD_SEMANTICS_DLG_TITLE")?>",
							dialogSummary: "<?=GetMessageJS("CRM_LEAD_REBUILD_SEMANTICS_DLG_SUMMARY")?>",
							actionName: "REBUILD_SEMANTICS",
							serviceUrl: "<?='/bitrix/components/bitrix/crm.lead.list/list.ajax.php?'.bitrix_sessid_get()?>"
						}
					}
				);
				builderPanel.layout();
			}
		);
	</script>
<?endif;?>
<?if($arResult['NEED_FOR_REBUILD_LEAD_ATTRS']):?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			var link = BX("rebuildLeadAttrsLink");
			if(link)
			{
				BX.bind(
					link,
					"click",
					function(e)
					{
						var msg = BX("rebuildLeadAttrsMsg");
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
