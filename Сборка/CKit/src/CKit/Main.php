<?php

namespace CKit;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat as F;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\utils\Color;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener
{
	public $kit;
	public $vip;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
}

     public function noDeath(PlayerDeathEvent $e){
	   $this->kit[$e->getPlayer()->getName()] = 1;
	   $this->vip[$e->getPlayer()->getName()] = 1;
	}
	
	public function noJoin(PlayerJoinEvent $e){
		$this->kit[$e->getPlayer()->getName()] = 1;
		$this->vip[$e->getPlayer()->getName()] = 1;
  }

       public function onCommand(CommandSender $p, Command $c, $label, array $args){
       	if($c == "kit"){
              if($this->kit[$p->getName()] == 1){
			      $this->kit[$p->getName()] = 0;
                                   /*      меч     */
                 $ironSword = Item::get(272,0,1);
                 $ironSword->setCustomName("§dМеч игрока§5 ".$p->getName());
                 $ironSword->addEnchantment((Enchantment:: getEnchantment(9)->setLevel(2)));
                 $p->getInventory()->addItem($ironSword);
                               /*      топор     */
                 $ironAxe = Item::get(275,0,1);
                 $ironAxe->setCustomName("§dТопор игрока§5 ".$p->getName());
                 $ironAxe->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(2)));
                 $p->getInventory()->addItem($ironAxe);
                             /*     лопата    */
                 $ironL = Item::get(273,0,1);
                 $ironL->setCustomName("§dЛопата игрока§5 ".$p->getName());
                 $ironL->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(2)));
                 $p->getInventory()->addItem($ironL);
                          /*      кирка    */
                 $ironK = Item::get(274,0,1);
                 $ironK->setCustomName("§dКирка игрока§5 ".$p->getName());
                 $ironK->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(2)));
                 $p->getInventory()->addItem($ironK);
                           /*     Броня    */
                 $armorH = Item::get(298,0,1);
                 $r = mt_rand(1,700);
                 $rr = mt_rand(1,700);
                 $rrr = mt_rand(1,700);
                 $armorH->setCustomColor(Color::getRGB($r,$rr,$rrr));
                 $armorH->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(2)));
                 $armorH->setCustomName("§dШлем игрока§5 ".$p->getName());
                 $p->getInventory()->addItem($armorH);
                              /*        Нагрудник     */
                 $armorC = Item::get(299,0,1);
                 $t = mt_rand(1,700);
                 $tt = mt_rand(1,700);
                 $ttt = mt_rand(1,700);
                 $armorC->setCustomColor(Color::getRGB($t,$tt,$ttt));
                 $armorC->setCustomName("§dНагрудник игрока§5 ".$p->getName());
                 $armorC->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(2)));
                 $p->getInventory()->addItem($armorC);
                                /*        Штаны        */
                 $armorS = Item::get(300,0,1);
                 $rq = mt_rand(1,700);
                 $rrq = mt_rand(1,700);
                 $rrrq = mt_rand(1,700);
                 $armorS->setCustomColor(Color::getRGB($rq,$rrq,$rrrq));
                 $armorS->setCustomName("§dШтаны игрока§5 ".$p->getName());
                 $armorS->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(2)));
                 $p->getInventory()->addItem($armorS);
                           /*          Ботинки        */
                 $armorL = Item::get(301,0,1);
                 $rk = mt_rand(1,700);
                 $rrk = mt_rand(1,700);
                 $rrrk = mt_rand(1,700);
                 $armorL->setCustomColor(Color::getRGB($rk,$rrk,$rrrk));
                 $armorL->setCustomName("§dБотинки игрока§5 ".$p->getName());
                 $armorL->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(2)));
                 $p->getInventory()->addItem($armorL);
                            /*        Вещи       */
                 $bow = Item::get(261,0,1);
                 $bow->setCustomName("§dЛук игрока§5 ".$p->getName());
                 $p->getInventory()->addItem($bow);
                 
                 $arrow = Item::get(262,0,40);
                 $p->getInventory()->addItem($arrow);
                 
                 $apple = Item::get(260,0,16);
                 $p->getInventory()->addItem($apple);
                 
                 $steak = Item::get(364,0,8);
                 $p->getInventory()->addItem($steak);
                 
                 $enderperl = Item::get(368,0,16);
                 $p->getInventory()->addItem($enderperl);
                 
                 $arbuz = Item::get(360,0,10);
                 $p->getInventory()->addItem($arbuz);
                 
                 $goldapple = Item::get(322,0,5);
                 $p->getInventory()->addItem($goldapple);
                 
                 $wood = Item::get(17,0,16);
                 $p->getInventory()->addItem($wood);
                 
                 $tooth = Item::get(50,0,45);
                 $p->getInventory()->addItem($tooth);
                 
                 $glass = Item::get(20,0,15);
                 $p->getInventory()->addItem($glass);
                 
                 $prowerka = Item::get(280,0,1);
                 $prowerka->setCustomName("§aПроверка региона");
                 $p->getInventory()->addItem($prowerka);
                 $p->sendMessage("§8(§aКит§7-§aСтарт§8)§f Вам был выдан §aначальный набор!");
                 $p->sendMessage("§8(§aКит§7-§aСтарт§8)§f VIP набор §7- §b/vip");
                }else{
                 $p->sendMessage("§bMine§cScar§8)§f Вы уже брали этот набор!");
                 $p->sendMessage("§bMine§cScar§8)§f После смерти вы снова §bсможете§f взять этот набор.");
            }
       }
             
                if($c == "vip"){
			     if($this->vip[$p->getName()] == 1){
			      $this->vip[$p->getName()] = 0;
                                   /*      меч     */
                 $ironSword = Item::get(276,0,1);
                 $ironSword->setCustomName("§dМеч игрока§5 ".$p->getName());
                 $ironSword->addEnchantment((Enchantment:: getEnchantment(9)->setLevel(5)));
                 $p->getInventory()->addItem($ironSword);
                               /*      топор     */
                 $ironAxe = Item::get(279,0,1);
                 $ironAxe->setCustomName("§dТопор игрока§5 ".$p->getName());
                 $ironAxe->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(5)));
                 $p->getInventory()->addItem($ironAxe);
                             /*     лопата    */
                 $ironL = Item::get(277,0,1);
                 $ironL->setCustomName("§dЛопата игрока§5 ".$p->getName());
                 $ironL->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(5)));
                 $p->getInventory()->addItem($ironL);
                          /*      кирка    */
                 $ironK = Item::get(278,0,1);
                 $ironK->setCustomName("§dКирка игрока§5 ".$p->getName());
                 $ironK->addEnchantment((Enchantment:: getEnchantment(15)->setLevel(5)));
                 $p->getInventory()->addItem($ironK);
                           /*     Броня    */
                 $armorH = Item::get(310,0,1);
                 $r = mt_rand(1,700);
                 $rr = mt_rand(1,700);
                 $rrr = mt_rand(1,700);
                 $armorH->setCustomColor(Color::getRGB($r,$rr,$rrr));
                 $armorH->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(5)));
                 $armorH->setCustomName("§dШлем игрока§5 ".$p->getName());
                 $p->getInventory()->addItem($armorH);
                              /*        Нагрудник     */
                 $armorC = Item::get(311,0,1);
                 $t = mt_rand(1,700);
                 $tt = mt_rand(1,700);
                 $ttt = mt_rand(1,700);
                 $armorC->setCustomColor(Color::getRGB($t,$tt,$ttt));
                 $armorC->setCustomName("§dНагрудник игрока§5 ".$p->getName());
                 $armorC->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(5)));
                 $p->getInventory()->addItem($armorC);
                                /*        Штаны        */
                 $armorS = Item::get(312,0,1);
                 $rq = mt_rand(1,700);
                 $rrq = mt_rand(1,700);
                 $rrrq = mt_rand(1,700);
                 $armorS->setCustomColor(Color::getRGB($rq,$rrq,$rrrq));
                 $armorS->setCustomName("§dШтаны игрока§5 ".$p->getName());
                 $armorS->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(5)));
                 $p->getInventory()->addItem($armorS);
                           /*          Ботинки        */
                 $armorL = Item::get(313,0,1);
                 $rk = mt_rand(1,700);
                 $rrk = mt_rand(1,700);
                 $rrrk = mt_rand(1,700);
                 $armorL->setCustomColor(Color::getRGB($rk,$rrk,$rrrk));
                 $armorL->setCustomName("§dБотинки игрока§5 ".$p->getName());
                 $armorL->addEnchantment((Enchantment:: getEnchantment(0)->setLevel(5)));
                 $p->getInventory()->addItem($armorL);
                            /*        Вещи       */
                 $bow = Item::get(261,0,3);
                 $bow->setCustomName("§dЛук игрока§5 ".$p->getName());
                 $p->getInventory()->addItem($bow);
                 
                 $arrow = Item::get(262,0,64);
                 $p->getInventory()->addItem($arrow);
                 
                 $diamond = Item::get(264,0,10);
                 $p->getInventory()->addItem($diamond);
                 
                 $iroon = Item::get(265,0,20);
                 $p->getInventory()->addItem($iroon);
                 
                 $goold = Item::get(266,0,15);
                 $p->getInventory()->addItem($goold);
                 $p->sendMessage("§8(§aКит§7-§aСтарт§8)§f Вам был выдан §bVIP набор!");
                 $p->sendMessage("§8(§aКит§7-§aСтарт§8)§f Начальный набор §7- §b/kit");
               }else{
                 $p->sendMessage("§bMine§cScar§8)§f Вы уже брали этот набор!");
                 $p->sendMessage("§bMine§cScar§8)§f После смерти вы снова §bсможете§f взять этот набор.");
         }
      }              
   }
}                   