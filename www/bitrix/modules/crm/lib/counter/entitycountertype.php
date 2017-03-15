<?php
namespace Bitrix\Crm\Counter;
class EntityCounterType
{
	const UNDEFINED = 0;
	const IDLE  = 1;
	const PENDING = 2;
	const OVERDUE = 4;

	const ALL = 7;  //IDLE|PENDING|OVERDUE

	const FIRST = 1;
	const LAST = 4;

	const IDLE_NAME  = 'IDLE';
	const PENDING_NAME = 'PENDING';
	const OVERDUE_NAME = 'OVERDUE';
	const ALL_NAME = 'ALL';

	private static $all = null;

	/**
	 * @param int $typeID Type ID.
	 * @return bool
	 */
	public static function isDefined($typeID)
	{
		if(!is_numeric($typeID))
		{
			return false;
		}

		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		return $typeID === self::IDLE
			|| $typeID === self::PENDING
			|| $typeID === self::OVERDUE
			|| $typeID === self::ALL;
	}

	public static function isGrouping($typeID)
	{
		return $typeID == self::ALL;
	}

	/**
	 * @param int $typeID Type ID.
	 * @return string
	 */
	public static function resolveName($typeID)
	{
		if(!is_numeric($typeID))
		{
			return '';
		}

		$typeID = (int)$typeID;

		if($typeID === self::IDLE)
		{
			return self::IDLE_NAME;
		}
		elseif($typeID === self::PENDING)
		{
			return self::PENDING_NAME;
		}
		elseif($typeID === self::OVERDUE)
		{
			return self::OVERDUE_NAME;
		}
		elseif($typeID === self::ALL)
		{
			return self::ALL_NAME;
		}
		return '';
	}
	/**
	 * @param string $typeName Type Name.
	 * @return int
	 */
	public static function resolveID($typeName)
	{
		if(!is_string($typeName))
		{
			return self::UNDEFINED;
		}

		$typeName = strtoupper($typeName);
		if($typeName === self::IDLE_NAME)
		{
			return self::IDLE;
		}
		elseif($typeName === self::PENDING_NAME)
		{
			return self::PENDING;
		}
		elseif($typeName === self::OVERDUE_NAME)
		{
			return self::OVERDUE;
		}
		elseif($typeName === self::ALL_NAME)
		{
			return self::ALL;
		}
		return self::UNDEFINED;
	}
	/**
	 * @return array
	 */
	public static function getAll($enableGrouping = false)
	{
		if(self::$all === null)
		{
			self::$all = array(self::IDLE, self::PENDING, self::OVERDUE);
		}

		if(!$enableGrouping)
		{
			return self::$all;
		}
		return array_merge(self::$all, array(self::ALL));
	}
	public static function getGroupings()
	{
		return array(self::ALL);
	}
	public static function joinType(array $typeIDs)
	{
		$result = 0;
		foreach($typeIDs as $typeID)
		{
			$result |= $typeID;
		}
		return $result;
	}
	public static function splitType($typeID)
	{
		if(!is_numeric($typeID))
		{
			return array();
		}

		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		if(!EntityCounterType::isGrouping($typeID))
		{
			return array($typeID);
		}

		$results = array();
		foreach(self::getAll() as $ID)
		{
			if(($typeID&$ID) === $ID)
			{
				$results[] = $ID;
			}
		}
		return $results;
	}
}