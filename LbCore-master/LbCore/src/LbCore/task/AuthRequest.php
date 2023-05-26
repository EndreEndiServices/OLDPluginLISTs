<?php

namespace LbCore\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use LbCore\player\exceptions\WrongPassException;
use LbCore\LbCore;
use LbCore\player\exceptions\PlayerBaseException;

class AuthRequest extends LbAsyncTask {
	/*	 * @var int */

	protected $playerId;
	/*	 * @var string */
	protected $playerName;
	/*	 * @var string */
	protected $playerPassword;

	/** @var bool */
	protected $isNeedsNewPassHash;
	/*	 * @var string */
	protected $bcryptPassHash;
	/*	 * @var string */
	protected $passHash;

	/** @var bool */
	protected $isTruePass = false;

	/**
	 * @param string $playerName
	 * @param int $playerId
	 */
	public function __construct($playerName, $playerId, $isNeedsNewPassHash, $bcryptPassHash, $passHash, $password) {
		$this->playerName = $playerName;
		$this->playerId = $playerId;
		$this->isNeedsNewPassHash = $isNeedsNewPassHash;
		$this->bcryptPassHash = $bcryptPassHash;
		$this->passHash = $passHash;
		$this->playerPassword = $password;
	}

	/**
	 * Send request for player saved data
	 */
	public function onRun() {
		$this->isTruePass = $this->checkPass($this->playerPassword);
	}

	/**
	 * Prepare options by received data
	 * 
	 * @param Server $server
	 * @return bool
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);

		if ($player instanceof LbPlayer) {
			try {
				if ($this->isTruePass) {
					$player->setAuthenticated(true);
					$player->updateDisplayedName();
					$player->savePassword($this->playerPassword);
					$lbcore = LbCore::getInstance();
					if (!is_null($lbcore)) {
						$lbcore->onSuccessfulLogin($player);
					}
				} else {
					throw new WrongPassException($player);
				}
			} catch (PlayerBaseException $e) {
				$player->sendLocalizedMessage($e->getMessage());
			}
		}
	}

	private function checkPass($password) {
		if ($this->isNeedsNewPassHash === false) {
			return password_verify($password, $this->bcryptPassHash);
		} else {
			return hash('md5', $password) === $this->passHash;
		}
	}

}
