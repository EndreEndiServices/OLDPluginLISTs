<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 11/10/2016
 * Time: 18:42
 */

namespace SliceKits\Task;


use pocketmine\level\sound\AnvilFallSound;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\BlockPlaceSound;
use pocketmine\level\sound\ButtonClickSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\SplashSound;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use pocketmine\Server;
use SliceKits\Loader;

class Sounds extends PluginTask
{

    public function __construct(Loader $plugin, Player $player, $sound) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
        $this->sound = $sound;
    }

    /**
     * Actions to execute when run
     *
     * @param $currentTick
     *
     * @return void
     */
    public function onRun($currentTick)
    {
        $player = $this->player;
        $level = $this->player->getLevel();
        switch($this->sound){
            case "pop":
                $level->addSound(new PopSound($player));
                break;
            case "fall":
                $level->addSound(new BlazeShootSound($player));
                break;
            case "button":
                $level->addSound(new ButtonClickSound($player));
                break;
            case "block":
                $level->addSound(new GhastSound($player));
                break;
            case "pvp":
                $this->plugin->isStartedConfig()->set("pvp.off",true);
                $this->plugin->isStartedConfig()->save();
                Server::getInstance()->broadcastMessage("§a>>> PVP FOI LIBERADO <<<");
                Server::getInstance()->broadcastTip("§a>>> PVP FOI LIBERADO <<<");
                Server::getInstance()->broadcastPopup("§a>>> PVP FOI LIBERADO <<<");
                break;
        }
    }
}