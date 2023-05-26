<?php
namespace ARCore\Auth;

use ARCore\Auth\Tasks\TimeoutTask;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EventListener implements Listener {
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function onBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        if($entity instanceof Player && !$this->plugin->isAuthenticated($entity)) {
            $event->setCancelled();
        }
        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if($damager instanceof Player && !$this->plugin->isAuthenticated($damager)) {
                $event->setCancelled();
            }
        }
    }

    public function onPickupArrow(InventoryPickupArrowEvent $event) {
        $player = $event->getInventory()->getHolder();
        if($player instanceof Player && !$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onPickupItem(InventoryPickupItemEvent $event) {
        $player = $event->getInventory()->getHolder();
        if($player instanceof Player && !$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $recipients = array();
        if(!$this->plugin->isAuthenticated($player)) {
            if($this->plugin->auth->get("chat-login")) {
                if($this->plugin->isRegistered($player->getName())) {
                    $this->plugin->login($player, $message);
                } else {
                    if(!isset($this->plugin->confirmPassword[strtolower($player->getName())])) {
                        $this->plugin->confirmPassword[strtolower($player->getName())] = $message;
                        $player->sendMessage($this->plugin->auth->get("confirm-password"));
                    } else {
                        if($this->plugin->confirmPassword[strtolower($player->getName())] == $message) {
                            $this->plugin->register($player, $message, $message);
                            unset($this->plugin->confirmPassword[strtolower($player->getName())]);
                        } else {
                            $player->sendMessage($this->plugin->auth->get("password-not-match"));
                            unset($this->plugin->confirmPassword[strtolower($player->getName())]);
                        }
                    }
                }
            }
            $event->setCancelled();
        } else {
            if($this->plugin->isCorrectPassword($player, $message)) {
                $player->sendMessage($this->plugin->auth->get("dont-say-password"));
                $event->setCancelled();
            }
        }
        if(!$this->plugin->auth->get("see-messages")) {
            foreach($event->getRecipients() as $recipient) {
                if(!$recipient instanceof Player || $this->plugin->isAuthenticated($recipient)) {
                    array_push($recipients, $recipient);
                }
            }
            $event->setRecipients($recipients);
        }
    }

    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $message = strtolower($event->getMessage());
        $args = explode(" ", $message);
        $forgotpasswordaliases = array(
            "/forgotpassword",
            "/forgetpassword",
            "/forgotpw",
            "/forgetpw",
            "/forgotpwd",
            "/forgetpwd",
            "/fpw",
            "/fpwd",
            "/forgot");
        if(!$this->plugin->isAuthenticated($player)) {
            if($message[0] == "/") {
                if(!in_array($args[0], $forgotpasswordaliases) && $args[0] !== "/log" && $args[0] !== "/reg") {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onExhaust(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onConsume(PlayerItemConsumeEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            $event->setCancelled();
        }
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $player->sendMessage($this->plugin->auth->get("join-message"));
        $this->plugin->messagetick[strtolower($player->getName())] = 0;
        if($this->plugin->isRegistered($player->getName())) {
            $player->sendMessage($this->plugin->auth->get("login"));
        } else {
            $player->sendMessage($this->plugin->auth->get("register"));
        }
        if($this->plugin->auth->get("invisible")) {
            $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
            $player->setDataProperty(Entity::DATA_SHOW_NAMETAG, Entity::DATA_TYPE_BYTE, 0);
        }
        if($this->plugin->auth->get("blindness")) {
            $effect = Effect::getEffect(15);
            $effect->setAmplifier(99);
            $effect->setDuration(999999);
            $effect->setVisible(false);
            $player->addEffect($effect);
            $effect = Effect::getEffect(16);
            $effect->setAmplifier(99);
            $effect->setDuration(999999);
            $effect->setVisible(false);
            $player->addEffect($effect);
        }
        if($this->plugin->auth->get("auto-authentication")) {
            $data = $this->plugin->getPlayer($player->getName());
            if(!is_null($data)) {
                if($player->getUniqueId()->toString() == $data["uuid"]) {
                    $this->plugin->force($player);
                    return true;
                }
            }
        }
        $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new TimeoutTask($this->plugin, $player), $this->plugin->auth->get("timeout") * 20);
    }

    public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        if(!$this->plugin->isAuthenticated($player)) {
            if(!$this->plugin->auth->get("allow-movement")) {
                $event->setCancelled();
            }
        }
    }

    public function onPrelogin(PlayerPreLoginEvent $event) {
        $player = $event->getPlayer();
        if($this->plugin->auth->get("single-session")) {
            if(!is_null($p = $this->plugin->getServer()->getPlayerExact($player->getName())) && $this->plugin->isAuthenticated($p)) {
                $player->close("", "Already logged in!");
                $event->setCancelled();
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        $this->plugin->logout($player);
    }

}
