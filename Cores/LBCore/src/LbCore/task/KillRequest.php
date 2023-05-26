<?php
namespace LbCore\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Save player death to db, increment kills counter
 */
class KillRequest extends LbAsyncTask {
	/**@var string*/
	protected $preyName;
	/**@var string*/
	protected $killerName;

	/**
	 * 
	 * @param Player $prey
	 * @param Player $killer
	 */
	public function __construct(Player $prey, Player $killer) {
		$this->preyName = $prey->getName();
		$this->killerName = $killer->getName();
	}

	/**
	 * Validate player name
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function validate($name) {
		if(preg_match('/[^a-z_\-0-9]/i', $name)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Save prey and killer data in db
	 * 
	 * @return boolean
	 */
	public function onRun() {
		if (!$this->validate($this->preyName) || !$this->validate($this->killerName)) {
			return false;
		}
		$preyName = $this->preyName;
		Utils::postURL(self::API_URI.'kill_request', array(
			'auth' => self::AUTH_STRING,
			'return' => false,
			'username' => $preyName,
			'action'   => 'killed',
		), 6);
		
		$killerName = $this->killerName;
		Utils::postURL(self::API_URI.'kill_request', array(
			'auth' => self::AUTH_STRING,
			'return' => false,
				'username' => $killerName,
				'action'   => 'killer',
		), 6);
	}

	public function onCompletion(Server $server) {}
}
