<?php
namespace LbCore\task;

use LbCore\language\Translate;
use pocketmine\scheduler\PluginTask;
use VipLounge\VIPLounge;

use LbCore\LbCore;

/**
 * Run every tick and work with base plugin repeatable operations
 */
class TournamentTick extends PluginTask {
	const RESTART_TICK = 21600;
	/**@var int*/
	protected $lastTick = 0;
	/**@var int*/
	protected $chatCount = 0;
	/**@var bool*/
	protected $stopping = false;
	/**@var int*/
	protected $launchNumber = 0;
	/**@var Plugin*/
	private $plugin;

	/**
	 * 
	 * @param Plugin $plugin
	 */
	public function __construct($plugin) {
		parent::__construct($plugin);
		$this->lastTick = time();
		$this->plugin = $plugin;
	}

	/**
	 * Check for pushed players,
	 * update players count,
	 * clear chat,
	 * restart server each 6 hours
	 * 
	 * @param $currentTick
	 */
	public function onRun($currentTick) {
		if($this->stopping) {
			return false;
		}

		if(time() > $this->lastTick) {
			$this->lastTick = time();
			//check for pushed players (from VIP lounge) every 3 seconds
 			if ($this->lastTick % 3 == 0) {
				$players = $this->plugin->getServer()->getOnlinePlayers();
				foreach ($players as $player) {
					if ($player->isPushed()) {
 						VIPLounge::getInstance()->sendNPCMessage($player);
						$player->updatePushStatus(false);
 					}
 				}
 			}
		} else {
			return false;
		}

		// every 3 seconds
		if (is_int(time() / 3)) {
			$this->plugin->getServer()->getScheduler()->scheduleAsyncTask(new UpdatePlayerCount());
		}

		// clear array with players which wrote in chat over the last 2 seconds
 		$this->chatCount++;
 		if($this->chatCount === 2) {
 			$this->plugin->filter->clearRecentChat();
 			$this->chatCount = 0;
 		}
		
		$this->launchNumber++;
		
		// trying restart server
		$ticksUntilRestart = self::RESTART_TICK - $this->launchNumber;
		if ($ticksUntilRestart === 0) {
			Translate::getInstance()->broadcastMessageLocalized("RESTARTING");
			$this->plugin->getServer()->shutdown();
			$this->stopping = true;
		} else if ($ticksUntilRestart <= 5) {
			Translate::getInstance()->broadcastMessageLocalized("ABOUT_TO_RESTART", array($ticksUntilRestart, "s"), Translate::PREFIX_GAME_EVENT);
		}
	}
}