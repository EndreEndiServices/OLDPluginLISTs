<?php
namespace LbCore\task;

use LbCore\language\Translate;
use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Change password request
 */
class ChangePassword extends LbAsyncTask {
	/**@var string*/
	private $newPassHash;
	/**@var string*/
	private $oldPassHash;
	/** @var string */
	private $bcryptPassHash;
	/**@var string*/
	private $playerName;
	/**@var bool*/
	private $result;
	/**@var bool*/
	private $failed = false;
	
	private $oldBcryptPassHash;
	
	private $saveNewPass;
	
	/**
	 * @param string $oldBcryptPassHash
	 * @param string $newPassHash
	 * @param string $playerName
	 * @param string $oldPassHash
	 * @param string $newPassHash
	 */
	public function __construct($oldBcryptPassHash, $bcryptPassHash, $playerName, $oldPassHash, $newPassHash) {
		$this->bcryptPassHash = $bcryptPassHash;
		$this->newPassHash = $newPassHash;
		$this->oldPassHash = $oldPassHash;
		$this->playerName = $playerName;
		$this->oldBcryptPassHash = $oldBcryptPassHash;
		$this->saveNewPass = $bcryptPassHash;
	}
	
	/**
	 * Send new password data to db
	 * 
	 * @return boolean
	 */
	public function onRun() {
		if(preg_match('/[^a-z_\-0-9]/i', $this->playerName)) {
			$this->result = false;
			return false;
		}
		
		$this->bcryptPassHash = password_hash($this->bcryptPassHash, PASSWORD_BCRYPT);
		$this->newPassHash = $this->getStringHash($this->newPassHash);
		$this->oldPassHash = $this->getStringHash($this->oldPassHash);
		
		$results = Utils::postURL(self::API_URI.'password_change', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $this->playerName,
			'new_hash' => '',
			'old_hash' => $this->oldPassHash,
			'bcrypt_hash' => $this->bcryptPassHash,
		), 7);
		$data = json_decode($results, true);
		if (!isset($data['stat']) || $data['stat'] != "ok") {
			$this->failed = true;
		}
	}

	/**
	 * Inform player about result, change back chat mode
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
		if($player instanceof LbPlayer) {
			if ($this->failed) {
				$player->sendLocalizedMessage("PASSWORD_NOT_CHANGED");
				$player->changeChatModeToNormal();
				return;
			}
			
			$player->setNeedNewPass(false);
			$player->setPassHash($this->newPassHash, true);
			$player->savePassword($this->saveNewPass);
			$player->sendLocalizedMessage("PASSWORD_CHANGED", array(), Translate::PREFIX_PLAYER_ACTION);
		}
	}
	
	
	private function getStringHash(string $somePass) {
		$passHash = '';
		if (empty($this->oldBcryptPassHash)) {
			$passHash = hash('md5', $somePass);
		} else {
			$passHash = password_hash($somePass, PASSWORD_BCRYPT);
		}
		
		return $passHash;
	}
}
