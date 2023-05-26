<?php

namespace VipLounge;

use pocketmine\plugin\Plugin;
use pocketmine\Server;
use LbCore\language\Translate;
use pocketmine\utils\TextFormat;
use LbCore\data\PluginData;

/**
 * VIPLounge class controls all VIP lounge options: creating guards, 
 * calling listener to handle moving and interact events
 *
 * @author minto
 */
class VIPLounge {
	
	/** @var VIPLounge*/
	private static $instance;	
	/** @var string, contains current game plugin name*/
	private $gameType;
	/** @var array */
	private $loungeGuard = array();
	/*cafe sign coords*/
	public static $cafeSigns = array(
		"minX" => -2,
		"maxX" => 34,
		"minZ" => 93,
		"maxZ" => 98,
		"minY" => 29,
		"maxY" => 30
	);
	
	const UPDATE_FREQUENCY = 5;
	const FOOD_LIMIT = 20;//max allowed number of food items
	/** vip tile coords*/
	const VIP_TILE_COORD_Y = 30;
	const VIP_TILE_COORD_Z = 83;
	/*cure types*/
	const CURE_TYPE_COFFEE = "coffee";
	const CURE_TYPE_DRINK = "drink";
	
	private function __construct() {
		$this->gameType = PluginData::getGameType();
		//protect
	}
	
	private function __clone() {
		//protect
	}
	
	private function __wakeup() {
		//protect
	}
	
	/**
	 * @return VIPLounge
	 */
	static public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
	
	/**
	 * enable base classes - self, EventListener and GuardsTask
	 * 
	 * @param Plugin $plugin
	 */
	public static function enable(Plugin $plugin) {
		self::getInstance();		
		Server::getInstance()->getPluginManager()->registerEvents(
			new VIPLoungeEventListener(), $plugin
		);
		Server::getInstance()->getScheduler()->scheduleRepeatingTask(
			new VIPLoungeGuardsTask($plugin), self::UPDATE_FREQUENCY
		);
	}

	
	/**
	 * Creation of guard by coords from file
	 * 
	 * @param $guardAreaFile
	 */
	public function initLoungeGuards($guardAreaFile = null) {
		if (is_null($guardAreaFile)) {
			$guardAreaFile = __DIR__.'/../Data/'.$this->gameType.'/loungeGuardAreas.json';
		}
		$level = Server::getInstance()->getDefaultLevel();
		$this->loungeGuard = array();		
		//collect data from json and apply this to new guard
		if ($json = file_get_contents($guardAreaFile)) {
			$areas = json_decode($json);
			foreach ($areas as $areaData) {
				$spawnRotation = $areaData->guardRotation;
				$knockbackVector = $areaData->knockbackVector;
				foreach ($areaData->guardSpawnPoints as $spawnPoint) {
					$npc = new VIPLoungeSecurity(
						$spawnPoint, $level, $areaData->area, $spawnRotation, $knockbackVector
					);
					$npc->spawnToAll();
					$npc->chunk->allowUnload = false;
					$this->loungeGuard[] = $npc;
				}
			}
		}
	}

	
	/**
	 * Method gets info about current initialized guards
	 * 
	 * @return object
	 */
	public function getLoungeGuards() {
		return $this->loungeGuard;
	}
	
	/**
	 * Send message from NPC to player
	 * 
	 * @param LbPlayer $player
	 */
	public function sendNPCMessage($player) {
		$messages = Translate::getInstance()->getTranslatedString($player->language, "VIP_LOUNGE_ERROR");
 		$message = $messages[rand(0, count($messages) - 1)];
		$nps = Translate::getInstance()->getTranslatedString($player->language, "BOUNCER_NPC");
 		$player->sendMessage(TextFormat::GRAY . $nps . " " . TextFormat::WHITE . $message);
 	}
	
}
