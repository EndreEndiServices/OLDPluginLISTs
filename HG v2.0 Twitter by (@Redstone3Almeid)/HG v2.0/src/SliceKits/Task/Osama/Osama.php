<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 14/10/2016
 * Time: 20:44
 */

namespace SliceKits\Task\Osama;


use pocketmine\level\Position;
use SliceKits\Necessary\Explosion;
use pocketmine\math\Vector3;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\entity\Effect;

use SliceKits\Loader;

class Osama extends PluginTask
{

    public function __construct(Loader $plugin, Player $player) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }

    /**
     * Actions to execute when run
     *
     * @param $currentTick
     *
     * @return void
     */
    public function onRun($currentTick)
    {
        $this->player->getInventory()->removeItem(Item::get(282, 0, 1));
        $this->player->getInventory()->addItem(Item::get(369, 0, 1));

        $this->player->sendMessage("§c-> §aAgora você pode usar seu Kit!");
        $this->player->sendMessage("§c-> §aOlhe seu inventario");
    }

}