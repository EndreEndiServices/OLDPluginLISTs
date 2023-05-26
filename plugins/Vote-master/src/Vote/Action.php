<?php

namespace Vote;

use pocketmine\Player;
use pocketmine\utils\TextFormat;


class Action{

    private $plugin;

    public function __construct(Vote $plugin){
        $this->plugin=$plugin;
        
    }

    /**
     * This function is for developers,
     * He can performs many operations
     * on the players and other environments.
     *
     * @param Player $player
     */
    public function Player(Player $player){
        $type = "Coins";
        $coins = mt_rand(1, 10) * 10;
        
        $player->sendMessage(TextFormat::BOLD.TextFormat::DARK_AQUA."»".TextFormat::RESET.TextFormat::DARK_AQUA." You have win ".TextFormat::RESET.TextFormat::BOLD.strtoupper($coins." ".$type).TextFormat::RESET.TextFormat::DARK_AQUA." come back ".TextFormat::BOLD.TextFormat::WHITE."TOMORROW".TextFormat::RESET.TextFormat::DARK_AQUA." to win even more! ");

        if($this->plugin->getServer()->getPluginManager()->getPlugin("Fireworks")){
            $FireworkColors = [1 => "red", 6 => "dark_aqua", 10 => "green", 11 => "yellow", 12 => "aqua", 13 => "purple", 15 => "white"];

            $this->plugin->getServer()->getPluginManager()->getPlugin("Fireworks")->entity_Fireworks($player, $player->asVector3()->add(0, 2, 0), 10, true, array_rand($FireworkColors), true, true, mt_rand(0, 3));

        }

        if($this->plugin->getServer()->getPluginManager()->getPlugin("Coins")){
            $this->plugin->getServer()->getPluginManager()->getPlugin("Coins")->exchangeCoins($player, $coins);

        }else{
            $player->sendMessage("Critical error #Vote_Coins_".$coins." report on twitter.");

        }

    }


}





