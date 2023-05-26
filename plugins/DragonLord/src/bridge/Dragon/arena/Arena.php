<?php

namespace bridge\Dragon\arena;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEatEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityExplodeEvent;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\item\Item;
use pocketmine\block\Block;

use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\level\Explosion;

use pocketmine\Player;
use bridge\Main;

class Arena implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getServer()
    {
        return $this->plugin->getServer();
    }

    public function onMove(PlayerMoveEvent $e)
    {
        $p = $e->getPlayer();
        $name = strtolower($p->getName());

        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }

        /*if($arena->stat < 3) {
           if ($e->getTo()->getFloorY() < 54) {
               $p->teleport($p->getLevel()->getSafeSpawn());
           }
       }*/
        if ($arena->stat < 3 or $arena->stat > 3) {
            return true;
        }
        $pos = $arena->getpointPos($p);
        if ($p->distance($pos) <= 3) {
            $arena->addpoint($p);
            return true;
        }
        $poss = $arena->getpointPos($p, false);
        if ($p->distance($poss) <= 3) {
            $p->getInventory()->clearAll();
            $arena->respawnPlayer($p);
            $p->sendMessage("§cТы не можешь пригать в свой портал!");
        }
    }

    public function onBreak(BlockBreakEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }
        if ($arena->stat < 3 or $arena->stat > 3) {
            $e->setCancelled();
            return true;
        }
        $b = $e->getBlock();
        if ($b->getId() !== 159) {
            $e->setCancelled();
        }
    }

    public function onEat(EntityEatEvent $e)
    {
        $ent = $e->getEntity();
        if ($ent instanceof Player) {
            $arena = $this->getPlugin()->getPlayerArena($ent);
            if (is_null($arena)) {
                return true;
            }
            $item = $e->getResidue();
            if ($item->getId() == 322) {
                $ent->setHealth($ent->getMaxHealth());
                $ent->setFood(20);
            }
        }
    }


    public function onPlace(BlockPlaceEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }
        if ($arena->stat < 3 or $arena->stat > 3) {
            $e->setCancelled();
            return true;
        }
        $b = $e->getBlock();
        $spawn = $arena->getSpawn1();

        if ($b->y > ($spawn->y + 15)) {
            $e->setCancelled();
            $p->sendMessage("§cЭто лимит для блоков!");
            return true;
        }
        $pos1 = $arena->getRespawn1(false);
        $pos2 = $arena->getRespawn2(false);
        $pos3 = $arena->getPos1(false);
        $pos4 = $arena->getPos2(false);
        $vector = new Vector2($b->x, $b->z);

        if (($vector->distance($pos1) <= 5) or ($vector->distance($pos3) <= 6) or ($vector->distance($pos4) <= 6) or ($vector->distance($pos2) <= 5)) {
            $e->setCancelled();
        } else {
            $b->getLevel()->setBlock($b, $b);
        }
    }

    public function onDeath(PlayerDeathEvent $e)
    {
        $e->setDeathMessage("");
    }

    public function onInteract(PlayerInteractEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }
        $item = $e->getItem();
		
        if ($arena->stat !== 2) {
            $custom = $item->getCustomName();
            if ($item->getId() == 355) {
                $e->setCancelled();

                $arena->quit($p);
                $p->sendMessage("§f- §cПокидаем арену...");
                //$this->getPlugin()->api->openCategory($p, "hub");
            }
            if ($item->getId() == 351 and $custom == "§9§lBLUE") {
                $e->setCancelled();
                if ($arena->getTeam($p) == "blue") {
                    $p->sendMessage("§cYou already is in team Blue!");
                    return true;
                }
                if ($arena->setTeam("blue", $p)) {
                    $p->sendMessage("§aТы зашёл за синих");
                } else {
                    $p->sendMessage("§cTeam blue is full!");
                }
            }
            if ($item->getId() == 351 and $custom == "§c§lRED") {
                $e->setCancelled();
                if ($arena->getTeam($p) == "red") {
                    $p->sendMessage("§cТы и так в этой команде!");
                    return true;
                }
                if ($arena->setTeam("red", $p)) {
                    $p->sendMessage("§aТы зашёл за красных");
                } else {
                    $p->sendMessage("§cYou are already in this team");
                }
            }
        }
    }



    public function onDamage(EntityDamageEvent $e)
    {
        $ent = $e->getEntity();
        if ($ent instanceof Player) {
            $name = strtolower($ent->getName());
            $arena = $this->getPlugin()->getPlayerArena($ent);
            if (is_null($arena)) {
                return true;
            }
            if ($arena->stat < 3 or $arena->stat > 3) {
                $e->setCancelled();
                if ($e->getCause() == 11) {
                    if ($arena->stat > 3) {
                        $ent->getInventory()->clearAll();
                        $arena->respawnPlayer($ent, false);
                        return true;
                    }
                    $level = $ent->getLevel();
                    $ent->teleport($level->getSafeSpawn());
                    return true;
                }
            }
            if ($e->getCause() == 4) {
                $e->setCancelled();
                return true;
            }
            if ($e->getCause() == 10 or $e->getCause() == 9) {
                $e->setCancelled();
                return true;
            }
            if ($e->getCause() == 11) {
                $e->setCancelled();
                $ent->getInventory()->clearAll();
                $arena->respawnPlayer($ent);
                return true;
            }
            $cause = $ent->getLastDamageCause();
            $damage = $e->getFinalDamage();
            if ($e instanceof EntityDamageByEntityEvent) {
                $p = $e->getDamager();
                if ($p instanceof Player) {
                    if ($arena->isTeamMode() && $arena->isTeam($p, $ent)) {
                        $e->setCancelled();
                        return true;
                    }
                }
            }
            if (($ent->getHealth() - round($damage)) <= 1) {
                $e->setCancelled();
                $ent->getInventory()->clearAll();
                $arena->respawnPlayer($ent);
                if ($e instanceof EntityDamageByEntityEvent) {
                    $p = $e->getDamager();
                    if ($p instanceof Player) {
                            $arena->broadcast($arena->createBaseColor($ent) . "§a был убит игроком " . $arena->createBaseColor($p), 3);
                            return true;
                    }
                }
                    $arena->broadcast($arena->createBaseColor($p). "§f умер", 3);
                return true;
            }
            switch ($e->getCause()) {
                case 1:
                case 2:
                case 4:
                case 11:
                    if ($cause instanceof EntityDamageByEntityEvent) {
                        $ev = new EntityDamageByEntityEvent($e->getDamager(), $ent, 1, $e->getDamage());
                        $ent->setLastDamageCause($ev);
                    }
                    break;
            }
        }
    }

    public function onQuit(PlayerQuitEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (!is_null($arena)) {
            $team = $arena->getTeam($p);
            if ($team == "blue") {
                $arena->broadcast("§9" . $p->getName() . "§7 покинул игру!", 3);
            } elseif ($team == "red") {
                $arena->broadcast("§c" . $p->getName() . "§7 покинул игру!", 3);
            }
            $arena->quit($p, false);
        }
    }

    public function onData(DataPacketReceiveEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }
        $packet = $e->getPacket();
        $name = strtolower($p->getName());
        switch ($packet::NETWORK_ID) {
            case 0x29:
                $e->setCancelled();
                $item = $packet->item;
                $p->getInventory()->addItem($item);
                break;
        }
    }

    public function onC(PlayerCommandPreprocessEvent $e)
    {
        $p = $e->getPlayer();
        $arena = $this->getPlugin()->getPlayerArena($p);
        if (is_null($arena)) {
            return true;
        }
        $cmd = strtolower($e->getMessage());
        if (substr($cmd, 0, 1) == "/") {
            if (!$p->hasPermission("bridge.cmd")) {
                $e->setCancelled();
            }
            $args = explode(" ", $cmd);
            if (substr($args[0], 1) == "bridge") {
                if (isset($args[1])) {
                    if (strtolower($args[1]) == "left") {
                        $e->setCancelled();
                        $team = $arena->getTeam($p);
                        if ($team == "blue") {
                            $arena->broadcast("§9" . $p->getName() . "§7 покинул игру!", 3);
                        } elseif ($team == "red") {
                            $arena->broadcast("§c" . $p->getName() . "§7 покинул игру!", 3);
                        }
                        $arena->quit($p);
                        $p->getInventory()->clearAll();
                        return true;
                    }
                }
            } elseif (substr($args[0], 1) == "kill") {
                $e->setCancelled();
                return true;
            }
            if (!$p->hasPermission("bridge.cmd")) {
                $e->setCancelled();
            }
        }
    }

}