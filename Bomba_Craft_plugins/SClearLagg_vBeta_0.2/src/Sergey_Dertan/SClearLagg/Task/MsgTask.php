<?php
namespace Sergey_Dertan\SClearLagg\Task;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as F;
use Sergey_Dertan\SClearLagg\SClearLaggMainFolder\SClearLaggMain;

/**
 * Class MsgTask
 * @package Sergey_Dertan\SClearLagg\Task
 */
class MsgTask extends PluginTask
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
        Server::getInstance()->broadcastMessage(F::GREEN . SClearLaggMain::getInstance()->config->get("PreClear-msg"));
    }
}