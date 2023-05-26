<?php
namespace LbCore\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Send request for new friend
 */
class SendFriendRequest extends LbAsyncTask {
	/**@var string*/
	protected $requestor;
	/**@var string*/
	protected $acceptor;
	/**@var string*/
	protected $hash;
	
	protected $action = 'send';
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
		"ALREADY_FRIENDS" => "ALREADY_FRIENDS",
		"DUPLICATE_REQUEST" => "DUPLICATE_REQUEST",
		"NO_PENDING_REQUESTS" => "INVALID_REQUEST_DATA",
		"ACCEPTOR_INSUFFICENT_RANK" => "TOO_MANY_FRIENDS",
		"REQUESTOR_INSUFFICENT_RANK" => "TOO_MANY_FRIENDS",
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
	 * Send request, handling errors
	 * 
	 * @return bool
	 */
	public function onRun() {
		$friendList = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=list&acceptor=1&requestor='.$this->requestor.'&auth='.$this->hash, array(), 5);
		
		$friendList = json_decode($friendList, TRUE);
		if ($friendList && array_walk($friendList['requests']['in'], strtolower)) {
			if (in_array(strtolower($this->acceptor), $friendList['requests']['in'])) {
				$result = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=accept&acceptor='.$this->requestor.'&requestor='.$this->acceptor.'&auth='.$this->hash, array(), 10);
				$this->action = 'accept';
			} else {
				$result = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=request&requestor='.$this->requestor.'&acceptor='.$this->acceptor.'&auth='.$this->hash, array(), 10);
			}
		}
		
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
				if ($this->action == 'send') {
					$player->sendLocalizedMessage("FRIEND_REQUEST_SENT", array($this->acceptor));
				} elseif ($this->action == 'accept') {
					$player->sendLocalizedMessage("FRIEND_ACCEPTED", array($this->acceptor));
				}
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
