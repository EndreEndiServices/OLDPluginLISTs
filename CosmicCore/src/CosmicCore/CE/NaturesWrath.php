<?php
namespace CosmicCore\CE;

use CosmicCore\CosmicCore;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat as TF;

class NaturesWrath extends PluginTask
{

    public function __construct(CosmicCore $owner, Player $player)
    {
        $this->player = $player;
        $this->owner = $owner;
        parent::__construct($owner);
    }

    public function onRun($currentTick)
    {
        $this->owner->strike($this->player);
        $this->player->sendMessage(TF::BOLD . TF::GREEN . "**NATURE'S WRATH!**");
        $this->player->sendTip(TF::GREEN . "*NATURE'S WRATH!*");
    }
}
