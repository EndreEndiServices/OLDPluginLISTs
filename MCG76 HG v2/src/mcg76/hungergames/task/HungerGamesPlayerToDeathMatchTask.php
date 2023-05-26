<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;
use mcg76\hungergames\level\GameLevelModel;
use mcg76\hungergames\level\GamePlayer;
use mcg76\hungergames\portal\MapPortal;
use mcg76\hungergames\utils\MagicUtil;
use pocketmine\entity\Effect;
use pocketmine\math\Vector3;
use pocketmine\level\sound\LaunchSound;
use pocketmine\level\sound\DoorSound;

/**
 * HungerGamesPlayerToDeathMatchTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesPlayerToDeathMatchTask extends PluginTask {
	private $plugin;
	private $lv;
	public function __construct(HungerGamesPlugIn $plugin, GameLevelModel $lv) {
		$this->plugin = $plugin;
		$this->lv = $lv;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			if (empty ( $this->lv ) || empty ( $this->lv->currentMap)) {
				return;
			}
			$start_time = microtime(true);
			$k=1;			
			foreach ( $this->lv->currentMap->livePlayers as $gamer ) {
				$gamer->player->teleport ( new Vector3 ( $this->lv->currentMap->deathMatchEnter->x, $this->lv->currentMap->deathMatchEnter->y, $this->lv->currentMap->deathMatchEnter->z ) );
				MagicUtil::addEffect ( $gamer->player, Effect::INVISIBILITY, 1 );
				$players [] = $gamer->player;
				$this->lv->level->addSound ( new LaunchSound ( $gamer->player->getPosition () ), $players );
				foreach ( $this->lv->currentMap->livePlayers as $gp ) {
					$gamer->showPlayerTo ( $gp->player );
				}
				unset($players);
				$this->plugin->log ( "[HungerGamesPlayerToDeathMatchTask] ".$this->lv->type." | death-match |" . $this->lv->currentMap->name ." TP player [".$k++. "]". $gamer->player->getName () .")");
			}			
			$this->plugin->log("[HungerGamesPlayerToDeathMatchTask: took ".(microtime(true)-$start_time));						
		} catch ( \Exception $e ) {
			$this->plugin->printError ( $e );
		}
	}
	public function onCancel() {
	}
}
