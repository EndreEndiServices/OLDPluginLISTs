<?php

namespace mcg76\hungergames\task;

use mcg76\hungergames\main\HungerGamesPlugIn;
use mcg76\hungergames\level\GameLevelModel;
use mcg76\hungergames\level\GamePlayer;
use mcg76\hungergames\utils\MagicUtil;
use pocketmine\scheduler\PluginTask;

/**
 * HungerGamesWorldEventTask
 *
 * Copyright (C) 2015 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author MCG76
 *        
 */
class HungerGamesWorldEventTask extends PluginTask {
	private $plugin;
	
	/**
	 *
	 * @param HungerGamesPlugIn $plugin        	
	 */
	public function __construct(HungerGamesPlugIn $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			foreach ( $this->plugin->getAvailableLevels () as $lv ) {
				if ($lv instanceof GameLevelModel) {
					if (count ( $lv->joinedPlayers ) > 0) {
						if ($lv->type === GameLevelModel::LEVEL_TWO || $lv->type === GameLevelModel::LEVEL_VIP ) {
							$this->generateRandomEffect ( $lv );
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			$this->plugin->printError ( $e );
		}
	}
	
	/**
	 *
	 * @param GameLevelModel $lv        	
	 */
	public function generateRandomEffect(GameLevelModel $lv) {
		if (! empty ( $lv->currentMap ) && count ( $lv->currentMap->livePlayers ) > 0 && ($lv->type === 2)) {
			if ($lv->currentStep === GameLevelModel::STEP_HUNTING or $lv->currentStep === GameLevelModel::STEP_DEATH_MATCH) {
				foreach ( $lv->currentMap->livePlayers as $gplayer ) {
					if ($gplayer instanceof GamePlayer) {
						$eid = MagicUtil::generateRandomEffects ();
						MagicUtil::addEffect ( $gplayer->player, $eid, 80 );
						$particleName = MagicUtil::matchEffectParticles ( $eid );
						if (! is_null ( $eid ) && ! is_null ( $particleName )) {
							MagicUtil::addParticles ( $lv->currentMap->level, "explode", $gplayer->player->getPosition (), 120 );
						}
					}
				}
			}
		}
	}
	
	
	public function onCancel() {
	}
}
