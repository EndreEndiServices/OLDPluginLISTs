<?php

namespace ParadoxUHC;

use ParadoxUHC\Commands\MainEventCommand;
use ParadoxUHC\Commands\MainHelpOpCommand;
use ParadoxUHC\Commands\MainHostCommand;
use ParadoxUHC\Commands\MainKTCommand;
use ParadoxUHC\Commands\MainMuteAllCommand;
use ParadoxUHC\Commands\MainScenariosCommand;
use ParadoxUHC\Commands\MainSpectateCommand;
use ParadoxUHC\Commands\MainUHCCommand;
use ParadoxUHC\Commands\MainStatsCommand;
use ParadoxUHC\Commands\MainReportCommand;
use ParadoxUHC\Commands\MainInfoCommand;
use ParadoxUHC\Commands\MainColorCommand;
use ParadoxUHC\Commands\MainLanguageCommand;
use ParadoxUHC\Tasks\DeathTask;
use ParadoxUHC\Tasks\Timer;
use ParadoxUHC\Tasks\VampireTask;
use ParadoxUHC\Tasks\BombTask;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\Leaves;
use pocketmine\block\SkullBlock;
use pocketmine\entity\CaveSpider;
use pocketmine\entity\Chicken;
use pocketmine\entity\Cow;
use pocketmine\entity\Pig;
use pocketmine\entity\Sheep;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Spider;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\IPlayer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Food;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Chest;
use pocketmine\tile\FlowerPot;
use pocketmine\tile\Sign;
use pocketmine\tile\Skull;
use pocketmine\Player;
use pocketmine\plugin\PluginBase as P;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent as Breaker;
use pocketmine\event\Listener as L;
use pocketmine\event\player\PlayerJoinEvent as Join;
use pocketmine\event\player\PlayerQuitEvent as Quit;
use pocketmine\event\player\PlayerDeathEvent as Death;
use pocketmine\event\entity\EntityRegainHealthEvent as Regen;
use pocketmine\event\player\PlayerItemConsumeEvent as Eat;
use pocketmine\event\player\PlayerLoginEvent as Login;
use pocketmine\event\entity\EntityDamageEvent as Damage;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerChatEvent as Chat;
use pocketmine\event\server\DataPacketReceiveEvent as Packet;
use pocketmine\network\protocol\EntityEventPacket as EntityEvent;
use pocketmine\event\block\BlockUpdateEvent as Update;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\Effect;
use pocketmine\entity\InstantEffect;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerPreLoginEvent as PreLogin;
use pocketmine\event\player\PlayerInteractEvent as Tap;
use pocketmine\block\Chest as C;
use pocketmine\event\inventory\CraftItemEvent as Craft;
use pocketmine\event\block\BlockPlaceEvent as Place;
use pocketmine\event\server\ServerEvent as Server;
use pocketmine\event\entity\EntityDeathEvent as EDeath;
use pocketmine\event\player\PlayerRespawnEvent as Respawn;
use pocketmine\utils\UUID;

class UHC extends P implements L
{

    public $relogPos = [];
    public $queue = [];
    public $kills = [];
    const STATUS_WAITING = -1;
    const STATUS_COUNTDOWN = 0;
    const STATUS_GRACE = 1;
    const STATUS_PVP = 2;
    const STATUS_TP_1 = 3;
    const STATUS_TELE_1 = 4;
    const STATUS_TP_2 = 5;
    const STATUS_TELE_2 = 6;
    const STATUS_LAST = 7;
    public $players = array();
    public $status = self::STATUS_WAITING;
    public $config;
    public $team;
    public $player;
    public $namedtag;
    public $killer;
    public $deathpos = [];
    public $prefix = TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."UHC".TF::DARK_GRAY.TF::BOLD."]".TF::RESET;

    /**
     * Enable and Disable Functions
     */
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."players/");
        $this->config = new Config($this->getDataFolder()."/config.yml", Config::YAML, array(
            "#Scenarios for UHCs, by Irish",
            "#Choose whether Cut-Clean is on or off.",
            "cutclean" => true,
            "#Choose whether gold is on or off",
            "goldless" => false,
            "#Choose whether diamonds are on or off",
            "diamondless" => false,
            "#Choose whether golden heads are on or off",
            "heads" => false,
            "#Choose whether head pole system is on or off",
            "pole" => true,
            "#WIP, choose whether Timebomb is on or off. Must have head pole system off!",
            "timebomb" => false,
            "#Choose whether players take damage when mining diamonds",
            "blood-diamonds" => false,
            "#Choose whether barebones is on or off",
            "barebones" => false,
            "#Choose whether fireless is on or off",
            "fireless" => false,
            "#Choose whether no-fall is on or off",
            "nofall" => false,
            "#Choose whether Chicken is toggled",
            "chicken" => false,
            "#Choose whether cripple is toggled",
            "cripple"=> false,
            "#Choose whether cat eyes is toggled",
            "cat-eyes"=> false,
            "#Choose whether lights-out is toggled",
            "lights-out" => false,
            "#Choose whether vampire is toggled",
            "vampire"=> false,
            "#Choose whether Ultra Paranoid is toggled",
            "paranoid" => false,
            "#Choose multiplication of ores",
            "multi-ore" => 1,
            "#Choose whether Amphibian is toggled",
            "amphibian" => false,
            "#Apple Rate",
            "apple"=> 25,
            "#Choose whether the UHC is split or non-split",
            "split"=>"both",
            "#Global Mute",
            "mute"=>false,
            "#Border",
            "border"=>1000
            ));
            $this->config->save();
        $this->getLogger()->info(TF::GRAY . 'The plugin has been enabled!');
        if($this->scenarioIsOn("heads")) {
            $this->getServer()->getCraftingManager()->registerRecipe((new ShapedRecipe(Item::get(Item::GOLDEN_APPLE, 2, 1)->setCustomName(TF::GOLD . 'Golden Head'),
                "XXX",
                "XYX",
                "XXX"
            ))->setIngredient("X", Item::get(Item::GOLD_INGOT, 0, 9))->setIngredient("Y", Item::get(Item::SKULL, 3, 1)));
        }
        new Timer($this, $this);
        $this->registerAllCommands();
    }

    public function onPreLogin(PreLogin $event){
        $player = $event->getPlayer();
        $name = $player->getName();
        if(!file_exists($this->getDataFolder()."players/".strtolower($name).".yml")) {
            $this->player = new Config($this->getDataFolder() . "players/" . strtolower($name) . ".yml", Config::YAML, array(
                "#------UHC Stats/Others------",
                "#Players killed in a UHC.",
                "Kills" => 0,
                "#Number of deaths in a UHC",
                "Deaths" => 0,
                "#Amount of games played",
                "UHCs" => 0,
                "#Number of UHCs won.",
                "Wins" => 0,
                "#Total number of diamonds found",
                "Diamonds" => 0,
                "#Total number of golden heads eaten.",
                "Heads" => 0,
                "#Language spoken",
                "language" => "english",
                "#---------------------"
            ));
            $this->player->save();
        }
    }

    public function onDisable()
    {
        $this->getLogger()->info(TF::GRAY.'The plugin has been disabled!');
    }

    //Custom Functions
    
    public function registerAllCommands(){
        $this->getServer()->getCommandMap()->registerAll("paradox_1.1", [new MainHelpOpCommand($this),new MainKTCommand($this), new MainSpectateCommand($this),new MainEventCommand($this), new MainUHCCommand($this), new MainScenariosCommand($this), new MainHostCommand($this), new MainStatsCommand($this), new MainReportCommand($this), new MainMuteAllCommand($this), new MainInfoCommand($this), new MainColorCommand($this), new MainLanguageCommand($this)]);
    }

    public function finalHeal(){
        foreach($this->getServer()->getOnlinePlayers() as $player){
            $health = $player->getMaxHealth();
            $food = $player->getMaxFood();
            $player->setHealth($health);
            $player->setFood($food);
        }
    }

    //events

    public function onBreak(Breaker $event){
        $player = $event->getPlayer();
        $this->config = new Config($this->getDataFolder()."config.yml");
        $this->player = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);
        $multiore = $this->config->get("multi-ore");
        $apple = $this->config->get("apple");
        
        if(($player->getLevel() === $this->getServer()->getLevelByName("UHC"))&&($this->status === self::STATUS_WAITING || $this->status === self::STATUS_COUNTDOWN)){
            $event->setCancelled(true);
            return;
        }
        if($player->getLevel() == $this->getServer()->getLevelByName("hubuhc")){
            if(!($player->hasPermission("uhc.perms.bypass"))){
                $event->setCancelled(true);
                return;
            }
        }
        $block = $event->getBlock()->getId();
        switch($block){
            case Block::GOLD_ORE:
                if($this->scenarioIsOn("cutclean")){
                    if($this->scenarioIsOn("goldless")){
                        $event->setDrops([Item::get(Item::AIR)]);
                        return;
                    }
                    if($this->scenarioIsOn("barebones")){
                        $event->setDrops([Item::get(Item::IRON_INGOT, 0, $multiore)]);
                        return;
                    }
                    $event->setDrops([Item::get(Item::GOLD_INGOT, 0, $multiore)]);
                    return;
                }
                if(!($this->scenarioIsOn("cutclean"))){
                    $event->setDrops([Item::get(Item::GOLD_ORE, 0, $multiore)]);
                }
                break;
            case Block::DIAMOND_ORE:
                $diamonds = $this->player->get("Diamonds");
                $this->player->set("Diamonds", $diamonds + 1);
                $this->player->save();
                if($this->scenarioIsOn("diamondless")){
                    $event->setDrops([Item::get(Item::AIR)]);
                    return;
                    }
                if($this->scenarioIsOn("blood-diamonds")){
                    $ev = new EntityDamageByBlockEvent(Block::get(Block::DIAMOND_ORE), $player, DAMAGE::CAUSE_CUSTOM, 1);
                    $player->attack(1, $ev);
                    $event->setDrops([Item::get(Item::DIAMOND, 0, $multiore)]);
                    return;
                }
                if($this->scenarioIsOn("barebones")){
                    $event->setDrops([Item::get(Item::IRON_INGOT, 0, $multiore)]);
                    return;
                }
                $event->setDrops([Item::get(Item::DIAMOND, 0, $multiore)]);   
                    break;
            case Block::IRON_ORE:
                if($this->scenarioIsOn("cutclean")){
                    $event->setDrops([Item::get(Item::IRON_INGOT, 0, $multiore)]);
                }
                break;
            case Block::LEAVES:
            case Block::LEAVES2:
                $rand = mt_rand(1, $apple);
                switch($rand){
                    case 1:
                        $event->setDrops([Item::get(Item::APPLE)]);
                }
                break;
            case Block::GRAVEL:
                if($this->scenarioIsOn("cutclean")){
                        $event->setDrops([Item::get(Item::FLINT)]);
                    }
            }
    }
    
    public function onEat(Eat $event){
        $item = $event->getItem();
        $this->config = new Config($this->getDataFolder()."config.yml");
        $name = $item->getName();
        $id = $item->getId();
        $meta = $item->getDamage();
        $player = $event->getPlayer();
        if($id == Item::GOLDEN_APPLE) {
            if ($meta == 2) {
                if ($name === TF::GOLD . "Golden Head") {
                    $player->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(20 * 9));
                    $health = $player->getHealth();
                    $player->setHealth($health + 8);
                    $this->player = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);
                    $heads = $this->player->get("Heads");
                    $this->player->set("Heads", $heads + 1);
                    $this->player->save();
                }
            }
        }
        if($this->scenarioIsOn("vampire")){
            if($id == Item::ROTTEN_FLESH){
                if($meta == 1){
                    if($name == TF::RED."Human Flesh"){
                        $player->addEffect(Effect::getEffect(Effect::STRENGTH)->setAmplifier(1)->setDuration(20 * 15));
                        $player->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(20 * 20));
                        $player->addEffect(Effect::getEffect(Effect::ABSORPTION)->setAmplifier(0)->setDuration(20 * 2 * 60));
                        foreach($player->getEffects() as $effect){
                            if($effect->isBad()){
                                $player->removeEffect($effect);
                            }
                        }
                        new VampireTask($this, $player);
                    }
                }
            }
        }
        
    }

    public function onRegen(Regen $event){
        $player = $event->getEntity();
        if($player instanceof Player){
            if($player->hasEffect(Effect::getEffect(Effect::HEALING))){

            }
            if(!$player->hasEffect(Effect::getEffect(Effect::REGENERATION))){
                $reason = $event->getRegainReason();
                switch($reason){
                    case Regen::CAUSE_REGEN:
                        $event->setCancelled();
                        break;
                    case Regen::CAUSE_EATING:
                        $event->setCancelled();
                }
            }
        }
    }

    public function onJoin(Join $event)
    {
        $player = $event->getPlayer();
        if($player->isSurvival()){
            if(!isset($this->queue[$player->getName()])){
                $this->queue[$player->getName()] = $player->getName();
            }
        }
        if(isset($this->relogPos[$player->getName()])){
            $player->teleport($this->relogPos[$player->getName()]);
        }

        $event->setJoinMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::GREEN.'»'.TF::DARK_GRAY.TF::BOLD.']'.TF::RESET.TF::GRAY." ".$player->getDisplayName());
    }

    public function onQuit(Quit $event)
    {
        $player = $event->getPlayer();
        if(isset($this->queue[$player->getName()])){
            unset($this->queue[$player->getName()]);
        }
        if(!isset($this->relogPos[$player->getName()])){
            $this->relogPos[$player->getName()] = clone $player->getPosition();
        }
        $event->setQuitMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::RED.'«'.TF::DARK_GRAY.TF::BOLD.']'.TF::RESET.TF::GRAY." ".$player->getDisplayName());
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $this->config = new Config($this->getDataFolder()."config.yml");
        if($this->config->get("mute") === "true") {
            if (!($player->hasPermission("uhc.perms.talk"))) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage($this->getPrefix(). TF::RED . "You can't talk during the global mute!");
            }
        }
    }

    public function onEntityDeath(EDeath $event){
        $entity = $event->getEntity();
        if($this->scenarioIsOn("cutclean")) {
            if ($entity instanceof Chicken) {
                $event->setDrops([Item::get(Item::COOKED_CHICKEN, 0, mt_rand(3, 5))]);
                $entity->getLevel()->dropItem(new Vector3($entity->x, $entity->y, $entity->z), Item::get(Item::FEATHER, 0, mt_rand(2, 4)));
            }
            if ($entity instanceof Cow) {
                $event->setDrops([Item::get(Item::COOKED_BEEF, 0, mt_rand(3, 5))]);
                $entity->getLevel()->dropItem(new Vector3($entity->x, $entity->y, $entity->z), Item::get(Item::LEATHER, 0, mt_rand(2, 4)));
            }
            if ($entity instanceof Sheep) {
                $event->setDrops([Item::get(Item::COOKED_MUTTON, 0, mt_rand(3, 5))]);
            }
            if ($entity instanceof Spider) {
                $event->setDrops([Item::get(Item::SPIDER_EYE, 0, mt_rand(0, 2))]);
                $entity->getLevel()->dropItem(new Vector3($entity->x, $entity->y, $entity->z), Item::get(Item::STRING, 0, mt_rand(2, 5)));
            }
            if ($entity instanceof CaveSpider) {
                $event->setDrops([Item::get(Item::SPIDER_EYE, 0, mt_rand(0, 2))]);
                $entity->getLevel()->dropItem(new Vector3($entity->x, $entity->y, $entity->z), Item::get(Item::STRING, 0, mt_rand(2, 5)));
            }
            if ($entity instanceof Skeleton) {
                $event->setDrops([Item::get(Item::ARROW, 0, 2)]);
                $entity->getLevel()->dropItem(new Vector3($entity->x, $entity->y, $entity->z), Item::get(Item::BONE, 0, mt_rand(0, 2)));
            }
            if ($entity instanceof Zombie) {
                $event->setDrops([Item::get(Item::BAKED_POTATO, 0, mt_rand(1, 5))]);
            }
        }
    }

    /**
     * @param Death $event
     */
    public function onDeath(Death $event)
    {
        $player = $event->getEntity();
        $event->setDeathMessage($this->getPrefix().TF::GRAY." A player has been eliminated!");
        $this->player = new Config($this->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml", Config::YAML);
        $this->player->set("Deaths", $this->player->get("Deaths")+1);
        $this->player->save();
        $cause = $player->getLastDamageCause();
        if(isset($this->queue[$player->getName()])){
            unset($this->queue[$player->getName()]);
        }
        if($cause instanceof EntityDamageByEntityEvent){
            $killer = $cause->getDamager();
            if($killer instanceof Player){
                $kname = $killer->getName();
                $this->kills[$kname] = $this->kills[$kname] + 1;
                if($this->getLanguage($killer) == "english"){
                    $killer->sendMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::BLUE."AlphaUHC".TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' A kill has been added to your stats!');
                }
                if($this->getLanguage($killer) == "spanish"){
                    $killer->sendMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::BLUE."AlphaUHC".TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Una matanza ha sido agregado a sus estadísticas!');
                }
                $this->player = new Config($this->getDataFolder()."players/". strtolower($killer->getName()).".yml",Config::YAML);
                $kills = $this->player->get("Kills");
                $this->player->set("Kills", $kills + 1);
                $this->player->save();
            }
        }
        if ($player->hasPermission("uhc.perms.spectate")) {
            $player->setGamemode(3);
            if($this->getLanguage($player) == "english"){
                $player->sendMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::BLUE."AlphaUHC".TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." You have died! Use /spectate [name] to spectate a player!");
            }
            if($this->getLanguage($player) == "spanish"){
                $player->sendMessage(TF::DARK_GRAY.TF::BOLD.'['.TF::RESET.TF::BLUE."AlphaUHC".TF::BOLD.TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." ¡Te has muerto! Uso /spectate [nombre] spectate un jugador!");
            }
        }
        if ($this->scenarioIsOn("diamondless")) {
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::DIAMOND, 0, 1));
        }
        if ($this->scenarioIsOn("vampire")) {
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::ROTTEN_FLESH, 0, 3)->setCustomName(TF::RED."Human Flesh"));
        }
        if ($this->scenarioIsOn("goldless")) {
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::GOLD_INGOT, 0, 8));
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::GOLDEN_APPLE, 2, 1)->setCustomName(TF::GOLD . 'Golden Head'));
        }
        if ($this->scenarioIsOn("barebones")) {
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::DIAMOND, 0, 1));
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::STRING, 0, 2));
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::ARROW, 0, 32));
                $player->getLevel()->dropItem(new Vector3($player->x, $player->y, $player->z), Item::get(Item::GOLDEN_APPLE, 0, 1));
        }
        if ($this->scenarioIsOn("pole")) {
            $this->setHead($player);
        }
        if(!($player->hasPermission("uhc.perms.spectate"))){
            $task = new DeathTask($this, $player);
            $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($task, 0, 20);
            if($this->getLanguage($player) == "english"){
                $player->sendMessage(TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."AlphaUHC".TF::DARK_GRAY.TF::BOLD."]".TF::RESET.TF::GRAY." You have 30 seconds to say your final words before getting kicked and unwhitelisted!");
            }
            if($this->getLanguage($player) == "spanish"){
                $player->sendMessage(TF::DARK_GRAY.TF::BOLD."[".TF::RESET.TF::BLUE."AlphaUHC".TF::DARK_GRAY.TF::BOLD."]".TF::RESET.TF::GRAY." Tienes 30 segundos para decir sus últimas palabras antes de ser expulsado y unwhitelisted!");
            }
        }
    }

    /**
     * @param Damage $event
     */
    public function onDamage(Damage $event){
        $cause = $event->getCause();
        $entity = $event->getEntity();
        $e = $this->getEvent();
        if($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            if($entity instanceof Player){
                if($damager instanceof Player){
                    switch($e){
                        case self::STATUS_WAITING:
                            $event->setCancelled();
                            break;
                        case self::STATUS_COUNTDOWN:
                            $event->setCancelled();
                            break;
                        case self::STATUS_GRACE:
                            $event->setCancelled();
                            break;
                    }
                }
            }
        }
        if($cause == Damage::CAUSE_FALL){
            switch($e){
                case self::STATUS_WAITING:
                case self::STATUS_COUNTDOWN:
                    $event->setCancelled();

            }
        }


    }

    
    public function onPlace(Place $event){
        $block = $event->getBlock()->getId();
        $level = $event->getPlayer()->getLevel();
        if($level == $this->getServer()->getLevelByName("hubuhc")){
            $player = $event->getPlayer();
            if(!($player->hasPermission("uhc.perms.bypass"))){
                $event->setCancelled(true);
            }
        }
        if($this->scenarioIsOn("lights-out")){
            if($block == Block::TORCH){
                $event->setCancelled();
                $event->getPlayer()->sendMessage($this->getPrefix().TF::GOLD." You can't place torches in a lights-out game!");
            }
        }

    }

    public function setHead(Player $player){
        $level = $player->getLevel();
        $x = $player->x;
        $y = $player->y;
        $z = $player->z;
        $level->setBlock(new Vector3($x, $y+1, $z), Block::get(Block::SKULL_BLOCK), true, true);
        $level->setBlock(new Vector3($x, $y, $z), Block::get(Block::NETHER_BRICK_FENCE));
        $chunk = $level->getChunk($x >> 4, $z >> 4);
        $nbt = new CompoundTag("", [
            new StringTag("id", Tile::SKULL),
            new StringTag("SkullType", 3),
            new IntTag("x", $x),
            new IntTag("y", $y+1),
            new IntTag("z", $z),
            new StringTag("Rot", 0)
        ]);
        Tile::createTile("Skull", $chunk, $nbt);
        $t = $level->getTile(new Vector3($x, $y+1, $z));
        $level->addTile($t);



    }

    public function onPreProcess(PlayerCommandPreprocessEvent $event){
        $player = $event->getPlayer();
        if(!($player->hasPermission("uhc.perms.bypass"))){
            $cmd = $event->getMessage();
            switch($cmd){
                case "/help":
                    $event->setCancelled();
                    $player->sendMessage(TF::GOLD."------ ParadoxUHC Commands ------");
                    if($player->hasPermission("uhc.perms.color")){
                        $player->sendMessage(TF::GOLD."/color:".TF::GRAY. " Change the color of your nametag.");
                    }
                    $player->sendMessage(TF::GOLD."/info:".TF::GRAY. " Lists more information about the UHC.");
                    $player->sendMessage(TF::GOLD."/help:".TF::GRAY. " Displays available commands.");
                    $player->sendMessage(TF::GOLD."/helpop:".TF::GRAY. " Use this to send messages to hosts if you need anything.");
                    $player->sendMessage(TF::GOLD."/language:".TF::GRAY. " Change your language.");
                    $player->sendMessage(TF::GOLD."/report:".TF::GRAY. " Report hackers, abusive people, or spammers.");
                    $player->sendMessage(TF::GOLD."/scenarios:".TF::GRAY. " Displays the scenarios for the UHC.");
                    if($player->hasPermission("uhc.perms.spectate")){
                        $player->sendMessage(TF::GOLD."/spectate:".TF::GRAY. " Spectate players after dying.");
                    }
                    $player->sendMessage(TF::GOLD."/stats:".TF::GRAY. " See your stats in our UHCs.");
                    break;
                case "/spawn":
                case "/me":
                case "/kill":
                case "/version":
                    $event->setCancelled();
                    break;




            }
        }
    }



    public function scenarioIsOn(string $scenario){
        $this->config = new Config($this->getDataFolder()."config.yml");
        $sc = $this->config->get($scenario);
        if($sc == true){
            return true;
        }
        return false;
    }
    public function getLanguage(Player $player){
        $this->player = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml");
        return $this->player->get("language");
    }
    
    public function setLanguage(Player $player, $language){
        $this->player = new Config($this->getDataFolder()."players/".strtolower($player->getName()).".yml");
        $this->player->set("language", $language);
    }

    public function getPrefix(){
        return $this->prefix;
    }
    public function getEvent(){
        return $this->status;
    }

    public function getBorder(){
        $this->config = new Config($this->getDataFolder()."config.yml");
        return $this->config->get("border");
    }
    
    



    
    

}