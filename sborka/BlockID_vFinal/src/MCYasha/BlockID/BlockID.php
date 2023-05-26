<?php

namespace MCYasha\BlockID;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerItemHeldEvent;


class BlockID extends PluginBase implements Listener {
	public $id = array();
	public function onEnable() {	
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
    }
	public function onDisable() {
	}		
		public function onCommand(CommandSender $p, Command $command, $label, array $args) {
      if(strtolower($command->getName()) == "itid")
    	{
    		$brek = $p->getID();
    		$pos = $p->getName();
    		
    		if (! (in_array($brek, $this->id)))
    		{
    			$this->id[$pos] = $brek;
    			$p->sendMessage("§4[§eBlockID§4]§a Распознание ID предмета активированно. возьмите в руку предмет, чтобы узнать его ID ");
    		}
    		else
    		{
    			$fr = array_search($brek, $this->id);
    			unset($this->id[$fr]);
    			$p->sendMessage("§4[§eBlockID§4]§a Распознание ID предмета выключено.");
  }
 }
}
	public function onItemHeld(PlayerItemHeldEvent $event)
	{
		$log = $event->getPlayer()->getID();
		
		if(in_array($log, $this->id))
		{
		$bl = $event->getPlayer()->getInventory()->getItemInHand();
			$event->getPlayer()->sendMessage("§aТы взял в руку:§4 $bl");
		}
	}
}