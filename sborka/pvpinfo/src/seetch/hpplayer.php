<?php

namespace seetch;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntity;
use pocketmine\event\entity\EntityDamageEvent;

class hpplayer extends PluginBase implements Listener {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onEntityDamageByEntity(EntityDamageEvent $event) {
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if($entity instanceof Player && $damager instanceof Player) {
                $hp = $entity->getHealth() - $event->getFinalDamage();
                $hpDamager = $damager->getHealth - $event->getFinalDamage();
                $entity->sendPopup("§f Тебя атаковал§a " .$damager->getName(). "§f. У тебя осталось:§a " .$hp);
                $damager->sendPopup("§f Ты атаковал§a " .$entity->getName(). "§f. Отняв у него:§a " .$hpDamager);
            }
        }
    }
}