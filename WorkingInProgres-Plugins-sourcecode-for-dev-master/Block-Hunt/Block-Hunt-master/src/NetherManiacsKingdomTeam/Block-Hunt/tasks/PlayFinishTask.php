<?php

namespace NetherManiacsKingdom\Block-Hunt\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\event\Cancellable;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\BlockHuntGameKit;


class PlayFinishTask extends PluginTask {
	private $plugin;
	private $cancelled = false;
	private $arena;
	public function __construct(BlockHuntPlugIn $plugin, ArenaModel $arena) {
		$this->plugin = $plugin;
		$this->arena = $arena;
		parent::__construct ( $plugin );
	}
	public function onRun($ticks) {
		if ($this->cancelled) {
			return;
		}		
		$this->plugin->controller->announceArenaGameFinish ( $this->arena );
	}
	public function onCancel() {
		$this->cancelled = true;
		parent::onCancel ();
	}
}
