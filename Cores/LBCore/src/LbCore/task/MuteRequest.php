<?php
namespace LbCore\task;

use LbCore\task\LbAsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * Mute char for player
 */
class MuteRequest extends LbAsyncTask {
	/**@var int*/
	protected $playerId;
	/**@var string*/
    protected $playerName;
    /**@var string*/
    protected $moderatorName;
    /**@var int*/
    protected $playerMuteTime = 0;
    /**@var string*/
    protected $muteText;

	/**
	 * @param string $playerName
	 * @param string $moderatorName
	 */
    public function __construct($playerName, $moderatorName) {
        $this->playerName = $playerName;
        $this->moderatorName = $moderatorName;
        $this->playerMuteTime = 0;
    }

	/**
	 * Send request for player saved data
	 */
    public function onRun() {
		$link = new \mysqli('accessory.lbsg.net', 'ingamekits', 'jdyhu7c7olaP3', 'ingamekits'); 
        $result = $link->query('SELECT username, end_ban_date, ban_type FROM player_mute WHERE username like "'.$this->playerName.'"');
        $row = $result->fetch_assoc();
        if(is_null($row)){
            $this->muteText = "15 minutes";
            $this->muteTime = time()+900;
            $insert = $link->prepare('INSERT INTO player_mute (username, end_ban_date, ban_type) VALUES ("'.$this->playerName.'", '.$this->muteTime.', "15m")');
            $insert->execute();
        }else{
            if($row["ban_type"] == "15m"){
                $this->muteText = "1 hour";
                $this->muteTime = time()+3600;
            }else{
                $this->muteText = "24 hours";
                $this->muteTime = time()+86400;
            }
            $update = $link->prepare('UPDATE player_mute SET end_ban_date='.$this->muteTime.', ban_type="24h" WHERE username="'.$this->playerName.'"');
            $update->execute();
        }
        $link->close();
    }

    /**
	 * Prepare options by received data
	 * 
     * @param Server $server
     * @return bool
     */
    public function onCompletion(Server $server) {
		$player = $server->getPlayer($this->playerName);
        $moderator = $server->getPlayer($this->moderatorName);
        $player->setMuteTime($this->muteTime);
        $player->sendMessage(TextFormat::GRAY."You have been blocked from chat for ".$this->muteText."\n".TextFormat::GRAY."Moderator: ".$this->moderatorName.".  Be nicer once your chat comes back");
        $moderator->sendMessage(TextFormat::GRAY.$this->playerName." is mute for ".$this->muteText);
    }
    
}
