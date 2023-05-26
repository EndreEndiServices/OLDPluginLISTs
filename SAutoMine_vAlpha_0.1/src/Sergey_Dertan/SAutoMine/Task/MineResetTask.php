<?php
namespace Sergey_Dertan\SAutoMine\Task;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as F;
use Sergey_Dertan\SAutoMine\SAutoMineMainFolder\SAutoMineMain;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\math\Vector3;


class MineResetTask extends PluginTask
{

    function __construct(SAutoMineMain $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    function onRun($currentTick)
    {
        $pos = $this->plugin->config->getAll()["MinePos"];
        $ids = $this->plugin->config->getAll()["MineBlocks"];
        if (count($pos) !== 0 and count($ids) !== 0) {
            $maxX = $pos["max"][0];
            $minX = $pos["min"][0];
            $maxY = $pos["max"][1];
            $minY = $pos["min"][1];
            $maxZ = $pos["max"][2];
            $minZ = $pos["min"][2];
            for ($x = $minX; $x <= $maxX; ++$x) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    for ($z = $minZ; $z <= $maxZ; ++$z) {
                        $id = $ids[mt_rand(0, count($ids) - 1)];
                        Server::getInstance()->getDefaultLevel()->setBlock(new Vector3($x, $y, $z), Block::get($id));
                    }
                }
            }
            Server::getInstance()->broadcastMessage(F::YELLOW . $this->plugin->config->getAll()["ResetMsg"]);
        }
    }
}