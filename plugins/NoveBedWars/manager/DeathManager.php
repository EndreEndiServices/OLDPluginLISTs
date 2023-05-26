<?php

namespace BedWars\manager;

use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\entity\Projectile;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DeathManager{
    
    public $arena;
    
    public function __construct(Arena $arena){
        $this->arena = $arena;
    }
    
    public function onDeath(PlayerDeathEvent $e){
        /** @var Player $p */
        $p = $e->getEntity();
        $game = $this->arena->getPlayer($p) instanceof GamePlayer ? $this->arena->getPlayer($p) : null;
        $lastDmg = $p->getLastDamageCause();
        $pColor = $game->getTeam()->getChat();
        $escape = false;
        if($lastDmg instanceof EntityDamageEvent){
            if($lastDmg instanceof EntityDamageByEntityEvent){
                /** @var Player $killer */
                $killer = $lastDmg->getDamager();
                $gamer = $this->arena->getPlayer($killer) instanceof GamePlayer ? $this->arena->getPlayer($killer) : null;
                if($killer instanceof Player and $gamer instanceof GamePlayer){
                    $dColor = $gamer->getTeam()->getChat();
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was slain by ".$dColor."{$killer->getName()}");
                    $this->arena->bw->mysqlmgr->addKill($killer->getName());
                }
                return;
            }
            if($lastDmg instanceof EntityDamageByChildEntityEvent){
                $arrow = $lastDmg->getChild();
                /** @var Player $killer */
                $killer = $lastDmg->getDamager();
                if($arrow instanceof Projectile){
                    $gamer = $this->arena->getPlayer($killer) instanceof GamePlayer ? $this->arena->getPlayer($killer) : null;
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was shot by ".$gamer->getTeam()->getChat().$killer->getName());
                    $this->arena->bw->mysqlmgr->addKill($killer->getName());
                }
                return;
            }
            $killer = null;
            if(isset($this->arena->bw->listener->players[strtolower($p->getName())]['tick']) && $this->arena->bw->listener->players[strtolower($p->getName())]['tick'] >= $this->arena->bw->getServer()->getTick()){
                $escape = true;
                $dColor = $this->arena->bw->listener->players[strtolower($p->getName())]['killer_color'];
                $killer = $this->arena->bw->listener->players[strtolower($p->getName())]['killer'];
            }
        switch($lastDmg->getCause()){
            case 0:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." walked into a cactus while trying to escape ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    return;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was pricked to death");
                break;
            case 3:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." suffocated in a wall");
                break;
            case 4:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was doomed to fall by ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." fell from high place");
                break;
            case 5:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." walked into a fire whilst fighting ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." went up in flames");
                break;
            case 6:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was burnt to a crisp whilst fighting ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." burned to death");
                break;
            case 7:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." tried to swim in lava while trying to escape ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." tried to swim in lava");
                break;
            case 8:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." drowned whilst trying to escape ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." drowned");
                break;
            case 9:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." blew up");
                break;
            case 10:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." blew up");
                break;
            case 11:
                if($escape === true){
                    $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." was doomed to fall by ".$dColor.$killer);
                    $this->arena->bw->mysqlmgr->addKill($killer);
                    break;
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." fell out of the world");
                break;
            case 12:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." died");
                break;
            case 13:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." died");
                break;
            case 14:
                if($escape === true){
                    $this->arena->bw->mysqlmgr->addKill($killer);
                }
                $this->arena->messageAllPlayers($pColor."{$p->getName()}".TextFormat::GRAY." died");
                break;
        }
        }
    }
}