<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 02/10/2016
 * Time: 19:31
 */

namespace SliceKits\Task\Ghoul;

use pocketmine\level\Position;
use SliceKits\Necessary\Explosion;
use pocketmine\math\Vector3;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\item\Item;

use pocketmine\entity\Effect;

use SliceKits\Loader;

class GhoulTask extends PluginTask
{

    /**
     * GhoulTask constructor.
     * @param Loader $plugin
     * @param Player $player
     */
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
        $this->player->getInventory()->addItem(Item::get(405, 0, 1));

        $this->player->sendMessage("§c-> §aAgora você pode usar seu Kit!");
        $this->player->sendMessage("§c-> §aOlhe seu inventario");
    }
}