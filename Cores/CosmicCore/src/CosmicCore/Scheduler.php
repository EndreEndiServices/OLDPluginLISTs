<?php
namespace CosmicCore;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

class Scheduler extends PluginTask
{

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    public function onRun($currentTick)
    {
        foreach ($this->plugin->players as $player => $time) {
            if ((time() - $time) > $this->plugin->interval) {
                $p = $this->plugin->getServer()->getPlayer($player);
                if ($p instanceof Player) {
                    $p->sendMessage($this->plugin->p("a", "!") . "You have left combat. You can now log out safely.");
                    unset($this->plugin->players[$player]);
                } else unset($this->plugin->players[$player]);
            }
        }
    }
}