<?php
namespace LbCore\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Send request from VIP player to enable or disable vip bonuses
 */
class VIPRequest extends LbAsyncTask {
	/**@var string*/
	protected $playerName;
	/**@var bool*/
	protected $result;

	/**
	 * Prepare valid player name
	 * 
	 * @param string $playerName
	 * @return boolean
	 */
	public function __construct($playerName) {
		if (!$this->validate($playerName)) {
			return false;
		}
		$this->playerName = $playerName;
	}

	private function validate($name) {
		return preg_match('/[a-z_\-0-9]/i', $name);
	}

	/**
	 * Send request to db
	 */
	public function onRun() {
		$result = Utils::postURL(self::API_URI.'vip_request', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $this->playerName,
		), 5);
		$res = json_decode($result, true);
		$this->result = (bool) $res['vipenabled'];
	}

	/**
	 * Inform player about result, save his new status, update name
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
		$msg = !$this->result ? "VIP_DISABLED" : "VIP_ENABLED";
		if($player instanceof LbPlayer) {
			$player->sendLocalizedMessage($msg);
			$player->vipEnabled($this->result);
			$player->updateDisplayedName();
		}
	}
}
