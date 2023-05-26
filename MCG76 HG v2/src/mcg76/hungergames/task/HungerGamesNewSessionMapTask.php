<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;

/**
 * HungerGamesNewSessionMapTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesNewSessionMapTask extends PluginTask {
	private $plugin;
	private $sourceMapName;
	public function __construct(HungerGamesPlugIn $plugin, $sourceMapName) {
		$this->plugin = $plugin;
		$this->sourceMapName = $sourceMapName;
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
			$success = $this->createSessionMap ( $this->sourceMapName );
			if ($success) {
				$this->plugin->log ( "[HG]HungerGamesNewSessionMapTask-> Unable to create new session map [" . $this->sourceMapName . "]" );
			}
			$this->plugin->log ( "[HG]HungerGamesNewSessionMapTask->createSessionMap took " . (microtime ( true ) - $start_time) );
		} catch ( \Exception $e ) {
			$this->plugin->printError ( $e );
		}
	}
	private function createSessionMap($sourceMapName) {
		$targetWorldName = $sourceMapName . "_TEMP";
		$this->plugin->log ( "[HG] HungerGamesNewSessionMapTask: target world name [" . $targetWorldName . "]" );
		LevelUtil::deleteSessionWorld ( $targetWorldName );
		return LevelUtil::createSessionWorld ( $sourceMapName, $targetWorldName );
	}
	public function onCancel() {
	}
}
