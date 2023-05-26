<?php

namespace BedWars\listener;

use BedWars\arena\Arena;
use BedWars\BedWars;
use BedWars\game\GamePlayer;
use BedWars\game\Team;

use pocketmine\block\Block;
use pocketmine\entity\Arrow;
use pocketmine\entity\Snowball;
use pocketmine\entity\Villager;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;


class ArenaListener implements Listener {

    private $arena;
    private $bw;
    
    public function __construct(Arena $arena, BedWars $bw){
        $this->arena = $arena;
        $this->bw = $bw;
    }
    
    public function onArrow(InventoryPickupArrowEvent $e){
      if ($e->getArrow() instanceof Arrow){
        $e->setCancelled(true);
      }
    }
    
    public function onTouch(PlayerInteractEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if($e->isCancelled()){
            return;
        }
        if($game !== null and $game->isShopping()){
            $e->setCancelled();
            $this->arena->shopmgr->stopShopping($p);
            return;
        }
        if($e->getAction() === $e::RIGHT_CLICK_AIR && $game !== null && $game->isSpectating() && ($p->getInventory()->getItemInHand()->getId()) === Item::COMPASS){
            $inv = $p->getInventory();
            foreach ($this->arena->getPlayers() as $game){
                if (!$game instanceof GamePlayer){
                    return;
                }
                $player = $game->getPlayer();
                if ($this->bw->mtcore->mysqlmgr->getRank($p->getName()) != "hrac"){
                    $inv->addItem(Item::get(Item::DIAMOND_SWORD, 0, 1)->setCustomName($player->getDisplayName()));
                }
                else {
                    $inv->addItem(Item::get(Item::STONE_SWORD, 0, 1)->setCustomName($player->getDisplayName()));
                }
            } 
        }
    }
    
    public function onQuit(PlayerQuitEvent $e){
        $game = $this->arena->getPlayer($e->getPlayer()) instanceof GamePlayer ? $this->arena->getPlayer($e->getPlayer()) : null;
        if($game instanceof GamePlayer and ($game->getTeam() instanceof Team or $game->isSpectating())){
            $this->arena->leaveArena($e->getPlayer());
        }
    }
    
    public function onRespawn(PlayerRespawnEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if($game === null){
            return;
        }
        if($game->isShopping()){
            $this->arena->shopmgr->stopShopping($p);
            return;
        }
        /** @var Team $team */
        $team = $game instanceof GamePlayer ? $game->getTeam() : null;
        if ($team->bed === null){
                $this->arena->unsetPlayer($p);
        }
    }
    
    public function onDeath(PlayerDeathEvent $e){
        $p = $e->getEntity();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if ($game instanceof GamePlayer) {
            $team = $game->getTeam();
        }
        $e->setDrops([]);
        $e->setDeathMessage("");
        if(!$game instanceof GamePlayer or !$team instanceof Team){
            return;
        }
        if ($this->arena->bw->mtcore->mysqlmgr->getRank($p->getName()) != "hrac"){
          $e->setKeepInventory(true);
          $p->sendMessage($this->arena->bw->prefix.TF::GREEN."You didn't lose your items due to ".TF::GOLD."VIP Respawn!");
        }
        else {
          $e->setKeepInventory(false);
        }
        if($game->isShopping()){
            $this->arena->shopmgr->stopShopping($p);
        }
        if($this->arena->phase === Arena::RUNNING){
            $this->arena->deathmgr->onDeath($e);
            $this->bw->mysqlmgr->addDeath($p->getName());
            if($team->bed === null){
                $this->bw->mysqlmgr->addLoss($p->getName());
                $this->arena->unsetPlayer($p);
                $p->getInventory()->clearAll();
                $p->sendMessage($this->bw->prefix.TF::YELLOW. "Well played");
                $this->arena->startSpectating($p, true);
            }
        }
    }
    
    public function onDropItem(PlayerDropItemEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null; 
        if($game->isShopping()){
            $e->setCancelled();
            $this->arena->shopmgr->stopShopping($p);
            return;
        }
        if(!$p->isOp() && $game->getTeam() === null or $game === null){
            $e->setCancelled();
        }
    }
    
    public function onHit(EntityDamageEvent $e){
        $victim = $e->getEntity();
        if ($victim instanceof Player) {
            $game = $this->arena->getPlayer($victim) instanceof GamePlayer ? $this->arena->getPlayer($victim) : null;
        }
        else {
            $game = null;
        }
        if($game instanceof GamePlayer and $game->isSpectating()){
            $e->setCancelled();
        }
        if($e instanceof EntityDamageByEntityEvent ){
            /** @var Player $killer */
            $killer = $e->getDamager();
            $gamer = $this->arena->getPlayer($killer) instanceof GamePlayer ? $this->arena->getPlayer($killer) : null;
            /** @var Arena $arena */
            $arena = $this->bw->getArena($killer) instanceof Arena ? $this->bw->getArena($killer) : null;
            if (!$arena instanceof Arena){
                return;
            }
            if($gamer instanceof GamePlayer and $gamer->isSpectating()){
                $e->setCancelled();
                return;
            }
            if($victim instanceof Villager && $killer instanceof Player && $arena->getPhase() === Arena::RUNNING && $killer->getGamemode() === 0){
                $this->bw->getLogger()->info("Melo by se to spustit :/");
                $this->arena->shopmgr->startShopping($killer);
                $e->setCancelled();
            }
            if($killer instanceof Player && $victim instanceof Player){
                if(!$game instanceof GamePlayer or !$gamer instanceof GamePlayer or $game->getTeam() === $gamer->getTeam() or !$this->arena->isRunning()){
                    $e->setCancelled();
                    return;
                }
                if ($game->getTeam() !== $gamer->getTeam()){
                    /** @var Team $teamer */
                    $teamer = $gamer->getTeam();
                    $this->bw->listener->players[strtolower($victim->getName())]['killer'] = $killer->getName();
                    $this->bw->listener->players[strtolower($victim->getName())]['killer_color'] = $teamer->getChat();
                    $this->bw->listener->players[strtolower($victim->getName())]['tick'] = $this->bw->getServer()->getTick() + 200;
                }
                if($game->isShopping()){
                    $this->arena->shopmgr->stopShopping($victim);
                    return;
                }
                if($gamer->isShopping()){
                    $this->arena->shopmgr->stopShopping($killer);
                    return;
                }
            }
        }
        if(!$victim instanceof Player){
            return;
        }
            if($e instanceof EntityDamageByChildEntityEvent){
                $killer = $e->getDamager();
                $gamer = $this->arena->getPlayer($killer) instanceof GamePlayer ? $this->arena->getPlayer($killer) : null;
                if($gamer instanceof GamePlayer and $gamer->isSpectating()){
                    $e->setCancelled();
                    return;
                }
                if(!$game instanceof GamePlayer or !$gamer instanceof GamePlayer){
                    $e->setCancelled();
                    return;
                }
                if($game->getTeam() === $gamer->getTeam()){
                    $e->setCancelled();
                    return;
                }
                if($game->isShopping()){
                    $this->arena->shopmgr->stopShopping($victim);
                    return;
                }
                if($gamer->isShopping()){
                    $this->arena->shopmgr->stopShopping($killer);
                    return;
                }
                /** @var Team $teamer */
                $teamer = $gamer->getTeam();
                $this->bw->listener->players[strtolower($victim->getName())]['killer'] = $killer->getName();
                $this->bw->listener->players[strtolower($victim->getName())]['killer_color'] = $teamer->getChat();
                $this->bw->listener->players[strtolower($victim->getName())]['tick'] = $this->bw->getServer()->getTick() + 200;
            }
    }
    
    public function onBlockBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        $e->setInstaBreak(true);
        if($b->level->getName() == "BedWars_hub" && !$p->isOp()){
            $e->setCancelled();
            return;
        }
        if ($game instanceof GamePlayer){
        if($game->isSpectating()){
            $e->setCancelled();
            return;
        }
        if(!$game->isPlaying()){
            return;
        }
        if($game->isShopping()){
            $e->setCancelled();
            $this->arena->shopmgr->stopShopping($p);
            return;
        }
        }
        if($this->arena->phase === Arena::RUNNING){
            $e->setCancelled();
            return;
        }
        if ($game instanceof GamePlayer and $game->getTeam() === null){
            $e->setCancelled();
            return;
        }
        if($e->getBlock()->getId() === Block::CHEST){
            $e->setCancelled();
            return;
        }
        if($e->getBlock()->getId() === Block::BED_BLOCK){
            $this->arena->breakBed($p, $e);
            return;
        }
        $allowedBlocks = [24, 2, 30, 42, /*Item::TRAPPED_CHEST,*/ 89, 121, 19, 92, 49, 45];
        if (!in_array($b->getId(), $allowedBlocks)){
            $e->setCancelled();
            return;
	}    
    }
    
    public function onBlockPlace(BlockPlaceEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if (!$game instanceof GamePlayer){
            $e->setCancelled(true);
            return;
        }
        if($game->isSpectating()){
            $e->setCancelled();
            return;
        }
        if(!$game->isPlaying()){
            return;
        }
        if($game->isShopping()){
            $e->setCancelled();
            $this->arena->shopmgr->stopShopping($p);
            return;
        }
        if(!$this->arena->isRunning() or $game->getTeam() === null){
            $e->setCancelled();
            return;
        }
        $allowedBlocks = [24, 2, 30, 42/*, Item::TRAPPED_CHEST*/, 89, 121, 19, 92, 49, 45];
        if (!in_array($b->getId(), $allowedBlocks)){
            $e->setCancelled();
            return;
        }
    }
    
    public function onItemHold(PlayerItemHeldEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if ($e->getItem()->getId() === Item::SNOWBALL){
            $p->sendPopup("Ender Pearl");
        }
        /*if ($e->getItem()->getId() === Item::TRAPPED_CHEST){
            $p->sendPopup("Ender Chest");
        } */
        if($game instanceof GamePlayer and $game->getTeam() instanceof Team){
            $this->arena->shopmgr->buy($p, $e->getItem(), $e, $e->getInventorySlot());
        }
        $sd = $p;
        if (!$game instanceof GamePlayer){
            return;
        }
        /** @var Team $team */
        $team = $game->getTeam();
        if($game instanceof GamePlayer and !$game->isPlaying() and !$game->isSpectating()){
            switch("{$e->getItem()->getId()}:{$e->getItem()->getDamage()}"){
                case "159:14":
                    if ($game instanceof GamePlayer and $this->arena !== null and $this->arena->getPhase() === Arena::PRESTART){
                        if ($game->getTeam() === $this->arena->getTeam("red")){
                            $sd->sendMessage($this->arena->bw->prefix.TF::RED."You are already in ".TF::RED."red team!");
                            return;
                        }
                        if ($team instanceof Team){
                            $team->removePlayer($game);
                            $game->removeFromTeam();
                        }
                        $this->arena->getTeam("red")->addPlayer($game);
                        $game->addToTeam($this->arena->getTeam("red"));
                        $sd->sendMessage($this->arena->bw->prefix.TF::GREEN."Joined ".TF::RED."red.");
                    }
                    break;
                case "159:11":
                    if ($game instanceof GamePlayer and $this->arena !== null and $this->arena->getPhase() === Arena::PRESTART){
                        if ($game->getTeam() === $this->arena->getTeam("blue")){
                            $sd->sendMessage($this->arena->bw->prefix.TF::RED."You are already in ".TF::BLUE."blue".TF::RED." team!");
                            return;
                        }
                        if ($team instanceof Team){
                            $team->removePlayer($game);
                            $game->removeFromTeam();
                        }
                        $this->arena->getTeam("blue")->addPlayer($game);
                        $game->addToTeam($this->arena->getTeam("blue"));
                        $sd->sendMessage($this->arena->bw->prefix.TF::GREEN."Joined ".TF::BLUE."blue.");
                    }
                    break;
                case "159:5":
                    if ($game instanceof GamePlayer and $this->arena !== null and $this->arena->getPhase() === Arena::PRESTART){
                        if ($game->getTeam() === $this->arena->getTeam("green")){
                            $sd->sendMessage($this->arena->bw->prefix.TF::RED."You are already in ".TF::GREEN."green".TF::RED." team!");
                            return;
                        }
                        if ($team instanceof Team){
                            $team->removePlayer($game);
                            $game->removeFromTeam();
                        }
                        $this->arena->getTeam("green")->addPlayer($game);
                        $game->addToTeam($this->arena->getTeam("green"));
                        $sd->sendMessage($this->arena->bw->prefix.TF::GREEN."Joined ".TF::GREEN."green.");
                    }
                    break;
                    break;
                case "159:4":
                    if ($game instanceof GamePlayer and $this->arena !== null and $this->arena->getPhase() === Arena::PRESTART){
                        if ($game->getTeam() === $this->arena->getTeam("yellow")){
                            $sd->sendMessage($this->arena->bw->prefix.TF::RED."You are already in ".TF::YELLOW."yellow".TF::RED." team!");
                            return;
                        }
                        if ($team instanceof Team){
                            $team->removePlayer($game);
                            $game->removeFromTeam();
                        }
                        $this->arena->getTeam("yellow")->addPlayer($game);
                        $game->addToTeam($this->arena->getTeam("yellow"));
                        $sd->sendMessage($this->arena->bw->prefix.TF::GREEN."Joined ".TF::YELLOW."yellow.");
                    }
                    break;
            }
        }
    }
    
    public function onBucketFill(PlayerBucketFillEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if(!$p->isOp() or $game instanceof GamePlayer){
            $e->setCancelled();
        }
    }
    
    public function onBucketEmpty(PlayerBucketEmptyEvent $e){
        $p = $e->getPlayer();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if(!$p->isOp() or $game instanceof GamePlayer){
            $e->setCancelled();
        }
    }
    
    public function onCraft(CraftItemEvent $e){
        $e->setCancelled(true);
    }
    
    public function onBedEnter(PlayerBedEnterEvent $e){
        $e->setCancelled();
    }
    
    public function onShot(ProjectileLaunchEvent $e){
        $arrow = $e->getEntity();
        /** @var Player $p */
        $p = $arrow->shootingEntity;
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        if($arrow instanceof Arrow){
            if($game->getTeam() !== null){
                $p->getInventory()->addItem(Item::get(262, 0, 1));
            }
        }
    }
    
    public function onProjectileHit(ProjectileHitEvent $e){
        $ent = $e->getEntity();
        if($ent instanceof Snowball && $ent->shootingEntity instanceof Player){
            $ent->shootingEntity->teleport($ent->getPosition());
        }
    }
    
}

