<?php
/**
 * Created by PhpStorm.
 * User: ZZach
 * Date: 28/03/2019
 * Time: 20:23
 */

namespace Zedstar16\DevStats;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class StatsTask extends Task
{
    public function onRun(Int $currentTick)
    {
        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            if(in_array($p->getName(), Main::$indev)) {
                Main::addsc($p);
            }
        }
    }
}