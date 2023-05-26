<?php
namespace LbCore\task;

use LbCore\language\Translate;
use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Get amount of coins for specified player
 */
class CoinsRequest extends LbAsyncTask {
	/**@var string - name of coins owner*/
	protected $ownerName;
	/**@var string - name of player who makes request*/
	protected $senderName;
	/**@var int|bool*/
	protected $result;
	
	/**
	 * 
	 * @param string $ownerName
	 * @param string $senderName
	 */
	public function __construct($ownerName, $senderName) {
		$this->ownerName = $ownerName;
		$this->senderName = $senderName;
	}
	
	/**
	 * Send request for coins to db
	 * 
	 * @return int|boolean
	 */
	public function onRun() {
		if(preg_match('/[^a-z_\-0-9]/i', $this->ownerName)) {
			$this->result = false;
			return false;
		}
		$result = Utils::postURL(self::API_URI.'coins_request', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $this->ownerName,
		), 5);

		$data = json_decode($result, true);
		if(is_int(intval($data['coins']))) {
			$this->result = $data['coins'];
		} else {
			$this->result = false;
		}
	}
	
	/**
	 * Inform player about result
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->senderName);
		
		if($player instanceof Player) {
			if($this->result === false) {
				$player->sendLocalizedMessage(
					"PASSWORD_CHANGED", array(), Translate::PREFIX_PLAYER_ACTION
				);
			} else {
				if($player->getName() === $this->ownerName) {
					$player->sendLocalizedMessage(
						"YOUR_COINS", array($this->result), Translate::PREFIX_PLAYER_ACTION
					);
				} else {
					$player->sendLocalizedMessage(
						"PLAYER_HAS_COINS", array($this->ownerName, $this->result), Translate::PREFIX_PLAYER_ACTION
					);
				}
			}
		}
	}
}
