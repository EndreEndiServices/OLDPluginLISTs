<?php
 
namespace CloudMine;
 
//block events
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
//entity events
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityTeleportEvent;
//player events
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
//COMMANDS
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecuter;
//ITEM
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\WoodenAxe;
//NBT тэги
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
//инвентари всякие
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\BigShapedRecipe;
//utills and math
use pocketmine\utils\TextFormat as F;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
//Entity and Player and Tile
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Human;
use pocketmine\entity\Villager;
use pocketmine\entity\Projectile;
use pocketmine\entity\Arrow;
use pocketmine\tile\ItemFrame;
use pocketmine\item\ItemBlock;
use pocketmine\tile\Sign;
use pocketmine\entity\Effect;
use pocketmine\tile\Tile;
use pocketmine\tile\Chest;
use pocketmine\entity\Entity;
//всякое говно
use pocketmine\plugin\PluginBase;
use pocketmine\permission\Permissible;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use onebone\economyapi\EconomyAPI;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\level\particle\FloatingTextParticle; 
 
class job extends PluginBase implements Listener{
 
   public $miner = array();
   public $cleaner = array();
   public  $finder = array();
   public  $treebreak = array();

public function onEnable(){
$this->getServer()->getPluginManager()->registerEvents($this, $this);
}

public function onCommand(CommandSender $s, Command $cmd, $label, array $args){
switch($cmd->getName()){
case "job":
if(isset($args[0])){
if($args[0] == "шахтер"){
    if(in_array($s->getName(), $this->miner) || in_array($s->getName(), $this->cleaner) || in_array($s->getName(), $this->finder) || in_array($s->getName(), $this->treebreak)){
    $s->sendMessage("§6• §aСначала покиньте работу!");
   }else{
    $s->sendMessage("§6• §eВы успешно устроились на работу: §bШахтёр");
    $this->miner[$s->getName()] = true;
  }
}elseif($args[0] == "уборщик"){
    if(in_array($s->getName(), $this->miner) || in_array($s->getName(), $this->cleaner) || in_array($s->getName(), $this->finder) || in_array($s->getName(), $this->treebreak)){
     $s->sendMessage("§6• §cСначала покиньте работу!");
   }else{
    $s->sendMessage("§6• §eВы успешно устроились на работу: §bУборщик");
    $this->cleaner[$s->getName()] = true;
   }
}elseif($args[0] == "искатель"){
    if(in_array($s->getName(), $this->miner) || in_array($s->getName(), $this->cleaner) || in_array($s->getName(), $this->finder) || in_array($s->getName(), $this->treebreak)){
     $s->sendMessage("§6• §eСначала покиньте работу!");
   }else{
    $s->sendMessage("§6• §eВы успешно устроились на работу: §bИскатель");
    $this->finder[$s->getName()] = true;
   }
}elseif($args[0] == "лесоруб"){
    if(in_array($s->getName(), $this->miner) || in_array($s->getName(), $this->cleaner) || in_array($s->getName(), $this->finder) || in_array($s->getName(), $this->treebreak)){
     $s->sendMessage("§6• §eСначала покиньте работу!");
   }else{
    $s->sendMessage("§6• §eВы успешно устроились на работу: §bЛесоруб");
    $this->treebreak[$s->getName()] = true;
   }
}elseif($args[0] == "list"){
$s->sendMessage("§7-------§eJOBS§7-------\n§b/job лесоруб §7- §eУстроится лесорубом\n§b/job шахтер §7- §eУстроится шахтером\n§b/job искатель §7- §eУстроиться искателем\n§b/job уборщик §7- §eУстроиться уборщиком листвы");
}elseif($args[0] == "leave"){
    if(in_array($s->getName(), $this->cleaner)){
 unset($this->cleaner[$s->getName()]); $s->sendMessage(" §6• §eВы успешно уволились"); 
   }elseif(in_array($s->getName(), $this->miner)){
 unset($this->miner[$s->getName()]); $s->sendMessage(" §6• §eВы успешно уволились"); 
   }elseif(in_array($s->getName(), $this->finder)){
 unset($this->finder[$s->getName()]); $s->sendMessage(" §6• §eВы успешно уволились"); 
   }elseif(in_array($s->getName(), $this->treebreak)){
 unset($this->treebreak[$s->getName()]); $s->sendMessage(" §6• §eВы успешно уволились"); 
   }else{ $s->sendMessage("§bJobs §> §eСначала устройтесь на работу!"); }
}
}else{
$s->sendMessage("§7-------§eJOBS§7-------\n§b/job <работа>§7 - §eПрисоединиться к работе.\n§b/job list§7 - §eПосмотреть список работ.\n§b/job leave §7- §eУволится с работы.");
}
break;
}
}




public function breakJob(BlockBreakEvent $e){
$p = $e->getPlayer();
$x = round($e->getBlock()->getX());
$y = round($e->getBlock()->getY());
$z = round($e->getBlock()->getZ());
if($p->getGamemode() == 0){
 if($e->getBlock()->getId() == 1){
   if(in_array($p->getName(), $this->miner)) {
    EconomyAPI::getInstance()->addMoney($p, 1);
    $p->sendPopup("§e+1$");
   }
 }
 if($e->getBlock()->getId() == 18){
   if(in_array($p->getName(), $this->cleaner)) {
      EconomyAPI::getInstance()->addMoney($p, 1);
      $p->sendPopup("§e+1$");
   }
 }
 if($e->getBlock()->getId() == 14 || $e->getBlock()->getId() == 56){
   if(in_array($p->getName(), $this->finder)){
      EconomyAPI::getInstance()->addMoney($p, 50);
      $p->sendPopup("§e+50$");
   }
 }
 if($e->getBlock()->getId() == 17){
   if(in_array($p->getName(), $this->treebreak)){
      EconomyAPI::getInstance()->addMoney($p, 1);
      $p->sendPopup("§e+1$");
   }
 }
}
}




} //end class