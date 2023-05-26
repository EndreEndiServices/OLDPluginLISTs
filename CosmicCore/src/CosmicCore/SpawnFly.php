<?php
namespace CosmicCore;

use pocketmine\scheduler\PluginTask;

class SpawnFly extends PluginTask
{

    public function __construct(CosmicCore $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($currentTick)
    {
        $this->plugin->restoreOps();
        foreach ($this->plugin->getServer()->getLevelByName("world")->getPlayers() as $pl){
            if (!$this->plugin->isDonator($pl)) {
                if ($this->plugin->isAtSpawn($pl)) {
                    if (!$pl->getAllowFlight()) $pl->setAllowFlight(true);
                } else {
                    if ($pl->getAllowFlight()) $pl->setAllowFlight(false);
                }
            }
        }
    }
}
