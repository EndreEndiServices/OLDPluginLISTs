<?php

namespace mcg76\plugin\worldedit;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\tile\Sign;
use mcg76\util\SimplePortal\portal\Portal;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\event\player\PlayerItemHeldEvent;

/**
 * MCPE World Edit - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is". 
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2015 minecraftgenius76
 * 
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *
 */

class WorldEditListener implements Listener {
	public $pgin;
	public function __construct(WorldEditPlugIn $pg) {
		$this->pgin = $pg;
	}
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$block = $event->getBlock ();
		$x = $block->x;
		$y = $block->y;
		$z = $block->z;
		if ($this->getPlugIn ()->pos_display_flag == 1) {
			$event->getPlayer ()->sendMessage ("[WE] touching ". $block . " [x=" . round($block->x) . " y=" . round($block->y) . " z=" . round($block->z) . "] " );
		}
	}
	
	public function onBlockBreak(BlockBreakEvent $event) {
		$b = $event->getBlock ();
		$player = $event->getPlayer ();
		$output = "";
		$session = &$this->getCommands ()->session ( $player );		
		if ($session != null && $session ["wand-usage"] == true) {
			if (! isset ( $session ["wand-pos1"] ) || $session ["wand-pos1"] == null) {
				$session ["wand-pos1"] = $b;
				$this->getCommands ()->setPosition1 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
				$player->sendMessage ( $output );
				return;
			}
			if (! isset ( $session ["wand-pos2"] ) || $session ["wand-pos2"] == null) {
				$session ["wand-pos2"] = $b;
				$this->getCommands ()->setPosition2 ( $session, new Position ( $b->x - 0.5, $b->y, $b->z - 0.5, $player->getLevel () ), $output );
				$player->sendMessage ( $output );
				return;
			}
		}
	
	}
	protected function getPlugIn() {
		return $this->pgin;
	}
	protected function getCommands() {
		return $this->pgin->weCommands;
	}
	protected function log($msg) {
		return $this->pgin->getLogger ()->info ( $msg );
	}
}