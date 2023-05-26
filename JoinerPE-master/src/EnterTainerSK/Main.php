
<?php

namespace EnterTainerSK;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;
use pocketmine\level\sound\FizzSound;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Main extends PluginBase implements Listener {
	
	  public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("#####################");
        $this->getLogger()->info(" Joiner by EnterTainerSK LOADED!");
        $this->getLogger()->info("#####################");
    }

	 public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool {
  switch($command->getName()){
   case "lobby":
		$spawn = $this->getServer()->getDefaultLevel()->getSafeSpawn();
		$this->getServer()->getDefaultLevel()->loadChunk($spawn->getFloorX(), $spawn->getFloorZ());
		$sender->teleport($spawn,0,0);
		$sender->sendMessage("§7(§e!§7)§a Teleporting to main §elobby§a!");
		$sender->addTitle("§l§aⓘ", "§eWelcome back on lobby!");
     		break;
   return true;
      case "spawn":
      $command = "transferserver zetralcraft.tk 19132";
	   $this->getServer()->getCommandMap()->dispatch($sender, $command);
	     break;
	     }
	return true;
	}

    public function onJoin(PlayerJoinEvent $event){
	$event->setJoinMessage("§7[§a+§7]§e " . $event->getPlayer()->getName());
	$player = $event->getPlayer();	
	$on = count($this->getServer()->getOnlinePlayers());
	$max = $this->getServer()->getMaxPlayers();
   $nick = $player->getName();
	$inv = $player->getInventory();
	$player->sendMessage("§9⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛§r\n \n §l§a>§7 Welcome on server§a " . $nick . " \n §a>§7 Actualy online is:§6 " . $on."§e/§6".$max."\n  \n§9⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛⬛");
   $player->addTitle("§l§aⓘ", "§eWelcome on server §c". $player->getNameTag());
			$player->getLevel()->broadcastLevelSoundEvent($player->asVector3(), LevelSoundEventPacket::SOUND_TWINKLE);
     }

    public function onQuit(PlayerQuitEvent $event){
	$event->setQuitMessage("§7[§c - §7]§e " . $event->getPlayer()->getName());
	  }
	
    public function onDisable() {
        $this->getLogger()->info("#####################");
        $this->getLogger()->info(" Joiner by EnterTainerSK DISABLED!");
        $this->getLogger()->info("#####################");
      }
}

