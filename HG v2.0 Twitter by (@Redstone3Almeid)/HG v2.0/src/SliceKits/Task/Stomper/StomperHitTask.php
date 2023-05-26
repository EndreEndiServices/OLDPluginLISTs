<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 02/10/2016
 * Time: 11:02
 */

namespace SliceKits\Task\Stomper;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\level\Explosion;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Effect;

use SliceKits\Loader;

class StomperHitTask extends PluginTask{

    public function __construct(Loader $plugin, Player $player) {
        parent::__construct ($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }
    public function onRun($currentTick) {
        $explosion = new Explosion(new Position($this->player->x, ($this->player->y), $this->player->z, $this->player->getLevel()), 4);
        //$explosion->explodeA();
        $explosion->explodeB();

    }
}


