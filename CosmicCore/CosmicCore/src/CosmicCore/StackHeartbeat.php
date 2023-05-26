<?php
namespace CosmicCore;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class StackHeartbeat extends PluginTask
{

    /** @var Main */
    private $plugin;

    /** @var int */
    private $stackingRange = 16;

    /**
     * StackHeartBeat constructor
     *
     * @param Main $plugin
     */
    public function __construct(CosmicCore $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->levels = array("world", "nether");
        $plugin->getServer()->getScheduler()->scheduleRepeatingTask($this, (int)$plugin->getConfig()->get("stack-delay", 10) * 20);
    }

    /**
     * @param $tick
     */
    public function onRun($tick)
    {
        foreach ($this->plugin->getServer()->getLevels() as $level) {
            if (!in_array($level->getName(), $this->levels)) continue;
            foreach ($level->getEntities() as $e) {
                if (($e instanceof Player or !$e instanceof Living) or ($e->getDataProperty(Entity::DATA_NO_AI) == 1 ? true : false)) continue;
            }
        }
    }
}
