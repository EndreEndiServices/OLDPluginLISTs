<?php
namespace CosmicCore\Tasks\CosmicFX;

use pocketmine\scheduler\PluginTask;
use pocketmine\Player;
use pocketmine\math\Vector3;
use CosmicCore\CosmicCore;

class FXTask extends PluginTask
{

    public function __construct(CosmicCore $plugin , Player $player)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun($currentTick)
    {
        if ($this->player->isMoving()) {
            if ($this->plugin->getParticle($this->plugin->getPlayerParticle($this->player), new Vector3($this->player->x, $this->player->y, $this->player->z)) !== null) {
                $this->player->getLevel()->addParticle($this->plugin->getParticle($this->plugin->getPlayerParticle($this->player), new Vector3($this->player->x, $this->player->y + 0.7, $this->player->z)));
            }
        }
    }
}
