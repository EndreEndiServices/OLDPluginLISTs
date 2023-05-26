<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;
use mcg76\hungergames\level\GameLevelModel;
use pocketmine/level/Level;
use mcg76\hungergames\level\GamePlayer;
use mcg76\hungergames\portal\MapPortal;
use mcg76\hungergames\utils\MagicUtil;
use pocketmine\entity\Effect;
use pocketmine\level\sound\LaunchSound;

/**
 * HungerGamesLevelPlayerToArenaTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesLevelPlayerToArenaTask extends PluginTask {
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
			if (empty ( $this->lv )) {
				return;
			}
			
			$start_time = microtime ( true );
			$k=1;
			foreach ( $this->lv->currentMap->livePlayers as $gamer ) {
				$targetWorldName = $gamer->levelName . "_TEMP";
				$this->lv->level->addSound ( new LaunchSound ( $gamer->player->getPosition () ), array (
						$gamer->player 
				) );
				MapPortal::teleportToMap ( $targetWorldName, $gamer->player );
				$this->lv->currentMap->enterArena ( $gamer );
				MagicUtil::addEffect ( $gamer->player, Effect::INVISIBILITY, 1 );
				foreach ( $this->lv->currentMap->livePlayers as $gp ) {
					if ($gp instanceof GamePlayer) {
						$gamer->hidePlayerFrom ( $gp->player );
					}
				}				
				$this->plugin->log ( "[HungerGamesLevelPlayerToArenaTask] ".$this->lv->type." | selected map " . $targetWorldName ." TP player [".$k++. "]". $gamer->player->getName () .")");
			}			
			$this->plugin->log ( "[HungerGamesLevelPlayerToArenaTask: took " . (microtime ( true ) - $start_time) );
		} catch ( \Exception $e ) {
			$this->plugin->printError ( $e );
		}
	}
	public function onCancel() {
	}
}
