<?php

namespace Fixplug;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as color;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config; 

class Fixplug extends PluginBase Implements Listener{

  public $tag = "§9[§eReport§9] §f";

  public function onEnable(){
 $this->getServer()->getPluginManager()->registerEvents($this, $this);

if(!is_dir($this->getDataFolder())){ 
   @mkdir($this->getDataFolder()); 
} 
$this->config = new Config($this->getDataFolder()."config.yml",Config::YAML);
 $this->getLogger()->info("§c>§a==========Report==========§c<");
      }

  public function onDisable(){}

   public function onCommand(CommandSender $sender, Command $command, $label, array $args){
  if($sender instanceof Player){
        switch(strtolower($command->getName())){
           case "report":
                    if(!isset($args[0])){
      $sender->sendMessage($this->tag."§fИспользование /report <ник> [причина]");
      $sender->sendMessage($this->tag."§fТакже укажите Ник обидчика в причине!"); 
              return true;
                }
  $pl = $sender->getServer()->getPlayer($args[0]);
  if($pl instanceof Player){
    if(isset($args[1])){
  $motivo = implode(" ", $args);
								$worte = explode(" ", $motivo);
								unset($worte[0]);
								$motivo = implode(" ", $worte);
  $this->config->set($motivo);
  $this->config->save();
         $sender->sendMessage($this->tag."§6>§fЖалоба успешно отправлена");
     foreach($this->getServer()->getOnlinePlayers() as $p){
									if($p->isOp()){
										$p->sendMessage("§6>§c---------------§8[§e§lReport§r§8]§c---------------§6<\n§9Жалуется§8: §e".$args[0]."\n§9Причина§8: §e".$motivo."\n§9Жалоба на§8: §e".$sender->getName()."\n§9IP Адресс§8: §e".$pl->getAddress()."\n§9Клиент ID§8: §e".$pl->getClientId()."\n§6>§c---------------------------------------§6<");
                  }
            }
       } else {
   $sender->sendMessage($this->tag."§fИспользование /report <ник> [причина]");
         return true;
     }
   } else {
    $sender->sendMessage("§c".$args[0]." §eне онлайн");
    return true;
                   }
             }
       } else {
    $sender->sendMessage($this->tag."§cO comando só funciona dentro do jogo!");
      return true;       
      }
  }
}
    
    
    
      