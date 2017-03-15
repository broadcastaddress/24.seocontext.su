<?php
namespace Bitrix\ImOpenLines\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class SessionCheckTable
 *
 * Fields:
 * <ul>
 * <li> SESSION_ID int mandatory
 * <li> DATE_CLOSE datetime optional
 * <li> DATE_QUEUE datetime optional
 * </ul>
 *
 * @package Bitrix\Imopenlines
 **/

class SessionCheckTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_session_check';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'SESSION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('SESSION_CHECK_ENTITY_SESSION_ID_FIELD'),
			),
			'DATE_CLOSE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_CHECK_ENTITY_DATE_CLOSE_FIELD'),
			),
			'DATE_QUEUE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('SESSION_CHECK_ENTITY_DATE_QUEUE_FIELD'),
			),
			'SESSION' => array(
				'data_type' => 'Bitrix\ImOpenLines\Model\Session',
				'reference' => array('=this.SESSION_ID' => 'ref.ID'),
			),
		);
	}
}