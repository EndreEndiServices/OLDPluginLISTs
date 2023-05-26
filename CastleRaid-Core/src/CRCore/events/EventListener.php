<?php
/**
 * -==+CastleRaid Core+==-
 * Originally Created by QuiverlyRivarly
 * Originally Created for CastleRaidPE
 *
 * @authors: CastleRaid Developer Team
 */
declare(strict_types=1);
namespace CRCore\events;
use CRCore\core\Loader;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use CRCore\commands\teleport\Handle\Sessions;
use pocketmine\item\Item;
use CRCore\RandomUtils;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
class EventListener implements Listener{
    private $main;
    public function __construct(Loader $main){
        $this->main = $main;
        $main->getServer()->getPluginManager()->registerEvents($this, $main);
    }
    public function onDataPacket(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if($packet instanceof ServerSettingsRequestPacket){
            $packet = new ServerSettingsResponsePacket();
            $packet->formData = file_get_contents($this->main->getDataFolder() . "tsconfig.json");
            $packet->formId = 5928;
            $event->getPlayer()->dataPacket($packet);
        }elseif($packet instanceof ModalFormResponsePacket){
            $formId = $packet->formId;
            if($formId !== 5928){
                return;
            }
        }
    }
    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $event->setJoinMessage("§l§8(§e!§8)§r §a{$player->getName()} joined the server!");
        $player->addTitle(TextFormat::GREEN . TextFormat::BOLD . "Chrystal Factions");
        $this->main->hud[$player->getName()] = true;
        $event->getPlayer()->getServer()->getCommandMap()->dispatch($player, "gl");
        $h = round($player->getHealth()) / $player->getMaxHealth() * 100;
        $player->setNameTag($player->getDisplayName() . TextFormat::GREEN . "♥" . $h . "%");
    }
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $event->setQuitMessage("§l§8(§e!§8)§r §c{$player->getName()} left the server!");
    }
    public function onPlayerLogin(PlayerLoginEvent $event) : void{
        $event->getPlayer()->teleport($this->main->getServer()->getDefaultLevel()->getSafeSpawn());
    }
    public function onCommandPreProcess(PlayerCommandPreprocessEvent $event) : void{
        $message = $event->getMessage();
        if($message{strlen($message) - 1} === "/"){
            $event->setMessage("/" . substr($message, 0, -1));

                }
            }

    public function onDeath(PlayerDeathEvent $event){

        $player = $event->getPlayer();

            $dead = $player->getName() ;

            $cause = $player->getLastDamageCause();

            if($cause instanceof EntityDamageByEntityEvent){

                $killer = $cause->getDamager();

                $kill = $killer->getName();


                    $kill = $killer->getName();

                $event->setDeathMessage(TextFormat::RED . $dead . TextFormat::YELLOW . " §cwas §ckilled §cby  " . TextFormat::RED . $kill);

            }else{
            }

                $cause = $player->getLastDamageCause()->getCause();

                if($cause === EntityDamageEvent::CAUSE_SUFFOCATION){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " suffocated");

                }elseif($cause === EntityDamageEvent::CAUSE_DROWNING){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " drowned");

                }elseif($cause === EntityDamageEvent::CAUSE_FALL){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " fell from a high place");

                }elseif($cause === EntityDamageEvent::CAUSE_FIRE){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " burned");

                }elseif($cause === EntityDamageEvent::CAUSE_FIRE_TICK){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " burned");

                }elseif($cause === EntityDamageEvent::CAUSE_LAVA){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " tried to swim in lava");

                }elseif($cause === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION){

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " explode");

                }else{

                    $event->setDeathMessage("§l§8(§e!§8)§r " . TextFormat::RED . $dead . " died");
                }

            }

  public function onEntityDamage(EntityDamageEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player){
            $h = round($player->getHealth()) / $player->getMaxHealth() * 100;
            switch($h){
                case $h <= 100 && $h >= 80;
                    $thing = TextFormat::GREEN . "♥ " . $h . "%";
                    break;
                case $h <= 79 && $h >= 60;
                    $thing = TextFormat::DARK_GREEN . "♥ " . $h . "%";
                    break;
                case $h <= 59 && $h >= 40;
                    $thing = TextFormat::RED . "♥ " . $h . "%";
                    break;
                case $h <= 39 && $h >= 20;
                    $thing = TextFormat::RED . "♥ " . $h . "%";
                    break;
                case $h <= 19 && $h >= 0;
                    $thing = TextFormat::DARK_RED . "♥ " . $h . "%";
                    break;
                default;
                    $thing = "♥ " . $h . "%";
                    break;
            }
            $player->setNameTag($player->getDisplayName() . "\n " . $thing);
}
    }


}
