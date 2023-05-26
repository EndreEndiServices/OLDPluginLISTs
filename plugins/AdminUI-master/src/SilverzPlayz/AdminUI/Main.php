<?php

declare(strict_types=1);

namespace SilverzPlayz\AdminUI;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\{Command,CommandSender};
use pocketmine\utils\Config;
use pocketmine\command\PluginIdentifiableCommand;

class Main extends PluginBase {

    public function onEnable() {
        $this->getLogger()->info("AdminUI Enabled by SilverzPlayz");
    }


public function runAsOp(Player $player, String $cmd){
if ($player->isOp()) {
                      $this->getServer()->dispatchCommand($player, $cmd);
                  } else {
                      $this->getServer()->addOp($player->getName());                     $this->getServer()->dispatchCommand($player, $cmd);
                      $this->getServer()->removeOp($player->getName());
                  }
              }


public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if ($sender instanceof Player and $command->getName() == "admin") {
if(!$sender->hasPermission("adminui.command")){
			$sender->sendMessage("§cYou dont have permission");
			return false;
		}
            $this->AdminPage($sender);
        }
        return true;
    }




    public function AdminPage(Player $player) {
    
    	
        $form = new SimpleForm(function (Player $player, $data){
            if ($data === null) {
                return;
            }
            switch ($data) {
            	   case 0: 
$this->BanPage($player);

                break;

            	   case 1: 
$this->UnbanPage($player);

                break;

            	   case 2: 
$this->KickPage($player);

                break;
                case 3: 
$this->InfoPage($player);

                break;
                    
            }
            }
        );
        $form->setTitle("§b§lAdminUI");
         $form->setContent("Run your Admin commands with Ease with a UI");
         $form->addButton("§3Ban\n§7Ban a player");
$form->addButton("§3Unban\n§7Unban a player");
         $form->addButton("§3Kick\n§7Kick a player");
$form->addButton("§b§lInformation");
        $form->addButton("§8Close");
        $form->sendToPlayer($player);
    }
    
    
   
    public function InfoPage(Player $player) {
    
    	
        $form = new SimpleForm(function (Player $player, $data){
            if ($data === null) {
                return;
            }
            switch ($data) {
            	   case 0: 
$this->AdminPage($player);

                break;

            	   
                    
            }
            }
        );
        $form->setTitle("§b§lInformation");
         $form->setContent("AdminUI was fully coded by SilverzPlayz! For the purpose of executing commands easily with help of a UI");
         $form->addButton("§8Return");
        $form->addButton("§8Close");
        $form->sendToPlayer($player);
    }
    
    
    
    public function BanPage(CommandSender $sender):bool{
        if(!($sender instanceof Player)){
                $sender->sendMessage("Ban a player", false);
                return true;
            }
            $form = new CustomForm(function (Player $player, $data){
                $result = $data[0];
                if ($result == null) {
                }
                switch ($result) {
                    case 0:
		
$this->getServer()->getCommandMap()->dispatch($player, $cmd = "ban $result" . " $result");
        $this->runAsOp($player, $cmd);
                

    break;
                }
            });
            $form->setTitle("§b§lBan");
            $form->addInput("Ban a player", "SilverzPlayz");
$form->addInput("Reason", "Hacking");
            $form->sendToPlayer($sender);
            return true;
    }

	   




    
    public function UnbanPage(CommandSender $sender):bool{
        if(!($sender instanceof Player)){
                $sender->sendMessage("Ban a player", false);
                return true;
            }
            $form = new CustomForm(function (Player $player, $data){
                $result = $data[0];
                if ($result == null) {
                }
                switch ($result) {
                    case 0:
		
$this->getServer()->getCommandMap()->dispatch($player, $cmd = "pardon $result");
        $this->runAsOp($player, $cmd);
                

    
break;
                }
            });
            $form->setTitle("§b§lUnban");
            $form->addInput("Unban a player", "SilverzPlayz");
            $form->sendToPlayer($sender);
            return true;
    }



public function KickPage(CommandSender $sender):bool{
        if(!($sender instanceof Player)){
                $sender->sendMessage("Kick a player", false);
                return true;
            }
            $form = new CustomForm(function (Player $player, $data){
                $result = $data[0];
                if ($result == null) {
                }
                switch ($result) {
                    case 0:
		
$this->getServer()->getCommandMap()->dispatch($player, $cmd = "kick $result");
        $this->runAsOp($player, $cmd);
                

    
break;
                }
            });
            $form->setTitle("§b§lKick");
            $form->addInput("Kick a player", "SilverzPlayz");
            $form->sendToPlayer($sender);
            return true;
    }


}
	    
