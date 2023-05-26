<?php

namespace magicalStick\flayzer;

use pocketmine\Player;
use pocketmine\utils\Config;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\command\{CommandSender, Command};

use pocketmine\entity\{Entity, Effect, Human};
use pocketmine\event\entity\{EntityDamageEvent, EntityDamageByEntityEvent};

class main extends PluginBase implements Listener {

	function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->config = new Config($this->getDataFolder() .'config.yml', Config::YAML);
		
		$this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
	}
	
	function onDamage(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent){

            $player = $event->getEntity();
            $damager = $event->getDamager();

            if($damager instanceof Player){
				foreach($damager->getInventory()->getContents() as $item){
                    if($item->getCustomName() === $this->config->get('stickone')){
                        $event->setDamage(5);
                    }
					
					if($item->getCustomName() === $this->config->get('sticktwo')){
                        $event->setDamage(7);
                    }
					
					if($item->getCustomName() === $this->config->get('stickthree')){
                        $event->setDamage(11);
                    }
					
					if($item->getCustomName() === $this->config->get('staffone')){
                        $effect = Effect::getEffect(18);         
						$effect->setVisible(false);        
						$effect->setAmplifier(1);         
						$effect->setDuration(20 * 5 * 20);    
						$player->addEffect($effect);
						
						$event->setDamage(8);
                    }
					
					if($item->getCustomName() === $this->config->get('stafftwo')){
                        $event->setDamage(10);
                    }
					
					if($item->getCustomName() === $this->config->get('staffthree')){
						$effect = Effect::getEffect(20);         
						$effect->setVisible(false);        
						$effect->setAmplifier(1);         
						$effect->setDuration(20 * 5 * 10);    
						$player->addEffect($effect);
						
                        $event->setDamage(12);
                    }
                }
			}
		}
	}
	
	function onCommand(CommandSender $sender, Command $command, $label, array $args){
	    switch($command->getName()){
			case 'magicalstick':
			    if(isset($args[0])){
					if($args[0] == 'stickone'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('stickone')]);
						$item->setCustomName($this->config->get('stickone'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addstickone'));
					}
					
					if($args[0] == 'sticktwo'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('sticktwo')]);
						$item->setCustomName($this->config->get('sticktwo'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addsticktwo'));
					}
					
					if($args[0] == 'stickthree'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('stickthree')]);
						$item->setCustomName($this->config->get('stickthree'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addstickthree'));
					}
			    } else {
					$sender->sendMessage($this->config->get('errorcommand'));
				}
			break;
			case 'staffstick':
			    if(isset($args[0])){
					if($args[0] == 'staffone'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('staffone')]);
						$item->setCustomName($this->config->get('staffone'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addstaffone'));
					}
					
					if($args[0] == 'stafftwo'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('sticktwo')]);
						$item->setCustomName($this->config->get('sticktwo'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addsticktwo'));
					}
					
					if($args[0] == 'staffthree'){
				        $item = \pocketmine\item\Item::get(280, 0, 1);
						$item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						//$item->setLore([$this->config->get('staffthree')]);
						$item->setCustomName($this->config->get('staffthree'));
		                $sender->getInventory()->addItem($item);
						
						$sender->sendMessage($this->config->get('addstaffthree'));
					}
			    } else {
					$sender->sendMessage($this->config->get('errorcommandstaff'));
				}
			break;
			case 'staff':
			    if(isset($args[0])){
					# staff
					if($args[0] == 'staffone'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneystaffone')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('staffone')]);
						    $item->setCustomName($this->config->get('staffone'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneystaffone'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					}
					
					if($args[0] == 'stafftwo'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneystafftwo')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('stafftwo')]);
						    $item->setCustomName($this->config->get('stafftwo'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneystafftwo'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					}
					
					if($args[0] == 'staffthree'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneystaffthree')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('staffthree')]);
						    $item->setCustomName($this->config->get('staffthree'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneystaffthree'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					}
					
					#stick 
					if($args[0] == 'stickone'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneystickone')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('stickone')]);
						    $item->setCustomName($this->config->get('stickone'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneystickone'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					}
					
					if($args[0] == 'sticktwo'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneysticktwo')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('sticktwo')]);
						    $item->setCustomName($this->config->get('sticktwo'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneysticktwo'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					}
					
					if($args[0] == 'stickthree'){
					    if($this->economy->myMoney($sender) >= $this->config->get('countmoneystickthree')){ 
				            $item = \pocketmine\item\Item::get(280, 0, 1);
						    $item->addEnchantment(\pocketmine\item\enchantment\Enchantment::getEnchantment(9)->setLevel(0));
						    //$item->setLore([$this->config->get('stickthree')]);
						    $item->setCustomName($this->config->get('stickthree'));
		                    $sender->getInventory()->addItem($item);
						
						    $sender->sendMessage($this->config->get('addstaffone'));
							$this->economy->reduceMoney($sender, $this->config->get('countmoneystickthree'));
						} else {
							$sender->sendMessage(str_replace('{money}', $this->economy->myMoney($sender), $this->config->get('errorcommandstaff')));
						}
					} else {
						$sender->sendMessage($this->config->get('errorcommandbuy'));
					}
				}
			break;
		}
	}
}
?>