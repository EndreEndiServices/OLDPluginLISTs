<?php
namespace FH;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
class FH extends PluginBase{
public function onEnable(){
$this->getLogger()->info("§aПлагин успешно включен!§b Плагин взят с https://vk.com/westplugs");
}
public function onDisable(){
$this->getLogger()->info("§cПлагин выключен!");
}
public function onCommand(CommandSender $sender, Command $command, $label, array $args){
switch($command->getName()){
case "heal":
if($sender instanceof Player){
if($sender->hasPermission("fh.command.heal")){
if($sender->getHealth() == 20){
$sender->sendMessage("§8[§aЗдоровье§8]§7 Вы §eполностью§7 здоровы!");
}else{
$sender->setHealth(20);
$sender->sendMessage("§8[§aЗдоровье§8]§7 Вы §aпополнили§7 свое здоровье!");
}
}
}else{
$sender->sendMessage("§cЭту команду можно использовать только в игре!");
}
break;
case "food":
if($sender instanceof Player){
if($sender->hasPermission("fh.command.food")){
if($sender->getFood() == 20){
$sender->sendMessage("§8[§aГолод§8]§7 Вы §eне голодны§7!");
}else{
$sender->setFood(20);
$sender->sendMessage("§8[§aГолод§8]§7 Вы §aпополнили§7 голод!");
}
}
}else{
$sender->sendMessage("§cЭту команду можно использовать только в игре!");
}
break;
}
}
}
?>