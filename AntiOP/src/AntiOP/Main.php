<?php
namespace AntiOP;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{
  
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->getLogger()->info(TextFormat::BLUE . "AntiServerStop by paetti loaded.");
    }
    
    public function onDisable(){
        $this->getLogger()->info(TextFormat::BLUE . "AntiServerStop disabled.");
    }
public function onCommandPreProcess(PlayerCommandPreprocessEvent $event){

	

 $args = explode(" ", $event->getMessage());

if($args[0] == "/op"){
     
if (!($event->getPlayer() instanceof Player)){ 

 return true;
} else {
    $event->getPlayer()->sendMessage(TextFormat::DARK_RED."[Хуй тебе] Тут запрещена эта команда, кек.");
$event->setCancelled();
}


}
}

       public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch($command->getName()){

            
            case "antipure":

$sender->sendMessage(TextFormat::GREEN."Antiop v1.1 coded by SoKoL");
$sender->sendMessage(TextFormat::GREEN."YouTube: Black SoKoL IGM");


return true;

}
}
}
