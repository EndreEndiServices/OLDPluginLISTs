<?php
namespace LbCore\task;

use LbCore\player\LbPlayer;
use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;

/**
 * Get data for player when he is logged in
 */
class CheckMuteRequest extends LbAsyncTask {


    protected $playerMuteTime = 0;

	/**
	 * @param string $playerName
	 * @param int $playerId
	 */
    public function __construct($playerName, $playerId) {
        $this->playerName = $playerName;
        $this->playerId = $playerId;
    }

	/**
	 * Send request for player saved data
	 */
    public function onRun() {		
		
		$url = 'http://104.131.74.238/lbcore.php';
		$postParam = array(
            'auth' => self::AUTH_STRING,
            'action' => 'getEndBanDate',
            'username' => $this->playerName,
        );
		
        $result = Utils::postURL($url, $postParam, 5);
		$result = json_decode($result, true);
		
		if ($result && $result['state'] == 'success') {
			$this->playerMuteTime = $result['data']['end_ban_date'];
		} else if ($result['state'] == 'fail') {
			echo $result['message'].PHP_EOL;
		}
    }

    /**
	 * Prepare options by received data
	 * 
     * @param Server $server
     * @return bool
     */
    public function onCompletion(Server $server) {
		if($this->playerMuteTime && $this->playerMuteTime > 0) {
			$player = $server->getPlayer($this->playerName);		
			if ($player instanceof LbPlayer) {
				$player->setMuteTime($this->playerMuteTime);
			}
		}
    }

}
