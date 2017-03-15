<?php
namespace Bitrix\Crm\Counter;
use Bitrix\Main;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;

use Bitrix\Crm\DealTable;
use Bitrix\Crm\LeadTable;
use Bitrix\Crm\ActivityTable;
use Bitrix\Crm\UserActivityTable;

class EntityCounter
{
	/** @var int */
	protected $typeID = EntityCounterType::UNDEFINED;
	/** @var int */
	protected $entityTypeID = \CCrmOwnerType::Undefined;
	/** @var int */
	protected $userID = 0;
	/** @var array|null  */
	protected $extras = null;
	/** @var int|null */
	protected $currentValue = null;
	/** @var string  */
	protected $code = '';
	/** @var string */
	protected $lastCalculateOptionName = '';
	/** @var int|null  */
	protected $lastCalculatedTime = null;

	/**
	 * @param int $entityTypeID Entity Type ID (see \CCrmOwnerType).
	 * @param int $typeID Type ID (see EntityCounterType).
	 * @param int $userID User ID.
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function __construct($entityTypeID, $typeID, $userID = 0, array $extras = null)
	{
		$this->setEntityTypeID($entityTypeID);
		$this->setTypeID($typeID);
		$this->setUserID($userID > 0 ? $userID : \CCrmSecurityHelper::GetCurrentUserID());
		$this->setExtras($extras !== null ? $extras : array());
		$this->code = $this->resolveCode();
		$this->lastCalculateOptionName = $this->resolveLastCalculateOptionName();
	}
	/**
	 * @return int
	 */
	public function getTypeID()
	{
		return $this->typeID;
	}
	/**
	 * @return int
	 */
	public function getTypeName()
	{
		return EntityCounterType::resolveName($this->typeID);
	}
	/**
	 * @param int $typeID Type ID (see EntityCounterType).
	 * @return void
	 */
	protected function setTypeID($typeID)
	{
		if(!is_int($typeID))
		{
			$typeID = (int)$typeID;
		}

		$this->typeID = $typeID;
	}
	/**
	 * @return int
	 */
	public function getEntityTypeID()
	{
		return $this->entityTypeID;
	}
	/**
	 * @param int $entityTypeID Entity Type ID (see \CCrmOwnerType).
	 * @return void
	 */
	protected function setEntityTypeID($entityTypeID)
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

		$this->entityTypeID = $entityTypeID;
	}
	/**
	 * @return int
	 */
	public function getUserID()
	{
		return $this->userID;
	}
	/**
	 * @param int $userID User ID.
	 * @return void
	 */
	protected function setUserID($userID)
	{
		if(!is_int($userID))
		{
			$userID = (int)$userID;
		}

		if($userID < 0)
		{
			$userID = 0;
		}

		$this->userID = $userID;
	}
	protected function isOneDay()
	{
		return $this->typeID === EntityCounterType::PENDING
			|| $this->typeID === EntityCounterType::OVERDUE
			|| $this->typeID === EntityCounterType::ALL;
	}
	protected function isExpired()
	{
		return ($this->typeID === EntityCounterType::PENDING
			|| $this->typeID === EntityCounterType::OVERDUE
			|| $this->typeID === EntityCounterType::ALL)
			&& !$this->checkLastCalculatedTime();
	}
	public function getExtras()
	{
		return $this->extras;
	}
	protected function setExtras(array $extras)
	{
		$this->extras = $extras;
	}
	public function getExtraParam($name, $default = null)
	{
		return isset($this->extras) ? $this->extras[$name] : $default;
	}
	/**
	 * @param string $name Extra Parameter Name.
	 * @param int $default Default Value.
	 * @return int
	 */
	public function getIntegerExtraParam($name, $default = 0)
	{
		return isset($this->extras[$name]) ? (int)$this->extras[$name] : $default;
	}
	public function reset()
	{
		self::resetByCode($this->code, $this->userID);
	}
	public static function resetByCode($code, $userID = 0)
	{
		if(!(is_string($code) && $code !== ''))
		{
			return;
		}

		if($userID <= 0)
		{
			$userID = \CCrmSecurityHelper::GetCurrentUserID();
		}

		\CUserCounter::Set($userID, $code, -1, '**', '', false);
	}
	public function getCode()
	{
		return $this->code;
	}
	protected function resolveCode()
	{
		return static::prepareCode($this->entityTypeID, $this->typeID, $this->extras);
	}
	protected function resolveLastCalculateOptionName()
	{
		return $this->code !== '' ? "{$this->code}_last_calc" : '';
	}
	public static function prepareCodes($entityTypeID, $typeIDs, array $extras = null)
	{
		return EntityCounterManager::prepareCodes($entityTypeID, $typeIDs, $extras);
	}
	public static function prepareCode($entityTypeID, $typeID, array $extras = null)
	{
		return EntityCounterManager::prepareCode($entityTypeID, $typeID, $extras);
	}
	protected function checkLastCalculatedTime()
	{
		if($this->lastCalculateOptionName === '')
		{
			return false;
		}

		$current = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		if($this->lastCalculatedTime === null)
		{
			$this->lastCalculatedTime = (int)\CUserOptions::GetOption('crm', $this->lastCalculateOptionName, 0, $this->userID);
		}
		return $this->lastCalculatedTime >= $current;
	}
	protected function refreshLastCalculatedTime()
	{
		if($this->lastCalculateOptionName === '')
		{
			return;
		}

		$current = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		if($this->lastCalculatedTime !== $current)
		{
			$this->lastCalculatedTime = $current;
			\CUserOptions::SetOption('crm', $this->lastCalculateOptionName, $this->lastCalculatedTime, false, $this->userID);
		}
	}
	public function getValue($recalculate = false)
	{
		if($this->currentValue !== null)
		{
			return $this->currentValue;
		}

		$this->currentValue  = -1;
		if($this->code !== '' && !$recalculate && !$this->isExpired())
		{
			if($this->typeID === EntityCounterType::IDLE
				&& !\CCrmUserCounterSettings::GetValue(\CCrmUserCounterSettings::ReckonActivitylessItems, true))
			{
				$this->currentValue = 0;
			}
			else
			{
				$map = \CUserCounter::GetValues($this->userID, '**');
				if(isset($map[$this->code]))
				{
					$this->currentValue = (int)$map[$this->code];
				}
			}
		}

		if($this->currentValue < 0)
		{
			$this->synchronize();
		}

		return $this->currentValue;
	}
	/**
	 * Prepare queries
	 * @param array|null $options
	 * @return Query[]
	 */
	protected function prepareQueries(array $options = null)
	{
		if(!is_array($options))
		{
			$options = array();
		}

		$select = isset($options['SELECT']) ? $options['SELECT'] : '';
		if($select !== 'QTY' && $select !== 'ENTY')
		{
			$select = 'QTY';
		}

		$distinct = true;
		if($select === 'ENTY' && isset($options['DISTINCT']))
		{
			$distinct = $options['DISTINCT'];
		}

		$results = array();
		$typeIDs = EntityCounterType::splitType($this->typeID);
		foreach($typeIDs as $typeID)
		{
			//echo EntityCounterType::resolveName($typeID), "<br>";
			if($typeID === EntityCounterType::IDLE)
			{
				if(!\CCrmUserCounterSettings::GetValue(\CCrmUserCounterSettings::ReckonActivitylessItems, true)
					|| ($this->entityTypeID !== \CCrmOwnerType::Deal && $this->entityTypeID !== \CCrmOwnerType::Lead))
				{
					continue;
				}

				/** @var Query|null $query */
				if($this->entityTypeID === \CCrmOwnerType::Deal)
				{
					$query = new Query(DealTable::getEntity());
					$query->addFilter('=STAGE_SEMANTIC_ID', 'P');
				}
				else//if($this->entityTypeID === \CCrmOwnerType::Lead)
				{
					$query = new Query(LeadTable::getEntity());
					$query->addFilter('=STATUS_SEMANTIC_ID', 'P');
				}

				if($select === 'ENTY')
				{
					$query->addSelect('ID', 'ENTY');
				}
				else
				{
					$query->registerRuntimeField('', new ExpressionField('QTY', 'COUNT(%s)', 'ID'));
					$query->addSelect('QTY');
				}

				$query->registerRuntimeField(
					'',
					new ReferenceField('UA',
						UserActivityTable::getEntity(),
						array(
							'=ref.OWNER_ID' => 'this.ID',
							'=ref.OWNER_TYPE_ID' => new SqlExpression($this->entityTypeID),
							'=ref.USER_ID' => new SqlExpression(0)
						),
						array('join_type' => 'LEFT')
					)
				);
				$query->addFilter('==UA.OWNER_ID', null);

				if($this->userID > 0)
				{
					$query->addFilter('=ASSIGNED_BY_ID', $this->userID);
				}

				//echo '<pre>', $query->getQuery(), "</pre>";
				$results[] = $query;
			}
			else if($typeID === EntityCounterType::PENDING || $typeID === EntityCounterType::OVERDUE)
			{
				$query = new Query(ActivityTable::getEntity());

				if($select === 'ENTY')
				{
					$query->addSelect('BINDINGS.OWNER_ID', 'ENTY');
					if($distinct)
					{
						$query->addGroup('BINDINGS.OWNER_ID');
					}
				}
				else
				{
					$query->registerRuntimeField('', new ExpressionField('QTY', 'COUNT(DISTINCT %s)', 'BINDINGS.OWNER_ID'));
					$query->addSelect('QTY');
				}

				$query->addFilter('=BINDINGS.OWNER_TYPE_ID', $this->entityTypeID);

				if($this->userID > 0)
				{
					$query->addFilter('=RESPONSIBLE_ID', $this->userID);
				}

				$query->addFilter('=COMPLETED', 'N');

				if($typeID === EntityCounterType::PENDING)
				{
					$lowBound = new DateTime();
					$lowBound->setTime(0, 0, 0);
					$query->addFilter('>=DEADLINE', $lowBound);

					$highBound = new DateTime();
					$highBound->setTime(23, 59, 59);
					$query->addFilter('<=DEADLINE', $highBound);
				}
				elseif($typeID === EntityCounterType::OVERDUE)
				{
					$highBound = new DateTime();
					$highBound->setTime(0, 0, 0);
					$query->addFilter('<DEADLINE', $highBound);
				}

				//echo '<pre>', $query->getQuery(), "</pre>";
				$results[] = $query;
			}
			else
			{
				$typeName = EntityCounterType::resolveName($typeID);
				throw new Main\NotSupportedException("The '{$typeName}' is not supported in current context");
			}
		}
		return $results;
	}
	public function calculateValue()
	{
		$result = 0;
		$queries = $this->prepareQueries(array('SELECT' => 'QTY'));
		foreach($queries as $query)
		{
			$dbResult = $query->exec();
			$fields = $dbResult->fetch();
			if(is_array($fields))
			{
				$result += (int)$fields['QTY'];
			}
		}
		return $result;
	}
	public function synchronize()
	{
		$this->currentValue = $this->calculateValue();
		if($this->code !== '')
		{
			\CUserCounter::Set($this->userID, $this->code, $this->currentValue, '**', '', false);
			if($this->isOneDay())
			{
				$this->refreshLastCalculatedTime();
			}
		}
	}
	/**
	 * Get details page URL.
	 * @param string $url Base URL.
	 * @return string
	 */
	public function prepareDetailsPageUrl($url = '')
	{
		$urlParams = array('counter' => strtolower($this->getTypeName()), 'clear_nav' => 'Y');
		self::externalizeExtras($this->extras, $urlParams);

		if($url === '')
		{
			$url = self::getEntityListPath();
		}
		return \CHTTP::urlAddParams($url, $urlParams);
	}
	public static function externalizeExtras(array $extras, array &$params)
	{
		if(!empty($extras))
		{
			foreach($extras as $k => $v)
			{
				$params["extras[{$k}]"] = $v;
			}
		}
	}
	public static function internalizeExtras(array $params)
	{
		return isset($params['extras']) && is_array($params['extras']) ? $params['extras'] : array();
	}
	/**
	 * Get entity list path.
	 * @static
	 * @return string
	 */
	protected function getEntityListPath()
	{
		return \CCrmOwnerType::GetListUrl($this->entityTypeID, false);
	}
	/**
	 * @param array|null $params List Params (MASTER_ALIAS, MASTER_IDENTITY and etc).
	 * @return array
	 */
	public function prepareEntityListFilter(array $params = null)
	{
		if(!is_array($params))
		{
			$params = array();
		}

		$union = array();
		$queries = $this->prepareQueries(array('SELECT' => 'ENTY', 'DISTINCT' => false));
		foreach($queries as $query)
		{
			$union[] = $query->getQuery();
		}

		$sql = implode(' UNION ALL ', $union);
		$masterAlias = isset($params['MASTER_ALIAS']) ? $params['MASTER_ALIAS'] : 'L';
		$masterIdentity = isset($params['MASTER_IDENTITY']) ? $params['MASTER_IDENTITY'] : 'ID';
		return array('__CONDITIONS' => array(array('SQL' => "{$masterAlias}.{$masterIdentity} IN ({$sql})")));
	}
}