<?php
namespace lotto;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use onebone\economyapi\EconomyAPI;
use pocketmine\utils\TextFormat as color;
use pocketmine\level\sound\AnvilFallSound;

class Main extends PluginBase{
    public function onEnable() {
       
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch ($command){
            case 'lotto':
                if(count($args) == 1 && $sender instanceof Player){
                if(($money = (int) $args[0]) <= 10000){
                  //  $Smoney = EconomyAPI::getInstance()->reduceMoney($sender->getName(), $args[0]);
                    $smoney = EconomyAPI::getInstance()->myMoney($sender->getName());
                 if($smoney < $args[0]){
                     $sender->sendMessage(color::RED."you dont have enough money!");
                     return;
                 }   
                 $r = rand(1, 100);
                   $group = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($sender);

        $groupname = $group->getName();
	$chance = 35;
	switch($groupname){
		case "guest":
		$chance = 35;
		break;
		case "user":
		$chance = 37;
		break;
		case "gamer":
		$chance = 50;
		break;
		case "member":
		$chance = 66;
		break;
		case "vip":
		$chance = 52;
		break;
        case "membervip":
        $chance = 52;
        break;
	}
                 if($r <= $chance){
                     EconomyAPI::getInstance()->addMoney($sender->getName(), $args[0]);
                     $sender->sendMessage(color::GREEN."Congrats! you won §a$".$args[0]);
                     $sender->getLevel()->addSound(new AnvilFallSound($sender), [$sender]);
                    // EconomyAPI::getInstance()->reduceMoney($sender->getName(), $args[0]);
                 }  else {
                     $sender->sendMessage(color::RED."you lost $".$args[0]."! better luck next time.");
                     EconomyAPI::getInstance()->reduceMoney($sender->getName(), $args[0]);
                 }
                }  else {
                 $sender->sendMessage(color::RED."the Max is 10000!");   
                }
                }  else {
                $sender->sendMessage(color::RED."usage: /lotto (money) *note that the max is 500 and you must have the money you inputted");    
                }
                break;
        }
    }
}
