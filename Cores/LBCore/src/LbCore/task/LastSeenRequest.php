<?php
namespace LbCore\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Save last player's login time
 */
class LastSeenRequest extends LbAsyncTask {
	/**@var string*/
	protected $playerName;
	protected $playerIp;

	/**
	 * @param string $playerName
	 */
	public function __construct($playerName) {
		$this->playerName = $playerName;
		$player = Server::getInstance()->getPlayer($playerName);
		if ($player instanceof LbPlayer) {
			$this->playerName = $playerName;
			$this->playerIp = $player->getAddress();

		}
	}

	/**
	 * Check if player name is valid
	 * 
	 * @param string $playerName
	 * @return bool
	 */
	private function validate($playerName) {
		//return preg_match('/[^a-z_\-0-9]/i', $playerName);
		if(preg_match('/[^a-z_\-0-9]/i', $playerName)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Send request to db
	 */
	public function onRun() {
		if(!$this->validate($this->playerName)) {
			return false;
		}
		$username =  $this->playerName;
		$result = Utils::postURL('http://data.lbsg.net/apiv3/database.php', array(
			'auth' => self::AUTH_STRING,
			'return' => false,
			'cmd' => 'UPDATE login SET lastSeen = \''. time() .'\', lastIP = \''.$this->playerIp.'\' WHERE username = \'' . $username . '\'',
		), 5);
	}

	public function onCompletion(Server $server) {}
}
