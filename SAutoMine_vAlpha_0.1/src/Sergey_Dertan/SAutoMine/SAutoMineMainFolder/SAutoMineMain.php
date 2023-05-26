<?php

namespace Sergey_Dertan\SAutoMine\SAutoMineMainFolder;

use pocketmine\utils\TextFormat as F;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Sergey_Dertan\SAutoMine\Task\MineResetTask;
use Sergey_Dertan\SAutoMine\Command\SAutoMineCommandExecutor;


class SAutoMineMain extends PluginBase
{
    public $pos1 = array(), $pos2 = array(), $config;
    protected $resetTask;

    function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
            "MineResetTime" => 420,
            "MineBlocks" => array(),
            "ResetMsg" => "",
            "MinePos" => array()
        ));
        $time = intval($this->config->get("MineResetTime")) * 20;
        Server::getInstance()->getScheduler()->scheduleRepeatingTask($this->resetTask = new MineResetTask($this), $time);
        if (count($this->config->get("MinePos")) == 0) {
        }
    }

    function onCommand(CommandSender $s, Command $cmd, $label, array $args)
    {
        return new SAutoMineCommandExecutor($this, $s, $cmd, $args);
    }

    function onDisable()
    {
        $this->config->save();
    }
}