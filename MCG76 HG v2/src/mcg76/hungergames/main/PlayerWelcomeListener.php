<?php

namespace mcg76\hungergames\main;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\Server;
use mcg76\hungergames\portal\MapPortal;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerEvent;

/**
 * MCG76 PlayerWelcomeListener
 *
 * Copyright (C) 2014 minecraftgenius76
 * YouTube Channel: http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76@gmail.com
 *        
 */
class PlayerWelcomeListener implements Listener {
	public $plugin;
	public function __construct(HungerGamesPlugIn $plugin) {
		$this->plugin = $plugin;
	}
	
	/**
	 *
	 * @param PlayerJoinEvent $event        	
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			$this->onWelcomePlayer ( $event, $event->getPlayer () );
			$this->plugin->log ( "[HG] onPlayerJoin for player " . $event->getPlayer ()->getName () );
		}
		$this->showGameMode ( $event->getPlayer () );
		$this->showFirstTimerMessage ( $event->getPlayer () );
	}
	
	/**
	 *
	 * @param PlayerRespawnEvent $event        	
	 */
	public function onPlayerRespawn(PlayerRespawnEvent $event) {
		if ($event->getPlayer () instanceof Player) {
			if ($this->plugin->isOnSpawnTeleportPlayerToLobby () && ! empty ( $this->plugin->hubSpawnPos )) {
				$event->setRespawnPosition ( $this->plugin->hubSpawnPos );
			}
			$this->onWelcomePlayer ( $event, $event->getPlayer () );
			$this->plugin->log ( "[HG] PlayerWelcomeListener onPlayerRespawn for player " . $event->getPlayer ()->getName () );
		}
	}
	
	/**
	 *
	 * @param PlayerEvent $event        	
	 * @param Player $player        	
	 */
	public function onWelcomePlayer(PlayerEvent $event, Player $player) {
		$this->plugin->setGameDefaultPermissionNode ( $player );
		if ($event instanceof PlayerJoinEvent) {
			if ($this->plugin->isOnJoinTeleportPlayerToLobby ()) {
				if (round ( $player->x ) != round ( $this->plugin->hubSpawnPos->x ) && round ( $player->y ) != round ( $this->plugin->hubSpawnPos->y ) && round ( $player->z ) != round ( $this->plugin->hubSpawnPos->z )) {
					MapPortal::teleportingToLobby ( $player, $this->plugin->hubLevelName, $this->plugin->hubSpawnPos );
					$this->plugin->log ( "[HG] on-join teleport lobby location " . $this->plugin->hubSpawnPos->x . " " . $this->plugin->hubSpawnPos->y . " " . $this->plugin->hubSpawnPos->z );
				}
			}
		} elseif ($event instanceof PlayerRespawnEvent) {
			if ($this->plugin->isOnSpawnTeleportPlayerToLobby ()) {
				MapPortal::teleportingToLobby ( $player, $this->plugin->hubLevelName, $this->plugin->hubSpawnPos );
				$this->plugin->log ( "[HG] on-spawn teleport lobby location " . $this->plugin->hubSpawnPos->x . " " . $this->plugin->hubSpawnPos->y . " " . $this->plugin->hubSpawnPos->z );
				if (! is_null ( $this->plugin->hubSpawnPos )) {
					if (round ( $player->getSpawn ()->x ) != round ( $this->plugin->hubSpawnPos->x ) && round ( $player->getSpawn ()->y ) != round ( $this->plugin->hubSpawnPos->y ) && round ( $player->getSpawn ()->z ) != round ( $this->plugin->hubSpawnPos->z )) {
						$player->getLevel ()->setSpawnLocation ( $this->plugin->hubSpawnPos );
					}
				}
			}
		}
		if ($this->plugin->isOnJoinClearAllPlayerInventory ()) {
			$player->getInventory ()->clearAll ();
			HungerGameKit::clearAllInventories ( $player );
		}
		if ($this->plugin->isOnJoinClearAllPlayerEffects ()) {
			$player->removeAllEffects ();
		}
		if (! $player->isOp () && ! $player->isSurvival ()) {
			$player->setGamemode ( Player::SURVIVAL );
		}
		$player->getLevel ()->updateAllLight ( $player->getPosition () );
		$player->getLevel ()->updateAround ( $player->getPosition () );
	}
	
	private function showFirstTimerMessage(Player $player) {
		if ($this->plugin->enableTimeWelcomeText && $this->plugin->enableWelcomeBack) {
			$playerExist = $this->plugin->profileManager->isPlayerExist ( $player->getName () );			
			if (! $playerExist) {
				if ($this->plugin->enableTimeWelcomeText) {
					$player->sendMessage ( $this->plugin->firstTimeWelcomeText );
				}
			} else {
				if ($this->plugin->enableWelcomeBack) {
					$player->sendMessage ( "[HG] " . TextFormat::GREEN . $this->plugin->welcomeBackText1 . " [" . TextFormat::GOLD . $player->getName () .TextFormat::GREEN . "]" );
					$player->sendMessage ( TextFormat::GRAY . $this->plugin->welcomeBackText2 );
					$this->plugin->commandHandler->showMyWins ( $player );
				}
			}
		}
	}
	
	private function showGameMode(Player $player) {
		$modeName = "";
		switch ($player->getGamemode ()) {
			case Player::ADVENTURE :
				$modeName = "Adventure";
				break;
			case Player::CREATIVE :
				$modeName = "Creative";
				break;
			case Player::SURVIVAL :
				$modeName = "Survival";
				break;
			case Player::SPECTATOR :
				$modeName = "Spectator";
				break;
		}
		$player->sendMessage ( TextFormat::GRAY . "[HG] You are in " . TextFormat::RED . $modeName );
	}
}