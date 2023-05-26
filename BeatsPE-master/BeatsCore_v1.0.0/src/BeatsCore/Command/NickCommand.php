<?php
namespace BeatsCore\Command;
use BeatsCore\Core;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
class NickCommand extends BaseCommand {
  public $nick = [];
  private $plugin;
  
  public function __construct(Core $plugin) {
    $this->plugin = $plugin;
    parent::__construct($plugin, "nick", "Give yourself a Nick!", "/nick", ["nick"]);
  }
  public function execute(CommandSender $sender, $commandLabel, array $args): bool{
  
    if($sender instanceof Player) {
      if($sender->hasPermission("beats.all") || $sender->hasPermission("beats.cmd.all") || $sender->hasPermission("beats.nick")) {
        if(!isset($args[0])) {
          $sender->sendMessage("§l§dBeats§bNick §8»§r §cUsage: /nick <name>, to turn off: /nick off");
     }else{
        if($args[0] == "off") {
          $this->unNick($sender);
          $sender->sendMessage("§l§dBeats§bNick §8»§r §cYou have been unnicked!");
        }else{
          $this->nick($sender, $args[0]);
          $sender->sendMessage("§l§dBeats§bNick §8»§r §aYou have nicked yourself as: " . $args[0] . "§a!");
      }
  }
      }else{
        $sender->sendMessage(Core::PERM_RANK);
        return true;
  }
      }else{
        $sender->sendMessage(Core::USE_IN_GAME);     
      }
      return true;
  }

  public function nick($player, $nick) {
    if($player instanceof Player){
      $player->setDisplayName($nick);
      $player->setNameTag("§e".$nick);
    }
  }
  public function unNick($player) {
    if($player instanceof Player){
      $player->setDisplayName($player->getName());
      $player->setNameTag("§e".$player->getName());
    } 
  }
  public function getPlugin(): Plugin {
		return $this->plugin;
	}
  public function getServer() {
		return $this->getPlugin()->getServer();
	}
}