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

/*
 * MCG76 SelfDestroyShell
* Copyright (C) 2014 minecraftgenius76
* YouTube Channel: http://www.youtube.com/user/minecraftgenius76
*
*
* SelfDestroyShell 
* ------------------------------------------------------------
* @author mcg76
*
*/
class SelfDestroyShell extends PluginTask {
	public $plugin;
	public $player;
	public $sx;
	public $sy;
	public $sz;

	/**
	 * 
	 * @param SafeCamp $pg
	 * @param unknown $pname
	 * @param unknown $x
	 * @param unknown $y
	 * @param unknown $z
	 */
	public function __construct(SkyWarsPlugIn $pg, Player $p, $x, $y, $z) {
		$this->owner = $pg;
		$this->plugin = $pg;
		$this->player = $p;
		$this->sx = $x;
		$this->sy = $y;
		$this->sz = $z;				
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \pocketmine\scheduler\Task::onRun()
	 */
	public function onRun($currentTick) {
		$this->log ( "Self destroy shell - " . $this->player->getName() );
		$builder = new BlockBuilder ( $this->plugin);
		$builder->removeShell($this->player, 4, $this->sx, $this->sy, $this->sz);
		$this->player->sendMessage("shell has self-destructed!");		
	}
	
	public function log($message) {
		$this->plugin->getLogger ()->info ( $message );
	}
}