<?php

/*
 *               _                      _ _
 *      /\      | |                    | (_)
 *     /  \   __| |_ __ ___ _ __   __ _| |_ _ __   ___
 *    / /\ \ / _` | '__/ _ \ '_ \ / _` | | | '_ \ / _ \
 *   / ____ \ (_| | | |  __/ | | | (_| | | | | | |  __/
 *  /_/    \_\__,_|_|  \___|_| |_|\__,_|_|_|_| |_|\___|
 *
 * This plugin cannot be shared, or used by anyone else.
 * The only people allowed to use this, must have permission by AppleDevelops.
 * If you don't have permission, and use this plugin, I will not be afraid to take action.
 *
 * @author AppleDevelops
 *
 */

namespace Adrenaline\Tasks;

use Adrenaline\CoreLoader;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

class AdvertisementTask extends PluginTask{

    public $player, $length, $length1, $plugin;

    public function __construct(CoreLoader $plugin/*, Player $player*/){
        parent::__construct($plugin/*, $player*/);
        //$this->player = $player;
        $this->length = -1;
        $this->length1 = -1;
        $this->plugin = $plugin;
    }

    public function onRun($currentTick){
        $this->length = $this->length+1;
        $this->length1 = $this->length1+1;
        $messages = array("§l§1GratonePix §8> §4Factions\n§r§lTwitter: @GratonePix§r");

        $messagekey = $this->length;
        $message = $messages[$messagekey];
        if($this->length === count($messages)-1) $this->length = -1;
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            if(!$p->isOp()) {
                $p->sendTip($message);
                $p->sendMessage($message . "\n\n" . $this->plugin->sendPrefix() . "Do you want ads to be removed? Purchase a rank to remove ads @ bit.ly/adrenalineuhc");
            }
        }
    }
}