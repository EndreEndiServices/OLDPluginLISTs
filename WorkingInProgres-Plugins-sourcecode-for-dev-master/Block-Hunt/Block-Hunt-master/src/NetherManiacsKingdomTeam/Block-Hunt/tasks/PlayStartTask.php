<?php

namespace NetherManiacsKingdom\Block-Hunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use mcg76\game\blockhunt\BlockHuntPlugIn;


class PlayStartTask extends PluginTask {
	private $plugin;
	private $cancelled = false;	
	
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	public function onRun($ticks) {
		if ($this->cancelled) {
			return;		
		}		
		$this->plugin->game_mode = 99;
	}
	
	public function onCancel() {
		$this->cancelled = true;	
		parent::onCancel();
	}
}
