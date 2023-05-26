<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 30/09/2016
 * Time: 21:50
 */

namespace SliceKits;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\LargeExplodeParticle;
use pocketmine\level\particle\SplashParticle;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\ZombieInfectSound;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\Server;
use pocketmine\utils\Config;

use pocketmine\Player;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\utils\Utils;
use SliceKits\Task\Damager\Damager;
use SliceKits\Task\Explosion\C4ItemTask;
use SliceKits\Task\Explosion\C4Task;
use SliceKits\Task\Ghoul\GhoulTask;
use SliceKits\Task\Localizer\Localizer;
use SliceKits\Task\Osama\Osama;
use SliceKits\Task\Sounds;
use SliceKits\Task\Stomper\StomperHitTask;
use SliceKits\Task\Stomper\StomperItemTask;
use SliceKits\Task\Stomper\StomperTask;

use pocketmine\entity\Effect;
use pocketmine\item\Item;

use pocketmine\level\Position;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;

use pocketmine\event\inventory\InventoryPickupItemEvent;

use pocketmine\event\player\PlayerUseFishingRodEvent;
use pocketmine\event\player\PlayerFishEvent;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\protocol\UseItemPacket;

use pocketmine\level\Explosion;
use pocketmine\utils\Random;

use pocketmine\scheduler\CallbackTask;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;

use pocketmine\entity\Living;
use pocketmine\entity\Entity;


/**
 * @property  interface
 */
class Loader extends PluginBase implements Listener
{
    private $interface;

    /**
     *
     */
    public function onEnable()
    {

        @mkdir($this->getDataFolder());
        $this->configuration = new Config($this->getDataFolder()."kit.yml", Config::YAML, Array());
        $this->started = new Config($this->getDataFolder()."started.yml", Config::YAML, Array());
        $this->stats = new Config($this->getDataFolder()."stats.yml", Config::YAML, Array());
        $this->players = new Config($this->getDataFolder()."players.yml", Config::YAML, Array());

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "autoCraft")), 1);

        $this->isStartedConfig()->set("hg.started",false);
        $this->isStartedConfig()->set("pvp.off",false);
        $this->isStartedConfig()->save();

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "winGame")), 80);

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "onParticles")), 7);

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "isBussola")), 1);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "ChangeBussola")), 8*20);

        $this->kits = new Kits($this);

        $this->getLogger()->warning("§aO Sistema do HG está ligado!");
    }

    public function onParticles(){
        foreach(Server::getInstance()->getOnlinePlayers() as $players){
            if($this->isStartedConfig()->get("hg.started") === false){
                $player = $players->getPlayer();
                $level = $player->getLevel();
                $level->addParticle(new HappyVillagerParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new CriticalParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new SplashParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new HappyVillagerParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));
                $level->addParticle(new CriticalParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));
                $level->addParticle(new SplashParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));

                $level->addParticle(new HappyVillagerParticle(new Vector3($player->getX(),$player->getY() + 2.5,$player->getZ())));
                $level->addParticle(new CriticalParticle(new Vector3($player->getX(),$player->getY() + 2.5,$player->getZ())));
                $level->addParticle(new SplashParticle(new Vector3($player->getX(),$player->getY() + 2.5,$player->getZ())));

                $level->addParticle(new HappyVillagerParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new CriticalParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new SplashParticle(new Vector3($player->getX() - 0.5,$player->getY() + 0.5,$player->getZ() + 0.5)));
                $level->addParticle(new HappyVillagerParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));
                $level->addParticle(new CriticalParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));
                $level->addParticle(new SplashParticle(new Vector3($player->getX() + 0.5,$player->getY() + 0.5,$player->getZ() - 0.5)));
            }
        }
    }

    public function onConfig(){
        return $this->configuration;
    }

    public function isStartedConfig(){
        return $this->started;
    }

    public function isStats(){
        return $this->stats;
    }

    /**
     *
     */
    public function isBussola(){
        //if($this->isStartedConfig()->get("hg.started") === true){
            foreach(Server::getInstance()->getOnlinePlayers() as $players){
                $player = $players->getPlayer();
                if($player->getInventory()->getItemInHand()->getId() === 345){
                    $online = $this->getServer()->getPlayer($this->isPlayers()->get("compass"));
                    if($this->isPlayers()->get("compass")){
                        if($online->isOnline()){
                            $player->sendPopup("§bCoordenadas de ".$online->getName()." : §a".(int)$online->getX()." ".(int)$online->getY(). " ".(int)$online->getZ());
                            $player->sendTip("§bBussola apontando para §b".$online->getName()."\n§aSuas Coordenadas : ".(int)$player->getX()." ".(int)$player->getY()." ".(int)$player->getZ());
                        } else {
                            $player->sendTip("§bEstamos Detectando o Player!");
                        }
                    } else {

                    }
                }
            }
        //}
    }

    public function ChangeBussola(){
        $nicker = "nicker @r";
        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $nicker);
    }

    public function winGame(){
        foreach(Server::getInstance()->getOnlinePlayers() as $players){
            if($this->isStartedConfig()->get("hg.started") === true){
                if(count($this->getServer()->getOnlinePlayers()) === 1){
                    $players->getPlayer()->close("","§a".$players->getPlayer()->getName()." §cVocê venceu o HardcoreGames 2.0!");
                    Server::getInstance()->shutdown();
                }
                if(count($this->getServer()->getOnlinePlayers()) === 0){
                    Server::getInstance()->shutdown();
                }
            }
        }
    }

    public function autoCraft(){
        foreach(Server::getInstance()->getOnlinePlayers() as $players){
            if($this->isStartedConfig()->get("hg.started") === true){
                $player = $players->getPlayer();
                    $player->getInventory()->removeItem(Item::get(40,0,1));
                    $player->getInventory()->removeItem(Item::get(39,0,1));
            }
        }
    }

    /**
     * @param $kit
     */
    public function onKiter(){
        $p = new Kits($this);
        foreach(Server::getInstance()->getOnlinePlayers() as $players){
            $player = $players->getPlayer();
            $kit = $this->onConfig()->get($player->getName());
            switch($kit){
                case "pvp":
                    $p->isPVP($player);
                    break;
                case "fireman":
                    $p->isFireman($player);
                    break;
                case "kangaruu":
                    $p->isKangaruu($player);
                    break;
                case "stomper":
                    $p->isStomper($player);
                    break;
                case "pyromancer":
                    $p->isPyromancer($player);
                    break;
                case "miner":
                    $p->isMiner($player);
                    break;
                case "tank":
                    $p->isTank($player);
                    break;
                case "viper":
                    $p->isViper($player);
                    break;
                case "explosion":
                    $p->isExplosion($player);
                    break;
                case "ghoul":
                    $p->isGhoul($player);
                    break;
                case "snail":
                    $p->isSnail($player);
                    break;
                case "localizer":
                    $p->isLocalizer($player);
                    break;
                case "damager":
                    $p->isDamager($player);
                    break;
                case "trapper":
                    $p->isTrapper($player);
                    break;
                case "osama":
                    $p->isOsama($player);
                    break;
            }
            $p->isNone($player);
            $player->setHealth(20);
            $rand = rand(1,2);
            switch($rand){
                case 1:
                    $sound = "fall";
                    $move = new Sounds($this, $player, $sound);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    break;
                case 2:
                    $sound = "block";
                    $move = new Sounds($this, $player, $sound);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($move, 1);
                    break;
            }
        }
    }

    /**
     *
     */
    public function onDisable()
    {
        unlink($this->getDataFolder()."kit.yml");
        unlink($this->getDataFolder()."started.yml");
        unlink($this->getDataFolder()."stats.yml");
        unlink($this->getDataFolder()."players.yml");
        unset($this->kits);
        unset($this->started);
        unset($this->stats);
        unset($this->players);

        $this->getLogger()->warning("§aO Sistema do HG está Desligado!");
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args)
    {
        switch($command->getName()){
            case "kit":
                if($sender instanceof Player){
                    if($this->isStartedConfig()->get("hg.started") === false){
                        if(isset($args[0])){
                            switch(strtolower($args[0])){
                                case "pvp":
                                    $this->onConfig()->set($sender->getName(),"pvp");
                                    $this->onConfig()->save();
                                    $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT PVP");
                                    $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o KIT!");
                                    break;
                                case "stomper":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"stomper");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT STOMPER");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o STOMPER!");
                                    }
                                    break;
                                case "viper":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"viper");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT VIPER");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o VIPER!");
                                    }
                                    break;
                                case "fireman":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"fireman");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT FIREMAN");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o FIREMAN!");
                                    }
                                    break;
                                case "tank":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"tank");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT TANK");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o TANK!");
                                    }
                                    break;
                                case "explosion":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"explosion");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT EXPLOSION");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o EXPLOSION!");
                                    }
                                    break;
                                case "ghoul":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"ghoul");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT GHOUL");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o GHOUL!");
                                    }
                                    break;
                                case "snail":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"snail");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT SNAIL");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o SNAIL!");
                                    }
                                    break;
                                case "localizer":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"localizer");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT LOCALIZER");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o LOCALIZER!");
                                    }
                                    break;
                                case "damager":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"damager");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT DAMAGER");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o DAMAGER!");
                                    }
                                    break;
                                case "trapper":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"trapper");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT TRAPPER");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o TRAPPER!");
                                    }
                                    break;
                                case "kangaruu":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"kangaruu");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT KANGARUU");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o KANGARUU!");
                                    }
                                    break;
                                case "osama":
                                    if(!$sender->hasPermission("vip")){
                                        $sender->sendMessage("§a[ §6SliceHG §a] §cVocê não pode pegar este KIT!");
                                    } else {
                                        $this->onConfig()->set($sender->getName(),"osama");
                                        $this->onConfig()->save();
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bVocê selecionou o §6KIT OSAMA");
                                        $sender->sendMessage("§a[ §6SliceHG §a] §bEspere o HG iniciar para receber o OSAMA!");
                                    }
                                    break;
                            }
                        } else {
                            $sender->sendMessage("§a------");
                            $sender->sendMessage("§aFree: §bPVP | Protection | Vulnerability");
                            $sender->sendMessage("§aVIPS: §bKangaruu | Stomper | Osama | Explosion | Tank");
                            $sender->sendMessage("§a-> §bFireman | Viper | Snail | Ghoul | Localizer | Damager | Trapper");
                            $sender->sendMessage("§a-> §bCompre VIP em : §6hospedagem.slicebrasil.xyz");
                            $sender->sendMessage("§a------");
                        }
                    }
                }
                break;
            case "hgstart":
                if($sender instanceof ConsoleCommandSender){
                    if($this->isStartedConfig()->get("hg.started") === true){
                        $sender->sendMessage("§a[ §6SliceHG §a] §bO HG ja foi iniciado...");
                        return;
                    }
                    if(count($this->getServer()->getOnlinePlayers()) === 1){
                        Server::getInstance()->broadcastMessage("§a[ §6SliceHG §a] §cPlayers insuficientes :(");
                        return;
                    }
                    foreach($this->getServer()->getOnlinePlayers() as $players){

                        $rand = rand(1,2);
                        switch($rand) {
                            case 1:
                                $sound = "fall";
                                $move = new Sounds($this, $players, $sound);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 121 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 124 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 128 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 132 * 20);
                                break;
                            case 2:
                                $sound = "block";
                                $move = new Sounds($this, $players, $sound);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 121 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 124 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 128 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 132 * 20);
                                break;
                        }
                        $pvp = "pvp";
                        $pvpon = new Sounds($this, $players, $pvp);
                        $this->getServer()->getScheduler()->scheduleDelayedTask($pvpon, 120 * 20);
                    }
                    $this->isStartedConfig()->set("hg.started",true);
                    $this->isStartedConfig()->save();
                    $this->getServer()->broadcastMessage("§a[ §6SliceHG §a] §bO HG iniciou");

                    $borda = "wp border 500";
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $borda);

                    $this->onKiter();
                }
                break;
            case "forcestart":
                if($sender instanceof ConsoleCommandSender){
                    if($this->isStartedConfig()->get("hg.started") === true){
                        $sender->sendMessage("§a[ §6SliceHG §a] §bO HG ja foi iniciado...");
                        return;
                    }
                    foreach($this->getServer()->getOnlinePlayers() as $players){

                        $rand = rand(1,2);
                        switch($rand) {
                            case 1:
                                $sound = "fall";
                                $move = new Sounds($this, $players, $sound);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 121 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 124 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 128 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 132 * 20);
                                break;
                            case 2:
                                $sound = "block";
                                $move = new Sounds($this, $players, $sound);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 121 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 124 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 128 * 20);
                                $this->getServer()->getScheduler()->scheduleDelayedTask($move, 132 * 20);
                                break;
                        }
                        $pvp = "pvp";
                        $pvpon = new Sounds($this, $players, $pvp);
                        $this->getServer()->getScheduler()->scheduleDelayedTask($pvpon, 120 * 20);
                    }
                    $this->isStartedConfig()->set("hg.started",true);
                    $this->isStartedConfig()->save();
                    $this->getServer()->broadcastMessage("§a[ §6SliceHG §a] §bO HG iniciou");

                    $pvpon = "after 120 wp pvp on";
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $pvpon);
                    $borda = "wp border 500";
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $borda);


                    $this->onKiter();
                }
                break;
            case "nicker":
                if(isset($args[0])){
                    if($this->getServer()->getPlayer($args[0])->isOnline()){
                        $this->isPlayers()->set("compass",$args[0]);
                        $this->isPlayers()->save();
                    } else {
                        return true;
                    }
                }
                break;
            case "stats":
                if($sender instanceof Player){
                    if($this->isStats()->get($sender->getName()) === "0"){
                        $sender->sendMessage("§2--------");
                        $sender->sendMessage("§aKills §6: §a0");
                        $sender->sendMessage("§2--------");
                    } else {
                        $sender->sendMessage("§2--------");
                        $sender->sendMessage("§aKills §6: §a".$this->isStats()->get($sender->getName()));
                        $sender->sendMessage("§2--------");
                    }
                }
                break;
        }
    }

    public function onPlayerDeathEvent(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        if ($player instanceof Player)
        {
            $cause = $player->getLastDamageCause();
            if($cause instanceof EntityDamageByEntityEvent)
            {
                $damager = $cause->getDamager();
                if($damager instanceof Player)
                {
                    $this->isStats()->set($damager->getName(), $this->isStats()->get($damager->getName()) + 1);
                    $this->isStats()->save(); // Important!
                }
            }
        }
    }

    public function onBreakStarted(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $x = $event->getBlock()->getX();
        $y = $event->getBlock()->getY();
        $z = $event->getBlock()->getZ();
        if($this->isStartedConfig()->get("hg.started") === false){
            $event->setCancelled(true);
            $player->sendTip("§a[ §6SliceHG §a] §bO HG NÃO iniciou ainda!");
            return;
        }
        if($event->getBlock()->getId() == 39){
            $event->setCancelled(true);
            $event->getPlayer()->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(282));
            $player->getLevel()->setBlock($block, new Block(Block::AIR));
        }
        if($event->getBlock()->getId() == 40){
            $event->setCancelled(true);
            $event->getPlayer()->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(282));
            $player->getLevel()->setBlock($block, new Block(Block::AIR));
        }
    }

    public function onJoin(PlayerPreLoginEvent $event){
        if($this->isStartedConfig()->get("hg.started") === true){
            if(!$event->getPlayer()->hasPermission("staff")){
                $event->getPlayer()->close("","§aO HardcoreGames ja está em Andamento!");
            } else {
                $this->isStats()->set($event->getPlayer()->getName(),0);
                $this->isStats()->save();
            }
        } else {
            $this->isStats()->set($event->getPlayer()->getName(),0);
            $this->isStats()->save();
        }
    }

    public function onDeati(PlayerDeathEvent $event){
        $event->getPlayer()->close("","§aVocê morreu :( Espere o HG acabar");
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onHardcoreGamesJoin(PlayerJoinEvent $event){
        $event->getPlayer()->getInventory()->clearAll();
        $player = $event->getPlayer();
        if(Utils::getOS() == "Win" or Utils::getOS() == "win" or Utils::getOS() == "Msys"){
            $player->setNameTag("§bWin10 ".$player->getName());
            $player->setDisplayName("§bWin10 ".$player->getName());
        } elseif(Utils::getOS() == "android"){
            $player->setNameTag("§bMCPE ".$player->getName());
            $player->setDisplayName("§bMCPE ".$player->getName());
        }
        if(count($this->getServer()->getOnlinePlayers()) >= 2){
            $iniciar = "after 120 hgstart";
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $iniciar);
            Server::getInstance()->broadcastMessage("§a-> §bHG irá iniciar em 2 minutos");
        }
        /** @var PlayerJoinEvent $this */
    }

    public function isPlayers(){
        return $this->players;
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onPlaceStarted(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        if($this->isStartedConfig()->get("hg.started") === false){
            $event->setCancelled(true);
            $player->sendTip("§a[ §6SliceHG §a] §bO HG NÃO iniciou ainda!");
            return;
        }
            if($this->onConfig()->get($event->getPlayer()->getName()) === "stomper"){
                if($event->getBlock()->getId() === 120){
                    if($this->isStartedConfig()->get("hg.started") === false){
                        $event->setCancelled(true);
                        $player->sendTip("§a[ §6SliceHG §a] §bO HG NÃO iniciou ainda!");
                        return;
                    }
                    $event->setCancelled(true);
                    $taskcountdown = new StomperItemTask($this, $event->getPlayer());
                    $taskstomper = new StomperTask($this, $event->getPlayer());
                    $taskstomperhit = new StomperHitTask($this, $event->getPlayer());

                    $player->addEffect(Effect::getEffect(11)->setAmplifier(40)->setDuration(160)->setVisible(false));

                    $this->getServer()->getScheduler()->scheduleDelayedTask($taskstomper, 10);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($taskstomperhit, 100);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($taskcountdown, 600);

                    $player->sendMessage("§a-> §cSeu kit está em cooldown de 30s");

                    $player->getInventory()->removeItem(Item::get(120, 0, 64));
                }
            } else {
                //$event->setCancelled(true);
                //$player->getInventory()->removeItem(Item::get(120, 0, 64));
            }
            if($this->onConfig()->get($event->getPlayer()->getName()) === "explosion"){
                if($event->getBlock()->getId() == 113){
                    if($this->isStartedConfig()->get("hg.started") === false){
                        $event->setCancelled(true);
                        $player->sendTip("§a[ §6SliceHG §a] §bO HG NÃO iniciou ainda!");
                        return;
                    }
                    $event->setCancelled(true);
                    $task = new C4Task($this, $event->getPlayer());
                    $taskc4countdown = new C4ItemTask($this, $event->getPlayer());

                    $player->addEffect(Effect::getEffect(11)->setAmplifier(10)->setDuration(100)->setVisible(false));

                    $this->getServer()->getScheduler()->scheduleDelayedTask($task, 80);
                    $this->getServer()->getScheduler()->scheduleDelayedTask($taskc4countdown, 900);

                    $player->addEffect(Effect::getEffect(11)->setAmplifier(40)->setDuration(160)->setVisible(false));

                    $player->getInventory()->removeItem(Item::get(113, 0, 64));
                    $player->sendMessage("§a-> §cSeu kit está em cooldown de 45s");

                }
            } else {
                //$event->setCancelled(true);
                //$player->getInventory()->removeItem(Item::get(113, 0, 64));
            }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onUse(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if($this->isStartedConfig()->get("hg.started") === false){
            $event->setCancelled(true);
            $player->sendTip("§a[ §6SliceHG §a] §bO HG NÃO iniciou ainda!");
            return;
        }
        if(count($player->getEffects()) != 3) {
            if($event->getItem()->getID() == 282) {
                $player->setFood(20);
                $player->setHealth($player->getHealth()+3.5*2);
                //$player->sendPopup("§b§lSopa Tomada!");
                $player->getInventory()->removeItem(Item::get(282, 0, 1));
                $player->getInventory()->addItem(Item::get(281, 0, 1));
            }
        }
        if($this->onConfig()->get($player->getName()) === "ghoul"){
            if($event->getItem()->getId() === 405){
                $player->getInventory()->removeItem(Item::get(405, 0, 1));
                $ghoul = new GhoulTask($this, $event->getPlayer());
                $this->getServer()->getScheduler()->scheduleDelayedTask($ghoul, 900);
                $player->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(3)->setDuration(7*20)->setVisible(false));
                $player->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(1)->setDuration(7*20)->setVisible(false));
                $player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(1)->setDuration(7*20)->setVisible(false));
                $player->sendMessage("§a[ §6SliceHG §a] §cCooldown : 45 segundos");
            }
        } else {
            $player->getInventory()->removeItem(Item::get(405, 0, 1));
        }
        if($this->onConfig()->get($player->getName()) === "localizer"){
            if($event->getItem()->getId() === 289){
                $player->getInventory()->removeItem(Item::get(289, 0, 1));
                $localizer = new Localizer($this, $event->getPlayer());
                $this->getServer()->getScheduler()->scheduleDelayedTask($localizer, 30*20);
                foreach(Server::getInstance()->getOnlinePlayers() as $players){
                    $level = $players->getLevel();
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 2.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 3.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 4.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 5.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 6.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 7.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 8.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 9.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 10.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 11.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 12.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 13.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 14.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 15.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 16.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 17.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 18.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 19.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 20.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 21.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 22.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 23.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 2.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 3.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 4.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 5.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 6.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 7.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 8.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 9.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 10.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 11.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 12.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 13.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 14.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 15.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 16.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 17.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 18.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 19.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 20.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 21.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 22.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 23.5,$players->getZ())));
                }
                $player->sendMessage("§a[ §6SliceHG §a] §cCooldown : 30 segundos");
            }
        } else {
            $player->getInventory()->removeItem(Item::get(289, 0, 1));
        }
        if($this->onConfig()->get($player->getName()) === "damager"){
            if($event->getItem()->getId() === 372){
                $player->getInventory()->removeItem(Item::get(372, 0, 1));
                $damager = new Damager($this, $event->getPlayer());
                $this->getServer()->getScheduler()->scheduleDelayedTask($damager, 50*20);
                foreach(Server::getInstance()->getOnlinePlayers() as $players){
                    $level = $players->getLevel();
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 2.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 3.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 4.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 5.5,$players->getZ())));
                    $level->addParticle(new LargeExplodeParticle(new Vector3($players->getX(),$players->getY() + 6.5,$players->getZ())));
                    $players->setHealth($players->getHealth() - 8);
                    $level->addSound(new GhastSound($players));
                    $level->addSound(new GhastSound($players));
                    $level->addSound(new GhastSound($players));
                    $level->addSound(new GhastSound($players));
                    $level->addSound(new BlazeShootSound($players));
                    $level->addSound(new BlazeShootSound($players));
                    $level->addSound(new BlazeShootSound($players));
                    $level->addSound(new BlazeShootSound($players));
                    $level->addSound(new ZombieInfectSound($players));
                    $player->setHealth($player->getHealth() + 8);
                }
                $player->sendMessage("§a[ §6SliceHG §a] §cCooldown : 50 segundos");
            }
        } else {
            $player->getInventory()->removeItem(Item::get(289, 0, 1));
        }
        if($this->onConfig()->get($player->getName()) === "kangaruu") {
            if ($event->getItem()->getId() === 288) {
                $player = $event->getPlayer();
                $strength = 1;
                if($player->getLevel()->getBlock($player->floor()->subtract(0,1))->getId() === Block::AIR){
                    $event->setCancelled(true);
                    $event->getPlayer()->sendTip("§cVocê está no AR!!!!!");
                    return;
                }
                switch($player->getDirection()) {
                    case 0:
                        $player->knockBack($player, 0, 1, 0, $strength);
                        return true;
                    case 1:
                        $player->knockBack($player, 0, 0, 1, $strength);
                        return true;
                    case 2:
                        $player->knockBack($player, 0, -1, 0, $strength);
                        return true;
                    case 3:
                        $player->knockBack($player, 0, 0, -1, $strength);
                        return true;
                }
                $event->getPlayer()->sendTip("§aVocê usou o Kangaruu");
            }
        } else {
            $player->getInventory()->removeItem(Item::get(288, 0, 1));
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function EntityDamageEvent(EntityDamageEvent $event){
        if($this->isStartedConfig()->get("pvp.off") === false){
            $event->setCancelled(true);
            return;
        }
        if($event->getEntity() instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION or EntityDamageEvent::CAUSE_ENTITY_EXPLOSION){
            if($event->getEntity()->isSneaking()){
                $event->setCancelled(true);
            }
            return;
        }
        if($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            $entity = $event->getEntity();
                if($damager instanceof Player){
                    if($this->onConfig()->get($event->getDamager()->getName()) === "viper"){
                        $random = new Random();
                        switch($random->nextRange(1,4)){
                            case 1:
                                $event->getEntity()->addEffect(Effect::getEffect(19)->setAmplifier(0)->setDuration(80)->setVisible(true));
                                break;
                            case 2:
                                break;
                            case 3:
                                break;
                            case 4:
                                break;
                        }
                    } else {
                        $damager->getInventory()->removeItem(Item::get(376, 0, 1));
                    }
                    if($damager->getInventory()->getItemInHand()->getId() === 372){
                        if($this->onConfig()->get($event->getDamager()->getName()) === "snail"){
                            $event->getEntity()->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(0)->setDuration(10*20)->setVisible(true));
                        } else {
                            $damager->getInventory()->removeItem(Item::get(372, 0, 1));
                        }
                    }
                    if($damager->getInventory()->getItemInHand()->getId() === 377){
                        if($this->onConfig()->get($event->getDamager()->getName()) === "fireman"){
                            $event->getEntity()->setOnFire(6);
                        } else {
                            $damager->getInventory()->removeItem(Item::get(376, 0, 1));
                        }
                    }
                    if($damager->getInventory()->getItemInHand()->getId() === 336){
                        if($this->onConfig()->get($event->getDamager()->getName()) === "tank"){
                            $entity = $event->getEntity();
                            $rand = new Random();
                            switch($rand->nextRange(1, 7)){
                                case 1:
                                    $explosion = new Explosion(new Position($entity->x, ($entity->y -1), $entity->z, $entity->getLevel()), 1);
                                    $explosion->explodeB();
                                    break;
                                case 2:
                                    break;
                                case 3:
                                    break;
                                case 4:
                                    break;
                                case 5:
                                    break;
                                case 6:
                                    break;
                                case 7:
                                    break;
                            }
                        } else {
                            $damager->getInventory()->removeItem(Item::get(376, 0, 1));
                        }
                    }
                    if($this->onConfig()->get($event->getDamager()->getName()) === "trapper"){
                        if($entity instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_PROJECTILE){
                            $entity->teleport(new Vector3($damager->getX(), $damager->getY() + 0.1, $damager->getZ()));
                            $entity->sendMessage("§aVocê foi puxado pelo kit TRAPPER de ".$damager->getName());
                            $damager->sendMessage("§aVocê puxou ".$entity->getName());
                            $event->setDamage(1);
                        }
                    } else {
                        $damager->getInventory()->removeItem(Item::get(344, 0, 1));
                    }
                    if($this->onConfig()->get($event->getDamager()->getName()) === "kangaruu") {
                        if($damager instanceof Player && $event->getCause() === EntityDamageEvent::CAUSE_FALL){
                            $event->setCancelled(true);
                            $damager->setHealth($damager->getHealth() - 5);
                        }
                    }
                }
            }
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
                $command = strtolower($event->getMessage());
                if($command{0} == "/"){
                    $command = explode(" ", $command);
                    if($this->isStartedConfig()->get("hg.started") === true){
                        if($command[0] == "/kit") {
                            $event->getPlayer()->sendMessage("§2-------------");
                            $event->getPlayer()->sendMessage("§a[ §6SliceHG §a] §bO HG já iniciou");
                            $event->getPlayer()->sendMessage("§a[ §6SliceHG §a] §bEntão os Kits estão BLOQUEADOS!");
                            $event->getPlayer()->sendMessage("§2-------------");
                            $event->setCancelled(true);
                        }
                    } else {
                        $event->setCancelled(false);
                    }
                }
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    /*public function onPacketReceived(DataPacketReceiveEvent $event)
    {
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if($this->onConfig()->get($player->getName()) === "osama") {
            if ($pk instanceof UseItemPacket and $pk->face === 0xff) {
                $item = $player->getInventory()->getItemInHand();
                if ($item->getId() == 369) {
                    foreach ($player->getInventory()->getContents() as $item) {
                                $nbt = new CompoundTag ("", [
                                    "Pos" => new ListTag ("Pos", [
                                        new DoubleTag ("", $player->x),
                                        new DoubleTag ("", $player->y + $player->getEyeHeight()),
                                        new DoubleTag ("", $player->z)
                                    ]),
                                    "Motion" => new ListTag ("Motion", [
                                        new DoubleTag ("", -\sin($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)),
                                        new DoubleTag ("", -\sin($player->pitch / 180 * M_PI)),
                                        new DoubleTag ("", \cos($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI))
                                    ]),
                                    "Rotation" => new ListTag ("Rotation", [
                                        new FloatTag ("", $player->yaw),
                                        new FloatTag ("", $player->pitch)
                                    ])
                                ]);

                                $f = 2.1;
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball = Entity::createEntity("PrimedTNT", $player->chunk, $nbt);
                                $snowball->setMotion($snowball->getMotion()->multiply($f));
                                $snowball->spawnToAll();
                                $player->getLevel()->addSound(new BlazeShootSound($player), [$player]);
                                $player->getInventory()->removeItem(Item::get(369, 0, 1));
                                $player->sendMessage("§a[ §6SliceHG §a] §cCooldown : 45 segundos");
                                $damager = new Osama($this, $event->getPlayer());
                                $this->getServer()->getScheduler()->scheduleDelayedTask($damager, 45*20);
                            }
                    }
                }
        }
    }*/

    public function onPrime(ExplosionPrimeEvent $event){
        $event->setBlockBreaking(false);
        $event->setForce(5);
    }

}