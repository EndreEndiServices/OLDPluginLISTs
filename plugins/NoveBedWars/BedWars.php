<?php

namespace BedWars;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

use BedWars\mysql\MySQLManager;
use BedWars\arena\Arena;
use BedWars\game\GamePlayer;
use BedWars\game\Team;
use BedWars\listener\EventListener;
use BedWars\utils\WorldData;

use MTCore\MTCore;


class BedWars extends PluginBase implements Listener {
    
    public $prefix = (TF::BOLD.TF::BLACK."[ ".TF::WHITE."Bed".TF::DARK_RED."Wars ".TF::BLACK."] ".TF::RESET.TF::WHITE);
 
    /** @var WorldData $wd */
    public $wd;
    /** @var Arena[] $arena */
    public $arena;
    /** @var MySQLManager $mysqlmgr */ 
    public $mysqlmgr;
    /** @var Level $lobby */
    public $lobby;
    /** @var MTCore $mtcore */
    public $mtcore;
    /** @var EventListener $listener */
    public $listener;
    
    public function onEnable(){
        $this->getLogger()->info($this->prefix.TF::GREEN."Enabled!");
        $this->wd = new WorldData($this);
        $this->mtcore = $this->getServer()->getPluginManager()->getPlugin("MTCore");
        $this->arena = [
          "bw-1" => new Arena($this, 1),  
          "bw-2" => new Arena($this, 2),
          "bw-3" => new Arena($this, 3)
        ];
        $this->getServer()->getPluginManager()->registerEvents($this->arena["bw-1"]->getListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents($this->arena["bw-2"]->getListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents($this->arena["bw-3"]->getListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents($this->listener = new EventListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->mysqlmgr = new MySQLManager($this);
        $this->mysqlmgr->createMySQLConnection();
        $this->lobby = $this->getServer()->getDefaultLevel();
        $this->lobby->setTime("5000");
        $this->lobby->stopTime();
    }
    
    public function onDisable(){
        foreach ($this->arena as $arena){
            $arena->stopGame();
        }        
    }
    
    public function getArena(Player $p){
        $name = false;
        foreach ($this->arena as $arena){
            if ($name === false){
                if ($arena->getPlayer($p) instanceof GamePlayer){
                    $name = $arena;
                }
            }
        }
        return $name;
    }
    
    public function onCommand (CommandSender $sd, Command $cmd, $label, array $args){
        if (!$sd instanceof Player){
            return false;
        }
        $arena = $this->getArena($sd) instanceof Arena ? $this->getArena($sd) : null;
        $game = $arena !== null ? $arena->getPlayer($sd) : null;
        $team = $game instanceof GamePlayer ? $game->getTeam() : null;
        switch ($cmd->getName()){
            case "blue":
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("blue")){
                        $sd->sendMessage($this->prefix.TF::RED."You are already in ".TF::BLUE."blue".TF::RED." team!");
                        return true;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }    
                    $arena->getTeam("blue")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("blue"));
                    $sd->sendMessage($this->prefix.TF::GREEN."Joined ".TF::BLUE."blue.");
                    return true;
                }    
            break;
            case "green":
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("green")){
                        $sd->sendMessage($this->prefix.TF::RED."You are already in ".TF::GREEN."green".TF::RED." team!");
                        return true;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }    
                    $arena->getTeam("green")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("green"));
                    $sd->sendMessage($this->prefix.TF::GREEN."Joined green.");
                    return true;
                }   
            break;
            case "red":
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("red")){
                        $sd->sendMessage($this->prefix.TF::RED."You are already in ".TF::RED."red team!");
                        return true;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }    
                    $arena->getTeam("red")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("red"));
                    $sd->sendMessage($this->prefix.TF::GREEN."Joined ".TF::RED."red.");
                    return true;
                }   
            break;
            case "yellow":
                if ($game instanceof GamePlayer and $arena !== null and $arena->getPhase() === Arena::PRESTART){
                    if ($game->getTeam() === $arena->getTeam("yellow")){
                        $sd->sendMessage($this->prefix.TF::RED."You are already in ".TF::YELLOW."yellow".TF::RED." team!");
                        return true;
                    }
                    if ($team instanceof Team){
                        $team->removePlayer($game);
                        $game->removeFromTeam();
                    }    
                    $arena->getTeam("yellow")->addPlayer($game);
                    $game->addToTeam($arena->getTeam("yellow"));
                    $sd->sendMessage($this->prefix.TF::GREEN."Joined ".TF::YELLOW."yellow.");
                    return true;
                }   
            break;
            case "lobby":
                $team = $game instanceof GamePlayer ? $game->getTeam() : null;
                if ($team instanceof Team){
                    $team->removePlayer($game);
                    $game->removeFromTeam();
                }
                if ($arena !== null){
                    $arena->unsetPlayer($sd);
                }
                $sd->teleport($this->lobby->getSpawnLocation());
                $sd->sendMessage($this->prefix.TF::GOLD."Teleporting to lobby...");
                return true;
            break;
            case "stats:":
                $sd->sendMessage(TF::BLUE."> Your ".TF::WHITE.TF::BOLD."Bed".TF::DARK_RED."Wars".TF::RESET.TF::BLUE." stats ".TF::BLUE." <\n"
                    . TF::DARK_GREEN."Kills: ".TF::DARK_PURPLE.$this->mysqlmgr->getKills($sd->getName())."\n"
                    . TF::DARK_GREEN."Deaths: ".TF::DARK_PURPLE.$this->mysqlmgr->getDeaths($sd->getName())."\n"
                    . TF::DARK_GREEN."Wins: ".TF::DARK_PURPLE.$this->mysqlmgr->getWins($sd->getName())."\n"
                    . TF::DARK_GREEN."Losses: ".TF::DARK_PURPLE.$this->mysqlmgr->getLosses($sd->getName())."\n"
                    . TF::DARK_GREEN."Beds destroyed: ".TF::DARK_PURPLE.$this->mysqlmgr->getBeds($sd->getName())."\n"
                    . TF::GRAY."---------------------");
                return true;
            break;
            case "message":
                if($arena !== null && $game instanceof GamePlayer and $game->isSpectating()){
                    $sd->sendMessage($this->prefix.TF::GRAY.TF::BOLD."Sorry, but /msg isn't available for spectators.");
                    return true;
                }
                if(!isset($args[1])){
                    $sd->sendMessage($this->prefix.TF::GRAY."Use /msg [player] message");
                    return true;
                }
                $pl = $this->getServer()->getPlayer($args[0]);
                if(!$pl instanceof Player){
                    $sd->sendMessage($this->prefix."This player doesn't exist or isn't online!");
                    return true;
                }
                $msg = \str_replace($args[0], "", \implode(" ", $args));
                $pl->sendMessage($sd->getDisplayName().TF::DARK_AQUA." -> ".TF::AQUA.$msg);
                return true;
            break;
            case "vote":
                if ($arena === null or !$game instanceof GamePlayer) {
                    return true;
                }
                if(!isset($args[0]) or isset($args[1])){
                    $sd->sendMessage($this->prefix.TF::GRAY."Use /vote [number | map name]");
                    return true;
                }
                $arena->votemgr->voteForMap($sd, $args[0]);
                return true;
            break;
            case "bw":
            case "bedwars":
                if (!$sd->isOp()){
                    $sd->sendMessage($this->prefix.TF::RED."Sorry, but this command is avaible only for ops");
                    return true;
                }
                if (isset($args[0]) and !isset($args[1])){
                    switch ($args[0]){
                        case "start":
                            if ($arena !== null and $arena->getPhase() === Arena::PRESTART){
                                $arena->startGame(true);
                            }
                        break;
                        case "stop":
                            if ($arena !== null and $arena->isRunning()){
                                $arena->stopGame();
                            }
                        break;
                        case "list":
                            $players = [];
                            foreach ($arena->getPlayers() as $gamer) {
                                if ($gamer instanceof GamePlayer)  {
                                    $temp = [$gamer->getPlayer()->getName() => $game->getPlayer()];
                                    $players = \array_merge($temp, $players);
                                }
                            }
                            $sd->sendMessage($this->prefix.TF::DARK_GREEN."Players in arena: ".TF::GOLD.\implode(" ",$players));
                        break;
                    }    
                }
            break;
        }
        return true;
    }   
    
    
}

