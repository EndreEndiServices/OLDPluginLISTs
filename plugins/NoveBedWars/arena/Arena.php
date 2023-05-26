<?php

namespace BedWars\arena;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\level\particle\LargeExplodeParticle;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat as TF;

use BedWars\BedWars;
use BedWars\game\Bed;
use BedWars\game\GamePlayer;
use BedWars\game\Team;
use BedWars\listener\ArenaListener;
use Bedwars\manager\DeathManager;
use BedWars\manager\ShopManager;
use Bedwars\manager\VoteManager;
use BedWars\manager\WorldManager;
use BedWars\task\ArenaScheduler;
use BedWars\task\PopupTask;

class Arena {
    
    const OFF = 0;
    const PRESTART = 1;
    const WAITING = 1;
    const RUNNING = 2;
    const ENDING = 3;
    
    private $name;
    /** @var BedWars $bw */
    public $bw;
    public $starting;
    public $phase;
    /** @var GamePlayer[] $lobbyp */
    public $lobbyp;
    /** @var GamePlayer[] $players */
    public $players;
    /** @var GamePlayer[] $spectators */
    public $spectators;
    /** @var Team[] $teams */
    public $teams;
    /** @var Bed[] $beds */
    public $beds;
    /** @var Level $map */
    public $map;
    /** @var ArenaListener $listener */
    public $listener;
    /** @var ArenaScheduler $scheduler */
    public $scheduler;
    /** @var PopupTask $popup */
    public $popup;
    /** @var DeathManager $deathmgr */
    public $deathmgr;
    /** @var ShopManager $shopmgr */
    public $shopmgr;
    /** @var VoteManager $votemgr */
    public $votemgr;
    /** @var WorldManager $worldmgr */
    public $worldmgr;
    
    public function __construct(BedWars $bw, $name){
        $this->name = $name;
        $this->starting = false;
        $this->phase = self::WAITING;
        $this->bw = $bw;
        $this->lobbyp = [];
        $this->players = [];
        $this->spectators = [];
        $this->teams = ["red" => new Team($this, "red", TF::RED, []), "blue" => new Team($this, "blue", TF::AQUA, []), "green" => new Team($this, "green", TF::GREEN, []), "yellow" => new Team($this, "yellow", TF::YELLOW, [])];
        $this->beds = ["red" => new Bed($this->teams["red"]), "blue" => new Bed($this->teams["blue"]), "green" => new Bed($this->teams["green"]), "yellow" => new Bed($this->teams["yellow"])];
        $this->listener = new ArenaListener($this, $this->bw);
        $this->bw->getServer()->getScheduler()->scheduleRepeatingTask($this->scheduler = new ArenaScheduler($this), 20);
        $this->bw->getServer()->getScheduler()->scheduleRepeatingTask($this->scheduler = new PopupTask($this), 20);
        $this->deathmgr = new DeathManager($this);
        //$this->endermgr = new EnderManager($this);
        $this->shopmgr = new ShopManager($this);
        $this->votemgr = new VoteManager($this);
        $this->worldmgr = new WorldManager($this);
    }
    
    public function getListener(){
        return $this->listener;
    }
    
    public function getPhase(){
        return $this->phase;
    }
    
    public function joinToArena(Player $p){
        if ($this->getPhase() === self::RUNNING){
            $p->sendMessage($this->bw->prefix.TF::BLUE."Joining as spectator...");
            $this->startSpectating($p);
            return;
        }
        elseif ($this->getPhase() === self::ENDING){
            $p->sendMessage($this->bw->prefix.TF::RED."This arena has ended. Please wait...");
            return;
        }
        else {
            $ranks = ["sponzor", "extra", "owner", "builder", "youtuber"];
            if(count($this->getPlayers()) >= 16 && !$p->isOp() && !in_array($this->bw->mtcore->mysqlmgr->getRank($p->getName()), $ranks)){
                $p->sendMessage($this->bw->prefix.TF::RED."Arena is full\n".$this->bw->prefix.TF::AQUA."Buy EXTRA to connect even arena is full!");
                return;
            }
            $temp = [$p->getName() => new GamePlayer($this, $p)];
            $this->lobbyp = \array_merge($temp, $this->lobbyp);
            $p->setDisplayName("§5[Lobby]  ".$this->bw->mtcore->getDisplayRank($p)." ".$p->getName()."§f");
            $p->setNameTag($p->getName());
            $p->sendMessage($this->bw->prefix.TF::GREEN."Joining to bw-$this->name...");
            $p->teleport($this->bw->wd->get("Lobby", "lobby"));
            $p->setSpawn($this->bw->wd->get("Lobby", "lobby"));
            $inv = $p->getInventory();
            $inv->clearAll();
            $this->addTeamBlocks($inv);
            $this->bw->mtcore->unsetLobby($p);
            $this->checkLobby();
            return;
        }    
    }
    
    public function leaveArena(Player $p){
        $game = $this->getPlayer($p) instanceof GamePlayer ? $this->getPlayer($p) : null; 
        if (!$game instanceof GamePlayer){
            return;
        }
        if($game->isShopping()){
            $this->shopmgr->stopShopping($p);
        }
        /** @var Team $team */
        if($team = $game->getTeam() instanceof Team){
            $p->setSpawn($this->bw->lobby->getSpawnLocation());
            $this->bw->mysqlmgr->addLoss($p->getName());
            $p->sendMessage($this->bw->prefix."Leaving arena...");
            unset($this->bw->listener->players[$p->getName()]);
        }
        $this->unsetPlayer($p);
        if($this->getPhase() === self::RUNNING){
            $this->checkAlive();
        }
        if($p->isOnline()){
            $p->teleport($this->bw->lobby->getSpawnLocation());
        }
        $p->setHealth(20);
        $this->bw->mtcore->setLobby($p);
        $p->setExpBarPercent(0);
        $p->setExpLevel(0);
    }
    
    public function startGame($force = false) {
        $this->selectMap(true);
        $this->phase = self::RUNNING;
        if (count($this->lobbyp) < 8 && $force === false) {
            $this->messageAllPlayers($this->bw->prefix. "8 players are needed to start the arena!");
            $this->scheduler->startTime = 120;
            $this->bw->getServer()->unloadLevel($this->map);
            return;
        }
        foreach ($this->lobbyp as $name => $game) {
            $p = $game->getPlayer();
            if ($p->isOnline()) {
                $temp = [$name => $game];
                $this->players = \array_merge($temp, $this->players);
                unset($this->lobbyp[strtolower($p->getName())]);
                if ($game->getTeam() === null) {
                    /** @var Team[] $pole */
                    $pole = [1 => $this->teams["red"], 2 => $this->teams["blue"], 3 => $this->teams["green"], 4 => $this->teams["yellow"]];
                    $one = \count($pole[1]->getPlayers());
                    $two = \count($pole[2]->getPlayers());
                    $three = \count($pole[3]->getPlayers());
                    $four = \count($pole[4]->getPlayers());
                    if ($one < $two and $one < $three and $one < $four and $one !== 4){
                        $pole[1]->addPlayer($game);
                        $game->addToTeam($pole[1]);
                    }
                    elseif ($two < $one and $two < $three and $two < $four and $two !== 4){
                        $pole[2]->addPlayer($game);
                        $game->addToTeam($pole[2]);
                    }
                    elseif ($three < $one and $three < $two and $three < $four and $three !== 4){
                        $pole[3]->addPlayer($game);
                        $game->addToTeam($pole[3]);
                    }
                    elseif ($four < $one and $four < $two and $four < $three and $four !== 4){
                        $pole[4]->addPlayer($game);
                        $game->addToTeam($pole[4]);
                    }
                    else {
                        for ($i = 1; $i <= 4; $i++){
                            if (\count($pole[$i]->getPlayers()) < 4){
                                $pole[$i]->addPlayer($game);
                                $game->addToTeam($pole[$i]);
                                break;
                            }
                        }
                    }
                    
                }
                if (!$this->bw->getServer()->isLevelLoaded($this->map)) {
                    $this->bw->getServer()->loadLevel($this->map->getFolderName());
                }
                $this->map->setTime(5000);
                $this->map->stopTime;
                /** @var Team $team */
                $team = $game->getTeam();
                /** @var Vector3 $vector */
                $vector = $this->bw->wd->get($this->map->getFolderName(), $team->getColor()."_spawn");
                if ($vector instanceof Vector3){
                    $p->teleport(new Position($vector->getX()+0.5, $vector->getY(), $vector->getZ()+0.5, $this->map));
                }
                $p->getInventory()->clearAll();
                if ($this->bw->mtcore->mysqlmgr->getRank($p->getName()) != "hrac") {
                    $p->sendMessage($this->bw->prefix.TF::GREEN."You gained VIP items!");
                    $p->getInventory()->addItem(Item::get(336, 0, 16));
                    $p->getInventory()->addItem(Item::get(265, 0, 4));
                    $p->getInventory()->addItem(Item::get(266, 0, 1));
                }
                $p->getInventory()->sendContents($p);
                $p->setExpBarPercent(0);
                $p->setExpLevel(0);
                $p->setHealth(20);
            }
        }
        $this->messageAllPlayers($this->bw->prefix.TF::AQUA."Game started!");
    }
    
    public function stopGame(){
        foreach(array_merge($this->players, $this->spectators) as $name => $game){
            if (!$game instanceof GamePlayer){
                return;
            }
            $p = $game->getPlayer();
            if($p->isOnline()){
                if($game->isShopping()){
                    $this->shopmgr->stopShopping($p);
                }
                $this->stopSpectating($p);
                $p->getInventory()->clearAll();
                $p->getInventory()->sendContents($p);
                $p->removeAllEffects();
                $p->setNameTag($this->bw->mtcore->getDisplayRank($p)."  ".$p->getName());
                $p->setDisplayName($this->bw->mtcore->getDisplayRank($p)." ".$p->getName());
                $p->setHealth(20);
                $p->teleport($this->bw->lobby->getSpawnLocation());
                $p->setLevel($this->bw->lobby);
                $this->bw->mtcore->setLobby($p);
            }
        }
        $this->lobbyp = [];
        $this->players = [];
        $this->spectators = [];
        $this->scheduler->gameTime = 3600;
        $this->scheduler->startTime = 120;
        $this->scheduler->drop = 0;
        $this->scheduler->sign = 0;
        $this->popup->ending = 0;
        $this->votemgr->currentTable = [];
        $this->votemgr->stats = [];
        $this->votemgr->players = [];
        $this->votemgr->createVoteTable();
        $this->shopmgr->players = [];
        $this->phase = self::WAITING;
        foreach ($this->teams as $team){
            foreach ($team->getPlayers() as $name => $game){
                $team->removePlayer($game);
                $game->removeFromTeam();
            }
        }
    }
    
    public function breakBed(Player $p, BlockBreakEvent $e){
        /** @var GamePlayer $game */
        $game = $this->getPlayer($p) instanceof GamePlayer ? $this->getPlayer($p) : null;
        if (!$game instanceof GamePlayer){
            echo "není";
            return false;
        }
        /** @var Team $team */
        $team = $game->getTeam();
        $b = $e->getBlock();
        if (new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), ($team->getColor()."_bed")) or new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), $team->getColor()."_bed2")){
            $p->sendMessage($this->bw->prefix.TF::RED."You can not break your own bed");
            $e->setCancelled();
            return false;
        }
        /** @var Team $bedteam */
        if (new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), ("red_bed")) or new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), "red_bed2")){
            $bedteam = $this->teams["red"];
        }
        if (new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), ("blue_bed")) or new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), "blue_bed2")){
            $bedteam = $this->teams["blue"];
        }
        if (new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), ("green_bed")) or new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), "green_bed2")){
            $bedteam = $this->teams["green"];
        }
        if (new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), ("yellow_bed")) or new Vector3($b->getFloorX(), $b->getFloorY(), $b->getFloorZ()) === $this->bw->wd->get($this->map->getFolderName(), "yellow_bed2")){
            $bedteam = $this->teams["yellow"];
        }
        if(!$bedteam->bed instanceof Bed){
            return false;
        }
        foreach($bedteam->getPlayers() as $name => $gamepl){
            $pl = $gamepl->getPlayer();
            if($p->isOnline()){
                $pl->setSpawn($this->bw->lobby->getSpawnLocation());
            }
        }
        $this->bw->mysqlmgr->addBed($p->getName());
        $b = $e->getBlock();
        $this->map->addParticle(new LargeExplodeParticle(new Vector3($b->x, $b->y, $b->z)));
        $pk = new ExplodePacket();
        $pk->x = $b->x;
        $pk->y = $b->y;
        $pk->z = $b->z;
        $pk->radius = 5;
        foreach(array_merge($this->players, $this->spectators) as $game){
            /** @var Player $pl */
            $pl = $game->getPlayer();
            $pl->dataPacket($pk);
        }
        $team = $game->getTeam();
        $color = $team->getChat();
        $name = $team->getColor();
        $this->messageAllPlayers(TF::GRAY."================[ ".TF::DARK_AQUA."Progress".TF::GRAY." ]================\n"
                                                  . $color.$p->getName().TF::GRAY." from ".$color.$name.TF::GRAY." team destroyed ".$bedteam->getChat().$bedteam->getColor().TF::GRAY." team bed\n"
                        .TF::GRAY         . "==========================================");
        $bedteam->bed->setDestroyed();
        return true;
    }
    
    public function isRunning(){
        if ($this->getPhase() !== self::OFF){
            return true;
        }
        else {
            return false;
        }
    }
    
    public function getTeam($color){
        return $this->teams[$color];
    }
    
    public function getBed(Team $team){
        return $team->getBed();
    }
    
    public function getPlayer(Player $p){
        if (isset($this->lobbyp[$p->getName()])){
            return $this->lobbyp[$p->getName()];
        }
        elseif (isset($this->players[$p->getName()])){
            return $this->players[$p->getName()];
        }
        elseif (isset($this->spectators[$p->getName()])){
            return $this->spectators[$p->getName()];
        }
        else {
            return false;
        }
    }
    
    public function getPlayers(){
        return \array_merge($this->players, $this->spectators);
    }
    
    public function getWinningTeam(){
        $one = \count($this->teams["red"]->getPlayers());
        $two = \count($this->teams["blue"]->getPlayers());
        $three = \count($this->teams["green"]->getPlayers());
        $four = \count($this->teams["yellow"]->getPlayers());
        $pole = [$one, $two, $three, $four];
        $biggest = 0;
        $winning = $this->getTeam("blue");
        foreach ($pole as $i => $count){
            if ($count > $biggest){
                $biggest = $count;
                $policko = [0 => $this->teams["red"], 1 => $this->teams["blue"], 2 => $this->teams["green"], 3 => $this->teams["yellow"]];
                $winning = $policko[$i];
            }
        }
        return $winning;
    }
    
    public function messageAllPlayers($msg){
        if ($this->getPhase() === self::WAITING){
            foreach ($this->lobbyp as $game){
                $p = $game->getPlayer();
                if ($p->isOnline()){
                    $p->sendMessage($msg);
                }
            }
        }
        else {
            foreach (\array_merge($this->players, $this->spectators) as $game){
                /** @var GamePlayer $gamin */
                $gamin = $game;
                /** @var Player $p */
                $p = $gamin->getPlayer();
                if ($p->isOnline()){
                    $p->sendMessage($msg);
                }
            }
        }
    }

    public function checkAlive(){

    }
    
    public function unsetPlayer(Player $p){
        $game = $this->getPlayer($p) instanceof GamePlayer ? $this->getPlayer($p) : null;
        if (!$game instanceof GamePlayer){
            return;
        }
        $this->stopSpectating($p);
        try {
            unset($this->players[strtolower($p->getName())]);
            unset($this->lobbyp[strtolower($p->getName())]);
            unset($this->spectators[strtolower($p->getName())]);
        } catch (Exception $ex) {}
        $p->setNameTag($this->bw->mtcore->getDisplayRank($p)."  ".$p->getName());
        $p->setDisplayName($this->bw->mtcore->getDisplayRank($p)." ".$p->getName());
        $p->setGamemode(0);
        if($p->isOnline()){
            $p->getInventory()->clearAll();
        }
    }
    
    public function dropBronze(){
        $chests = [$this->map->getTile($this->bw->wd->get($this->map->getFolderName(), 'blue_bronze')), $this->map->getTile($this->bw->wd->get($this->map->getFolderName(), "red_bronze")), $this->map->getTile($this->bw->wd->get($this->map->getFolderName(), 'yellow_bronze')), $this->map->getTile($this->bw->wd->get($this->map->getFolderName(), 'green_bronze'))];
        foreach($chests as $chest){
            if($chest instanceof Chest){
                $inv = $chest->getInventory();
                $inv->addItem(Item::get(336, 0, 1));
            }
        }
    }
    
    public function dropIron(){
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['blue_iron'], Item::get(265));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['red_iron'], Item::get(265));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['yellow_iron'], Item::get(265));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['green_iron'], Item::get(265));
    }
    
    public function dropGold(){
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['blue_gold'], Item::get(266));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['red_gold'], Item::get(266));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['yellow_gold'], Item::get(266));
        $this->map->dropItem($this->bw->wd->data[$this->map->getFolderName()]['green_gold'], Item::get(266));
    }
    
    public function getGameStatus(){
        $beds = [0 => "§c✖   ", 1 => "§c✖   ", 2 => "§c✖   ", 3 => "§c✖   "];
        foreach ($this->teams as $color => $instance){
            if ($instance->bed instanceof Bed){
                $toReplace = [
                  "blue" => 0,
                  "red" => 1,
                  "yellow" => 2,
                  "green" => 3
                ];
                $beds[$toReplace[$color]] = "§a✔   ";
            }
        }
        return "§9Blue: ".$beds[0]."§4Red: ".$beds[1]."§eYellow: ".$beds[2]."§aGreen: ".$beds[3]."\n".\count($this->teams["blue"]->getPlayers())."         ".\count($this->teams["red"]->getPlayers())."         ".\count($this->teams["yellow"]->getPlayers())."         ".\count($this->teams["green"]->getPlayers())."         ";
    }
    
    
    public function selectMap($force = false){
        if(count($this->lobbyp) < 8 && $force === false){
            $this->messageAllPlayers($this->bw->prefix."8 players are needed to select the map!");
            $this->starting = false;
            $this->scheduler->startTime = 120;
            return;
        }
        $stats = $this->votemgr->stats;
        asort($stats);
        if(!isset($this->votemgr->currentTable[array_keys($stats)[2] - 1])){
            $map = $this->votemgr->currentTable[0];
        }else {
            $map = $this->votemgr->currentTable[array_keys($stats)[2] - 1];
        }
        if($this->bw->getServer()->isLevelLoaded($map)){
            $this->bw->getServer()->unloadLevel($this->bw->getServer()->getLevelByName($map));
        }
        $this->worldmgr->deleteWorld($map);
        $this->worldmgr->addWorld($map);
        $this->map = $this->bw->getServer()->getLevelByName($map);
        foreach($this->lobbyp as $game){
            $p = $game->getPlayer();
            if($p->isOnline()){
                $p->sendMessage(TF::BOLD.TF::YELLOW.$map.TF::GOLD." was chosen");
            }
        }
    }
    
    public function checkLobby(){
        if(count($this->getPlayers()) >= 8 && $this->phase === self::WAITING){
            $this->starting = true;
        }
    }
    
    public function addTeamBlocks(PlayerInventory $inv){
        $inv->setItem(0, Item::get(159, 11, 1));
        $inv->setItem(1, Item::get(159, 14, 1));
        $inv->setItem(2, Item::get(159, 4, 1));
        $inv->setItem(3, Item::get(159, 5, 1));
        for($i = 0; $i < 9; $i++){
            $inv->setHotbarSlotIndex(35, $i);
        }
        $inv->sendContents($inv->getHolder());
    }
    
    public function startSpectating(Player $p, $respawn = false){
        $game = $this->getPlayer($p) instanceof GamePlayer ? $this->getPlayer($p) : null; 
        if($game instanceof GamePlayer and $game->getTeam() !== null){
            return;
        }
        if ($this->getPhase() === self::ENDING){
            $p->sendMessage($this->bw->prefix. TF::RED."Sorry, but arena is unjoinable in this phase.");
        }
        $temp = [$p->getName() => new GamePlayer($this, $p)];
        $this->spectators = \array_merge($temp, $this->spectators);
        $p->getInventory()->clearAll();
        /** @var GamePlayer $gamer */
        $gamer = $this->getPlayers()[array_rand($this->getPlayers())];
        $randPlayer = $gamer->getPlayer();
        if($respawn !== true){
            $p->teleport(new Position($randPlayer->x, $randPlayer->y, $randPlayer->z, $this->map));
        }
        else{
            $p->setSpawn(new Position($p->x + 1, $p->y + 1, $p->z + 1, $this->map));
        }
        $p->setSneaking(false);
        $p->setGamemode(3);
        $p->getInventory()->setItem(0, Item::get(Item::COMPASS)->setCustomName(strtolower($randPlayer->getName())));
        $p->getInventory()->setHotbarSlotIndex(0, 0);
        $p->getInventory()->sendContents($p);
        foreach($this->bw->getServer()->getOnlinePlayers() as $pl){
            $p->despawnFrom($pl);
        }
        $this->bw->mtcore->unsetLobby($p);
    }
    
    public function stopSpectating(Player $p){
        $game = $this->getPlayer($p);
        if (!$game instanceof GamePlayer){
            return false;
        }
        if(!$game->isSpectating()){
            return false;
        }
        unset($this->spectators[strtolower($p->getName())]);
        $p->setGamemode(0);
        $p->spawnToAll();
        $p->setSpawn($this->bw->lobby->getSpawnLocation());
        $p->removeAllEffects();
        if(($inventory = $p->getInventory()) instanceof PlayerInventory){
            $p->getInventory()->clearAll();
        }
        $this->bw->mtcore->setLobby($p);
    }
    
}
