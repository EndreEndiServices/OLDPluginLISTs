<?php

namespace mamayadesu;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntity;
use pocketmine\event\entity\EntityDamageEvent;

class hpplayer extends PluginBase implements Listener {

private $cfg;

    public function onEnable() {
        if(! file_exists($this->getDataFolder())) @mkdir($this->getDataFolder());
        $this->cfg = new Config($this->getDataFolder()."config.yml", Config::YAML);
        if(empty($this->cfg->get("symbol"))) {
            $this->cfg->set("symbol", "|");
            $this->cfg->save();
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onEntityDamageByEntity(EntityDamageEvent $event) {
        $s = $this->cfg->get("symbol");
        if($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            $damager = $event->getDamager();
            if($entity instanceof Player && $damager instanceof Player) {
                $hp = $entity->getHealth();
                switch($hp) {
                    case 20:
                        $format = "§f§2".$s.$s.$s.$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 19:
                        $format = "§f §2".$s.$s.$s.$s.$s.$s.$s.$s.$s."§7".$s."§f";
                        break;
                
                    case 18:
                        $format = "§f §2".$s.$s.$s.$s.$s.$s.$s.$s.$s."§8".$s."§f";
                        break;

                    case 17:
                        $format = "§f §2".$s.$s.$s.$s.$s.$s.$s.$s."§7".$s."§8".$s."§f";
                        break;

                    case 16:
                        $format = "§f §2".$s.$s.$s.$s.$s.$s.$s.$s."§8".$s.$s."§f";
                        break;

                    case 15:
                        $format = "§f §2".$s.$s.$s.$s.$s.$s.$s."§7".$s."§8".$s.$s."§f";
                        break;

                    case 14:
                        $format = "§f §a".$s.$s.$s.$s.$s.$s.$s."§8".$s.$s.$s."§f";
                        break;

                    case 13:
                        $format = "§f §a".$s.$s.$s.$s.$s.$s."§7".$s."§8".$s.$s.$s."§f";
                        break;

                    case 12:
                        $format = "§f §a".$s.$s.$s.$s.$s.$s."§8".$s.$s.$s.$s."§f";
                        break;

                    case 11:
                        $format = "§f §a".$s.$s.$s.$s.$s."§7".$s."§8".$s.$s.$s.$s."§f";
                        break;

                    case 10:
                        $format = "§f §a".$s.$s.$s.$s.$s."§8".$s.$s.$s.$s.$s."§f";
                        break;

                    case 9:
                        $format = "§f §e".$s.$s.$s.$s."§7".$s."§8".$s.$s.$s.$s.$s."§f";
                        break;

                    case 8:
                        $format = "§f §e".$s.$s.$s.$s."§8".$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 7:
                        $format = "§f §6".$s.$s.$s."§7".$s."§8".$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 6:
                        $format = "§f §6".$s.$s.$s."§8".$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 5:
                        $format = "§f §6".$s.$s."§7".$s."§8".$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 4:
                        $format = "§f §c".$s.$s."§8".$s.$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 3:
                        $format = "§f §c".$s."§7".$s."§8".$s.$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 2:
                        $format = "§f §4".$s."§8".$s.$s.$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 1:
                        $format = "§f §8".$s.$s.$s.$s.$s.$s.$s.$s.$s.$s."§f";
                        break;

                    case 0:
                        $format = "§f §4DEATH§f]";
                        break;
                }
                $damager->sendPopup("§3 Player §e→ §6".$entity->getName()."\n §3Health §e→ ".$format."\n"."\n                                              §e             Troll§cCraft");
            }
        }
    }
}