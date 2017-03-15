<?php

namespace Bitrix\Disk\Internals\Entity;

/**
 * Class Query
 * @package Bitrix\Disk\Internals\Entity
 * @internal
 */
final class Query extends \Bitrix\Main\Entity\Query
{
	/**
	 * Generates where condition by filter.
	 * @return string
	 */
	public function getWhere()
	{
		return $this->buildWhere();
	}
}