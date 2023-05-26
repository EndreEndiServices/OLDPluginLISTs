<?php

declare(strict_types=1);

namespace BeatsCore\tasks;

use BeatsCore\Core;
use pocketmine\scheduler\PluginTask;
use pocketmine\Player;

class HUDTask extends PluginTask{

    /** @var Core */
    private $plugin;
    /** @ver Player */
    private $player;

    public function __construct(Core $plugin, Player $player){
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun(int $currentTick) : void{
        if(isset($this->plugin->hud[$this->player->getName()])){
            $PurePerms = $this->plugin->getServer()->getPluginManager()->getPlugin("PurePerms");
            $EconomyAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI");
            $rank = $PurePerms->getUserDataMgr()->getGroup($this->player)->getName();
            $money = $EconomyAPI->myMoney($this->player);
            $x = round($this->player->getX());
            $y = round($this->player->getY());
            $z = round($this->player->getZ());
            $msg = "§l§dBeats§bPE §aFactions§r \n§3Rank: §b$rank §9Money: §5$money \n§eX: §6$x §l§8/ §r§eY: §6$y §l§8/ §r§eZ: §6$z";
            $this->player->sendPopup($msg);
        }
    }
}
