<?php

namespace SliceKits\Task\Stomper;

use pocketmine\level\Position;
use pocketmine\level\Explosion;
use pocketmine\math\Vector3;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Effect;

use pocketmine\item\Item;

use SliceKits\Loader;

class StomperTask extends PluginTask{
    
    public function __construct(Loader $plugin, Player $player) {
		parent::__construct ($plugin);
		$this->plugin = $plugin;
                $this->player = $player;
	}
        public function onRun($currentTick) {
		$this->player->teleport(new Vector3($this->player->getX(),128,$this->player->getZ()));
                
	}
}


