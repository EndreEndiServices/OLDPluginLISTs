<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;

/**
 * HungerGamesMapResetTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesMapResetTask extends PluginTask {
	private $plugin;
	private $arena;
	public function __construct(HungerGamesPlugIn $plugin, MapArenaModel $arena) {
		$this->plugin = $plugin;
		$this->arena = $arena;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			$start_time = microtime(true);
			$newLevel = $this->resetMap ( $this->arena );
			$this->plugin->log("[HungerGamesMapResetTask->resetMap took ".(microtime(true)-$start_time));
		} catch ( \Exception $e ) {
			$this->plugin->printError($e);
		}
	}
	private function resetMap(MapArenaModel $arena) {
		$targetWorldName = $arena->levelName . "_TEMP";
		LevelUtil::deleteSessionWorld ( $targetWorldName );
		$this->plugin->log("[HG] deleted [" . $targetWorldName . "]");
	}
	private function getArenaLevel(MapArenaModel $arena) {
		$level = null;
		foreach ( $this->plugin->getServer ()->getLevels () as $le ) {
			if ($le->getName () === $arena->levelName) {
				$level = $le;
				break;
			}
		}
		if ($level == null) {
			$this->plugin->getServer ()->loadLevel ( $arena->levelName );
			$level = $this->plugin->getServer ()->getLevelByName ( $arena->levelName );
		}
		return $level;
	}
	public function onCancel() {
	}
}
