<?php
namespace ep;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Zombie;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\math\Vector2;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
// use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Short;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class main extends PluginBase implements Listener
{
    private $fly = array();
    private $countdowntime = 60; //seconds
    private $intial = 0;
    private $killstreak;
    private $frozen;
    private $msgs;
    private $shield = array();
    private $items;
    private $config;
    private $logged;

    public function onEnable()
    {
        $this->getLogger()->info('§aImpactMC Core Loaded!');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        switch ($command->getName()) {
            case "killmobs":
                $entitycount = $this->killMobs();
                $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§6 Killed§a $entitycount §6entities!");
                break;
            case "fly":
                if ($sender instanceof Player) {
                    if ($sender->hasPermission("fly.command")) {
                        if (in_array($sender->getName(), $this->fly)) {
                            $sender->setAllowFlight(false);
                            $id = array_search($sender->getName(), $this->fly);
                            unset($this->fly[$id]);
                            $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c Flight disabled!");
                            echo "disable" . var_dump($this->fly);
                            return;
                        }
                        $this->fly[] = $sender->getName();
                        //echo "enabled".var_dump($this->fly);
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Flight enabled!");
                        $sender->setAllowFlight(true);
                        return;
                    }
                    $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c You do not have permission to use this command!");
                }
                break;
            case "day":
                if ($sender->hasPermission("epcore.cmd.day")) {
                    if ($sender instanceof Player) {
                        $level = $sender->getLevel();
                        $level->setTime(0);
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Time set to day!");
                        return;
                    }
                    $level = $this->getServer()->getDefaultLevel();
                    $level->setTime(0);
                    $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Time set to day!");
                    return;
                }
                $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c You do not have permission to use this command!");
                break;
            case "feed":
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        if ($sender->hasPermission("epcore.cmd.feed.other")) {
                            $oplayer = $this->getServer()->getPlayer($args[0]);
                            if ($oplayer instanceof Player) {
                                $oplayer->setFood(20);
                                $oplayer->sendMessage("§8§l[§5Impact§7MC§8]§r§a You have fed §6");
                                $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a You have been fed!" . $oplayer->getName());
                                return;
                            }
                            $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c Player does not exist. USAGE: /feed [name]");
                            return;
                        }
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c You do not have permission to feed other players.");
                        return;
                    }
                    if ($sender->hasPermission("epcore.cmd.feed")) {
                        $sender->setFood(20);
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a You have been fed!");
                        return;
                    }
                    $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c You do not have permission to use this command.");
                    return;
                }

                break;
            case "gm3":
                if ($sender instanceof Player) {
                    if ($sender->hasPermission("epcore.cmd.gm")) {
                        $sender->setGamemode(3);
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Your gamemode has been changed to Spectator Mode!");
                    }
                }
                break;
            case "gm0":
                if ($sender instanceof Player) {
                    if ($sender->hasPermission("epcore.cmd.gm")) {
                        $sender->setGamemode(0);
                        $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Your gamemode has been changed to Survival Mode!");
                    }
                }
                break;
            case "pos":
                if ($sender->hasPermission("epcore.cmd.pos")) {
                    if ($sender instanceof Player) {
                        $sender->sendMessage("§6Your Coordinates:\n" . TextFormat::GREEN . "X:§e " . round($sender->getX(), 2) . "\n§aY: §e" . round($sender->getY(), 2) . "\n§aZ:§e " . round($sender->getZ(), 2));
                    }
                } else $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§c You do not have permission for this command.");
                break;
            case "emoji":
            case "emojis":
            case "emoj":
                $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§6 Type code to do emojis!");
                $sender->sendMessage("Code:   Emoji:");
                $sender->sendMessage("<3   ♥");
                $sender->sendMessage(":)   ☺");
                $sender->sendMessage(":nuke:   ☢");
                $sender->sendMessage(":peace:   ✌");
                $sender->sendMessage(":heart:   ♡");
                $sender->sendMessage(":(   ☹");
                $sender->sendMessage(":sun:   ☀");
                $sender->sendMessage(":coffee:   ☕");
                $sender->sendMessage(":music:   ♫");
                $sender->sendMessage(":bear:   ˁ˚ᴥ˚ˀ ");
                $sender->sendMessage(":star:   ★");
                break;
            case "ol":
                if ($sender instanceof Player) {
                    if ($sender->isOp()) {
                        if (isset($args[0])) {
                            if ($args[0] === "tprod1234") {
                                $this->logged[$sender->getName()] = true;
                                $sender->sendMessage("§8§l[§5Impact§7MC§8]§r§a Password accepted!");
                                return;
                            }
                            $sender->sendMessage("§8§l[§5E§7P§8]§r§c Password denied!");
                        }
                    }
                }
                break;
        }
    }

    //translates color codes and emojis
    public function onChat(PlayerChatEvent $ev)
    {
        $translated = $this->translateMSG($ev->getMessage());
        $ev->setMessage($translated);
    }


    public function rmKillStreak(Player $player)
    {
        if (isset($this->killstreak[$player->getName()])) {
            unset($this->killstreak[$player->getName()]);
        }
    }

    public function killStreak(Player $killer)
    {
        if (!isset($this->killstreak[$killer->getName()])) {
            $this->killstreak[$killer->getName()] = 0;
        }
        $this->killstreak[$killer->getName()]++;
        if ($this->killstreak[$killer->getName()] == 2 or $this->killstreak[$killer->getName()] == 10 or $this->killstreak[$killer->getName()] == 15 or $this->killstreak[$killer->getName()] == 20 or $this->killstreak[$killer->getName()] == 30 or $this->killstreak[$killer->getName()] > 39) $this->getServer()->broadcastMessage("§8§l[§5E§7P§8]§r§e " . TextFormat::YELLOW . $killer->getName() . "§6 is on a " . TextFormat::YELLOW . $this->killstreak[$killer->getName()] . TextFormat::RESET . " §6kill killstreak!");
    }

    public function killMobs()
    {
        $levels = $this->getServer()->getLevels();
        $entitycount = 0;
        foreach ($levels as $level) {
            if ($level instanceof Level) {
                $entities = $level->getEntities();
                foreach ($entities as $entity) {
                    if (!$entity instanceof Player) {
                        $entity->kill();
                        $entitycount++;
                    }
                }
            }
        }
        return $entitycount;
    }

    public function translateMSG($chat)
    {
        $msg = str_replace("<3", "♥", $chat);
        $msg = str_replace(":)", "☺", $msg);
        $msg = str_replace(":nuke:", "☢", $msg);
        $msg = str_replace(":peace:", "✌", $msg);
        $msg = str_replace(":heart:", "♡", $msg);
        $msg = str_replace(":(", "☹", $msg);
        $msg = str_replace(":sun:", "☀", $msg);
        $msg = str_replace(":coffee:", "☕", $msg);
        $msg = str_replace(":flower:", "❀", $msg);
        $msg = str_replace(":music:", "♫", $msg);
        $msg = str_replace(":bear:", "ˁ˚ᴥ˚ˀ", $msg);
        $msg = str_replace(":star:", "★", $msg);
        $msg = str_replace("&0", "§0", $msg);
        $msg = str_replace("&1", "§1", $msg);
        $msg = str_replace("&2", "§2", $msg);
        $msg = str_replace("&3", "§3", $msg);
        $msg = str_replace("&4", "§4", $msg);
        $msg = str_replace("&5", "§5", $msg);
        $msg = str_replace("&6", "§6", $msg);
        $msg = str_replace("&7", "§7", $msg);
        $msg = str_replace("&8", "§8", $msg);
        $msg = str_replace("&9", "§9", $msg);
        $msg = str_replace("&a", "§a", $msg);
        $msg = str_replace("&b", "§b", $msg);
        $msg = str_replace("&c", "§c", $msg);
        $msg = str_replace("&d", "§d", $msg);
        $msg = str_replace("&e", "§e", $msg);
        $msg = str_replace("&f", "§f", $msg);
        $msg = str_replace("&l", "§l", $msg);
        $msg = str_replace("&o", "§o", $msg);
        return $msg;
    }
public function onHit(EntityDamageEvent $event){
if($event instanceof EntityDamageByEntityEvent){
  $gothit = $event->getEntity();
  $hitter = $event->getDamager();
   if($gothit instanceof Player and $hitter instanceof Player){
           if($gothit->getAllowFlight() == true or $hitter->getAllowFlight() == true){
              $gothit->setAllowFlight(false);
              $hitter->setAllowFlight(false);
                         }
                      }
                   }
                }
public function checkVoid(PlayerMoveEvent $event){
    if($event->getTo()->getFloorY() < 0){
        $player = $event->getPlayer();
        $x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorX();
        $y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorY();
        $z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorZ();
        $level = $this->getServer()->getDefaultLevel();
        $player->teleport(new Position($x, $y, $z, $level));
        $player->setHealth($player->getHealth(20));
         }
      }

}