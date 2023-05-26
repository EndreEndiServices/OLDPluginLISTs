<?php
namespace LbCore\task;

use LbCore\language\Translate;
use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Send player's money to other player
 */
class CoinsTransfer extends LbAsyncTask {
	const OPERATION_INCREASE = "plus";
	const OPERATION_DECREASE = "minus";	
	/**@var string*/
	protected $from;
	/**@var string*/
	protected $to;
	/**@var int*/
	protected $amount;
	/**@var string*/
	protected $result;

	/**
	 * 
	 * @param string $from
	 * @param string $to
	 * @param int $amount
	 */
	public function __construct($from, $to, $amount) {
		$this->from = $from;
		$this->to = $to;
		$this->amount = intval($amount);
	}

	/**
	 * Send request to db after some checks
	 * 
	 * @return boolean
	 */
	public function onRun() {
		// checking name
		if(preg_match('/[^a-z_\-0-9]/i', $this->to)) {
			$this->result = false;
			return false;
		}
		$data = $this->getPlayerCoinsData($this->from);
		if (is_int(intval($data['coins']))) {
			if ($this->amount < $data['coins']) {
				$data = $this->getPlayerCoinsData($this->to);
				if (is_int(intval($data['coins']))) {
					$this->changePlayerCoins($this->from, $this->amount, self::OPERATION_DECREASE);
					$this->changePlayerCoins($this->to, $this->amount, self::OPERATION_INCREASE);
					$this->result = "success";
				} else {
					$this->result = "noexist";
				}
			} else {
				$this->result = false;
			}
		} else {
			$this->result = false;
			return false;
		}
	}

	/**
	 * Inform sender about results
	 * 
	 * @param Server $server
	 * @return boolean
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->from);
		if($player instanceof Player) {
			if($this->result === false) {
				$player->sendLocalizedMessage("ERR_INSUFFICENT_COINS", array(), Translate::PREFIX_PLAYER_ACTION);
				return false;
			}
			if($this->result === "noexist") {
				$player->sendLocalizedMessage("ERR_INSUFFICENT_COINS", array(), Translate::PREFIX_PLAYER_ACTION);
				return false;
			}
			$player->sendLocalizedMessage("SENT_COINS", array($this->amount, $this->to));
		}
	}
	
	/**
	 * Get info about player coins
	 * 
	 * @param string $playerName
	 * @return array
	 */
	private function getPlayerCoinsData($playerName) {
		$queryResult = Utils::postURL(self::API_URI.'coins_request', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $playerName,
		), 5);
		return json_decode($queryResult, true);
	}
	
	/**
	 * Update amount of coins by player name
	 * 
	 * @param string $playerName
	 * @param int $coinsNumber
	 * @param string $operation
	 */
	private function changePlayerCoins($playerName, $coinsNumber, $operation) {
		Utils::postURL(self::API_URI.'coins_transfer', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'action' => $operation,
			'username' => $playerName,
			'amount'  => $coinsNumber,
		), 10);
	}
}
