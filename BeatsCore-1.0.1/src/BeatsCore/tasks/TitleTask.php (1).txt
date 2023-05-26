<?php

declare(strict_types=1);

namespace BeatsCore\tasks;

use BeatsCore\Core;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class TitleTask extends PluginTask{

    /** @var Core */
    private $plugin;
    /** @var Player */
    private $player;

    public function __construct(Core $plugin, Player $player){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void{
        $this->player->addTitle("§k§aii§r §l§dBeats§bPE§r §k§aii§r", "§k§aii§r §l§3Factions§r §k§aii§r");
    }
}