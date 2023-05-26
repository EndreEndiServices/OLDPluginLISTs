<?php

namespace BedWars\listener;

use BedWars\BedWars;
use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use BedWars\game\Team;
use BedWars\task\PortalTask;
use pocketmine\block\WallSign;
use pocketmine\entity\Human;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class EventListener implements Listener {

    /** @var BedWars $bw */
    public $bw;
    /** @var Player[] $players */
    public $players;
    
    public function __construct(BedWars $bw){
        $this->bw = $bw;
        $this->players = [];
    }

    public function onDmg(EntityDamageEvent $e){
        if ($e instanceof EntityDamageByEntityEvent){
            $npc = $e->getEntity();
            if (!$npc instanceof Human){
                return;
            }
            $p = $e->getDamager();
            if (!$p instanceof Player){
                return;
            }
            $cmd = [
              "[Tokens]" => "tokens",
              "[Stats]" => "stats",
              "[VIP]" => "vip",
              "[Tutorial]" => "tutorial"
            ];
            $action = null;
            foreach ($cmd as $name => $cm){
                if (stripos($npc->getNameTag(), $name) !== false){
                    $action = $cm;
                    break;
                }
            }
            if ($action === null){
                return;
            }
            switch ($action){
                case "tokens":
                    $this->bw->getServer()->dispatchCommand($p, "tokens");
                break;
                case "stats":
                    $this->bw->getServer()->dispatchCommand($p, "stats");
                break;
                case "vip":
                    $p->sendMessage("§dLike playing on §6MineTox? §eBuy VIP!\n - §5VIP players have §fall§5 kits unlocked\n §5- §3they do not lose their items when they die \n§5 - they have lobby features §7§o[UPCOMING]");
                break;
                case "tutorial":
                    $p->sendMessage("§6[MineTox] §cThis feature isn't currently implemented");
            }
        }

    }

    public function onInteract(PlayerInteractEvent $e){
        $arena = $this->bw->getArena($e->getPlayer());
        $game = $arena instanceof Arena ? $arena->getPlayer($e->getPlayer()) : null;
        $team = $game instanceof GamePlayer ? $game->getTeam() : null;
        if ($e->getBlock() instanceof WallSign){
            /** @var Vector3 $vector */
            $vector = $e->getTouchVector();
            if ($vector == new Vector3(125, 20, 172)){
                $e->getPlayer()->knockBack($e->getPlayer(), 0, $e->getPlayer()->getDirectionVector()->getX(), $e->getPlayer()->getDirectionVector()->getZ());
            }
            if ($vector == $this->bw->wd->get("Lobby", "yellow_sign")){
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("yellow")){
                        $e->getPlayer()->sendMessage($this->bw->prefix.TF::RED."You are already in ".TF::YELLOW."yellow".TF::RED." team!");
                        return;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }
                    $arena->getTeam("yellow")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("yellow"));
                    $e->getPlayer()->sendMessage($this->bw->prefix.TF::GREEN."Joined ".TF::YELLOW."yellow.");
                    return;
                }
            }
            if ($vector == $this->bw->wd->get("Lobby", "blue_sign")){
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("blue")){
                        $e->getPlayer()->sendMessage($this->bw->prefix.TF::RED."You are already in ".TF::BLUE."blue".TF::RED." team!");
                        return;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }
                    $arena->getTeam("blue")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("blue"));
                    $e->getPlayer()->sendMessage($this->bw->prefix.TF::GREEN."Joined ".TF::BLUE."blue.");
                    return;
                }
            }
            if ($vector == $this->bw->wd->get("Lobby", "red_sign")){
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("red")){
                        $e->getPlayer()->sendMessage($this->bw->prefix.TF::RED."You are already in ".TF::RED."red".TF::RED." team!");
                        return;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }
                    $arena->getTeam("red")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("red"));
                    $e->getPlayer()->sendMessage($this->bw->prefix.TF::GREEN."Joined ".TF::RED."red.");
                    return;
                }
            }
            if ($vector == $this->bw->wd->get("Lobby", "green_sign")){
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("green")){
                        $e->getPlayer()->sendMessage($this->bw->prefix.TF::RED."You are already in ".TF::GREEN."green".TF::RED." team!");
                        return;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }
                    $arena->getTeam("green")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("green"));
                    $e->getPlayer()->sendMessage($this->bw->prefix.TF::GREEN."Joined ".TF::GREEN."green.");
                    return;
                }
            }

        }
    }
    
    public function onJoin(PlayerJoinEvent $ev){
        $p = $ev->getPlayer()->getName();
        if(!$this->bw->mysqlmgr->isPlayerRegistered($p)){
            $this->bw->mysqlmgr->registerPlayer($p);
        }
    }
    
    public function onQuit(PlayerQuitEvent $ev){
        $ev->setQuitMessage("");
    }
    
    public function onChat(PlayerChatEvent $ev){
        $p = $ev->getPlayer();
        $ev->setCancelled(true);
        if(!$this->bw->mtcore->isAuthed($p)){
            $p->sendMessage($this->bw->mtcore->getPrefix().TF::RED."You are not logged in");
            return;
        }
        $bad = ['192.168.', '78.157', 'leet.cc', '.tk', 'lbsg.net', 'inpvp.net', '93.91.250.135', '93.91', 'instantmcpe', 'kurva', 'kurvo', 'piča', 'pussy', 'kokot', 'kkt', 'pičo', 'kokote', 'seru', 'sereš', 'seres', 'curak', 'čůrák', 'curák'. 'cůrák', 'kunda', 'kundo', 'jeba', 'jebat', 'hovno', 'fuck', 'kreten', 'kretén', 'idiot', 'debil', 'blbec', 'mrd', 'pica', 'pico', 'pic', 'penis', 'shit', 'zkurvysyn', 'vyser', 'zaser', 'hovno', 'hovn', 'zasrany'];
        foreach($bad as $s){
            if(\stripos(\strtolower($ev->getMessage()), $s) !== false){
                $p->sendMessage($this->bw->prefix."Do not swear!");
                return;
            }
        }
        if ($p->getLevel() === $this->bw->lobby){
            $this->bw->mtcore->messageLobbyPlayers($ev->getMessage(), $p);
            return;
        }
        if($this->bw->getArena($p) !== null){
            return;
        }
        else {   
            $game = $this->bw->getArena($p)->getPlayer($p);
            if ($game instanceof GamePlayer and $game->isSpectating()){
                $p->sendMessage($this->bw->prefix.TF::ITALIC.TF::GRAY."Spectators can't talk in the chat!");
                return;
            }
            if ($game instanceof GamePlayer and $game->isShopping()) {
                $game->setShopping(false);
                return;
            }
            /** @var Team $team */
            $team = $game->getTeam();
            if ($game instanceof GamePlayer and $game->isPlaying() and $game->getTeam() instanceof Team){
                $team->messagePlayers($ev->getMessage());
                return;
            }
            else {
                $this->bw->mtcore->messageLobbyPlayers($ev->getMessage(), $p);
                return;
            }
        }
    }
    
    
    public function onMove(PlayerMoveEvent $e){
        $p = $e->getPlayer();
        if ($id = $p->getLevel()->getBlockIdAt($e->getTo()->getFloorX(),$e->getTo()->getFloorY(),$e->getTo()->getFloorZ()) == 90 and !$this->bw->getArena($p) instanceof Arena and !isset($this->players[$p->getName()])){
            $this->bw->getServer()->getScheduler()->scheduleDelayedTask(new PortalTask($this->bw, $p), 50);
            $temp = [$p->getName() => $p];
            $this->players = \array_merge($this->players, $temp);
        }
        if ($p->getLevel()->getBlockIdAt($e->getTo()->getFloorX(),$e->getTo()->getFloorY()-1,$e->getTo()->getFloorZ()) == 133){
            switch ("{".$e->getTo()->getFloorX()."_".($e->getTo()->getFloorY()-1)."_".$e->getTo()->getFloorZ()."}"){
                case "{125_18_135}":
                    $p->knockback($p, 0, -0, 6.1232339957368E-17, 1.5);
                break;
            }
        }
    }
    
}

