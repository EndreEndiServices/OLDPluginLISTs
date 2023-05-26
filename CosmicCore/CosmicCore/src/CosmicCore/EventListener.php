<?php
namespace CosmicCore;

use CosmicCore\CE\NaturesWrath;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\level\sound\ExpPickupSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\event\{
    Event, EventPriority, Listener
};
use pocketmine\event\entity\{
    EntityArmorChangeEvent, EntityDamageByEntityEvent, EntityDamageEvent, EntityTeleportEvent, EntityExplodeEvent, ExplosionPrimeEvent, EntitySpawnEvent, EntityRegainHealthEvent
};
use pocketmine\event\block\{
    BlockPlaceEvent, BlockBreakEvent
};
use pocketmine\event\player\{
    PlayerGameModeChangeEvent, PlayerChatEvent, PlayerCommandPreprocessEvent, PlayerDeathEvent, PlayerHungerChangeEvent, PlayerInteractEvent, PlayerItemConsumeEvent, PlayerItemHeldEvent, PlayerLoginEvent, PlayerQuitEvent, PlayerDropItemEvent
};
use pocketmine\{
    Player, Server
};
use pocketmine\block\{
    Block, Air
};
use pocketmine\entity\{
    Living, Effect
};
use pocketmine\item\{
    Armor, Item, ItemBlock, enchantment\Enchantment
};
use pocketmine\level\{
    Level, Position
};
use pocketmine\inventory\{
    PlayerInventory, ChestInventory
};
use pocketmine\utils\{
    Config, TextFormat as TF
};
use pocketmine\tile\{
    Tile, Chest, MobSpawner
};
use pocketmine\nbt\tag\{
    CompoundTag, IntTag, StringTag, ListTag
};

class EventListener implements Listener
{

    protected $spawnerData = [];
    protected $explodeChance = 2;
    protected $stackingRange = 16;
    protected $spawnStack = true;
    private $blockList = [];
    private $toExplode = [];

    public function __construct(CosmicCore $plugin)
    {
        $this->plugin = $plugin;
        $this->spawnerData = CosmicCore::parseSpawnerList($plugin->getConfig()->get("spawners", []));
        $this->explodeChance = $plugin->getConfig()->get("explode-chance", 2);
        $this->stackingRange = $plugin->getConfig()->get("stacking-range", 8);
        $this->spawnStack = $plugin->getConfig()->get("spawn-stack", true);
        $this->blockList = CosmicCore::parseBlockList($plugin->getConfig()->get("blocks", []));
        $this->ce = new CustomEnchantments($plugin);
        $this->config = $this->plugin->getConfig();
    }

    public function updateOverload(Player $p, $i)
    {
        $health = $p->getMaxHealth();
        if($health > 40) return;
        if ($i instanceof Armor && $i !== NULL) {
            if ($i->hasEnchantment(111)) {
                if ($i->isHelmet()) {
                    $p->setMaxHealth($health + 4);
                    $p->setHealth($p->getHealth());
                } elseif ($i->isChestplate()) {
                    $p->setMaxHealth($health + 8);
                    $p->setHealth($p->getHealth());
                } elseif ($i->isLeggings()) {
                    $p->setMaxHealth($health + 6);
                    $p->setHealth($p->getHealth());
                } elseif ($i->isBoots()) {
                    $p->setMaxHealth($health + 2);
                    $p->setHealth($p->getHealth());
                }
            }
        }
    }

    public function onLogin(PlayerLoginEvent $e)
    {
        $p = $e->getPlayer();
        $p->setMaxHealth(20);
        foreach ($p->getInventory()->getArmorContents() as $armor) $this->updateOverload($p, $armor);
        $this->plugin->updateHealthBar($p);
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        if (isset($this->plugin->players[$event->getEntity()->getName()])) {
            unset($this->plugin->players[$event->getEntity()->getName()]);
        }

        $player = $event->getPlayer();
        if ($player->getMaxHealth() > 20) $player->setMaxHealth(20);
        $cause = $player->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $cfg = new Config($this->plugin->getDataFolder() . "/bounties/" . strtolower($player->getName()) . ".yml", Config::YAML);
                EconomyAPI::getInstance()->addMoney($damager, $cfg->get("bounty"));
                $amount = $cfg->get("bounty");
                $dN = $damager->getName();
                $pN = $player->getName();
                if ($amount > 0) {
                    $this->plugin->getServer()->broadcastMessage(TF::DARK_GRAY . TF::BOLD . "<" . TF::AQUA . "Cosmic" . TF::WHITE . "Bounty" . TF::DARK_GRAY . "> " . TF::RESET . TF::WHITE . $dN . TF::AQUA . " assassinated " . TF::WHITE . $pN . TF::AQUA . " and earned" . TF::WHITE . " $" . $amount);
                    $this->plugin->setBounty($player->getName(), 0);
                }
            }
        }
        $event->setDeathMessage("");
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $i = $event->getItem();
        $id = $event->getBlock()->getId();
        $player = $event->getPlayer();
        $worth = $i->getDamage();
        $b = $event->getBlock();
        $blocked = array("373", "438", "261", "383");
        $limited = array("7", "46", "52", "57", "42", "22", "173", "14", "15", "16", "56", "120", "133", "154", "199");
        $banned = array("23", "54", "61", "62", "116", "117", "125", "145", "146", "154", "245", "199");
        if ($b->getId() == 54 && $b->getDamage() == 15) {
            $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
            $b->getLevel()->setBlock($b, Block::get(0));
            $block = Block::get(54);
            $b->getLevel()->setBlock(new Vector3($b->x, $b->y, $b->z), $block, true, true);
            $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::CHEST),
                new IntTag("x", floor($b->x)),
                new IntTag("y", floor($b->y)),
                new IntTag("z", floor($b->z))
            ]);
            $nbt->Items->setTagType(NBT::TAG_Compound);
            $tile = Tile::createTile("Chest", $b->getLevel()->getChunk($b->getX() >> 4, $b->getZ() >> 4), $nbt);
            $tile->getInventory()->clearAll();
            $event->setCancelled();
        }

        if ($player->isCreative()) {
            if (in_array($i->getId(), $blocked) || in_array($b->getId(), $blocked)) $event->setCancelled();
            if ($player->hasPermission("cosmicpe.limitations")) {
                if (in_array($i->getId(), $limited) || in_array($b->getId(), $limited)) $event->setCancelled();
            }
            if ($event->isCancelled()) $player->sendMessage($this->plugin->p("c", "!") . "You cannot use " . $i->getName() . " in creative mode.");
        }

        if ($i->getId() === 52 && $i->getDamage() === 101) {
            $iArray = array(10, 12, 19, 24);//17 = wither
            $mystery = $this->plugin->reward->getRandomKey($iArray);
            $ii = Item::get(52, $mystery, 1);
            $i->setCount($i->getCount() - 1);
            $player->getInventory()->setItem($player->getInventory()->getHeldItemSlot(), $ii);
            $event->setCancelled();
        }

        if ($id === 46) {
            $player->sendMessage($this->plugin->p("c", "!") . "TNT has been temporarily disabled.");
            $event->setCancelled();
        }

        if ($b->getId() === 146 && $b->getLevel()->getName() === "world" && $this->plugin->isAtWarzone($b)) {
            if (!$player->isSurvival() || $player->getAllowFlight() === true) {
                $player->sendMessage($this->plugin->p("c", "!") . TF::DARK_RED . "Envoy chests " . TF::RED . "can only be opened in survival mode and without /fly enabled");
                $event->setCancelled();
            }
            if ($player->isSurvival() && $player->getAllowFlight() === false) $this->plugin->openEnvoy($player, $event);
        }

        if ($i->getId() === 54) {
            $isSpaceChest = array("101", "102", "103");
            if (in_array($i->getId(), $isSpaceChest)) {
                if ($i->getDamage() === 101) $type = "Simple";
                if ($i->getDamage() === 102) $type = "Legendary";
                if ($i->getDamage() === 103) $type = "Elite";
                $player->sendMessage(TF::GREEN . "DEBUG: " . TF::AQUA . "Redeemed " . $type . " Cosmic Chest!");
                $this->plugin->giveSpaceChest($player, $type);
                $i->setCount($i->getCount() - 1);
                $player->getInventory()->setItem($player->getInventory()->getHeldItemSlot(), $i);
                $event->setCancelled(true);
            }
        }


        if ($i->getId() === 339 && $i->getDamage() > 0 && $i->getDamage() <= 32000) {//32000 LIMIT: MCPE can't handle bigger damage values.
            if ($i->getDamage() !== 2394) {
                $meta = $i->getDamage();
                EconomyAPI::getInstance()->addMoney($player, $i->getDamage());
                $player->sendMessage(TF::BOLD . TF::GREEN . "+ $" . $i->getDamage());
                $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
                $i->setCount($i->getCount() - 1);
                $player->getInventory()->setItem($player->getInventory()->getHeldItemSlot(), $i);
                $event->setCancelled();
            } else {
                foreach ($this->plugin->titles() as $title) {
                    $titledPaper = TF::BOLD . TF::AQUA . "Cosmic Title " . TF::RESET . TF::GRAY . "(Right Click)\n" . TF::LIGHT_PURPLE . "Title: " . TF::WHITE . $title;
                    if ($i->getCustomName() === $titledPaper) {
                        $player->sendMessage(TF::BOLD . TF::GREEN . "Unlocked Title: " . TF::RESET . TF::GOLD . $title);
                        $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
                        $this->plugin->consoleCmd("setuperm " . $player->getName() . " cosmicpe.title." . strtolower($title));
                        break;
                    }
                }
                $i->setCount($i->getCount() - 1);
                $player->getInventory()->setItem($player->getInventory()->getHeldItemSlot(), $i);
            }
        }

        if ($this->plugin->isAtWarzone($player)) {
            if ($i->getId() == 146) {
                $event->setCancelled();
            }
        }

        if ($i->getId() === 384 && $i->getDamage() > 0) {
            $i->setCount($i->getCount() - 1);
            $player->getInventory()->setItem($player->getInventory()->getHeldItemSlot(), $i);
            $player->addExperience($i->getDamage());
            $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
            $event->setCancelled();
        }

        if ($i instanceof Armor) {
            if ($b->getId() == 0) {
                $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
                if ($i->isHelmet()) {
                    if ($player->getInventory()->getHelmet() !== null) {
                        $player->getInventory()->addItem($player->getInventory()->getHelmet());// To avoid glitching.
                    }
                    $player->getInventory()->setHelmet($i);
                    $player->getInventory()->remove($i);
                } elseif ($i->isChestplate()) {
                    if ($player->getInventory()->getChestplate() !== null) {
                        $player->getInventory()->addItem($player->getInventory()->getChestplate());
                    }
                    $player->getInventory()->setChestplate($i);
                    $player->getInventory()->remove($i);
                } elseif ($i->isLeggings()) {
                    if ($player->getInventory()->getLeggings() !== null) {
                        $player->getInventory()->addItem($player->getInventory()->getChestplate());
                    }
                    $player->getInventory()->setLeggings($i);
                    $player->getInventory()->remove($i);
                } else {
                    if ($player->getInventory()->getBoots() !== null) {
                        $player->getInventory()->addItem($player->getInventory()->getBoots());
                    }
                    $player->getInventory()->setBoots($i);
                    $player->getInventory()->remove($i);
                }
            }
        }

        if (!$event->isCancelled()) {
            if ($b->getId() === Block::MONSTER_SPAWNER and $i->getId() === Item::SPAWN_EGG) $event->setCancelled(true);
        }

        if ($i->getId() === 392) {
            $b = $event->getBlock();
            if (isset($this->blockList[$b->getId()])) {
                if ($this->plugin->getData()->exists(CosmicCore::getBlockString($b))) {
                    $data = $this->plugin->getData()->get(CosmicCore::getBlockString($b));
                    $player->sendMessage(TF::LIGHT_PURPLE . "Durability of this block is: " . TF::WHITE . $data["health"] . "/4");
                } else $player->sendMessage(TF::LIGHT_PURPLE . "Durability of this block is: " . TF::WHITE . $this->blockList[$b->getId()] . "/4");
            }
        }
    }

    /**
     * @param EnityDamageEvent $event
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onHurt(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();

        if (CosmicCore::isStack($entity)) {
            if ($event->getDamage() >= $entity->getHealth()) {
                if ($entity instanceof Living) {
                    $entity->setLastDamageCause($event);
                    if (CosmicCore::removeFromStack($entity)) {
                        $event->setCancelled(true);
                        $entity->setHealth($entity->getMaxHealth());
                    }
                    CosmicCore::recalculateStackName($entity, $this->plugin->getConfig());
                }
            }
        }
        if ($entity instanceof Player) {
            if ($cause == EntityDamageEvent::CAUSE_FALL && $this->plugin->isAtWarzone($entity)) $event->setCancelled(true);
            if ($entity->getMaxHealth() > 40) $entity->setMaxHealth(20);
            if ($entity->isSurvival()) $this->plugin->updateHealthBar($entity);
        }

        if (($event instanceof \pocketmine\event\entity\EntityDamageByBlockEvent) && ($entity instanceof Player)) {
            $entity->setMaxHealth(20);
            foreach ($entity->getInventory()->getArmorContents() as $armr) $this->updateOverload($entity, $armr);
            $event->setCancelled();
        }

        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager() instanceof Player && $event->getEntity() instanceof Player) {
/*
                foreach ($event->getDamager()->getInventory()->getArmorContents() as $armor) {
                    if ($armor->hasEnchantment(125)) {
                        switch(mt_rand(1,18)) {
                            case 1:
                                $this->ce->callEnchant("NaturesWrath", $event->getDamager(),$event->getEntity(), $armor->getEnchantment(125)->getLevel(), $event);
                                break;
                        }
                    }
                }
*/
                foreach (array($event->getDamager(), $event->getEntity()) as $players) $this->plugin->setTime($players);
                if ($event->getDamager()->isCreative() && $event->getEntity()->isSurvival()) {
                    if ($event->getDamager()->hasPermission("cosmicpe.limitations")) {
                        $event->getDamager()->sendMessage($this->plugin->msg("You cannot pvp in creative!"));
                        $event->setCancelled(true);
                    }
                }
                if ($event->getEntity()->isSurvival() && $event->getEntity()->getAllowFlight() === true) {
                    $event->getEntity()->setAllowFlight(false);
                }
                if ($event->getDamager()->isCreative() || $event->getDamager()->getAllowFlight() === true || $this->plugin->essentialsPE->isGod($event->getDamager())) $event->setCancelled();

                $p = $event->getEntity();
                $pl = $event->getDamager();
                $it = $pl->getInventory()->getItemInHand();
                if (!$it->hasEnchantments()) return;

                if ($it->hasEnchantment(100)) $this->ce->callEnchant("Blindness", $pl, $p, $it->getEnchantment(100)->getLevel(), $event);
                if ($it->hasEnchantment(105)) $this->ce->callEnchant("Confusion", $pl, $p, $it->getEnchantment(105)->getLevel(), $event);
                if ($it->hasEnchantment(106)) $this->ce->callEnchant("Lightning", $pl, $p, $it->getEnchantment(106)->getLevel(), $event);
                if ($it->hasEnchantment(107)) $this->ce->callEnchant("Poison", $pl, $p, $it->getEnchantment(107)->getLevel(), $event);
                if ($it->hasEnchantment(108)) $this->ce->callEnchant("Frozen", $pl, $p, $it->getEnchantment(108)->getLevel(), $event);
                if ($it->hasEnchantment(110)) $this->ce->callEnchant("DoubleDamage", $pl, $p, $it->getEnchantment(110)->getLevel(), $event);
                if ($it->hasEnchantment(114)) $this->ce->callEnchant("Featherweight", $pl, $p, $it->getEnchantment(114)->getLevel(), $event);
                if ($it->hasEnchantment(115)) $this->ce->callEnchant("Disappearer", $pl, $p, $it->getEnchantment(115)->getLevel(), $event);
                if ($it->hasEnchantment(116)) $this->ce->callEnchant("Lifesteal", $pl, $p, $it->getEnchantment(116)->getLevel(), $event);
                if ($it->hasEnchantment(118)) $this->ce->callEnchant("Obliteration", $pl, $p, $it->getEnchantment(118)->getLevel(), $event);
                if ($it->hasEnchantment(119)) $this->ce->callEnchant("Wither", $pl, $p, $it->getEnchantment(119)->getLevel(), $event);
            }
        }
    }

    public function PlayerItemHeld(PlayerItemHeldEvent $ev)
    {
        $i = $ev->getItem();
        $p = $ev->getPlayer();

        $blocked = array("373", "438", "261", "383");
        $limited = array("7", "46", "52", "57", "42", "22", "173", "14", "15", "16", "56", "120", "133", "154", "199");
        $banned = array("23", "54", "61", "62", "116", "117", "125", "145", "146", "154", "245", "199");

        if ($p->isCreative()) {
            if (in_array($i->getId(), $blocked)) $p->getInventory()->setItem($p->getInventory()->getHeldItemSlot(), Item::get(0));
            if ($p->hasPermission("cosmicpe.limitations")) {
                if (in_array($i->getId(), $limited)) {
                    $p->sendMessage($this->plugin->p("c", "!") . "You cannot use " . $i->getName() . " in creative mode.");
                    $p->getInventory()->setItem($p->getInventory()->getHeldItemSlot(), Item::get(0));
                }
            }
        }

        $strength = Effect::getEffect(Effect::STRENGTH)->setDuration(999999999)->setVisible(false)->setAmplifier(1);
        if ($i->hasEnchantment(113)) {
            $p->addEffect($strength);
        }else{
            if ($p->hasEffect(Effect::STRENGTH) && $p->getEffect(5)->getDuration() > 3000) $p->removeEffect(Effect::STRENGTH);
        }

        foreach ($i->getEnchantments() as $en) {
            if ($en->getLevel() > 5) {
                $p->sendMessage($this->plugin->p("c", "text") . TF::RESET . TF::RED . "Removed " . $i->getName() . " due to high enchantment level(s).");
                $p->getInventory()->removeItem($i);
            }
        }

        if ($i->getId() === 52) {
            if (isset($this->spawnerData[$i->getDamage()])) {
                $d = $i->getDamage();
                if (!$i->hasCustomName()) {
                    $p->getInventory()->remove($i);
                    $n = TF::RESET . $this->spawnerData[$d]["name"] . "\n" . TF::GRAY . "Place em' carefully!";
                    $i->setCustomName($n);
                    $p->getInventory()->addItem($i);
                }
            }
        }

        if ($i->getId() === 54 && $i->getDamage() > 100) {
            if ($i->getDamage() === 101) $p->sendPopup(TF::RESET . TF::WHITE . TF::BOLD . "Simple Cosmic Chest " . TF::RESET . TF::GRAY . "(Right Click)");
            if ($i->getDamage() === 102) $p->sendPopup(TF::RESET . TF::LIGHT_PURPLE . TF::BOLD . "Legendary Cosmic Chest " . TF::RESET . TF::GRAY . "(Right Click)");
            if ($i->getDamage() === 103) $p->sendPopup(TF::RESET . TF::AQUA . TF::BOLD . "Elite Cosmic Chest " . TF::RESET . TF::GRAY . "(Right Click)");
        }
        if ($i->getId() === 339 && $i->getDamage() > 0 && $i->getDamage() <= 65500 && $i->getDamage() !== 2394) {
            $p->sendTip(TF::BOLD . TF::AQUA . "Cosmic Note " . TF::RESET . TF::GRAY . "(Right Click)");
        }
    }

    public function PlayerQuitEvent(PlayerQuitEvent $event)
    {
        $chest = $event->getPlayer()->getLevel()->getTile(new Position($event->getPlayer()->x, $event->getPlayer()->y - 4, $event->getPlayer()->z));
        if ($chest instanceof \pocketmine\block\Chest) {
            $inv = $chest->getInventory();
            $event->getPlayer()->getLevel()->setBlock(new Vector3($event->getPlayer()->x, $event->getPlayer()->y - 4, $event->getPlayer()->z), Block::get(54));
            $this->plugin->using[strtolower($event->getPlayer()->getName())] = null;
        }
        $n = strtolower($event->getPlayer()->getName());
        if (isset($this->plugin->state[$n])) unset($this->plugin->state[$n]);
        if (isset($this->plugin->players[$event->getPlayer()->getName()])) {
            $player = $event->getPlayer();
            if ((time() - $this->plugin->players[$player->getName()]) < $this->plugin->interval) {
                $player->kill();
            }
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     *
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event)
    {
        $command = explode(" ", strtolower($event->getMessage()));
        $blockedArr = array("/god", "/fly", "/gms", "/gmc");
        $player = $event->getPlayer();
        $protect = $this->plugin->getConfig()->get("pages");
        if ($this->plugin->isAtWarzone($player) && !isset($this->plugin->allowed()[strtolower($player->getName())])) {

            if ($command[0] === "/god" || $command[0] === "/gms" || $command[0] === "/gmc") {
                $player->sendMessage($this->plugin->p("c", "!") . "You cannot use " . $command[0] . " at spawn or in a warzone!");
                $event->setCancelled();
            }
            if ($command[0] === "/f") {
                if (isset($command[1]) && $command[1] === "claim") {
                    $player->sendMessage($this->plugin->p("c", "!") . "You cannot claim lands at spawn or in a warzone!");
                    $event->setCancelled();
                }
            }
        }
        if (isset($this->plugin->players[$event->getPlayer()->getName()])) {
            $cmd = strtolower(explode(' ', $event->getMessage())[0]);
            if (in_array($cmd, $blockedArr)) {
                $event->getPlayer()->sendMessage($this->plugin->p("c", "!") . "You cannot use that command while in combat!");
                $event->setCancelled();
            }
        }
        if (!file_exists($this->plugin->getDataFolder() . "help.yml")) {
            @mkdir($this->plugin->getDataFolder());
        }
        if ($command[0] === "/xp") {
            $this->plugin->sendExperienceStatistics($player);
            $event->setCancelled();
        }
        if ($command[0] === "/me") $event->setCancelled();
        if ($command[0] === "/help" or $command[0] === "/?") {
            if (isset($command[1]) && $command[1] > 0 && $command[1] < $protect + 1) {
                $messages = $this->plugin->getConfig()->get("page." . $command[1]);
                foreach ($messages as $msges) {
                    $player->sendMessage($msges);
                    $event->setCancelled();
                }
            } elseif (!isset($command[1]) or $command[1] > $protect or $command[1] < 1) {
                $helpInitial = $this->plugin->getConfig()->get("page.1");
                foreach ($helpInitial as $helpInit) {
                    $player->sendMessage($helpInit);
                    $event->setCancelled();
                }
            }
        }
    }

    public function onChat(PlayerChatEvent $e)
    {
        $p = $e->getPlayer();
        $m = $e->getMessage();

        $chatFilter = new Config($this->plugin->getDataFolder() . "chat.yml", Config::YAML);
        if (!isset($this->plugin->talked[$p->getName()])) {
            $this->plugin->talked[$p->getName()] = time();
        }

        if (isset($this->plugin->talked[$p->getName()])) {
            if (!$p->hasPermission("cosmicpe.freedom")) {
                $time = ($this->plugin->talked[$p->getName()] + 2) - time();
                if ($time > 0) {
                    $p->sendMessage($this->plugin->p("c", "!") . "You cannot send another message for " . $time . " seconds.");
                    $p->sendMessage(TF::GRAY . "Purchase a rank at " . TF::RED . "shop.cosmicpe.me " . TF::GRAY . "to reduce the delay between chat messages!");
                    $e->setCancelled();
                    return;
                }
            } else {
                $time = ($this->plugin->talked[$p->getName()] + 1) - time();
                if ($time > 0) {
                    $p->sendMessage($this->plugin->p("c", "!") . "You cannot send another message for " . $time . " seconds.");
                    $e->setCancelled();
                    return;
                }
            }
            if ($time <= 0) {
                $this->plugin->talked[$p->getName()] = time();
            }
        }
        $e->setMessage(ucfirst(strtolower($this->plugin->filterbadwords($m, $chatFilter->get("blocked-words")))));
        $this->plugin->talked[$p->getName()] = time();
    }

    public function blockBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        $level = $ev->getBlock()->getLevel();
        $block = $ev->getBlock();

        if (($level->getName() === "world") && $this->plugin->isAtWarzone($block)) {
            if (!isset($this->plugin->allowed()[strtolower($player->getName())])) {
                $player->sendMessage($this->plugin->p("c", "!") . "You can't destroy in a war zone!");
                $ev->setCancelled();
            }
        }

        if (isset($this->blockList[$block->getId()])) {
            if ($this->plugin->getData()->exists(CosmicCore::getBlockString($block))) {
                $this->plugin->getData()->remove(CosmicCore::getBlockString($block));
                $this->plugin->getData()->save(true);
            }
        }
        $id = $ev->getBlock()->getId();
        $item = $player->getInventory()->getItemInHand();
        $itemEn = $item->getEnchantments();
        $x = $ev->getBlock()->getX();
        $y = $ev->getBlock()->getY();
        $z = $ev->getBlock()->getZ();
        foreach ($itemEn as $enchantment) {
            $lvl = $enchantment->getLevel();
            if ($lvl > 1) {
                $c = mt_rand(2, $lvl);
            } else {
                $c = 2;
            }
            switch ($enchantment->getId()) {
                //VeinGlory
                case 120:
                    $blocks = array(14, 15, 16, 21, 56, 73, 74, 129, 157);
                    if (in_array($id, $blocks)) {
                        $player->getLevel()->setBlock(new Vector3($x, $y, $z), Block::get(0));
                        $ev->setDrops(array(Item::get(0)));
                    }
                    switch ($id) {
                        case 14:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(266, 0, $c));
                            break;
                        case 15:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(265, 0, $c));
                            break;
                        case 16:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(263, 0, $c));
                            break;
                        case 21:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(351, 4, $c));
                            break;
                        case 56:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(264, 0, $c));
                            break;
                        case 73:
                        case 74:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(331, 0, $c));
                            break;
                        case 129:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(388, 0, $c));
                            break;
                        case 157:
                            $player->getLevel()->dropItem(new Vector3($x, $y, $z), Item::get(406, 0, $c));
                            break;
                    }
                    break;
            }
        }
        if ($block->getId() === Block::MONSTER_SPAWNER) {
            $item = $ev->getItem();
            $allowed = [Item::IRON_PICKAXE, Item::DIAMOND_PICKAXE];
            if (in_array($item->getId(), $allowed)) {
                if ($item->isPickaxe()) {
                    if ($item->hasEnchantment(Enchantment::TYPE_MINING_SILK_TOUCH)) {
                        $level = $block->getLevel();
                        $pos = new Vector3($block->x, $block->y, $block->z);
                        $spawnerId = 0;
                        if (($tile = $level->getTile($pos)) instanceof MobSpawner) {
                            $spawnerId = $this->plugin->getSpawnerMetaFromId($tile->namedtag["EntityId"], $this->spawnerData);
                            $tile->close();
                        }
                        $item = Item::get(Item::MONSTER_SPAWNER, $spawnerId);
                        $level->dropItem(new Vector3($block->x, $block->y, $block->z), $item);
                        $level->setBlock($pos, Block::get(Block::AIR));
                        return;
                    }
                }
            }
            $level = $block->getLevel();
            $pos = new Vector3($block->x, $block->y, $block->z);
            if (($tile = $level->getTile($pos)) instanceof MobSpawner) {
                $tile->close();
            }
            $level->setBlock($pos, Block::get(Block::AIR));
        }
    }

    public function onItemConsume(PlayerItemConsumeEvent $event)
    {
        $poison = Effect::getEffect(19)->setVisible(false)->setDuration(350)->setAmplifier(1);
        $nausea = Effect::getEffect(9)->setVisible(false)->setDuration(350)->setAmplifier(1);
        $item = $event->getItem();
        $player = $event->getPlayer();
        $victim = strtolower($player->getName());

        if (!isset($this->plugin->victims[$victim])) {
            $this->plugin->victims[$victim] = null;
            $this->plugin->victims[$victim] = 0;
        }

        if ($item->getId() === 466 && $item->getDamage() === 0) {
            $this->plugin->victims[$victim]++;
            if ($this->plugin->victims[$victim] % 6 === 5) {
                $player->sendMessage($this->plugin->p("e", "!") . "You are about to contract Golden Apple Sickness!");
            }
            if ($this->plugin->victims[$victim] % 6 === 0) {
                $player->addEffect($poison);
                $player->addEffect($nausea);
                $player->sendMessage($this->plugin->p("e", "!") . "You have contracted Golden Apple Sickness!");
                $event->setCancelled();
            }
        }
        if ($item->getId() === 322 && $item->getDamage() === 0) {
            $this->plugin->victims[$victim]++;
            if ($this->plugin->victims[$victim] % 12 === 11) {
                $player->sendMessage($this->plugin->p("e", "!") . "You are about to contract Golden Apple Sickness!");
            }
            if ($this->plugin->victims[$victim] % 12 === 0) {
                $player->addEffect($poison);
                $player->addEffect($nausea);
                $player->sendMessage($this->plugin->p("e", "!") . "You have contracted Golden Apple Sickness!");
                $event->setCancelled();
            }
        }
    }

    public function stopAbuse(PlayerDropItemEvent $event)
    {
        $player = $event->getPlayer();
        if (!($player->isSurvival())) {
            $player->sendMessage($this->plugin->p("c", "!") . "Please switch back to survival mode if you'd like to drop your items.");
            $event->setCancelled();
        }
    }

    public function blockPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $pName = $player->getName();
        $item = $event->getItem();
        if ($item->getId() === 46) $event->setCancelled();
        if ($item->getId() === 52 && $item->getDamage() === 101) {
            $player->sendMessage($this->plugin->p("c", "!") . "You need to redeem this item before placing it!");
            $player->sendMessage($this->plugin->p("c", "!") . "Redeem this item by right-clicking it!");
            $event->setCancelled();
        }
        $blocked = array(7, 41, 42, 46, 57, 116, 117, 118, 120, 145, 152, 154, 165, 14, 15, 16, 56, 22, 52, 129, 133);
        if ($player->isCreative()) {
            if (in_array($item->getId(), $blocked)) {
                $player->sendMessage($this->plugin->p("c", "!") . "You cannot use/place this block in creative mode!");
                $event->setCancelled();
            }
        }

        if (($event->getBlock()->getLevel()->getName() === "world") && $this->plugin->isAtWarzone($event->getBlock())) {
            if (!isset($this->plugin->allowed()[strtolower($player->getName())])) {
                $player->sendMessage($this->plugin->p("c", "!") . "You can't build in a war zone.");
                $event->setCancelled();
            }
        }

        if (isset($this->spawnerData[$item->getDamage()]) && $item->getDamage() != 101) {
            $block = $event->getBlock();
            $pos = new Vector3($block->x, $block->y, $block->z);
            if ($item->getId() === Item::MONSTER_SPAWNER and $block->getId() === Block::MONSTER_SPAWNER) {
                $event->setCancelled(true);
                $level = $block->getLevel();
                $level->setBlock($pos, Block::get(Block::MONSTER_SPAWNER, $item->getDamage()), true, true);
                $nbt = new CompoundTag("", [new StringTag("id", CosmicCore::MOB_SPAWNER), new IntTag("x", $pos->x), new IntTag("y", $pos->y), new IntTag("z", $pos->z), new IntTag("EntityId", $this->spawnerData[$item->getDamage()]["id"])]);
                if ($item->hasCustomBlockData()) {
                    foreach ($item->getCustomBlockData() as $key => $v) {
                        $nbt->{$key} = $v;
                    }
                }
                Tile::createTile(CosmicCore::MOB_SPAWNER, $level->getChunk($block->x >> 4, $block->z >> 4), $nbt);
                if ($player != null && $player->isCreative()) {
                    $item->setCount($item->getCount());
                } else {
                    $item->setCount($item->getCount() - 1);
                }
                if ($item->getCount() <= 0) {
                    $item = Item::get(Item::AIR, 0, 0);
                }
                $player->getInventory()->setItemInHand($item);
            }
        }
    }

    public function onInventoryClose(InventoryCloseEvent $event)
    {
        $this->plugin->saveAndRemove($event);
    }

    public function onTeleport(EntityTeleportEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player) {
            if (isset($this->plugin->players[$p->getName()])) {
                $p->sendMessage($this->plugin->p("c", "!") . "This command didn't execute properly since you were in combat!");
                $e->setCancelled(true);
            }

            if ($this->plugin->isAtWarzone($p) && !isset($this->plugin->allowed()[strtolower($p->getName())])) {
                if ($this->plugin->essentialsPE->isGod($p)) {
                    $p->sendMessage($this->plugin->p("c", "!") . "You cannot be in god at spawn or in a warzone!");
                    $this->plugin->essentialsPE->switchGodMode($p);
                }
                if (!$p->isSurvival()) $p->setGamemode(0);
            }
        }
    }

    public function onHunger(PlayerHungerChangeEvent $e)
    {
        if ($this->plugin->isAtSpawn($e->getPlayer())) $e->setCancelled();
        $this->ce->updateGlobalEnchants($e->getPlayer());
    }

    public function onExplosion(EntityExplodeEvent $event)
    {
        $p = $event->getEntity();
        if ($p instanceof Player) {
            $p->setMaxHealth(20);
            foreach ($p->getInventory()->getArmorContents() as $armor) $this->updateOverload($p, $armor);
        }
        $id = $event->getEntity()->getId();
        if (isset($this->toExplode[$id])) {
            $affectedBlocks = CosmicCore::getExplosionAffectedBlocks($event->getPosition(), $this->toExplode[$id]);
            foreach ($affectedBlocks as $key => $block) {
                if (isset($this->blockList[$block->getId()])) {
                    $maxHealth = $this->blockList[$block->getId()];
                    if ($this->plugin->getData()->exists(CosmicCore::getBlockString($block))) {
                        $existing = $this->plugin->getData()->get(CosmicCore::getBlockString($block));
                        if (is_array($existing)) {
                            $health = $existing["health"] - 1;
                            $this->plugin->getData()->set(CosmicCore::getBlockString($block), ["health" => $health, "maxHealth" => $existing["maxHealth"]]);
                            $this->plugin->getData()->save(true);
                        }
                    } else {
                        $health = $maxHealth - 1;
                        $this->plugin->getData()->set(CosmicCore::getBlockString($block), ["health" => $health, "maxHealth" => $maxHealth]);
                        $this->plugin->getData()->save(true);
                    }
                    if (isset($health) and $health <= 0) {
                        if ($this->plugin->getData()->exists(CosmicCore::getBlockString($block))) {
                            $this->plugin->getData()->remove(CosmicCore::getBlockString($block));
                            $this->plugin->getData()->save(true);
                        }
                        $event->getPosition()->getLevel()->setBlock($block, Block::get(Block::AIR));
                        foreach ($block->getDrops(Item::get(Item::DIAMOND_PICKAXE)) as $item) $event->getPosition()->getLevel()->dropItem($block, Item::get($item[0], $item[1]));
                    }
                }
                if ($block->getId() === Block::MONSTER_SPAWNER) {
                    $level = $block->getLevel();
                    $pos = new Vector3($block->x, $block->y, $block->z);
                    if (mt_rand(1, $this->explodeChance) === 1) {
                        $spawnerId = 0;
                        if (($tile = $level->getTile($pos)) instanceof MobSpawner) {
                            $spawnerId = CosmicCore::getSpawnerMetaFromId($tile->namedtag["EntityId"], $this->spawnerData);
                            $tile->close();
                        }
                        $item = Item::get(Item::MONSTER_SPAWNER, $spawnerId);
                        $level->dropItem(new Vector3($block->x, $block->getY() + 1, $block->z), $item);
                        $level->setBlock($pos, Block::get(Block::AIR));
                    }
                }
            }
        }
    }

    public function onGameModeChange(PlayerGameModeChangeEvent $event)
    {
        $content = $event->getPlayer()->getInventory()->getContents();
        $event->getPlayer()->getInventory()->setContents($content);
        if (($event->getNewGamemode() == 1) && ($event->getPlayer()->hasPermission("cosmicpe.limitations"))) {
            $event->getPlayer()->sendMessage($this->plugin->msg("Please use such commands wisely."));
        }
    }

    public function onPickupItem(InventoryPickupItemEvent $event)
    {
        $player = $event->getInventory()->getHolder();
        if ($player instanceof Player) {
            if (($player->getGamemode() == 1) && ($player->hasPermission("cosmicpe.limitations"))) {
                $player->sendPopup($this->plugin->msg("You cannot pickup items in creative!"));
                $event->setCancelled(true);
            }
        }
    }

    public function onExplode(ExplosionPrimeEvent $e)
    {
        if (!$e->isCancelled()) {
            $this->toExplode[$e->getEntity()->getId()] = $e->getForce();
        }
        $p = $e->getEntity();
        if ($p instanceof Player) {
            $p->setMaxHealth(20);
            foreach ($p->getInventory()->getArmorContents() as $armor) $this->updateOverload($p, $armor);
        }
    }

    public function onSpawn(EntitySpawnEvent $event)
    {
        if ($this->spawnStack) {
            $entity = $event->getEntity();
            if ($entity instanceof Living && !$entity instanceof Player) CosmicCore::addToClosestStack($entity, $this->stackingRange, $this->plugin->getConfig());
        }
    }

    public function onRegainHealth(EntityRegainHealthEvent $event)
    {
        if ($event->getEntity() instanceof Player) $this->plugin->updateHealthBar($event->getEntity());
        $p = $event->getEntity();
        if ($p instanceof Player && $p->getHealth() > $p->getMaxHealth()) {
            $p->setMaxHealth(20);
        }
    }

    public function onCombust(\pocketmine\event\entity\EntityCombustEvent $e)
    {
        $p = $e->getEntity();
        if ($p instanceof Player) {
            $p->setMaxHealth(20);
            foreach ($p->getInventory()->getArmorContents() as $armor) $this->updateOverload($p, $armor);
        }
    }

    public function whileArmoured(EntityArmorChangeEvent $e)
    {
        $i = $e->getNewItem();
        $o = $e->getOldItem();
        $p = $e->getEntity();
        $ce = array(101, 102, 103, 104, 109);

        if (!isset($this->plugin->players[$e->getEntity()->getName()])) $this->updateOverload($p, $i);
        if ($p->hasPermission("cosmicpe.banned.effect")) $this->plugin->ce->updateArmorEffects($i, $p);
        foreach ($o->getEnchantments() as $enc) {
            foreach ($i->getEnchantments() as $iEn) {
                if ($iEn->getId() == $enc->getId()) return;
            }
            if ($enc->getId() == 111) {
                if ($p->getMaxHealth() > 20) {
                    if ($o->isHelmet()) {
                        $p->setMaxHealth($p->getMaxHealth() - 4);
                        if ($p->getHealth() > $p->getMaxHealth()) $p->setHealth($p->getMaxHealth());
                    } elseif ($o->isChestplate()) {
                        $p->setMaxHealth($p->getMaxHealth() - 8);
                        if ($p->getHealth() > $p->getMaxHealth()) $p->setHealth($p->getMaxHealth());
                    } elseif ($o->isLeggings()) {
                        $p->setMaxHealth($p->getMaxHealth() - 6);
                        if ($p->getHealth() > $p->getMaxHealth()) $p->setHealth($p->getMaxHealth());
                    } elseif ($o->isBoots()) {
                        $p->setMaxHealth($p->getMaxHealth() - 2);
                        if ($p->getHealth() > $p->getMaxHealth()) $p->setHealth($p->getMaxHealth());
                    }
                    $p->setHealth($p->getHealth());
                }
            }
            if ($this->ce->getEffectByEnchantment($enc->getId()) !== 36) {
                $p->removeEffect($this->ce->getEffectByEnchantment($enc->getId()));
            }
        }
        $p->setHealth($p->getHealth());
    }
}
