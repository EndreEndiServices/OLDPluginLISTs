<?php
namespace Sergey_Dertan\SClearLagg\Task;

use pocketmine\utils\TextFormat as F;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use Sergey_Dertan\SClearLagg\SClearLaggMainFolder\SClearLaggMain;

/**
 * Class ClearTask
 * @package Sergey_Dertan\SClearLagg\Task
 */
class ClearTask extends PluginTask
{
    /**
     * @param SClearLaggMain $main
     */
    function __construct(SClearLaggMain $main)
    {
        parent::__construct($main);
        $this->plugin = $main;
    }

    /**
     * @param $currentTick
     */
    function onRun($currentTick)
    {
        $msg = SClearLaggMain::getInstance()->config->get("Clear-msg");
        $msg = str_replace("@count", SClearLaggMain::getInstance()->getEntityManager()->removeEntities(), $msg);
        Server::getInstance()->broadcastMessage(F::GREEN . $msg);
    }
}