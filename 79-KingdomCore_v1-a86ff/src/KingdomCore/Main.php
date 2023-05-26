<?php

namespace KingdomCore;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\event\plugin\PluginDisableEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\TranslationContainer;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\Particle;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\utils\Color;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use AntiCheatPE\tasks\SettingsTask;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\ExplodePacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\level\Position\getLevel;
use pocketmine\plugin\PluginManager;
use pocketmine\plugin\Plugin;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\entity\Entity;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;
use pocketmine\network\protocol\UseItemPacket;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat as C;
use pocketmine\block\Block;
use Alert\AlertTask;
use AntiHack\AntiHackEventListener;
use Border\BorderListener;
use ChatFilter\ChatFilterTask;
use ChatFilter\ChatFilter;
use KitPvP\PvP;
use Portal\PortalListener;
use KingdomCore\Main;

class Main extends PluginBase implements Listener {
 
  public $users = [];

  public function onEnable(){
       $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
       $this->yml = $yml->getAll();
       $this->getLogger()->info(C::GREEN ."Starting KingdomCraft Core ". C::WHITE . $this->getConfig()->get("Version"));
       $this->getServer()->getPluginManager()->registerEvents($this ,$this);      
       $this->getServer()->loadLevel("PVP"); 
       $this->getServer()->loadLevel("game1"); 
       $this->getServer()->loadLevel("parkour");
       $this->saveResource("config.yml");
       $this->saveDefaultConfig();
  if($this->getConfig()->get("Dev_Mode") == "true"){
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Dev-Server-Name"));       
  }
       $this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name")); 
       $this->filter = new ChatFilter();
       $this->getServer()->getScheduler()->scheduleRepeatingTask(new AlertTask($this), 2000);
       $this->getServer()->getPluginManager()->registerEvents(new AntiHackEventListener(), $this);
       $this->getServer()->getPluginManager()->registerEvents(new BorderListener($this), $this);
       $this->getServer()->getScheduler()->scheduleRepeatingTask(new ChatFilterTask($this), 30);
       $this->getServer()->getPluginManager()->registerEvents(new PvP($this), $this);
       $this->getServer()->getPluginManager()->registerEvents(new PortalListener($this), $this);
       $this->getLogger()->info(C::GREEN ."Everything Loaded!");
  }

  public function ConfigAntiHack(){
       $yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);   
  }

  public function onRespawn(PlayerRespawnEvent $event){
       $player = $event->getPlayer();
       $player->getInventory()->clearAll();
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $this->Items($player);
       $this->setRank($player); 
  }

  public function onJoin(PlayerJoinEvent $event){ 
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn()); 
       $player = $event->getPlayer();
       $level = $this->getServer()->getLevelByName("hub");
       $br = C::RESET . C::WHITE . "\n";
       $text[0] = C::DARK_RED ."[". C::DARK_GRAY ."------------------------------------". C::DARK_RED ."]". $br . C::GRAY ."Welcome to ". C::AQUA ."Kingdom". C::BLUE ."Craft". $br . C::GRAY ."You are Playing on - play.kcmpe.net". $br . C::GRAY ."Hope you Enjoy you Stay!". $br .C::DARK_RED ."[". C::DARK_GRAY ."------------------------------------". C::DARK_RED ."]";
       $text[1] = C::BOLD . C::AQUA ."Kingdom". C::BLUE ."Craft";
       $text[2] = C::AQUA . "Welcome, ". C::WHITE . $player->getName();
       $text[3] = C::AQUA . "There is ". C::WHITE . count($this->getServer()->getOnlinePlayers()) . C::AQUA ." players online";
       $text[4] = C::AQUA . "Follow us on Twitter". C::WHITE ." @KingdomCraft33";
       $text[5] = C::AQUA . "Check out Our Website". C::WHITE ." kcmcpe.net";
       $player->getInventory()->clearAll();
       $this->Items($player);
       $this->setRank($player); 
       $player->sendMessage($text[0]);
       $level->addParticle(new FloatingTextParticle(new Vector3(171.5505, 68.8, 42.4863), "", $text[1]. $br . $br .$text[2]. $br . $br .$text[3]. $br . $br .$text[4]. $br . $br .$text[5]), [$event->getPlayer()]);
       $level->addParticle(new FloatingTextParticle(new Vector3(175.5505, 65.8, 66.4863), "", C::AQUA ."Parkour"), [$event->getPlayer()]);
       $level->addParticle(new FloatingTextParticle(new Vector3(158.5505, 65.8, 66.4863), "", C::AQUA ."Cafe"), [$event->getPlayer()]);
  }
   
  public function onItemUse(DataPacketReceiveEvent $event){
       $br = C::RESET . C::WHITE . "\n";
       $pk = $event->getPacket();
       $player = $event->getPlayer();
       $level = $event->getPlayer()->getLevel();
   if($pk instanceof UseItemPacket and $pk->face === 0xff) {
       $item = $player->getInventory()->getItemInHand();
   if($item->getName() == C::GREEN ."Help"){
       $this->Help($player);
   }
   elseif($item->getName() == C::GREEN ."Parkour"){ 
       $this->parkourLobby($player);   
   }
   elseif($item->getName() == C::GREEN ."Hub"){
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $player->getInventory()->clearAll();
       $this->Items($player);
       $this->setRank($player); 
   }
   elseif($item->getName() == C::GREEN ."Games"){
       $this->gamesLobby($player); 
   }
   elseif($item->getName() == C::GREEN ."Leaper"){ 
   if($player->getDirection() == 0){
       $player->knockBack($player, 0, 1, 0, 1);
   }
   elseif($player->getDirection() == 1){
       $player->knockBack($player, 0, 0, 1, 1);
   }
   elseif($player->getDirection() == 2){
       $player->knockBack($player, 0, -1, 0, 1);
   }
   elseif($player->getDirection() == 3){
       $player->knockBack($player, 0, 0, -1, 1);
     }
    }
   }
  }

//  public function onEnterAndExit(PlayerMoveEvent $event){
//       $player = $event->getPlayer();
//       $x = round($player->getX());
//       $y = round($player->getY());
//       $z = round($player->getZ());
//  if(($x >= 166 and $x <= 168) and ($y >= 66 and $y <= 67) and ($z >= 86) and $player->getLevel()->getName() == "hub" and $player->getDirection() == 1){
//       $player->sendPopup(C::GOLD . "[NPC]" . C::WHITE ." Welcome ". $player->getName());
//  }
//  elseif(($x >= 166 and $x <= 168) and ($y >= 66 and $y <= 67) and ($z >= 86) and $player->getLevel()->getName() == "hub" and $player->getDirection() == 3){
//       $player->sendPopup(C::GOLD ."[NPC]". C::WHITE ." Goodbye ". $player->getName());
//  }
//  elseif(($x >= 135) and ($y >= 66 and $y <= 67) and ($z <= 61 and $z <= 71) and $player->getLevel()->getName() == "hub" and $player->getDirection() == 2){
//       $player->sendPopup(C::GOLD ."[Cafe Manager]". C::WHITE ." Welcome ". $player->getName() ." To the Cafe");
//  }
//  elseif(($x >= 135) and ($y >= 66 and $y <= 67) and ($z <= 61 and $z <= 71) and $player->getLevel()->getName() == "hub" and $player->getDirection() == 0){
//       $player->sendPopup(C::GOLD ."[Cafe Manager]". C::WHITE ." Hope you come Again ". $player->getName());
//   }
//  }

  public function chatFilter(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $level = $event->getPlayer()->getLevel();
        $event->setRecipients($player->getLevel()->getPlayers());
  if(!in_array($event->getPlayer()->getDisplayName(), $this->users) && !$this->filter->check($event->getPlayer(), $event->getMessage())) { 
       $event->setCancelled(true);
   }
  }

   public function onPlayerChat(PlayerChatEvent $event) {
       $player = $event->getPlayer();
       $message = $event->getMessage();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
       $event->setFormat(C::GOLD ."0". C::WHITE .": ". $player->getName() . C::WHITE ." > ". $message);
  if($rank == "VIP"){
       $event->setFormat(C::GRAY ."[". C::GOLD ."VIP". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE ." > ". $message);
  }
  elseif($rank == "Owner"){
       $event->setFormat(C::GRAY ."[". C::DARK_PURPLE ."Owner". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE ." > ". $message); 
  }
  elseif($rank == "Co-Owner"){
       $event->setFormat(C::GRAY ."[". C::DARK_BLUE ."Co-Owner". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE ." > ". $message);
  }
  elseif($rank == "Admin"){
       $event->setFormat(C::GRAY ."[". C::GREEN ."Admin". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE ." > ". $message);
  }
  elseif($rank == "Mobcrush"){
       $event->setFormat(C::GRAY ."[". C::YELLOW ."MobCrush". C::GRAY ."] ". C::AQUA . $player->getName() . C::WHITE ." > ". $message);
   }
  }

   public function Commands(PlayerCommandPreprocessEvent $event) {
       $cmd = explode(" ", strtolower($event->getMessage()));
       $version = $this->getConfig()->get("Version");
       $player = $event->getPlayer();
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
   if($cmd[0] === "/plugins"){
       $player->sendMessage(C::GRAY ."Plugins (3):". C::GOLD ." KingdomAuth v1.0, KingdomCore ". $version .", SkyWarsCore v1.0");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/?" or $cmd[0] === "/version" or $cmd[0] === "/op" or $cmd[0] === "/deop" or $cmd[0] === "/effect" or $cmd[0] === "/kill" or $cmd[0] === "/enchant" or $cmd[0] === "/weather" or $cmd[0] === "/summon" or $cmd[0] === "/xp"){
       $player->sendMessage(C::RED ."Unknown command. Try /help for a list of commands");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/help"){ 
       $this->Help($player); 
       $event->setCancelled();
   }  
   elseif($cmd[0] === "/hub"){ 
       $player->getInventory()->clearAll();
       $player->getLevel()->addSound(new EndermanTeleportSound($player));
       $event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
       $this->setRank($player);  
       $this->Items($player);
       $event->setCancelled(true);
   }
   elseif($cmd[0] === "/gm"){ 
       $this->HelpGamemode($player); 
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/test"){
       $player->sendMessage("Your Direction '". round($player->getDirection()) ."'");
       $player->sendMessage("Your Name is '". $player->getName() ."'");
       $player->sendMessage("Your XYZ is '". round($player->getX()) ." / ". round($player->getY()) ." / ". round($player->getZ()) ."'");
       $player->sendMessage("Your Currently in '". $player->getLevel()->getName() ."'");
       $event->setCancelled(); 
   }
   elseif($cmd[0] === "/cape" and $player->isOp()){
       $input = array('Minecon_MineconSteveCape2011', 'Minecon_MineconSteveCape2012', 'Minecon_MineconSteveCape2013', 'Minecon_MineconSteveCape2015', 'Minecon_MineconSteveCape2016');
       $randomcape = array_rand($input);
       $player->setSkin($player->getSkinData(), $input[$randomcape]);
       $player->sendMessage(C::GOLD ."Random Cape Added");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/gms" and $player->isOp() and $player->getLevel()->getName() == "hub"){ 
       $player->setGamemode(0);
       $player->sendMessage(C::GOLD ."Your Gamemode has been updated");
       $event->setCancelled();
   }
   elseif($cmd[0] === "/gmc" and $player->isOp() and $player->getLevel()->getName() == "hub"){ 
       $player->setGamemode(1);
       $player->sendMessage(C::GOLD ."Your Gamemode has been updated");
       $event->setCancelled();
   } 
   elseif($player->getLevel()->getName() == "hub" and $player->isOp() and $cmd[0] === "/flyon"){ 
       $player->sendMessage(C::GREEN ."You are now in Flight mode!");
       $player->setAllowFlight(true);
       $event->setCancelled();
   }
   elseif($cmd[0] === "/flyon" and !$player->getLevel()->getName() == "hub"){
       $player->sendMessage(C::RED ."Woah! You cannot use that here");
       $player->setAllowFlight(false);
       $event->setCancelled();
   }
   elseif($cmd[0] === "/gms" and !$player->isOp() or $cmd[0] === "/gmc" and !$player->isOp() and $cmd[0] === "/gamemode" and !$player->isOp()){
       $player->sendMessage(C::RED ."Woah! This is for Staff Only");
       $event->setCancelled();
   }
  }

  public function signSetup(SignChangeEvent $event){
      $player = $event->getPlayer();
  if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
      $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
  if(!($sign instanceof Sign))
  {
  return true;
  }
       $sign = $event->getLines();
  if($sign[0] == "hub"){
       $event->setLine(0, C::DARK_RED ."[". C::GRAY ."-------------". C::DARK_RED ."]");
       $event->setLine(1, C::AQUA ."KingdomCraft");
       $event->setLine(2, C::AQUA ."0.16.0 Alpha");
       $event->setLine(3, C::DARK_RED ."[". C::GRAY ."-------------". C::DARK_RED ."]");
  } 
  elseif($sign[0] == "kit1" and $player->getLevel()->getName() == "game1"){
       $event->setLine(0, C::GRAY ."[" .C::AQUA ."Archer". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit1");
       $event->setLine(3, C::WHITE ."Tap for Kit");
  }
  elseif($sign[0] == "kit2" and $player->getLevel()->getName() == "game1"){
       $event->setLine(0, C::GRAY ."[" .C::RED ."Knight". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit2");
       $event->setLine(3, C::WHITE ."Tap for Kit");
  }
  elseif($sign[0] == "kit3" and $player->getLevel()->getName() == "game1"){
       $event->setLine(0, C::GRAY ."[" .C::GOLD ."Knockback". C::GRAY ."]");
       $event->setLine(1, C::WHITE ."kit3");
       $event->setLine(3, C::WHITE ."Tap for Kit");
    }
   }
  }

  public function gamesLobby($player){
       $player->setAllowFlight(false);
       $player->getLevel()->addSound(new EndermanTeleportSound($player));
       $player->sendMessage("-- ". C::AQUA ." Welcome to Games Lobby ". C::WHITE ." --");
       $player->teleport(Server::getInstance()->getLevelByName("game1")->getSafeSpawn());     
       $player->setHealth(20);
       $player->setFood(20);
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $player->getInventory()->setItem(8, Item::get(345, 0, 1)->setCustomName(C::GREEN ."Hub"));
  }

  public function parkourLobby($player){
       $player->setAllowFlight(false);
       $player->getLevel()->addSound(new EndermanTeleportSound($player));
       $player->sendMessage("-- ". C::AQUA ." Welcome to Games Lobby ". C::WHITE ." --");
       $player->teleport(Server::getInstance()->getLevelByName("parkour")->getSafeSpawn());     
       $player->setHealth(20);
       $player->setFood(20);
       $player->getInventory()->clearAll();
       $player->setGamemode(0);
       $player->getInventory()->setItem(8, Item::get(345, 0, 1)->setCustomName(C::GREEN ."Hub"));
  }
 
  public function setup($player){
       $player->setAllowFlight(false);
       $player->setMaxHealth(40);
       $player->setHealth(40);
       $player->setFood(20);
       $player->getInventory()->clearAll();
  }
 
  public function setRank($player){
       $rankyml = new Config($this->getDataFolder() . "/rank.yml", Config::YAML);
       $rank = $rankyml->get($player->getName());
       $player->setNameTag(C::GOLD ."0". C::WHITE .": ". $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2015');
  if($rank == "VIP"){
       $player->setNameTag(C::GRAY ."[". C::GOLD ."VIP". C::GRAY ."] ". C::AQUA . $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2012');
  }
  elseif($rank == "Owner"){
       $player->setNameTag(C::GRAY ."[". C::DARK_PURPLE ."Owner". C::GRAY ."] ". C::AQUA . $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2016');
  }
  elseif($rank == "Co-Owner"){
       $player->setNameTag(C::GRAY ."[". C::DARK_BLUE ."Co-Owner". C::GRAY ."] ". C::AQUA . $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2013');
  }
  elseif($rank == "Admin"){
       $player->setNameTag(C::GRAY ."[". C::GREEN ."Admin". C::GRAY ."] ". C::AQUA . $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2013');
  }
  elseif($rank == "Mobcrush"){
       $player->setNameTag(C::GRAY ."[". C::YELLOW ."MobCrush". C::GRAY ."] ". C::AQUA . $player->getName());
       $player->setSkin($player->getSkinData(), 'Minecon_MineconSteveCape2012');
   }
  }

  public function Help($player){
       $br = C::RESET . C::WHITE . "\n";
       $player->sendMessage(C::AQUA ."== Help Page 1 of 1 == ". $br . C::AQUA ."/hub:". C::WHITE ." Teleport player to hub". $br . C::AQUA ."/help:". C::WHITE ." lists all Commands". $br . C::AQUA ."/msg:". C::WHITE ." {player} Sends a private message to the given player". $br . C::AQUA ."/gm:".C::WHITE ." Allows Staff to Change Gamemode". $br . C::AQUA ."/flyon:". C::WHITE ." Allows Admins to fly");
  }
 
  public function HelpGamemode($player){
       $br = C::RESET . C::WHITE . "\n";
       $player->sendMessage(C::AQUA ."== Gamemode Help Page 1 of 1 ==". $br . C::AQUA ."/gms:". C::WHITE ." Survival Mode". $br . C::AQUA ."/gmc:". C::WHITE ." Creative Mode");
  }


  public function Items($player){
       $player->getInventory()->setItem(0, Item::get(378, 0, 1)->setCustomName(C::GREEN ."Games"));
       $player->getInventory()->setItem(3, Item::get(369, 0, 1)->setCustomName(C::GREEN ."Parkour"));
       $player->getInventory()->setItem(4, Item::get(288, 0, 1)->setCustomName(C::GREEN ."Leaper"));
       $player->getInventory()->setItem(5, Item::get(339, 0, 1)->setCustomName(C::GREEN ."Help"));
       $player->getInventory()->setItem(8, Item::get(345, 0, 1)->setCustomName(C::GREEN ."Hub"));
       $player->setGamemode(0);
       $player->setMaxHealth(20);
       $player->setHealth(20);
       $player->setFood(20);
  }

  public function onDeathMessage(PlayerDeathEvent $event){
        $event->setDeathMessage("");
        $player = $event->getEntity();
        $cause = $event->getEntity()->getLastDamageCause();
  if($cause instanceof EntityDamageByEntityEvent) {
        $player = $event->getEntity();
        $killer = $cause->getDamager();
  if($killer instanceof Player){
  if($player->getLevel()->getName() == "PVP"){     
        $player->setMaxHealth(20);
        $killer->sendMessage(C::GOLD ."You Killed ". C::WHITE . $player->getName());
        $player->sendMessage(C::GOLD ."You were Killed by ". C::WHITE . $killer->getName());
        $player->getInventory()->clearAll();
     }
    }
   } 
  }

  public function onHungerEvent(PlayerExhaustEvent $event){
          $player = $event->getPlayer();
  if($player->getLevel()->getName() == "hub") {
          $event->setCancelled(true);
   }
  }

  public function onDrop(PlayerDropItemEvent $event){
          $event->setCancelled(true);
  }

  public function onDamage(EntityDamageEvent $event){
          $player = $event->getEntity();
  if($player->getLevel()->getName() == "hub") {
          $event->setCancelled(true);
  }
  elseif($player->getLevel()->getName() == "game1"){ 
          $event->setCancelled(true);
   }
  }

  public function onBreak(BlockBreakEvent $event){
          $player = $event->getPlayer();
  if($player->getLevel()->getName() == "hub" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
  }
  elseif($player->getLevel()->getName() == "game1" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
  }
  elseif($player->getLevel()->getName() == "PVP" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
   }
  }

  public function onPlace(BlockPlaceEvent $event){
          $player = $event->getPlayer();
  if($player->getLevel()->getName() == "hub" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
  }
  elseif($player->getLevel()->getName() == "game1" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
  }
  elseif($player->getLevel()->getName() == "PVP" and !$this->getConfig()->get("Dev_Mode") == "true"){
          $event->setCancelled(true);
   }
  }

  public function onDisable(){
       $this->getLogger()->info(C::RED ."Shutting down KingdomCraft Core ". C::WHITE . $this->getConfig()->get("Version"));
       $this->getLogger()->info("Done!");
  }
}
