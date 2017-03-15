<?php
namespace Bitrix\Crm\Counter;
use Bitrix\Main;

class EntityCounterFactory
{
	static public function create($entityTypeID, $typeID, $userID = 0, array $extras = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!\CCrmOwnerType::IsDefined($entityTypeID))
		{
			throw new Main\ArgumentOutOfRangeException('entityTypeID',
				\CCrmOwnerType::FirstOwnerType,
				\CCrmOwnerType::LastOwnerType
			);
		}

		return $entityTypeID === \CCrmOwnerType::Deal
			? new DealCounter($typeID, $userID, $extras)
			: new EntityCounter($entityTypeID, $typeID, $userID, $extras);
	}
}