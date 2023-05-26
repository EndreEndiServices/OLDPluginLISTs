<?php

namespace GmWM\BlockEffects;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\entity\Effect;
use pocketmine\block\Block;

class BlockEffects extends PluginBase implements Listener{
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("§a✔ §cBlockEffects §bВключен");
        $this->path = $this->getDataFolder();
        @mkdir($this->path);
        if(!file_exists($this->path . "config.yml")) {
            $this->config = new Config($this->path . "config.yml", Config::YAML,array(
            "57:0" => array(
                array(
                    "effect" => 1,
	            "amplifier" => 10,
                    "duration" => 10,
                    ),
                ),
            "152:0" => array(
                array(
                    "effect" => 8,
                    "amplifier" => 10,
                    "duration" => 10,
                    ),
                ),
            ));
        } else {
            $this->saveConfig();
        }
    }
    
    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        $cfg = $this->getConfig();
        $block = $event->getPlayer()->getLevel()->getBlock($event->getPlayer()->floor()->subtract(0, 1));
        if($block instanceof Block) {
            $id = $block->getId();
            $meta = $block->getDamage();
            if($cfg->exists($id . ":" . $meta)) {
                $effects = $cfg->get($id . ":" . $meta);
                foreach($effects as $effect) {
                    $player->addEffect(Effect::getEffect((int)$effect["effect"])->setAmplifier((int)$effect["amplifier"])->setDuration((int)$effect["duration"] * 20));
                }
            }
        }
    }
    
    public function onDisable(){
        $this->getLogger()->info("§c✖ §bBlockEffects §cВыключен");
        $this->saveConfig();
    }
    
}