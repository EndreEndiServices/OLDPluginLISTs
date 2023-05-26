<?php
namespace LbCore\task;

use LbCore\language\Translate;
use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Get player statistics from db (like kills, death, coins)
 */
class StatsRequest extends LbAsyncTask {
	/**@var string*/
	protected $targetName;
	/**@var string*/
	protected $senderName;
	/**@var bool*/
	protected $result;
	/**@var int*/
	protected $coins = 0;
	/**@var int*/
	protected $kills = 0;
	/**@var int*/
	protected $deaths = 0;
	/**@var bool*/
	protected $isVIP = false;
	/**@var bool*/
	protected $isVIPplus = false;
	/**@var int*/
	protected $lastSeen = 0;
	/**@var string*/
	protected $lastIP = "unknown.lbsg.net";

	/**
	 * 
	 * @param string $targetName
	 * @param string $senderName
	 */
	public function __construct($targetName, $senderName) {
		$this->targetName = $targetName;
		$this->senderName = $senderName;
	}

	/**
	 * Send request to db
	 * 
	 * @return boolean
	 */
	public function onRun() {
		if(preg_match('/[^a-z_\-0-9]/i', $this->targetName)) {
			$this->result = false;
			return false;
		}
		$result = Utils::postURL(self::API_URI.'stats_request', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $this->targetName,
		), 5);

		$data = json_decode($result, true);
		if(is_int(intval($data['coins']))) {
			$this->coins = $data['coins'];
			$this->kills = $data['kills_total'];
			$this->deaths = $data['deaths_total'];
			if(stristr($data['productsBought'], '1') or stristr($data['productsBought'], '3')) {
				$this->isVIPplus = true;
			} elseif(stristr($data['productsBought'], '2')) {
				$this->isVIP = true;
			}
			$this->result = true;
		} else {
			$this->result = false;
		}
	}

	/**
	 * Show information to player or show error
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->senderName);

		if($player instanceof Player) {
			if($this->result === false) {
				$player->sendLocalizedMessage("ERR_PLAYER_NOT_FOUND", array(), Translate::PREFIX_ACTION_FAILED);
				
			} elseif ($this->kills || $this->deaths) {
				$player->sendLocalizedMessage("STATISTIC_HEADER", array($this->targetName));
				$player->sendLocalizedMessage("KILL_DEATH_COUNTS", array($this->kills, $this->deaths));
				
			} else {
				$player->sendLocalizedMessage("STATS_IS_EMPTY");
			}
		}
	}
}
