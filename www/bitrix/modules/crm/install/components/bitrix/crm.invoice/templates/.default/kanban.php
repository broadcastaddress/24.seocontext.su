<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/crm.invoice.menu/component.php');
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/crm.invoice.list/templates/.default/template.php');

$arResult['PATH_TO_INVOICE_EDIT'] = isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '';
$arResult['PATH_TO_INVOICE_LIST'] = isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '';
$arResult['PATH_TO_INVOICE_WIDGET'] = isset($arResult['PATH_TO_INVOICE_WIDGET']) ? $arResult['PATH_TO_INVOICE_WIDGET'] : '';
$arResult['PATH_TO_INVOICE_KANBAN'] = isset($arResult['PATH_TO_INVOICE_KANBAN']) ? $arResult['PATH_TO_INVOICE_KANBAN'] : '';

/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
	'bitrix:crm.control_panel',
	'',
	array(
		'ID' => 'INVOICE_LIST',
		'ACTIVE_ITEM_ID' => 'INVOICE',
		'PATH_TO_COMPANY_LIST' => isset($arResult['PATH_TO_COMPANY_LIST']) ? $arResult['PATH_TO_COMPANY_LIST'] : '',
		'PATH_TO_COMPANY_EDIT' => isset($arResult['PATH_TO_COMPANY_EDIT']) ? $arResult['PATH_TO_COMPANY_EDIT'] : '',
		'PATH_TO_CONTACT_LIST' => isset($arResult['PATH_TO_CONTACT_LIST']) ? $arResult['PATH_TO_CONTACT_LIST'] : '',
		'PATH_TO_CONTACT_EDIT' => isset($arResult['PATH_TO_CONTACT_EDIT']) ? $arResult['PATH_TO_CONTACT_EDIT'] : '',
		'PATH_TO_DEAL_LIST' => isset($arResult['PATH_TO_DEAL_LIST']) ? $arResult['PATH_TO_DEAL_LIST'] : '',
		'PATH_TO_DEAL_EDIT' => isset($arResult['PATH_TO_DEAL_EDIT']) ? $arResult['PATH_TO_DEAL_EDIT'] : '',
		'PATH_TO_LEAD_LIST' => isset($arResult['PATH_TO_LEAD_LIST']) ? $arResult['PATH_TO_LEAD_LIST'] : '',
		'PATH_TO_LEAD_EDIT' => isset($arResult['PATH_TO_LEAD_EDIT']) ? $arResult['PATH_TO_LEAD_EDIT'] : '',
		'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
		'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
		'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
		'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
		'PATH_TO_REPORT_LIST' => isset($arResult['PATH_TO_REPORT_LIST']) ? $arResult['PATH_TO_REPORT_LIST'] : '',
		'PATH_TO_DEAL_FUNNEL' => isset($arResult['PATH_TO_DEAL_FUNNEL']) ? $arResult['PATH_TO_DEAL_FUNNEL'] : '',
		'PATH_TO_EVENT_LIST' => isset($arResult['PATH_TO_EVENT_LIST']) ? $arResult['PATH_TO_EVENT_LIST'] : '',
		'PATH_TO_PRODUCT_LIST' => isset($arResult['PATH_TO_PRODUCT_LIST']) ? $arResult['PATH_TO_PRODUCT_LIST'] : ''
	),
	$component
);

if (!\CCrmPerms::IsAccessEnabled())
{
	return false;
}

if (!Bitrix\Crm\Integration\Bitrix24Manager::isAccessEnabled(CCrmOwnerType::Invoice))
{
	$APPLICATION->IncludeComponent('bitrix:bitrix24.business.tools.info', '', array());
}
else
{
	$entityType = \CCrmOwnerType::InvoiceName;

	$APPLICATION->IncludeComponent(
		'bitrix:crm.kanban.filter',
		'',
		array(
			'ENTITY_TYPE' => $entityType,
			'NAVIGATION_BAR' => array(
				'ITEMS' => array(
					array(
						'icon' => 'table',
						'id' => 'list',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_LIST'),
						'active' => 0,
						'url' => $arResult['PATH_TO_INVOICE_LIST']
					),
					array(
						'icon' => 'chart',
						'id' => 'widget',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_WIDGET'),
						'active' => 0,
						'url' => $arResult['PATH_TO_INVOICE_WIDGET']
					),
					array(
						'icon' => 'kanban',
						'id' => 'kanban',
						'name' => Loc::getMessage('CRM_INVOICE_LIST_FILTER_NAV_BUTTON_KANBAN'),
						'active' => 1,
						'url' => $arResult['PATH_TO_INVOICE_KANBAN']
					)
				),
				'BINDING' => array(
					'category' => 'crm.navigation',
					'name' => 'index',
					'key' => strtolower($arResult['NAVIGATION_CONTEXT_ID'])
				)
			)
		),
		$component,
		array('HIDE_ICONS' => true)
	);

//	$supervisorInv = \Bitrix\Crm\Kanban\SupervisorTable::isSupervisor($entityType) ? 'N' : 'Y';

	$APPLICATION->IncludeComponent(
		'bitrix:crm.interface.toolbar',
		'',
		array(
			'TOOLBAR_ID' => 'kanban_toolbar',
			'BUTTONS' => array(
				array(
					'TEXT' => Loc::getMessage('INVOICE_ADD'),
					'TITLE' => Loc::getMessage('INVOICE_ADD_TITLE'),
					'LINK' => CComponentEngine::MakePathFromTemplate(
									$arResult['PATH_TO_INVOICE_EDIT'],
									array('invoice_id' => 0)
								),
					'HIGHLIGHT' => 1,
				),
				/*array(
					'NEWBAR' => true
				),
				array(
					'TEXT' => Loc::getMessage('CRM_KANBAN_SUPERVISOR_' . $supervisorInv),
					'TITLE' => Loc::getMessage('CRM_KANBAN_SUPERVISOR_TITLE'),
					'LINK' => CCrmUrlUtil::AddUrlParams(
									CComponentEngine::MakePathFromTemplate(
										$arResult['PATH_TO_INVOICE_KANBAN']
									),
									array('supervisor' => $supervisorInv, 'clear_filter' => 'Y')
								)
				)*/
			)
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);

	$APPLICATION->IncludeComponent(
		'bitrix:crm.kanban',
		'',
		array(
			'ENTITY_TYPE' => $entityType
		),
		$component
	);
}
