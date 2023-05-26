<?php
namespace LbCore\task;

use LbCore\LbCore;
use LbCore\task\LbAsyncTask;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Task for accept friend request
 */
class AcceptFriendRequest extends LbAsyncTask {
	/**@var string*/
    protected $initiator;
	/**@var string*/
    protected $target;
	/**@var string*/
    protected $hash;
	/**@var bool*/
    protected $haveError = false;
	/**@var string*/
    protected $errorText = "";
	/**@var array*/
	protected $errorMap = array(
		"R_NOT_FOUND" => "PROFILE_NOT_FOUND",
		"A_NOT_FOUND" => "PLAYER_NOT_EXIST",
		"SAME_USER" => "TRYING_ADD_YOURSELF",
		"NO_PENDING_REQUESTS" => "INVALID_REQUEST_DATA",
		"R_NOT_VERIFIED" => "INCORRECT_HASH",
		"DATABASE_ERROR" => "UNKNOWN_ERROR",
		"ACCEPTOR_INSUFFICENT_RANK" => "TOO_MANY_FRIENDS",
		"REQUESTOR_INSUFFICENT_RANK" => "TOO_MANY_FRIENDS",
	);
    
	/**
	 * @param string $initiator
	 * @param string $target
	 * @param string $hash
	 */
	public function __construct($initiator, $target, $hash) {
        $this->initiator = $initiator;
        $this->target = $target;
        $this->hash = $hash;
    }
    
	/**
	 * Send request to db
	 * 
	 * @return bool
	 */
	public function onRun() {
        $result = Utils::postURL('http://data.lbsg.net/apiv4/friend.php?action=accept&acceptor='.$this->target.'&requestor='.$this->initiator.'&auth='.$this->hash, array(), 5);
		if (isset($this->errorMap[$result])) {
			$this->setError($this->errorMap[$result]);
		}
		
		return $this->haveError;
    }
    
	/**
	 * send message about request result
	 * 
	 * @param Server $server
	 */
	public function onCompletion(Server $server) {
        $player = $server->getPlayer($this->target);
        if($player instanceof Player) {
            if($this->haveError) {
                $player->sendLocalizedMessage("KNOWN_ERROR_PREFIX");
                $player->sendLocalizedMessage($this->errorText);
            } else {
                $player->sendLocalizedMessage("FRIEND_ACCEPTED", array($this->initiator));
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
