<?php

namespace safecreative;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\event\entity\EntityLevelChangeEvent;
use safecreative\MySqlManager;

class Main extends PluginBase implements Listener{
    
    public $players;
    public $mysql;
    
    public function onEnable(){
        $this->getServer ()->getPluginManager ()->registerEvents ($this, $this );
        $this->getLogger()->info("SafeCreative enabled!");
        try {
            if (! file_exists ( $this->getDataFolder () )) {
                @mkdir ( $this->getDataFolder (), 0777, true );
                if(!is_file($this->getDataFolder()."players.yml")){
                    file_put_contents($this->getDataFolder()."players.yml", $this->getResource()."players.yml");
                }
		}		
        }
        catch ( \Exception $e ) {
            $this->getLogger ()->error ( $e->getMessage());
	}
        $this->mysql = new MySqlManager($this);
        $this->players = new Config($this->getDataFolder()."players.yml",Config::YAML);
        $this->mysql->createMySQLConnection();
    }
    
    public function onDisable(){
        $this->getLogger()->info("SafeCreative disabled!");
    }
    
    public function onBlockBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        $x = $b->x;
        $y = $b->y;
        $z = $b->z;
        $level = $b->level->getName();
        if(!($level == "superflat" || $level == "bioms")){
            return;
        }
        if($e->isCancelled() == false && $this->mysql->isBlock($x, $y, $z, $level)){
            $e->setDrops([]);
            $this->mysql->removeBlock($x, $y, $z, $level);
        }
    }
    
    public function onBlockPlace(BlockPlaceEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        $x = $b->x;
        $y = $b->y;
        $z = $b->z;
        $level = $b->level->getName();
        if(!($level == "superflat" || $level == "bioms")){
            return;
        }
        if(!$p->isOp()){
            if($b->getId() == 46 || $b->getId() == 7){
                $e->setCancelled();
                $p->sendMessage(TextFormat::RED."Nemuzes pokladat tento block");
            }
        }
        if($p->getGamemode() == 1 && $p->hasPermission("sc.save") && !$p->isOp() && !$e->isCancelled()){
            $this->mysql->addBlock($x, $y, $z, $level);
        }
    }
    
    public function onPlayerInteract(PlayerInteractEvent $e){
        $p = $e->getPlayer();
        if($p->getGamemode() == 1 && !$p->isOp()){
        if($e->getItem()->getId() == 259 || $e->getItem()->getId() == 326 || $e->getItem()->getId() == 327 || $e->getItem()->getId() == 383){
            $e->setCancelled(true);
            $p->sendMessage(TextFormat::RED."Nemuzes pouzivat tento item");
        }
        }
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if($sender instanceof Player){
            switch(strtolower($cmd->getName())){
                case "creative":
                    if($sender->getLevel()->getName() == "PVP"){
                        $sender->sendMessage(TextFormat::RED."Nemuzes pouzivat creative v PVP arene");
                    }
                    if($sender->hasPermission("sc.creative")){
                        if($sender->getGamemode() != 1){
                            $this->addPlayer($sender);
                            $sender->setGamemode(1);
                            $sender->sendMessage(TextFormat::YELLOW."Your gamemode is now creative");
                        }
                        else{
                            $sender->sendMessage(TextFormat::YELLOW."You are already in creative");
                        }
                    }
                    break;
                case "survival":
                    if($sender->hasPermission("sc.survival")){
                        if($sender->getGamemode() !== 0){
                            $sender->setGamemode(0);
                            $this->removePlayer($sender);
                            $sender->sendMessage(TextFormat::YELLOW."Your gamemode is now survival");
                        }
                        else{
                            $sender->sendMessage(TextFormat::YELLOW."You are already in survival");
                        }
                    }
                    break;
            }
        }
        else{
            $sender->sendMessage(TextFormat::RED."run command in-game only!");
        }
    }
    
    public function addPlayer(Player $player){
            $this->saveInv($player);
            $this->players->save();
            $this->players->reload();
    }
    
    public function removePlayer(Player $player){
            $this->loadInv($player);
            $this->players->setNested(trim(strtolower($player->getName())).".inventory", null);
            $this->players->save();
            $this->players->reload();
    }
    
    /*public function addBlock(Block $b){
        if(!is_file($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")){
            file_put_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml", "$b->x:$b->y:$b->z");
            return;
        }
        if(substr(file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml"), -1) == "|"){
            file_put_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml", file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")."$b->x:$b->y:$b->z");
        }
        if(substr(file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml"), -1) != "|"){
            file_put_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml", file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")."|$b->x:$b->y:$b->z");
        }
    }
    
    public function removeBlock(Block $b){
        file_put_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml", str_replace("$b->x:$b->y:$b->z:", "", file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")));
    }
    
    public function blockExist(Block $b){
        if(!is_file($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")){
            return false;
        }
        foreach(explode("|", file_get_contents($this->getDataFolder()."{$b->getLevel()->getName()}_blocks.yml")) as $block){
            list($x, $y, $z) = explode(":", $block);
            if($x == $b->x && $y == $b->y && $z == $b->z){
                return true;
            }
        }
        return false;
    }*/
    
    public function saveInv(Player $player){
        $items = [];
        foreach($player->getInventory()->getContents() as $slot=>&$item){
            $items[$slot] = implode(":", [$item->getId(), $item->getDamage(), $item->getCount()]);
        }
        $this->players->setNested(trim(strtolower($player->getName())).".inventory", $items);
    }
    
    public function loadInv(Player $player){
        foreach($this->players->getNested(trim(strtolower($player->getName())).".inventory") as $slot => $t){
            list($id, $dmg, $count) = explode(":", $t);
            $item = Item::get($id, $dmg, $count);
            $player->getInventory()->setItem($slot, $item);
        }
    }
    
    public function onLevelChange(EntityLevelChangeEvent $e){
        $p = $e->getEntity();
        if($p instanceof Player){
            $lvl = $e->getTarget();
            if($lvl->getName() == "PVP" && $p->getGamemode() !== 0 && !$p->isOp()){
                $p->setGamemode(0);
                $this->removePlayer($p);
            }
        }
    }
}