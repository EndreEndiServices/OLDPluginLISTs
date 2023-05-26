<?php
namespace LbCore\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Send registration request to db
 */
class RegisterRequest extends LbAsyncTask {
	/**@var string*/
	protected $playerName;
	/**@var string*/
	protected $email;
	/**@var string*/
	protected $password;
	/**@var bool*/
	protected $requestFailed = false;
	/**@var string*/
	protected $passwordHash;

	/**
	 * 
	 * @param string $playerName
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($playerName, $email, $password) {
		$this->playerName = $playerName;
		$this->email = $email;
		$this->password = $password;
	}

	/**
	 * Send player data to db
	 */
	public function onRun() {
		$this->passwordHash = password_hash($this->password, PASSWORD_BCRYPT);
		$results = Utils::postURL(self::API_URI.'register_request', array(
			'auth' => self::AUTH_STRING,
			'return' => true,
			'username' => $this->playerName,
			'hash'     => $this->passwordHash,
			'email' => $this->email,
		), 5);
		
		$data = json_decode($results, true);
		if(!isset($data['stat']) || $data['stat'] != "ok"){
			$this->requestFailed = true;
		}
	}

	/**
	 * Inform player about registration result
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
		if($player instanceof LbPlayer) {
			if($this->requestFailed){
				$player->sendLocalizedMessage("REGISTRATION_FAILED");
				return;
			}
			
			$player->sendLocalizedMessage("REGISTRATION_SUCCESS");
			$player->setPassHash($this->passwordHash, true);
			$player->savePassword($this->password);
			$player->setAsRegistered();
		}
	}
}
