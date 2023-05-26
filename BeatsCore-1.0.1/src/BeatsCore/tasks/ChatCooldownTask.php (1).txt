<?php

declare(strict_types=1);

namespace BeatsCore\tasks;

use BeatsCore\Core;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class ChatCooldownTask extends PluginTask{

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
        unset($this->plugin->chat[$this->player->getName()]);
    }
}