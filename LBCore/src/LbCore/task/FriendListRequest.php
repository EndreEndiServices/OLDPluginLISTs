<?php
namespace LbCore\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Get list of friends and current requests for player
 */
class FriendListRequest extends LbAsyncTask {
	/**@var string*/
	protected $username;
	/**@var string*/
	protected $hash;
	/**@var \stdClass*/
	protected $friends;
	/**@var \stdClass*/
	protected $requests;
	/**@var bool*/
	protected $haveError = false;
	/**@var string*/
	protected $errorText = "";
	/**@var bool - get only friend requests*/
	protected $reqOnly = false;
	/**@var array*/
	protected $errorMap = array(
		"NOT_FOUND" => "PROFILE_NOT_FOUND",
		"NOT_VERIFIED" => "INCORRECT_HASH",
		"DATABASE_ERROR" => "UNKNOWN_ERROR",
	);

	/**
	 * 
	 * @param string $username
	 * @param string $hash
	 * @param bool $requestNotificationOnly
	 */
	public function __construct($username, $hash, $requestNotificationOnly = false) {
		$this->username = $username;
		$this->hash = $hash;
		$this->reqOnly = $requestNotificationOnly;
	}

	/**
	 * Send request to db
	 * 
	 * @return bool
	 */
	public function onRun() {
		$result = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=list&acceptor=1&requestor='.$this->username.'&auth='.$this->hash, array(), 5);
		if (isset($this->errorMap[$result])) {
			$this->setError($this->errorMap[$result]);
		} else {
			$data = json_decode($result, true);
			$this->friends = (object) $data['friends'];
			$this->requests = (object) $data['requests'];
		}
		
		return $this->haveError;
	}

	/**
	 * Show list to player
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
		// black magic
		$friends = (array) $this->friends;
		$requests = (array) $this->requests;
		
		$player = $server->getPlayer($this->username);	

		if($player instanceof Player) {
			if($this->reqOnly) {
				if(isset($requests['in']) && count($requests['in']) > 0) {
					$player->sendLocalizedMessage("FRIEND_LIST_REQUEST", array(count($requests['in'])));
				}
				return false;
			}
			if($this->haveError) {
				$player->sendLocalizedMessage("KNOWN_ERROR_PREFIX");
				$player->sendLocalizedMessage($this->errorText);
			} else {
				if(count($friends) > 0) {
					$player->sendLocalizedMessage("YOUR_FRIENDS");
					foreach($friends as $friend) {
						$player->sendMessage($friend);
					}
				} else {
					$player->sendLocalizedMessage("YOU_HAVE_NO_FRIENDS");
				}
				if(count($requests) > 0) {
					$player->sendLocalizedMessage("YOUR_REQUESTS");
					foreach($requests['in'] as $request) {
						$player->sendMessage($request);
					}
				} else {
					$player->sendLocalizedMessage("YOU_HAVE_NO_REQUESTS");
				}
			}
		}
	}
	
	/**
	 * 
	 * @param string $errorText
	 */
	private function setError($errorText) {
		$this->haveError = true;
		$this->errorText = $errorText;
		
	}
}
