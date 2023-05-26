<?php

namespace eban;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\plugin\PluginBase;

class eban extends PluginBase implements Listener{

    private $bans = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("eban Загружен");
        @mkdir($this->getDataFolder());
        if(file_exists($this->getDataFolder()."bans.txt")){
            $file = @file($this->getDataFolder()."bans.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach($file as $line){
                $array = explode("|", trim($line));
                $this->bans[$array[0]] = $array[1];
            }
        }
    }

    public function onDisable(){
		$this->getLogger()->info("eban IS OFF MAZAFAKA");
        $string = "";
        foreach($this->bans as $client => $name){
            $string .= $client."|".$name."\n";
        }
        @file_put_contents($this->getDataFolder()."bans.txt", $string);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch(strtolower($command->getName())){
            case "eban":
                if(!isset($args[0])){
                    return false;
                }
                $p = array_shift($args);
                $player = $this->getServer()->getPlayer($p);
                if($player !== null and $player->isOnline()){
                    $this->bans[$player->getClientId()] = strtolower($player->getName());
                    $string = "";
                    foreach($this->bans as $client => $name){
                        $string .= $client."|".$name."\n";
                    }
                    @file_put_contents($this->getDataFolder()."bans.txt", $string);
                    $string = "§aВы Были Забанены на сервере §eпо устройству";
                    if(isset($args[0])){
                        $string .= " §bПричина: ".implode(" ", $args);
                    }
                    $player->close("", $string);
                    $this->getServer()->broadcastMessage("§7(§aF§bC§7) §aИгрок§b ".$p. " §aбыл успешно забанен §eпо устройству.");
                }else{
                    $sender->sendMessage("§bИгрока ".$p." не существует!");
                }
                return true;
            break;
            case "uneban":
                if(!isset($args[0])){
                    return false;
                }
                if(($key = array_search(strtolower($args[0]), $this->bans)) !== false){
                    unset($this->bans[$key]);
                    $string = "";
                    foreach($this->bans as $client => $name){
                        $string .= $client."|".$name."\n";
                    }
                    @file_put_contents($this->getDataFolder()."bans.txt", $string);
                    $sender->sendMessage("§7(§aF§bC§7) §aИгрок ".$args[0]." успешно разбанен!");
                }else{
                    $sender->sendMessage("§cИгрок ".$args[0]." не был забанен!");
                }
                return true;
            break;
        }
        return true;
    }

    public function onPreLogin(PlayerPreLoginEvent $event){
        if(isset($this->bans[$event->getPlayer()->getClientId()])){
            $event->getPlayer()->close("", "§eВы были забанены на сервере!");
            $event->setCancelled();
        }
    }

}