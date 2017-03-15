<?
/**
 * This class is TEMPORAL, it will be removed as soon as possible without notification. DO NOT use it directly or implicitly.
 * Remove after changeset dcd03b1dfdfa is in stable.
 *
 * @access private
 * @internal
 */

namespace Bitrix\Tasks\Util\User;

final class Mock extends \CUser
{
	private $id;

	static $cache = array();

	public function __construct($id)
	{
		parent::__construct();

		$this->id = intval($id);
	}

	public static function getInstance($id)
	{
		if(!array_key_exists($id, static::$cache))
		{
			static::$cache[$id] = new static($id);
		}

		return static::$cache[$id];
	}

	public function getId()
	{
		return $this->id;
	}

	public function isAuthorized()
	{
		return true;
	}
}