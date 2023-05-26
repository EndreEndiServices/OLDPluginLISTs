<?php
namespace LbCore\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Send request to remove player from friends
 */
class RemoveFriendRequest extends LbAsyncTask {
	/**@var string*/
	protected $requestor;
	/**@var string*/
	protected $acceptor;
	/**@var string*/
	protected $hash;
	
//	protected $friends;
//	protected $requests;
	/**@var bool*/
	protected $haveError = false;
	/**@var string*/
	protected $errorText = "";
	/**@var array*/
	protected $errorMap = array(
		"R_NOT_FOUND" => "PROFILE_NOT_FOUND",
		"A_NOT_FOUND" => "PLAYER_NOT_EXIST",
		"SAME_USER" => "TRYING_ADD_YOURSELF",
		"R_NOT_VERIFIED" => "INCORRECT_HASH",
		"DATABASE_ERROR" => "UNKNOWN_ERROR",
	);

	/**
	 * 
	 * @param string $requestor
	 * @param string $acceptor
	 * @param string $hash
	 */
	public function __construct($requestor, $acceptor, $hash) {
		$this->requestor = $requestor;
		$this->acceptor = $acceptor;
		$this->hash = $hash;
	}

	/**
	 * Send request to db, check for errors
	 * 
	 * @return bool
	 */
	public function onRun() {
		$result = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=remove&requestor='.$this->requestor.'&acceptor='.$this->acceptor.'&auth='.$this->hash, array(), 5);
		if (isset($this->errorMap[$result])) {
			$this->setError($this->errorMap[$result]);
		}
		
		return $this->haveError;
	}

	/**
	 * Inform player about result
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->requestor);

		if($player instanceof Player) {
			if($this->haveError) {
				$player->sendLocalizedMessage("KNOWN_ERROR_PREFIX");
				$player->sendLocalizedMessage($this->errorText);
			} else {
				$player->sendLocalizedMessage("FRIEND_REMOVED", array($this->acceptor));
			}
		}
	}
	
	/**
	 * @param string $errorText
	 */
	private function setError($errorText) {
		$this->haveError = true;
		$this->errorText = $errorText;
		
	}
}
