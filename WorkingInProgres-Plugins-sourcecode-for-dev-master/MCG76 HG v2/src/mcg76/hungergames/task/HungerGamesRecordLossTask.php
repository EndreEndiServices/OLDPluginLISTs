<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;
use mcg76\hungergames\level\GameLevelModel;

/**
 * HungerGamesRecordLossTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesRecordLossTask extends PluginTask {
	private $plugin;
	private $lv;
	private $playerName;
	public function __construct(HungerGamesPlugIn $plugin, GameLevelModel $lv, $playerName) {
		$this->plugin = $plugin;
		$this->lv = $lv;
		$this->playerName = $playerName;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			$start_time = microtime ( true );
			$this->plugin->profileManager->addPlayerLoss ( $this->playerName );
			$this->plugin->log ( "[HungerGamesRecordLossTask->addPlayerLoss took " . (microtime ( true ) - $start_time));
			$start_time = microtime ( true );
			if (!empty($this->lv->currentMap)) {
				$this->plugin->storyManager->upsetPlayerLevelLoss ( $this->playerName, $this->lv->type, $this->lv->currentMap->name );
				$this->plugin->log ( "[HungerGamesRecordLossTask->upsetPlayerLevelLoss took " . (microtime ( true ) - $start_time) );
			}
		} catch ( \Exception $e ) {
			$this->plugin->printError ( $e );
		}
	}
	public function onCancel() {
	}
}
