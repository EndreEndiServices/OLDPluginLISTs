<?php
namespace MCFTWARS\task;

use pocketmine\scheduler\PluginTask;
use MCFTWARS\MCFTWARS;
use pocketmine\Player;
use pocketmine\level\Position;
class TeleportTask extends PluginTask {
	private $player, $pos;
	public function __construct(MCFTWARS $plugin, Player $player, Position $pos) {
		parent::__construct($plugin);
		$this->player = $player;
		$this->pos = $pos;
	}
	public function onRun($currentTick) {
		$this->player->teleport($this->pos);
	}
}