<?php

namespace MTCore;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\item\IronSword;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use MTCore\MySQLManager;
use MTCore\Auth;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class MTCore extends PluginBase implements Listener{

    /** @var  MySQLManager $mysqlmgr */
    public $mysqlmgr;

    /** @var  Auth $authmgr */
    public $authmgr;
    public $players = [];
    public $chatters = [];
    public $level;
    public $lobby;
    public $PP;
    
    public function onEnable(){
        $this->mysqlmgr = new MySQLManager($this);
        $this->mysqlmgr->createMySQLConnection();
        $this->authmgr = new Auth($this);
        $this->PP = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->level = $this->getServer()->getDefaultLevel();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->level->setTime(5000);
        $this->level->stopTime();
        $this->lobby = $this->level->getSpawnLocation();
        $this->getServer()->getNetwork()->setName(TextFormat::BLUE.TextFormat::BOLD."> ".TextFormat::RESET.TextFormat::AQUA."FreezeCraft".TextFormat::BLUE.TextFormat::BOLD." <".TextFormat::RESET."\n\n".TextFormat::AQUA."---------------------------------------------------------------------------------------------------------------------");
    }

    public function onPreLogin(PlayerPreLoginEvent $e){
        $player = $e->getPlayer();
        $ip = $this->mysqlmgr->getIP($player->getName());
        $id = $this->mysqlmgr->getUUID($player->getName());
        $sameIp = ($ip === $player->getAddress() && $id === $player->getUniqueId()) ? true : false;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if($p !== $player && strtolower($player->getName()) === strtolower($p->getName()) && (!$sameIp || $this->isAuthed($p))){
                $e->setCancelled(true);
                $e->setKickMessage(TextFormat::RED."The same nick is already playing");
                return;
            }
        }
        /*$rank = $this->mysqlmgr->getRank($player->getName());
        $ranks = ["vip+", "sponzor", "youtuber", "owner", "builder", "extra"];
        if(count($this->getServer()->getOnlinePlayers()) >= 25 && !$player->isOp() && !in_array(strtolower($rank), $ranks)){
            $e->setCancelled(true);
            $player->kick(TextFormat::RED."Sorry, but server is full. ".TextFormat::YELLOW."More servers will be added later", false);
            return;
        }*/
        $this->players[strtolower($player->getName())]['auth'] = false;
    }
    
    public function onJoin(PlayerJoinEvent $e){
        $p = $e->getPlayer();
        if(strtolower($p->getLevel()->getName()) === "spawn" || strtolower($p->getLevel()->getName()) === "pvp"){
            $this->getServer()->getScheduler()->scheduleDelayedTask(new JoinDelay($this, $p), 10);
        }
        $e->setJoinMessage("");
        foreach($this->getServer()->getOnlinePlayers() as $pl){
            $pl->sendPopup($p->getDisplayName().TextFormat::YELLOW." se pripojil");
        }
        $this->authmgr->checkLogin($p);
        $this->checkPlayer($p);
        $p->sendTip(TextFormat::GREEN."==============".TextFormat::GRAY."[ ".$this->getPrefix().TextFormat::GRAY." ]".TextFormat::GREEN."==============");
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if($sender instanceof Player){
            switch(strtolower($cmd->getName())){
                case "tokens":
                    $sender->sendMessage(TextFormat::YELLOW."Tokens: ".TextFormat::BLUE.$this->mysqlmgr->getTokens($sender->getName()));
                    break;
                case "coins":
                    $sender->sendMessage(TextFormat::YELLOW."Tokens: ".TextFormat::BLUE.$this->mysqlmgr->getTokens($sender->getName()));
                    break;
                case "money":
                    $sender->sendMessage(TextFormat::YELLOW."Tokens: ".TextFormat::BLUE.$this->mysqlmgr->getTokens($sender->getName()));
                    break;
                case "cash":
                    $sender->sendMessage(TextFormat::YELLOW."Tokens: ".TextFormat::BLUE.$this->mysqlmgr->getTokens($sender->getName()));
                    break;
                case "register":
                    if(count($args) < 2 || count($args) > 2){
                        $sender->sendMessage($this->getPrefix().TextFormat::GOLD."Use /register [password] [password]");
                        return;
                    }
                    if($args[0] != $args[1]){
                        $sender->sendMessage($this->getPrefix().TextFormat::RED."Both passwords must be same");
                        return;
                    }
                    $this->authmgr->register($sender, $args[0]);
                    break;
                case "login":
                    if(count($args) < 1 || count($args) > 1){
                        $sender->sendMessage($this->getPrefix().TextFormat::GOLD."Use /login [password]");
                        return;
                    }
                    $this->authmgr->login($sender, $args[0]);
                    break;
                case "gm":
                    if(!$sender->hasPermission("fc.gm")){
                        $sender->sendMessage($cmd->getPermissionMessage());
                        break;
                    }
                    if(count($args) !== 1){
                        $sender->sendMessage(TextFormat::YELLOW."Use /gm [gamemode]");
                        break;
                    }
                    switch($args[0]){
                        case 0:
                            $sender->setGamemode(0);
                            break;
                        case "survival":
                            $sender->setGamemode(0);
                            break;
                        case 1:
                            $sender->setGamemode(1);
                            break;
                        case "creative":
                            $sender->setGamemode(1);
                            break;
                        default:
                            $sender->sendMessage(TextFormat::YELLOW."Please specify a valid gamemode");
                            break;
                    }
            }
        }
        switch(strtolower($cmd->getName())){
            case "setrank":
                if(!$sender->isOp()){
                    return;
                }
                if(count($args) !== 2){
                    return;
                }
                $this->setRank($args[0], $args[1]);
                break;
            case "addcoins":
                if(!$sender->isOp()){
                    return;
                }
                if(count($args) !== 2){
                    return;
                }
                $this->mysqlmgr->addTokens($args[0], intval($args[1]));
                break;
        }
    }
    
    public function checkPlayer(Player $pl){
        $p = $pl->getName();
        if($this->mysqlmgr === null){
            return;
        }
        if(!$this->mysqlmgr->isPlayerRegistered($p)){
            $this->mysqlmgr->registerPlayer($p);
            return;
        }

        switch(strtolower($p)){
            case "creeperface":
                $pl->setOp(true);
                break;
            case "far7sen":
                $pl->setOp(true);
                break;
            case "gradinov":
                $pl->setOp(true);
                break;
            case "tomtam01":
                $pl->setOp(true);
                break;
            case "budy":
                $pl->setOp(true);
                break;
            case "scorpoman123":
                $pl->setOp(true);
                break;
            case "themike":
                $pl->setOp(true);
                break;
            case "moncule":
                $pl->setOp(true);
                break;
            case "coolmencz":
                $pl->setOp(true);
                break;
            case "shadowknightyt":
                $pl->setOp(true);
                break;
            default:
                $pl->setOp(false);
                break;
            }
    }
    
    public function onChat(PlayerChatEvent $e){
        $p = $e->getPlayer();
        if(!$this->isAuthed($p)){
            $p->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
            $e->setCancelled(true);
            return;
        }
        if(isset($this->chatters[strtolower($p->getName())])){
            $e->setCancelled();
            $p->sendMessage(TextFormat::RED."Pockej ".($this->chatters[strtolower($p->getName())]->finaltick - $this->getServer()->getTick()) / 20 . " sekund");
            return;
        }
        if(!$p->isOp() && !isset($this->chatters[strtolower($p->getName())])){
            $this->getServer()->getScheduler()->scheduleDelayedTask($delay = new ChatDelay($this, $p->getName()), 100);
            $this->chatters[strtolower($p->getName())] = $delay;
        }
        $ips = ['leet.cc', '.tk', 'lbsg.net', 'inpvp.net', '93.91.250.135', '93.91', 'instantmcpe'];
        foreach($ips as $slovo){
            if(strpos(str_replace(' ', '', trim($e->getMessage())), $slovo) !== false){
                $e->setCancelled();
                $p->sendMessage($this->getPrefix()."Do not advertise!");
                //$p->kick(TextFormat::GREEN."Reklama", false);
                return;
            }
        }
            $slova = ['kurva', 'kurvo', 'piča', 'pussy', 'kokot', 'kkt', 'pičo', 'kokote', 'seru', 'sereš', 'seres', 'curak', 'čůrák', 'curák'. 'cůrák', 'kunda', 'kundo', 'jeba', 'jebat', 'hovno', 'fuck', 'kreten', 'kretén', 'idiot', 'debil', 'blbec', 'mrd', 'pica', 'pico', 'pic', 'penis', 'shit', 'zkurvysyn', 'vyser', 'zaser', 'hovno', 'hovn'];
            foreach($slova as $s){
                if(strpos(strtolower($e->getMessage()), $s) !== false){
                    $e->setMessage(str_replace($slova, "*****", strtolower($e->getMessage())));
                    return;
                }
            }
    }
    
    public function getPrefix(){
        return "§l§a[§r§bFreezeCraft§l§a]§r§f ".TextFormat::RESET.TextFormat::WHITE;
    }
    
  public function onPlayerInteract(PlayerInteractEvent $event) {
      $p = $event->getPlayer();
    if(!$this->isAuthed($p)) {
        $p->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
      $event->setCancelled();
    }
      if($event->getItem()->getId() === Item::SPAWN_EGG && !$p->isOp()){
          $event->setCancelled();
      }
  }
  public function onPlayerDropItem(PlayerDropItemEvent $event) {
    if(!$this->isAuthed($event->getPlayer())) {
        $event->getPlayer()->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
      $event->setCancelled();
    }
  }
  public function onPlayerItemConsume(PlayerItemConsumeEvent $event) {
    if(!$this->isAuthed($event->getPlayer())) {
        $event->getPlayer()->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
      $event->setCancelled();
    }
  }
  public function onPlayerItemHeld(PlayerItemHeldEvent $event) {
    if(!$this->isAuthed($event->getPlayer())) {
      $event->setCancelled();
    }
  }
  public function onPlayerQuit(PlayerQuitEvent $event) {
    unset($this->players[strtolower($event->getPlayer()->getName())]);
    $event->setQuitMessage("");
      foreach($this->getServer()->getOnlinePlayers() as $pl){
          $pl->sendPopup($event->getPlayer()->getDisplayName().TextFormat::YELLOW." se pripojil");
      }
  }
  public function onEntityArmorChange(EntityArmorChangeEvent $event) {
    if(($player = $event->getEntity()) instanceof Player) {
      if(!$this->isAuthed($player)) {
        $event->setCancelled();
      }
    }
  }
  public function onEntityRegainHealth(EntityRegainHealthEvent $event) {
    if(($player = $event->getEntity()) instanceof Player) {
      if(!$this->isAuthed($player)) {
        $event->setCancelled();
      }
    }
  }
  public function onEntityShootBow(EntityShootBowEvent $event) {
    if(($player = $event->getEntity()) instanceof Player) {
      if(!$this->isAuthed($player)) {
        $event->setCancelled();
      }
    }
  }
  public function onEntityDamage(EntityDamageEvent $event){
      $entity = $event->getEntity();

      if($entity->getLevel()->getName() == "spawn" || $entity->getPosition()->distance($entity->getLevel()->getSpawnLocation()) < 20 || (!$entity instanceof Player && $event instanceof EntityDamageByEntityEvent && $event->getDamager() instanceof Player && $event->getDamager()->isOp() && $event->getDamager()->getInventory()->getItemInHand()->getId() === Item::IRON_SWORD)){
          $event->setCancelled();
          return;
      }

      if ($event instanceof EntityDamageByEntityEvent) {
          if (($attacker = $event->getDamager()) instanceof Player) {
              if (!$this->isAuthed($attacker)) {
                  $event->setCancelled();
              }
          }
      }
      if (($entity = $event->getEntity()) instanceof Player) {
          if (!$this->isAuthed($entity)) {
              $event->setCancelled();
          }
      }
  }

    public function onMove(PlayerMoveEvent $e){
        if(!$this->isAuthed($e->getPlayer())){
            $e->setCancelled();
            return;
        }
        /*if($p->getLevel()->getName() != "spawn"){
            return;
        }
        $pos = $p->getPosition();
        if($pos->x >= 141 && $pos->x <= 149 && $pos->y >= 4 && $pos->y <= 7 && $pos->z === 119){
            $this->getServer()->loadLevel("survival");
            $p->teleport($this->getServer()->getLevelByName("survival")->getSafeSpawn());
        }*/
    }
  public function onBlockPlace(BlockPlaceEvent $event) {
      $p = $event->getPlayer();
    if(!$this->isAuthed($p)) {
        $p->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
      $event->setCancelled();
    }
    if($p->getLevel() == $this->getServer()->getDefaultLevel() && !$p->isOp()){
        $event->setCancelled();
    }
  }
  public function onBlockBreak(BlockBreakEvent $event) {
      $p = $event->getPlayer();
      $event->setInstaBreak(true);
    if(!$this->isAuthed($p)) {
        $p->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
      $event->setCancelled();
    }
    if($p->getLevel() == $this->getServer()->getDefaultLevel() && !$p->isOp()){
        $event->setCancelled();
    }
  }
  
  public function isAuthed(Player $p){
      if($this->players[strtolower($p->getName())]['auth'] === true){
          return true;
      }
      return false;
  }
  
  public function commandPreprocces(PlayerCommandPreprocessEvent $e){
      $p = $e->getPlayer();
      $msg = strtolower($e->getMessage());
      if(!$this->isAuthed($p) && strpos($msg, '/login') !== 0 && strpos($msg, '/register') !== 0){
          $p->sendMessage($this->getPrefix().TextFormat::RED."You are not logged in");
          $e->setCancelled();
          return;
      }
      if(!$p->isOp() && (strpos($msg, '/me') === 0 || strpos($msg, '/tell') === 0)){
         $e->setCancelled();
         $p->sendMessage(TextFormat::RED."Unknown command. Try /help for list of commands");
      }
  }
  
    public function setRank($p, $rank){
        $levelName = null;
        $pl = $this->getServer()->getPlayer($p);
        if($pl == null){
            $pl = $this->getServer()->getOfflinePlayer($p);
        }
        switch(strtolower($rank)){
                        case "vip":
                            $this->mysqlmgr->setRank($p, "VIP");
                            $this->mysqlmgr->setTime($p, time() + 2592000);
                            $this->PP->setGroup($pl, $this->PP->getGroup("VIP"), $levelName);
                            break;
                        case "vip+":
                            $this->mysqlmgr->setRank($p, "VIP+");
                            $this->mysqlmgr->setTime($p, time() + 2592000);
                            $this->PP->setGroup($pl, $this->PP->getGroup("VIP+"), $levelName);
                            break;
                        case "sponzor":
                            $this->mysqlmgr->setRank($p, "Sponzor");
                            $this->mysqlmgr->setTime($p, time() + 7776000);
                            $this->PP->setGroup($pl, $this->PP->getGroup("Sponzor"), $levelName);
                            break;
                        case "hrac":
                            $this->mysqlmgr->setRank($p, "hrac");
                            $this->mysqlmgr->setTime($p, 0);
                            $this->PP->setGroup($pl, $this->PP->getGroup("hráč"), $levelName);
                            break;
                        case "hladmin":
                            $this->mysqlmgr->setRank($p, "owner");
                            $this->mysqlmgr->setTime($p, 0);
                            $this->PP->setGroup($pl, $this->PP->getGroup("Hl.Majitel"), $levelName);
                            break;
        }
        $this->getServer()->getLogger()->info(TextFormat::GREEN."$p group changed");
    }

    public function checkRank(Player $pl){
        $p = $pl->getName();
        switch($this->mysqlmgr->getRank($p)){
            case "VIP":
                if(!(time() >= $this->mysqlmgr->getTime($p))){
                    $time = round(($this->mysqlmgr->getTime($p) - time()) / 86400, 1);
                    $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."VIP rank expires in $time days");
                    $this->PP->setGroup($pl, $this->PP->getGroup("VIP"));
                    break;
                }
                $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."VIP rank expired");
                $this->setRank($p, "hrac");
                break;
            case "VIP+":
                if(!(time() >= $this->mysqlmgr->getTime($p))){
                    $time = round(($this->mysqlmgr->getTime($p) - time()) / 86400, 1);
                    $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."VIP+ rank expires in $time days");
                    $this->PP->setGroup($pl, $this->PP->getGroup("VIP+"));
                    $this->PP->setGroup($pl, $this->PP->getGroup("VIP+"));
                    break;
                }
                $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."VIP+ rank expired");
                $this->setRank($p, "hrac");
                break;
            case "Sponzor":
                if(!(time() >= $this->mysqlmgr->getTime($p))){
                    $time = round(($this->mysqlmgr->getTime($p) - time()) / 86400, 1);
                    $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."Sponzor rank expires in $time days");
                    $this->PP->setGroup($pl, $this->PP->getGroup("Sponzor"));

                    break;
                }
                $pl->sendMessage(TextFormat::GOLD."[FC_Core] ".TextFormat::GREEN."Extra rank expired");
                $this->setRank($p, "hrac");
                break;
        }
    }

    public function qery(QueryRegenerateEvent $e){
        if(count($this->getServer()->getOnlinePlayers()) >= 15){
            $e->setPlayerCount(15);
        }
        $e->setMaxPlayerCount(15);
    }
}