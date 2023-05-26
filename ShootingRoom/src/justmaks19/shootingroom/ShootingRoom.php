<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23.06.2016
 * Time: 12:27
 */

namespace justmaks19\shootingroom;


use justmaks19\shootingroom\task\BlockRemakeTask;
use justmaks19\shootingroom\task\TargetRemakeTask;
use onebone\economyapi\EconomyAPI;
use pocketmine\block\SignPost;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Arrow;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;

class ShootingRoom extends PluginBase implements Listener
{

    const PREFIX = TextFormat::LIGHT_PURPLE."[".TextFormat::YELLOW."ShootingRoom".TextFormat::LIGHT_PURPLE."] ".TextFormat::RESET;
    
    /** @var Config */
    public $config;

    /** @var EconomyAPI */
    public $economy;
    
    public $mode = [];

    public $data = [];

    public $game = [];

    public $time = [];

    public $shot = [];

    public $work = [];

    public $arrow = [
        "onGround" => false,
        "onBlock" => false,
        "isCollide" => false,
        "isAlive" => false
    ];

    public $particle = [];

    public function onEnable()
    {
      $this->getServer()->getPluginManager()->registerEvents($this, $this);
     @mkdir($this->getDataFolder());
     $this->config = new Config($this->getDataFolder()."/config.yml", Config::YAML, array( "timing" => 60, "price" => [ "type" => "money", "items" => [Item::EMERALD.":1"], "money" => 100 ], "prize" => [ "type" => "item", "items" => [Item::GOLD_INGOT.":5", Item::DIAMOND.":1"], "money" => 0 ], "arrow-count" => 10, "targets" => [], "arenas" => [], "info" => [] ));
 $this->economy = EconomyAPI::getInstance();
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        if(strtolower($command->getName()) == "shr") {
            if ($sender instanceof Player) {
                if (isset($args[0])) {
                    switch ($args[0]) {
                        case strtolower("help"):
                            if($sender->hasPermission("shr.command.setup")) {
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "=========================================");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot info §f- §6узнать информацию о себе.");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot addroom (название) §f- §6добавить новую тир-комнату.");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot removeroom (название) §f- §6удалить тир-комнату.");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot list §f- §6посмотреть все тир-комнаты.");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot addtarget §f- §6добавить мешень (блок).");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot open (название) §f- §6открыть тир.");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot close (название) §f- §6закрыть тир.");
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "=========================================");
                            }else{
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "=========================================");
                                $sender->sendMessage(TextFormat::YELLOW . "§7/shot info §f- §6узнать информацию о себе");
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE . "=========================================");
                            }
                            break;
                        case strtolower("info"):
                            if($sender->hasPermission("shr.command.info")){
                                $info = $this->config->getAll()["info"][$sender->getName()];
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE."===========================================");
                                $sender->sendMessage(TextFormat::YELLOW."§7[ShootingRange] §6Информация о вас: ");
                                $sender->sendMessage(TextFormat::YELLOW."§7Всего участий: §6".TextFormat::LIGHT_PURPLE.$info["parts"]);
                                $sender->sendMessage(TextFormat::YELLOW."§7Всего попаданий по цели: §6".TextFormat::LIGHT_PURPLE.$info["hits"]);
                                $sender->sendMessage(TextFormat::LIGHT_PURPLE."===========================================");
                            }
                            break;
                        case strtolower("addtarget"):
                            if($sender->hasPermission("shr.command.setup")){
                                if(!isset($this->mode[$sender->getName()])){
                                    $this->mode[$sender->getName()] = 0;
                                }
                                if($this->mode[$sender->getName()] == 0){
                                    $this->mode[$sender->getName()] = 1;
                                    $sender->sendMessage(TextFormat::YELLOW."§6Теперь нажмите на блок!");
                                }else{
                                    $sender->sendMessage(TextFormat::YELLOW."§6Нажмите на блок!");
                                }
                            }
                            break;
                        case strtolower("addroom"):
                            if($sender->hasPermission("shr.command.setup")){
                                if(isset($args[1])) {
                                    if (!isset($this->mode[$sender->getName()])) {
                                        $this->mode[$sender->getName()] = 0;
                                    }
                                    if ($this->mode[$sender->getName()] == 0) {
                                        $this->mode[$sender->getName()] = 2;
                                        $sender->sendMessage(TextFormat::YELLOW . "§6Теперь нажмите на табличку!");
                                        $this->data[$sender->getName()]["name"] = $args[1];
                                    } else {
                                        $sender->sendMessage(TextFormat::YELLOW . "§6Нажмите на табличку!");
                                    }
                                }else{
                                    $sender->sendMessage(TextFormat::YELLOW."§6Используй §7/shot addroom (название)");
                                }
                            }
                            break;
                        case strtolower("removeroom"):
                            if($sender->hasPermission("shr.command.setup")){
                                if(isset($args[1])) {
                                    $arena = $this->config->getAll()["arenas"];
                                    if(isset($arena[$args[1]])){
                                        $sender->sendMessage(TextFormat::YELLOW."§6Арена успешно удалена!");
                                        $this->config->remove($arena[$args[1]]);
                                        $this->config->save();
                                    }else{
                                        $sender->sendMessage(TextFormat::RED."§6Такой арены не сущесвует!");
                                    }
                                }else{
                                    $sender->sendMessage(TextFormat::YELLOW."§6Используй §7/shot removeroom (название)");
                                }
                            }
                            break;
                        case strtolower("list"):
                            if($sender->hasPermission("shr.command.setup")) {
                                $arena = $this->config->getAll()["arenas"];
                                $arenas = [];
                                foreach ($arena as $name => $data) {
                                    $arenas[] = $name;
                                }
                                $sender->sendMessage(TextFormat::YELLOW . "§6Все тир-арены: §7".implode(", ", $arenas));
                            }
                            break;
                        case strtolower("open"):
                            if($sender->hasPermission("shr.command.work")){
                                if(isset($args[1])){
                                    $arena = $this->config->getAll()["arenas"];
                                    if(isset($arena[$args[1]])){
                                        if($arena[$args[1]]["work"] == false){
                                            $sender->sendMessage(TextFormat::YELLOW."§6Арена§7 ".TextFormat::LIGHT_PURPLE.$args[1].TextFormat::YELLOW." успешно открыта для игроков!");
                                            $arena[$args[1]]["work"] = true;
                                            $this->config->set("arenas", $arena);
                                            $this->config->save();
                                        }else{
                                            $sender->sendMessage(TextFormat::RED."§6Эта арена уже открыта!");
                                        }
                                    }else{
                                        $sender->sendMessage(TextFormat::RED."§6Такой арены не существует!");
                                    }
                                }else{
                                    $sender->sendMessage(TextFormat::YELLOW."§6Используй §7/shot open (название)");
                                }
                            }
                            break;
                        case strtolower("close"):
                            if($sender->hasPermission("shr.command.work")){
                                if(isset($args[1])){
                                    $arena = $this->config->getAll()["arenas"];
                                    if(isset($arena[$args[1]])){
                                        if($arena[$args[1]]["work"] == true){
                                            $sender->sendMessage(TextFormat::YELLOW."§6Арена§7 ".TextFormat::LIGHT_PURPLE.$args[1].TextFormat::YELLOW." закрыта для игроков!");
                                            $arena[$args[1]]["work"] = false;
                                            $this->config->set("arenas", $arena);
                                            $this->config->save();
                                        }else{
                                            $sender->sendMessage(TextFormat::RED."§6Эта арена уже закрыта!");
                                        }
                                    }else{
                                        $sender->sendMessage(TextFormat::RED."§6Такой арены не существует!");
                                    }
                                }else{
                                    $sender->sendMessage(TextFormat::YELLOW."§6Используй §7/shot close (название)");
                                }
                            }
                            break;
                    }
                } else {
                    $sender->sendMessage(TextFormat::YELLOW."§6Используй §7/shot help");
                }
            }else{
                $sender->sendMessage(TextFormat::RED."§6Эту комманду можо использовать только в игре!");

            }
        }
    }

    public function onSetup(PlayerInteractEvent $event){
            $player = $event->getPlayer();
            $block = $event->getBlock();
            if(isset($this->mode[$player->getName()])){
                if($this->mode[$player->getName()] == 1){
                    $this->mode[$player->getName()] = 0;
                    $player->sendMessage(TextFormat::YELLOW."§7Цель установленна!");
                    $coords = [$block->x, $block->y, $block->z];
                    $targets = $this->config->getAll()["targets"];
                    $targets[] = $coords[0]." ".$coords[1]." ".$coords[2];
                    $this->config->set("targets", $targets);
                    $this->config->save();
                    unset($this->mode[$player->getName()]);
                }else if($this->mode[$player->getName()] == 2){
                    if($block instanceof SignPost) {
                        $this->mode[$player->getName()] = 3;
                        $player->sendMessage(TextFormat::YELLOW . "§6Табличка установленна!");
                        $sign = $player->getLevel()->getTile(new Vector3($block->getX(), $block->getY(), $block->getZ()));
                        if($sign instanceof Sign){
                            $sign->setText(TextFormat::LIGHT_PURPLE."[ShootRoom]", TextFormat::YELLOW.$this->data[$player->getName()]["name"], TextFormat::YELLOW."Войти!",TextFormat::LIGHT_PURPLE."==============");
                        }
                        $this->data[$player->getName()]["sign_join"] = round($sign->getX())." ".round($sign->getY())." ".round($sign->getZ());
                        $player->sendMessage(TextFormat::YELLOW."§6Теперь установите спавн игроков!");
                    }else{
                        $player->sendMessage(TextFormat::RED."§6Это не табличка!");
                    }
                }else if($this->mode[$player->getName()] == 3){
                    $this->mode[$player->getName()] = 4;
                    $this->data[$player->getName()]["spawn"] = round($block->getX())." ".round($block->getY())." ".round($block->getZ());
                    $this->data[$player->getName()]["level"] = $player->getLevel()->getName();
                    $player->sendMessage(TextFormat::YELLOW . "§6Точка спавна игроков установленна!");
                    $player->sendMessage(TextFormat::YELLOW."§6Теперь установите табличку выхода игроков!");
                }else if($this->mode[$player->getName()] == 4){
                    if($block instanceof SignPost) {
                        $this->mode[$player->getName()] = 0;
                        $player->sendMessage(TextFormat::YELLOW . "§6Табличка устоновленна!");
                        $sign = $player->getLevel()->getTile(new Vector3($block->getX(), $block->getY(), $block->getZ()));
                        if($sign instanceof Sign){
                            $sign->setText(TextFormat::LIGHT_PURPLE."[ShootRoom]", TextFormat::YELLOW.$this->data[$player->getName()]["name"], TextFormat::YELLOW."Выйти!",TextFormat::LIGHT_PURPLE."================");
                        }
                        $this->data[$player->getName()]["sign_quit"] = round($sign->getX())." ".round($sign->getY())." ".round($sign->getZ());
                        $player->sendMessage(TextFormat::YELLOW."§6Арена готова!");
                        $player->sendMessage(TextFormat::YELLOW."§6Теперь вы можете играть!");
                        $arena = $this->config->getAll()["arenas"];
                        $arena[$this->data[$player->getName()]["name"]] = [
                            "spawn" => $this->data[$player->getName()]["spawn"],
                            "sign_join" => $this->data[$player->getName()]["sign_join"],
                            "sign_quit" => $this->data[$player->getName()]["sign_quit"],
                            "level" => $this->data[$player->getName()]["level"],
                            "work" => false
                        ];
                        $this->config->set("arenas", $arena);
                        $this->config->save();
                        $this->work[$this->data[$player->getName()]["name"]] = false;
                        unset($this->data[$player->getName()]);
                        unset($this->mode[$player->getName()]);
                    }else{
                        $player->sendMessage(TextFormat::RED."§6Это не табличка!");
                    }
                }
            }
    }

    public function onShootRoomJoin(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if($block instanceof SignPost){
            $sign = $player->getLevel()->getTile(new Vector3($block->getX(), $block->getY(), $block->getZ()));
            if($sign instanceof Sign){
                if(!isset($this->mode[$player->getName()])) {
                    $text = $sign->getText();
                    if ($text[2] == TextFormat::YELLOW . "Войти!") {
                        $arenaData = explode(TextFormat::YELLOW, $text[1]);
                        $arn = $this->config->getAll()["arenas"][$arenaData[1]];
                        if($arn["work"] == true) {
                            $value = false;
                            $price = $this->config->getAll()["price"];
                            if($price["type"] == "item"){
                                $itemData = explode(":", $price["item"]);
                                $item = Item::get($itemData[0], 0, $itemData[1]);
                                if($player->getInventory()->contains($item)){
                                    $value = true;
                                    $player->getInventory()->remove($item);
                                }else{
                                    $player->sendMessage(self::PREFIX.TextFormat::YELLOW."§7Для участия нужно §6".$item->getCount()."§6шт §6".$item->getName()." ");
                                }
                            }else if($price["type"] == "money"){
                                $money = $price["money"];
                                if($this->economy->myMoney($player->getName()) >= $money){
                                    $value = true;
                                    $this->economy->reduceMoney($player->getName(), (float) $money);
                                }else{
                                    $player->sendMessage(self::PREFIX.TextFormat::YELLOW."§7Для участия нужно §6".$money."$");
                                }
                            }
                            if($value == true) {
                                if (!isset($this->game[$player->getName()])) {
                                    $line = explode(TextFormat::YELLOW, $text[1]);
                                    $arena = $this->config->getAll()["arenas"][$line[1]];
                                    $pos = explode(" ", $arena["spawn"]);
                                    $level = $this->getServer()->getLevelByName($arena["level"]);
                                    $player->teleport(new Position($pos[0], ($pos[1] + 1), $pos[2], $level));
                                    $bow = Item::get(Item::BOW, 0, 1);
                                    $player->getInventory()->addItem($bow);
                                    $arrow = Item::get(Item::ARROW);
                                    $arrow->setCount($this->config->getAll()["arrow-count"]);
                                    $player->getInventory()->addItem($arrow);
                                    $player->sendMessage(self::PREFIX . TextFormat::YELLOW . "Вы вошли на арену §6" . TextFormat::LIGHT_PURPLE . $text[1] . TextFormat::YELLOW . "!");
                                    $this->game[$player->getName()] = ["arena" => $text[1], "pos" => new Position($player->getX(), $player->getY(), $player->getZ(), $player->getLevel()), "pos_data" => [$player->getX(), $player->getY(), $player->getZ()], "level" => $player->getLevel()->getName()];
                                    $info = $this->config->getAll()["info"];
                                    $info[$player->getName()] = ["parts" => $info[$player->getName()]["parts"] + 1, "hits" => $info[$player->getName()]["hits"]];
                                    $this->config->set("info", $info);
                                    $this->config->save();
                                    foreach ($this->game as $name => $data) {
                                        if ($data["arena"] == $text[1]) {
                                            $this->getServer()->getPlayer($name)->sendTip(self::PREFIX . TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::YELLOW . " зашел на арену!");
                                        }
                                    }
                                    $this->shot[$player->getName()] = 10;
                                } else {
                                    $player->sendMessage(TextFormat::RED . "§7Вы уже в игре!");
                                }
                            }
                        }else{
                            $player->sendMessage(TextFormat::RED."§7Эта арена закрыта для посещения!!!");
                        }
                    } else if ($text[2] == TextFormat::YELLOW . "Выйти!") {
                        if (isset($this->game[$player->getName()])) {
                            $info = $this->game[$player->getName()];
                            $level = $this->getServer()->getLevelByName($info["level"]);
                            $player->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
                            $player->sendMessage(TextFormat::YELLOW . "§7Вы вышли с арены §6" . TextFormat::LIGHT_PURPLE . $text[1] . TextFormat::YELLOW . "!");
                            foreach ($this->game as $name => $data) {
                                if ($data["arena"] == $text[1]) {
                                    $this->getServer()->getPlayer($name)->sendTip(TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::YELLOW . " вышел из ShootRoom!");
                                }
                            }
                            unset($this->game[$player->getName()]);
                            $bow = Item::get(Item::BOW, 0, 1);
                            $player->getInventory()->removeItem($bow);
                            foreach($player->getInventory()->getContents() as $content){
                                if($content->getId() == Item::ARROW){
                                    if($content->getCount() == $this->shot[$player->getName()]){
                                        $player->getInventory()->remove($content);
                                    }
                                }else if($content->getId() == Item::BOW){
                                    $player->getInventory()->remove($content);
                                }
                            }
                            unset($this->shot[$player->getName()]);
                        } else {
                            $player->sendMessage(TextFormat::RED . "§7Вы не в игре!");
                        }
                    }
                }
            }
        }
    }

    public function onProjectileHitEvent(ProjectileHitEvent $event){
        $arrow = $event->getEntity();
        $player = $arrow->shootingEntity;
        if($arrow instanceof Arrow){
            if($player instanceof Player){
                if(isset($this->game[$player->getName()])) {
                    $positions = [
                        round($arrow->getX()) . " " . round($arrow->getY()) . " " . round($arrow->getZ()),
                        round($arrow->getX() - 1) . " " . round($arrow->getY()) . " " . round($arrow->getZ()),
                        round($arrow->getX() + 1) . " " . round($arrow->getY()) . " " . round($arrow->getZ()),
                        round($arrow->getX()) . " " . round($arrow->getY() - 1) . " " . round($arrow->getZ()),
                        round($arrow->getX()) . " " . round($arrow->getY() + 1) . " " . round($arrow->getZ()),
                        round($arrow->getX()) . " " . round($arrow->getY()) . " " . round($arrow->getZ() - 1),
                        round($arrow->getX()) . " " . round($arrow->getY()) . " " . round($arrow->getZ() + 1),
                    ];
                    foreach ($this->config->getAll()["targets"] as $target) {
                        foreach ($positions as $pos) {
                            if ($pos == $target) {
                                $prize = $this->config->getAll()["prize"];
                                if ($prize["type"] == "item") {
                                    foreach ($this->config->getAll()["prize"]["items"] as $stringItem) {
                                        $items = explode(":", $stringItem);
                                        $item = Item::get($items[0], 0, $items[1]);
                                        $player->getInventory()->addItem($item);
                                    }
                                    $player->sendMessage(self::PREFIX . TextFormat::YELLOW . "§7Вы попали в цель и получили §6" . $item->getCount() . "шт " . $item->getName() . "!");
                                } else if ($prize["type"] == "money") {
                                    $money = $prize["money"];
                                    $this->economy->addMoney($player->getName(), $money);
                                    $player->sendMessage(self::PREFIX . TextFormat::YELLOW . "§7Вы попали в цель и получили §6" . $money . "!");
                                }
                                $arrayPos = explode(" ", $pos);
                                $block = $player->getLevel()->getBlock(new Vector3($arrayPos[0], $arrayPos[1], $arrayPos[2]));
                                $this->getServer()->getScheduler()->scheduleRepeatingTask(new TargetRemakeTask($this, $player->getLevel(), $block, $positions[0]), 20);
                                $info = $this->config->getAll()["info"];
                                $info[$player->getName()] = ["parts" => $info[$player->getName()]["parts"], "hits" => $info[$player->getName()]["hits"] + 1];
                                $this->config->set("info", $info);
                                $this->config->save();
                                foreach ($this->game as $name => $data) {
                                    if ($data["arena"] == $this->game[$player->getName()]["arena"]) {
                                        $this->getServer()->getPlayer($name)->sendPopup(TextFormat::LIGHT_PURPLE . $player->getName() . TextFormat::YELLOW . " попал в цель!!");
                                    }
                                }
                            }
                        }
                    }
                    (int) $this->shot[$player->getName()]--;
                    $player->getLevel()->removeEntity($arrow);
                }
            }
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $info = $this->config->getAll()["info"];
        if(!isset($info[$player->getName()])){
            $info[$player->getName()] = ["parts" => 0, "hits" => 0];
            $this->config->set("info", $info);
            $this->config->save();
        }
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        if(isset($this->game[$player->getName()])){
            unset($this->game[$player->getName()]);
        }
        if(isset($this->mode[$player->getName()])){
            unset($this->mode[$player->getName()]);
        }
        if(isset($this->data[$player->getName()])){
            unset($this->data[$player->getName()]);
        }
        if(isset($this->shot[$player->getName()])){
            unset($this->shot[$player->getName()]);
        }
    }

    public function onDrop(PlayerDropItemEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        if(isset($this->game[$player->getName()])) {
            if ($item->getId() == Item::ARROW || $item->getId() == Item::BOW) {
                $event->setCancelled(true);
            }
        }
    }
    
    public function onDeath(PlayerDeathEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player){
            if(isset($this->game[$player->getName()])){
                unset($this->data[$player->getName()]);
                if(isset($this->shot[$player->getName()])){
                    $arrows = Item::get(Item::ARROW);
                    $arrows->setCount($this->shot[$player->getName()]);
                    $player->getInventory()->remove($arrows);
                    $player->getInventory()->remove(Item::get(Item::BOW));
                    unset($this->shot[$player->getName()]);
                }
            }
        }
    }

    public function onTp(EntityTeleportEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player){
            if(isset($this->game[$player->getName()])){
                unset($this->data[$player->getName()]);
            }
            if(isset($this->shot[$player->getName()])){
                $arrows = Item::get(Item::ARROW);
                $arrows->setCount($this->shot[$player->getName()]);
                $player->getInventory()->remove($arrows);
                $player->getInventory()->remove(Item::get(Item::BOW));
                unset($this->shot[$player->getName()]);
            }
        }
    }

    public function onPvp(EntityDamageEvent $event){
        $player = $event->getEntity();
        $cause = $player->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent){
            $damager = $cause->getDamager();
            if($player instanceof Player && $damager instanceof Player){
                if(isset($this->game[$player->getName()]) && isset($this->game[$damager->getName()])){
                    $event->setCancelled(true);
                }
            }
        }
    }
    
}
