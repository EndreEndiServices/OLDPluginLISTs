<?php

namespace Kits;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\sound\ExpPickupSound;
use pocketmine\Inventory;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {
	public $kit;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
}

    public function JoinKits(PlayerJoinEvent $event){
     $sender = $event->getPlayer();
     $name = $sender->getName();
       if(!$sender->hasPlayedBefore()){
        	#Шлем игрока
       	$item = Item::get(306, 0, 1);
           $item->setCustomName("§7§i* §f§lШлем игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->setHelmet($item);
           
            #Нагрудник игрока
       	$item = Item::get(307, 0, 1);
           $item->setCustomName("§7§i* §f§lНагрудник игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(1)->setLevel(1));
           $sender->getInventory()->setChestplate($item);
           
            #Поножи игрока
       	$item = Item::get(308, 0, 1);
           $item->setCustomName("§7§i* §f§lПоножи игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(2)->setLevel(1));
           $sender->getInventory()->setLeggings($item);
           
            #Ботинки игрока
       	$item = Item::get(309, 0, 1);
           $item->setCustomName("§7§i* §f§lБотинки игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->setBoots($item);
           
            #Меч игрока
       	$item = Item::get(276, 0, 1);
           $item->setCustomName("§7§i* §f§lМеч игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Кирка игрока
       	$item = Item::get(257, 0, 1);
           $item->setCustomName("§7§i* §f§lКирка игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Топор игрока
       	$item = Item::get(258, 0, 1);
           $item->setCustomName("§7§i* §f§lТопор игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Лопата игрока
       	$item = Item::get(256, 0, 1);
           $item->setCustomName("§7§i* §f§lЛопата игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Лук игрока
       	$item = Item::get(261, 0, 1);
           $item->setCustomName("§bЛук");
           $item->addEnchantment(Enchantment::getEnchantment(19)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Стрела
       	$item = Item::get(262, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Стейк
       	$item = Item::get(364, 0, 16);
           $sender->getInventory()->addItem($item);
           
            #Дерево
       	$item = Item::get(17, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Стекло
       	$item = Item::get(20, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Булыжник
       	$item = Item::get(4, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Кирпич
       	$item = Item::get(45, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Каменный кирпич
       	$item = Item::get(43, 5, 64);
           $sender->getInventory()->addItem($item);
           
            #Светящийся камень
       	$item = Item::get(89, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Алмаз
       	$item = Item::get(264, 0, 16);
           $sender->getInventory()->addItem($item);
           
            #Сундук
       	$item = Item::get(54, 0, 6);
           $sender->getInventory()->addItem($item);
           
            #Печка
       	$item = Item::get(61, 0, 1);
           $sender->getInventory()->addItem($item);
           
            #Зелье опыта
       	$item = Item::get(384, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #LuckyFood
       	$item = Item::get(342, 0, 1);
           $item->setCustomName("§a§iLuckyFood");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #LuckyArmor
       	$item = Item::get(407, 0, 1);
           $item->setCustomName("§b§iLuckyArmor");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #LuckyTools
       	$item = Item::get(408, 0, 1);
           $item->setCustomName("§c§iLuckyTools");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Бесплатный кейс
       	$item = Item::get(333, 0, 1);
           $item->setCustomName("§c§i§lБесплатный кейс§r");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Элитры
       	$item = Item::get(444, 0, 1);
           $item->setCustomName("§c§i§lЭлитры§r");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
          }
      }

     #Выдача через команду
    public function blyadonsdox(PlayerDeathEvent $e){
	   $this->kit[$e->getPlayer()->getName()] = 1;
	}
	
	public function nyblin(PlayerJoinEvent $e){
	   $this->kit[$e->getPlayer()->getName()] = 1;
  }
  
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
	   $sender = $sender;
       $name = strtolower($sender->getName());
		 switch(strtolower($cmd->getName())){
             case "kit":
               if(empty($args[0])){
                $sender->sendMessage("§8(§bКиты§8)§c × §fСтартовый Набор §a/kit start автор лисов");
             return;
             }
             if($args[0] == "start"){
              if($this->kit[$sender->getName()] == 1){
			      $this->kit[$sender->getName()] = 0;
			#Шлем игрока
       	$item = Item::get(306, 0, 1);
           $item->setCustomName("§7§i* §f§lШлем игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->setHelmet($item);
           
            #Нагрудник игрока
       	$item = Item::get(307, 0, 1);
           $item->setCustomName("§7§i* §f§lНагрудник игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(1)->setLevel(1));
           $sender->getInventory()->setChestplate($item);
           
            #Поножи игрока
       	$item = Item::get(308, 0, 1);
           $item->setCustomName("§7§i* §f§lПоножи игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(2)->setLevel(1));
           $sender->getInventory()->setLeggings($item);
           
            #Ботинки игрока
       	$item = Item::get(309, 0, 1);
           $item->setCustomName("§7§i* §f§lБотинки игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->setBoots($item);
           
            #Меч игрока
       	$item = Item::get(276, 0, 1);
           $item->setCustomName("§7§i* §f§lМеч игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(9)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Кирка игрока
       	$item = Item::get(257, 0, 1);
           $item->setCustomName("§7§i* §f§lКирка игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Топор игрока
       	$item = Item::get(258, 0, 1);
           $item->setCustomName("§7§i* §f§lТопор игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Лопата игрока
       	$item = Item::get(256, 0, 1);
           $item->setCustomName("§7§i* §f§lЛопата игрока §e".$sender->getName()." §r§7*");
           $item->addEnchantment(Enchantment::getEnchantment(15)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Лук игрока
       	$item = Item::get(261, 0, 1);
           $item->setCustomName("§bЛук");
           $item->addEnchantment(Enchantment::getEnchantment(19)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Стрела
       	$item = Item::get(262, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Стейк
       	$item = Item::get(364, 0, 16);
           $sender->getInventory()->addItem($item);
           
            #Дерево
       	$item = Item::get(17, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Стекло
       	$item = Item::get(20, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Булыжник
       	$item = Item::get(4, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Кирпич
       	$item = Item::get(45, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Каменный кирпич
       	$item = Item::get(43, 5, 64);
           $sender->getInventory()->addItem($item);
           
            #Светящийся камень
       	$item = Item::get(89, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #Алмаз
       	$item = Item::get(264, 0, 16);
           $sender->getInventory()->addItem($item);
           
            #Сундук
       	$item = Item::get(54, 0, 6);
           $sender->getInventory()->addItem($item);
           
            #Печка
       	$item = Item::get(61, 0, 1);
           $sender->getInventory()->addItem($item);
           
            #Зелье опыта
       	$item = Item::get(384, 0, 64);
           $sender->getInventory()->addItem($item);
           
            #LuckyFood
       	$item = Item::get(342, 0, 1);
           $item->setCustomName("§a§iLuckyFood");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #LuckyArmor
       	$item = Item::get(407, 0, 1);
           $item->setCustomName("§b§iLuckyArmor");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #LuckyTools
       	$item = Item::get(408, 0, 1);
           $item->setCustomName("§c§iLuckyTools");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Бесплатный кейс
       	$item = Item::get(333, 0, 1);
           $item->setCustomName("§c§i§lБесплатный кейс§r");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           
            #Элитры
       	$item = Item::get(444, 0, 1);
           $item->setCustomName("§c§i§lЭлитры§r");
           $item->addEnchantment(Enchantment::getEnchantment(0)->setLevel(1));
           $sender->getInventory()->addItem($item);
           $sender->sendMessage("§8(§eКит Старт§8)§b *§f Вы взяли §cкит набор");
                }else{
           $sender->sendMessage("§8(§eКит Старт§8)§b *§f Вы уже §cбрали §fкит набор!");
           $sender->sendMessage("§8(§eКит Старт§8)§b *§f После §cсмерти §aвы снова сможете§f взять этот набор.");
                }
            }
        }
    }
     #LuckyFood
    public function LuckyCases(PlayerInteractEvent $event){
     $player = $event->getPlayer();
     $inventory = $player->getInventory();
     $items = array(260,297,320,322,350,354,357,360,364,366,391,393,396,400,412,413,424);
     $rand = array_rand($items, 1);
       if($player->getItemInHand()->getId() == 342){
      $event->setCancelled();
      $inventory->removeItem(Item::get(342,0,1));
      $inventory->addItem(Item::get($items[$rand],0,1));
      $player->sendMessage("§7(§eLuckyBlock§7)§a × §fВы успешно открыли §aLuckyFood");
      $player->sendPopup("§6Загляни в инвентарь!");
      $player->getLevel()->addSound(new ExpPickupSound($player));
        }
      #LuckyArmor
     $inventory = $player->getInventory();
     $items = array(306,307,308,309,310,311,312,313,314,315,316,317,302,303,304,305);
     $rand = array_rand($items, 1);
       if($player->getItemInHand()->getId() == 407){
      $event->setCancelled();
      $inventory->removeItem(Item::get(407,0,1));
      $inventory->addItem(Item::get($items[$rand],0,1));
      $player->sendMessage("§7(§eLuckyBlock§7)§a × §fВы успешно открыли §bLuckyArmor");
      $player->sendPopup("§6Загляни в инвентарь!");
      $player->getLevel()->addSound(new ExpPickupSound($player));
       }
       #LuckyTools
      $inventory = $player->getInventory();
      $items = array(267,256,257,258,261,272,273,274,275,276,277,278,279,283,284,285,286);
      $rand = array_rand($items, 1);
        if($player->getItemInHand()->getId() == 408){
       $event->setCancelled();
       $inventory->removeItem(Item::get(408,0,1));
       $inventory->addItem(Item::get($items[$rand],0,1));
       $player->sendMessage("§7(§eLuckyBlock§7)§a × §fВы успешно открыли кейс §cLuckyTools");
       $player->sendPopup("Загляни в инвентарь!");
       $player->getLevel()->addSound(new ExpPickupSound($player));
        }
        #Бесплатный кейс
      $inventory = $player->getInventory();
      $items = array(1,2,4,5,17,20,35,43,45,53,54,64,65,71,86,87,88,89,101,103,116,155,168,169,170,200,201,264,265,266,322,368,444);
      $rand = array_rand($items, 1);
        if($player->getItemInHand()->getId() == 333){
       $event->setCancelled();
       $inventory->removeItem(Item::get(333,0,1));
       $inventory->addItem(Item::get($items[$rand],0,16));
       $player->sendMessage("§7(§eLuckyBlock§7)§a × §fВы успешно открыли §c§iБесплатный кейс");
       $player->sendPopup("§6Загляни в инвентарь!");
       $player->getLevel()->addSound(new ExpPickupSound($player));
        }
    }
}