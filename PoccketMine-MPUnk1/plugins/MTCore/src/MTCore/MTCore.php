<?php

namespace MTCore;

use MTCore\Object\PlayerData;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\Human;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerHungerChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class MTCore extends PluginBase implements Listener {

    /** @var Level $level */
    public $level;

    /** @var Position $lobby */
    public $lobby;

    /** @var PlayerData $players */
    public $players = [];
	
	public function onEnable(){
        $this->level = $this->getServer()->getDefaultLevel();
        $this->level->setTime(0);
        $this->level->stopTime();
        $this->lobby = $this->level->getSpawnLocation();
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new MessageTask($this), 2500);
        $this->getServer()->getLogger()->info(self::getPrefix()."§r§aENABLED!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function getPrefix(){
        return "§o§l§5»»ILOVEMCPE«« ";
    }

    public function onPreLogin(PlayerPreLoginEvent $e){
        $p = $e->getPlayer();
        if (\count($this->getServer()->getOnlinePlayers()) >= 1){
            if(!$p->hasPermission("imcpe.log.full")){
                $e->setKickMessage(self::getPrefix()."\n".
                    "§cServerul este plin.\n".
                    "§5Doar ce-i cu §6VIP §5se pot conecta§7!!\n".
                    "§eCumpara §6VIP §ede pe ILOVEMCPE.buycraft.net");
                $e->setCancelled(true);
            }
        }
    }

    public function onLogin(PlayerLoginEvent $e){
        $p = $e->getPlayer();
        $this->players[strtolower($p->getName())] = new PlayerData($p);
    }

    public function onJoin(PlayerJoinEvent $e){
        $e->setJoinMessage("");
        $p = $e->getPlayer();
        if($p->getGamemode() !== 0) {
            $p->setGamemode(0);
        }
        $this->setLobby($p, true);
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        $sd = $sender;
        $not = false;
        switch($cmd->getName()){
            case "msg":
                if (!$sd->hasPermission("imcpe.cmd.message")){
                    $sd->sendMessage(self::getPrefix()."§cYou don't have permission to use private messages\n".
                        "§eCumpara §6VIP §esi vei avea acces la §9/KIT VIP");
                    return true;
                }
                if (\count($args) < 2){
                    $sd->sendMessage(self::getPrefix()."§6Folosesti: /msg <jucator> <mesaj>");
                    return true;
                }
                $p = $this->getServer()->getPlayerExact($args[0]);
                if (!($p instanceof Player)){
                    $sd->sendMessage(self::getPrefix()."§cJucatorul nu exista!");
                    return true;
                }
                break;
            case "ban":
                if (!$sd->hasPermission("imcpe.ban")){
                    $sd->sendMessage(self::getPrefix()."§cNu ai permisiunea la aceasta comanda _|_ !");
                    return true;
                }
                if (\count($args) < 2){
                    $sd->sendMessage(self::getPrefix()."§6Foloseste: /ban <jucator> <motiv>");
                    return true;
                }
                $p = $this->getServer()->getPlayerExact($args[0]);
                if ($p instanceof Player && ($p->hasPermission("imcpe.immune"))){
                    $sd->sendMessage(self::getPrefix()."§cCan not ban this player; Perhaps you are trying to ban server staff?");
                    return true;
                }
                $reason = \str_replace($args[0], "", implode(" ", $args));
                break;
            case "help":
                $sd->sendMessage(
                    "§7-------------------------------------\n".
                    self::getPrefix()."§ePagina de ajutor §b1/1\n".
                    "§7-------------------------------------\n".
                    "§b/msg §e=> §aTrimite mesaje unui jucator\n".
                    "§b/kit §e=> §aPentru a alege kitul\n".
                    "§7-------------------------------------"
                );
                break;
            default:
                $not = true;
                break;
        }
        if($not && $sender instanceof ConsoleCommandSender){
            $sender->sendMessage(self::getPrefix()."§cTyto prikazy nejsou dostupne pro konzoli.");
            return true;
        }
    }
	
    public function onChat(PlayerChatEvent $e){
        $p = $e->getPlayer();

        /** @var PlayerData $pl */
        $pl = $this->players[strtolower($p->getName())];
        $diff = ($pl->getChatTick()+5) - time();
        if ($diff > 0 && !$pl->getChatTick() == 0 && !$p->hasPermission("imcpe.waitbypass")){
            $p->sendMessage(self::getPrefix()."§cTe rog sa astepti $diff secunde pentru a putea vorbi din nou");
            $pl->setTick(time());
            return;
        }
        $pl->setTick(time());

        $ips = [".cz", ".eu", ".sk", ".tk", ".com", ".net", "lifeboat", "inpvp" , "leet.cc" , "playmc.net" , "nycuro.ddns.net" , "mcx.minecraft-romania.ro" , "mcx"];
        foreach ($ips as $ip){
            if (stripos($e->getMessage(), $ip) !== false){
                $p->kick(self::getPrefix()."\n§cYou have been kicked due to:\n§eServer advertising");
                return;
            }
        }
        $slova = ['kurva', 'kurvo', 'piča', 'pussy', 'kokot', 'kkt', 'pičo', 'kokote', 'seru', 'sereš', 'seres', 'curak', 'čůrák',
            'curák' . 'cůrák', 'kunda', 'kundo', 'jeba', 'jebat', 'hovno', 'fuck', 'kreten', 'kretén', 'idiot', 'debil', 'blbec',
            'mrd', 'pica', 'pico', 'pic', 'penis', 'shit', 'zkurvysyn', 'vyser', 'zaser', 'hovno', 'hovn', 'zasrany'];
        foreach ($slova as $s) {
            if (stripos(strtolower($e->getMessage()), $s) !== false){
                $p->sendMessage(self::getPrefix()."§cDo not swear!");
                return;
            }
        }
        if ($pl->inLobby()) {
            $this->messageLobbyPlayers($e->getMessage(), $p);
            return;
        }
    }

    public function messageLobbyPlayers($message, Player $p){
        if (!$p->hasPermission("imcpe.color")){
            $message = str_replace("§", "", $message);
        }
        $msg = self::getDisplayRank($p).$p->getName()."§3 > ".self::getChatColor($p).$message;
        /** @var PlayerData $pl */
        foreach ($this->players as $pl) {
            if ($pl->inLobby()){
                $pl->getPlayer()->sendMessage($msg);
            }
        }
        $this->getServer()->getLogger()->info($msg);
    }

    public function onPlayerInteract(PlayerInteractEvent $e){
        $p = $e->getPlayer();
		
        /** @var PlayerData $pl */
        $pl = $this->players[strtolower($p->getName())];

        if ($pl->inLobby() && $e->getItem()->getId() === 347 && $e->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR){
            if ($pl->isPlayersVisible()){
                $this->despawnPlayersFrom($p);
                $p->getInventory()->remove($p->getInventory()->getItemInHand());
                $p->getInventory()->addItem(Item::get(347, 0, 1)->setCustomName("§r§eArata pjucatorii"));
                $p->sendMessage("§eVanished all players");
            }else{
                $this->spawnPlayersTo($p);
                $p->getInventory()->remove($p->getInventory()->getItemInHand());
                $p->getInventory()->addItem(Item::get(347, 0, 1)->setCustomName("§r§eAscunde jucatorii"));
                $p->sendMessage("§eToti jucatorii sunt acum invizibili");
            }
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $e){
		$player = $e->getPlayer();
    }

    public function onPlayerItemConsume(PlayerItemConsumeEvent $e){
		$player = $e->getPlayer();
    }

    public function onPlayerItemHeld(PlayerItemHeldEvent $e){
		$player = $e->getPlayer();
    }

    public function onPlayerQuit(PlayerQuitEvent $e) {
        unset($this->players[strtolower($e->getPlayer()->getName())]);
        $e->setQuitMessage("§ea iesit din joc");
    }

    public function onPlayerKick(PlayerKickEvent $e) {
        $e->setQuitMessage("a fost dat afara de pe server");
    }

    public function onEntityArmorChange(EntityArmorChangeEvent $e) {
        /** @var Player $p */
        $p = $e->getEntity();
    }

    public function onEntityInventoryChange(EntityInventoryChangeEvent $e) {
        /** @var Player $p */
        $p = $e->getEntity();
    }

    public function onEntityRegainHealth(EntityRegainHealthEvent $e) {
        /** @var Player $p */
        $p = $e->getEntity();
    }

    public function onEntityShootBow(EntityShootBowEvent $e){
        /** @var Player $p */
        $p = $e->getEntity();
    }

    public function onEntityDamage(EntityDamageEvent $e){
        /** @var Player $p */
        $p = $e->getEntity();
    }

    public function onBlockPlace(BlockPlaceEvent $e){
        $p = $e->getPlayer();
    }

    public function onBlockBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
    }

    public function commandPreprocces(PlayerCommandPreprocessEvent $e){
        $p = $e->getPlayer();
        $msg = strtolower($e->getMessage());
        $e->setMessage(str_replace("&", "§", $e->getMessage()));
    }

    public function setLobby(Player $p, $join = false) {
        $p->setHealth(20);
        $p->setFood(20);
		$p->removeAllEffects();

        /** @var PlayerData $pl */
        $pl = $this->players[$p->getName()];

        if (!$join && $p->getInventory() instanceof PlayerInventory) {
            $p->getInventory()->clearAll();
            $p->getInventory()->setItem(0, Item::get(Item::CLOCK, 0, 1)->setCustomName("§r§eHide Players"));
            $p->getInventory()->setItem(1, Item::get(Item::GOLD_INGOT, 0, 1));
            $p->getInventory()->setHotbarSlotIndex(0, 0);
            $p->getInventory()->setHotbarSlotIndex(1, 1);
            $p->getInventory()->sendContents($p);
        }
    }

    public function despawnPlayersFrom(Player $p) {
        foreach ($this->level->getPlayers() as $pl) {
            $pl->despawnFrom($p);
        }
    }

    public function spawnPlayersTo(Player $p) {
        foreach ($this->level->getPlayers() as $pl) {
            $pl->spawnTo($p);
        }
    }

    public function onHunger(PlayerHungerChangeEvent $e){
        if ($e->getPlayer()->getLevel() === $this->level){
            $e->setCancelled(true);
        }
    }

    public function sendText(Player $p, $perm){
        if ($p->hasPermission($perm)){
            $p->sendMessage("Mas pravo na permissi $perm");
            return;
        }
        $p->sendMessage("Nemas pravo na permissi $perm");
    }
}

class MessageTask extends Task{

    public static $messages = ["§3Voteaza pentru §5ILOVEMCPE §3la §abit.do/ilVOTE",
        "§3Do you need help? Ask us on the twitter: §atwitter.com/imcpe_MCPE",
        "§3You can buy VIP rank at §abit.do/mtBUY", "§3Want to play with friends? Join the same IP and port",
        "§3See server status at §astatus.imcpe.cz", "§3Register at our forums and get 3000 Tokens! §abit.do/mtFORUMS"
    ];
	
    private $i = 0;
    private $plugin;

    public function __construct(MTCore $plugin){
        $this->plugin = $plugin;
    }

    public function onRun($currentTick){
        if($this->i >= \count(self::$messages)){
            $this->i = 0;
        }
        foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
            $p->sendMessage(MTCore::getPrefix().self::$messages[$this->i]);
        }
        $this->plugin->getServer()->getLogger()->info(MTCore::getPrefix().self::$messages[$this->i]);
        $this->i++;
    }
}