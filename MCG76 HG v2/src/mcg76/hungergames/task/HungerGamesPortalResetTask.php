<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\arena\MapArenaModel;
use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\utils\LevelUtil;
use pocketmine\scheduler\PluginTask;
use mcg76\hungergames\level\GameLevelModel;

/**
 * HungerGamesPortalResetTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesPortalResetTask extends PluginTask {
	private $plugin;
	private $lv;
	private $action;
	public function __construct(HungerGamesPlugIn $plugin, GameLevelModel $lv, $action) {
		$this->plugin = $plugin;
		$this->lv = $lv;
		$this->action = $action;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			if (!empty($this->action) && $this->action==="open") {
				$output="";
				$this->lv->setPortalGate ( "open", $output);
				$this->plugin->log($this->lv->name."> Gate Open [".$output."]");
			}
			if (!empty($this->action) && $this->action==="close") {
				$output="";
				$this->lv->setPortalGate ( "close", $output);
				$this->plugin->log($this->lv->name."> Gate Close [".$output."]");
			}			
		} catch ( \Exception $e ) {
			$this->plugin->printError($e);
		}
	}

	public function onCancel() {
	}
}
