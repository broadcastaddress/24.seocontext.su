<?php
namespace Bitrix\Crm\Activity\Provider;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Request extends Base
{
	public static function getId()
	{
		return 'CRM_REQUEST';
	}

	public static function getTypeId(array $activity)
	{
		return 'REQUEST';
	}

	public static function getTypes()
	{
		return array(
			array(
				'NAME' => Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NAME'),
				'PROVIDER_ID' => static::getId(),
				'PROVIDER_TYPE_ID' => 'REQUEST'
			)
		);
	}

	public static function getName()
	{
		return Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_NAME');
	}

	/**
	 * @param null|string $providerTypeId Provider type id.
	 * @param int $direction Activity direction.
	 * @return bool
	 */
	public static function isTypeEditable($providerTypeId = null, $direction = \CCrmActivityDirection::Undefined)
	{
		return false;
	}

	public static function renderView(array $activity)
	{
		$html = '<div class="crm-task-list-meet">';

		if (!empty($activity['SUBJECT']))
		{
			$html .= '<div class="crm-task-list-meet-inner">
					<div class="crm-task-list-meet-item">'.Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_PLANNER_SUBJECT_LABEL').':</div>
					<div class="crm-task-list-meet-topic">'.htmlspecialcharsbx($activity['SUBJECT']).'</div>
				</div>';
		}
		if (!empty($activity['DESCRIPTION']))
		{
			$html .= '<div class="crm-task-list-meet-inner">
					<div class="crm-task-list-meet-item">'.Loc::getMessage('CRM_ACTIVITY_PROVIDER_REQUEST_PLANNER_DESCRIPTION_LABEL').':</div>
					<div class="crm-task-list-meet-element">'.$activity['DESCRIPTION_HTML'].'</div>
				</div>';
		}
		$html .= '</div>';

		return $html;
	}
}
