<?php

namespace mcg76\skywars;

use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
/**
 * MCG76 CountDownTimer
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 @author minecraftgenius76@gmail.com
 *
 */
class CountDownTimer extends PluginTask {
	public $plugin;
	public $targetTime;
	
	/**
	 *
	 * @param plugin $pg        	
	 * @param unknown $pname        	
	 * @param unknown $x        	
	 * @param unknown $y        	
	 * @param unknown $z        	
	 */
	public function __construct(SkyWarsPlugIn $pg, $targetTime) {
		$this->owner = $pg;
		$this->plugin = $pg;
		$this->targetTime = $targetTime;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \pocketmine\scheduler\Task::onRun()
	 */
	public function onRun($currentTick) {
		// $this->log ( "starting skywars game countdown timer " );
		if ($this->plugin->countDownCounter>0) {
			$this->plugin->countDownCounter --;
		}
		if ($this->plugin->countDownCounter != null && $this->plugin->countDownCounter > 0) {
			$message = 'Skywars play starting in ' . $this->plugin->countDownCounter . ' minute(s) ';
			$this->plugin->getServer ()->broadcastMessage ( $message );
		} else {
			// handle case only one player join the game
			if ($this->plugin->gamemode == 1) {
				if (count ( $this->plugin->skywarsPlayersWithShell ) == 1) {
					$this->plugin->gamemode = 0;
					$builder = new BlockBuilder ( $this->plugin );
					$cmd = new SkyWarsCommand ( $this->plugin );
					foreach ( $this->plugin->skyplayers as $player ) {
						$pos = $this->plugin->skywarsPlayersWithShell [$player->getName ()];
						$builder->removeShell ( $player->level, 4, $pos->x, $pos->y, $pos->z );
						$cmd->gotolobby ( $player );
					}
					$this->plugin->getServer ()->broadcastMessage ( "---------------------------------" );
					$this->plugin->getServer ()->broadcastMessage ( "Not enough players join Skywars, please start over!" );
					$this->plugin->getServer ()->broadcastMessage ( "---------------------------------" );
				}
			}
			
			// more playes
			if ($this->plugin->gamemode == 1) {
				if (count ( $this->plugin->skywarsPlayersWithShell ) > 1) {
					$this->plugin->getServer ()->broadcastMessage ( "Skywars started!" );
					$this->plugin->countDownCounter=0;
					// remove timer
					$this->plugin->startPlayTime = null;
					// update game mode
					$this->plugin->gamemode = 2;
					// remove player shell and start the game
					$builder = new BlockBuilder ( $this->plugin );
					foreach ( $this->plugin->skyplayers as $player ) {
						if (isset ( $this->plugin->skywarsPlayersWithShell [$player->getName ()] )) {
							$pos = $this->plugin->skywarsPlayersWithShell [$player->getName ()];
							$builder->removeShell ( $player->level, 4, $pos->x, $pos->y+10, $pos->z );
							$this->log ( TextFormat::RED . 'players :' . $player->getName () );
							// equip player
							ArenaKit::getSkywarKit ( $player );
							unset ( $this->plugin->skywarsPlayersWithShell [$player->getName ()] );
							// change player gamemode to speculator
							// $player->setGamemode ( 0 );
						}
					}
					$this->plugin->getServer ()->broadcastMessage ( "Go!" );
				}
			}
		}
	}
	public function log($message) {
		$this->plugin->getLogger ()->info ( $message );
	}
}