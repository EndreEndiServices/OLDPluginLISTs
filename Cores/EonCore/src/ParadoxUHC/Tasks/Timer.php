<?php
namespace ParadoxUHC\Tasks;

        use ParadoxUHC\UHC;
        use pocketmine\block\SignPost;
        use pocketmine\event\entity\EntityDamageByEntityEvent;
        use pocketmine\event\entity\EntityDamageByBlockEvent;
        use pocketmine\item\Item;
        use pocketmine\level\Position;
        use pocketmine\plugin\Plugin;
        use pocketmine\scheduler\PluginTask;
        use pocketmine\Player;
        use pocketmine\utils\TextFormat as TF;
        use pocketmine\event\entity\EntityDamageEvent as Damage;
        use pocketmine\entity\Effect;
        use pocketmine\level\sound\AnvilFallSound as Fall;
        use pocketmine\level\sound\ClickSound as Note;
        use pocketmine\level\sound\EndermanTeleportSound as Teleport;
        use pocketmine\block\Block;
        use pocketmine\math\Vector3;
        use pocketmine\utils\Config;
        use pocketmine\level\particle\PortalParticle as Portal;
        
        class Timer extends PluginTask
        {
            public $seconds = 0;
            public $heal = 60 * 5;
            public $countdown = 210;
            public $grace = 60 * 15;
            public $tp1 = 60 * 15;
            public $pvp = 60 * 25;
            public $tele1 = 2;
            public $tele2 = 2;
            public $last = 60 * 10;
            private $plugin;
            public $config;
            public $player;
            public $border;
            
        
        
            /**
             * Timer constructor.
             * @param Plugin $plugin
             * @internal param Plugin $this
             */
            public function __construct(Plugin $plugin){
                parent::__construct($plugin);
                $this->plugin = $plugin;
                $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this, 20);
            }
            
        
            /**
             * @param $currentTick
             */
            public function onRun($currentTick){
                        if ($this->plugin->status === UHC::STATUS_WAITING) {
                            $this->handleWaiting();
                            $this->countdown = 210;
                            $this->grace = 60 * 20;
                            $this->pvp = 60 * 40;
                            $this->tele1 = 2;
                            $this->tele2 = 2;
                            $this->last = 60 * 10;
                            $this->tp1 = 60 * 15;
                            $this->border = $this->plugin->getBorder();
                            foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                                $this->plugin->kills[$player->getName()] = 0;
                                $player->addEffect(Effect::getEffect(Effect::SATURATION)->setDuration(20 * 3)->setVisible(false)->setAmplifier(20));
                                if($player->isSurvival()) {
                                    if (!isset($this->plugin->queue[$player->getName()])) {
                                        $this->plugin->queue[$player->getName()] = $player->getName();
                                    }
                                }
                                else {
                                    if(isset($this->plugin->queue[$player->getName()])){
                                        unset($this->plugin->queue[$player->getName()]);
                                    }
                                }
                            }
                        }
                        if ($this->plugin->status === UHC::STATUS_COUNTDOWN) {
                            $this->handleCountdown();
                            foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                                if ((!$player->isSurvival()) && $player->hasPermission('uhc.commands.uhc')) {
                                    $player->setLevel(($this->getOwner()->getServer()->getLevelByName("UHC")));
                                } else {
                                    $player->resetFallDistance();
                                    $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml");
                                    $this->config = new Config($this->plugin->getDataFolder() . "config.yml");
                                    if ($this->config->get("fireless") == true) {
                                        $player->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setAmplifier(100)->setDuration(20 * 10000)->setVisible(false)->setAmbient(false));
                                    }
                                    $x = mt_rand(-$this->border, $this->border);
                                    $y = mt_rand(100, 140);
                                    $z = mt_rand(-$this->border, $this->border);
                                    $level = $player->getLevel();
                                    $player->setMaxHealth(20);
                                    $player->setFood(20);
                                    $player->setHealth(20);
                                    $player->setGamemode(0);
                                    if($this->countdown === 208){
                                        $level = $this->getOwner()->getServer()->getLevelByName("UHC");
                                        $player->setLevel($level);
                                        $player->teleport(new Position($x, 128, $z));
                                        $player->getLevel()->addSound(new Teleport(new Vector3($player->x, $player->y, $player->z)), [$player]);
                                        $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($player->getName()) . ".yml");
                                        $player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setAmplifier(100)->setDuration(20 * 15));
                                        $player->addEffect(Effect::getEffect(Effect::SLOWNESS)->setAmplifier(100)->setDuration(20 * 210));
                                        $player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(120)->setDuration(20 * 210));
                                        $player->addEffect(Effect::getEffect(Effect::JUMP)->setAmplifier($this->border)->setDuration(20 *210));
                                        $player->addEffect(Effect::getEffect(Effect::SATURATION)->setAmplifier(100)->setDuration(20 * 245));
                                        $player->getInventory()->clearAll();
                                        $uhcs = $this->player->get("UHCs");
                                        $this->player->set("UHCs", $uhcs + 1);
                                        $this->player->save();
                                    }
        
                                    if($this->countdown == 200){
                                        $this->config->set("mute", "true");
                                        $this->config->save();
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Global mute is now enabled!');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Mundo global está habilitado ahora!');
                                        }
                                    }
        
                                    if($this->countdown == 34){
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Welcome to Eon!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' There are a few rules that we have before you start playing.');
                                            
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Bievenido a EonUHC!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Hay algunas reglas que tenemos antes de que empieces a jugar.');
                                            
                                        }
                                    }
        
                                    if ($this->countdown === 29) {
                                        $px = $player->x;
                                        $py = $player->y;
                                        $pz = $player->z;
                                        if($this->config->get("split") === "true") {
                                            if($this->plugin->getLanguage($player) == "english"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Please remember that this is a split touch only UHC.');
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Any person that violates this will receive a ban depending on how many warnings you have.');
                                            }
                                            if($this->plugin->getLanguage($player) == "spanish"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Por favor recuerde que este es un toque de división única en el UHC.');
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Cualquier persona que rompa las reglas tendra ban dependiendo de cuantas advertencias tengas.');
                                            }
                                            return true;
                                        }
                                        if($this->config->get("split") === "false"){
                                            if($this->plugin->getLanguage($player) == "english"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Please remember that this is a non-split touch only UHC.');
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Any person that violates this will receive a ban depending on how many warnings you have.');
                                            }
                                            if($this->plugin->getLanguage($player) == "spanish"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Por favor, recuerde que este es un toque no dividida solamente UHC.');
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Cualquier persona que rompa las reglas tendra ban dependiendo de cuantas advertencias tengas.');
                                            }
                                            return true;
                                        }
                                        else {
                                            if($this->plugin->getLanguage($player) == "english"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Both split and non-split are allowed in this UHC.');
                                            }
                                            if($this->plugin->getLanguage($player) == "spanish"){
                                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Split y non-split ambos estan permitidos en este UHC.');
                                            }
                                        }
                                    }
                                    if ($this->countdown === 24) {
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' If you are caught hacking in any way, you will be banned!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' If you do see a hacker feel free to use the /report command!');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Si son capturados piratería en modo alguno , se le prohibe entrar al servidor!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Si usted ve un hacker no dude en utilizar el comando /report command!');
                                        }
                                    }
                                    if ($this->countdown === 19) {
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' If you are caught stalking streamers or YouTubers, you will be teleported to another location.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Strip-mining and staircasing is allowed in this UHC.');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET . TF::GRAY . ' Si te encontramos siguiendo a streameros y youtubers, te teleportearemos a otra parte.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Minería de tira y staircasing se permite en este UHC.');
                                        }
                                    }
                                    if ($this->countdown === 14) {
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' To find out what scenarios are in this UHC, do /scenarios.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' To find out all of your stats, do /stats.');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Para averiguar qué escenarios  estan en este UHC, solamente hagan /scenarios.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Para averiguar todas sus estadísticas ,solamente escribe /stats.');
                                        }
                                    }
                                    if ($this->countdown === 9) {
                                        
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' This is only a game so remember to have fun!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' There will be more updates soon, so remember to follow @EonUHCs for updates and upcoming UHCS!');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Esto es sólo un juego pero no se les olvide a divertirse!');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Va a ver más actualizaciones pronto , así que recuerde de seguir @EonUHCs para las actualizaciones y UHCS que van a llegar!');
                                        }
                                    }
                                    if ($this->countdown === 4) {
                                        if($this->plugin->getLanguage($player) == "english"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Final Heal happens at 10 minutes.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' PvP starts in 20 minutes.');
                                        }
                                        if($this->plugin->getLanguage($player) == "spanish"){
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Sanación final a los 10 minutos.');
                                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' PvP comienza en 20 minutos.');
                                        }
                                    }
                                }
                            }
                        }
                if($this->plugin->status === UHC::STATUS_GRACE){
                    $this->handleGrace();
                    if($this->grace === 1198) {
                        $this->config = new Config($this->plugin->getDataFolder() . "config.yml");
                        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                            $health = $player->getMaxHealth();
                            $hunger = $player->getMaxFood();
                            $player->setHealth($health);
                            $player->setFood($hunger);
                            if($this->plugin->getLanguage($player) == "english"){
                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' All players have been healed and their hunger nourished!');
                            }
                            if($this->plugin->getLanguage($player) == "spanish"){
                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Todos los jugadores se han curado y alimentado su hambre!');
                            }
                            if($this->config->get("chicken") == true){
                                $ev = new EntityDamageByBlockEvent(Block::get(Block::SAND), $player, Damage::CAUSE_CUSTOM, 19);
                                $player->attack(19, $ev);
                                $player->getInventory()->addItem(Item::get(Item::ENCHANTED_GOLDEN_APPLE, 0, 1));
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." Your chicken items have been added!");
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." Se le a añadido pollo en sus artículos!");
                                }
                            }
                            if($this->config->get("cat-eyes") == true){
                                $player->addEffect(Effect::getEffect(Effect::NIGHT_VISION)->setDuration(20 * 60 * 100));
                            }
                        }
                    }
                    if($this->grace === 1190) {
                        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                            $player->getInventory()->addItem(Item::get(Item::STEAK, 0, 16));
                            if($this->plugin->getLanguage($player) == "english"){
                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' All players have been given steak!');
                            }
                            if($this->plugin->getLanguage($player) == "spanish"){
                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY." A todos los jugadores se les ha dado filete!");
                            }
                            if ($player->hasPermission("uhc.perms.resistance")) {
                                $player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setDuration(20 * 45)->setAmplifier(9));
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' You have been given your resistance!');
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . ' Se le ha dado resistencia!');
                                }
                            }
                        }
                    }
                    if($this->grace === 599){
                        if($this->config->get("chicken") == false){
                            $this->finalHeal();
                        }
                    }
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                        if (($player->getX() >= $this->border || $player->getX() <= -$this->border || $player->getZ() >= $this->border || $player->getZ() <= -$this->border)) {
                            if($player->isSurvival()) {
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' You have been teleported inside of the border!');
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Ha sido transportado en el interior de la frontera!');
                                }
                                $player->knockBack($player, 0, -$player->x, -$player->z, 0.6);
                            }
                        }
                    }
                }
                if($this->plugin->status === UHC::STATUS_PVP){
                    $this->handlePvP();
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                        if (($player->getX() >= $this->border || $player->getX() <= -$this->border || $player->getZ() >= $this->border || $player->getZ() <= -$this->border)) {
                            if($player->isSurvival()) {
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' You have been teleported inside of the border!');
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Ha sido transportado en el interior de la frontera!');
                                }
                                $player->knockBack($player, 0, -$player->x, -$player->z, 0.6);
        
                            }
                        }
                    }
                }
                if($this->plugin->status === UHC::STATUS_TP_1){
                    $this->handleTP1();
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                        if (($player->getX() >= $this->border || $player->getX() <= -$this->border || $player->getZ() >= $this->border || $player->getZ() <= -$this->border)) {
                            if ($player->getGamemode() === 3 || $player->getGamemode() === 1) {
                            }
                            if($player->getLevel() === $this->plugin->getServer()->getLevelByName("hubuhc")){
        
                            }
                            else {
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' You have been teleported inside of the border!');
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Ha sido transportado en el interior de la frontera!');
                                }
                                $player->knockBack($player, 0, -$player->x, -$player->z, 0.6);
        
                            }
                        }
                    }
                }
                if($this->plugin->status === UHC::STATUS_TELE_1){
                    $this->handleTele1();
                    if($this->tele1 === 1){
                        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                            $players = $this->getOwner()->getServer()->getOnlinePlayers();
                            $px = $player->x;
                            $py = $player->y;
                            $pz = $player->z;
                            $x = mt_rand(-400, 400);
                            $y = mt_rand(110, 140);
                            $z = mt_rand(-400, 400);
                            $level = $player->getLevel();
                            if($player->getGamemode() === 3 || $player->getGamemode() === 1){
                            }
                            if($player->getLevel() === $this->plugin->getServer()->getLevelByName("hubuhc")){
        
                            }
                            if($px > 100 || $px < -100 || $pz >100 || $pz < -100) {
                                $player->addEffect(Effect::getEffect(Effect::DAMAGE_RESISTANCE)->setAmplifier(120)->setDuration(20 * 7));
                                $player->addEffect(Effect::getEffect(Effect::BLINDNESS)->setAmplifier(100)->setDuration(20 * 7));
                                $player->teleport(new Vector3($x, $y, $z));
                                $player->getLevel()->addSound(new Teleport(new Vector3($player->x, $player->y, $player->z)), [$player]);
                                $level->addParticle(new Portal(new Vector3($px, $py+3, $pz), [$player]));
                                $level->addParticle(new Portal(new Vector3($px+1, $py+3, $pz+1), [$player]));
                                $level->addParticle(new Portal(new Vector3($px-1, $py+3, $pz+1), [$player]));
                                $level->addParticle(new Portal(new Vector3($px-1, $py+3, $pz-1), [$player]));
                                $level->addParticle(new Portal(new Vector3($px+1, $py+3, $pz-1), [$player]));
                                $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::RED.' You have reached the border!');
                            }
                        }
                    }
                    if($this->tele1 === 0){
                        $this->plugin->status = UHC::STATUS_LAST;
                        $this->tele1 = 2;
                    }
                }
                if($this->plugin->status === UHC::STATUS_LAST){
                    $this->handleLast();
                    foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                        if(($player->getX() >= 100 || $player->getX() <= -100 || $player->getZ() >= 100 || $player->getZ() <= -100)) {
                            if ($player->getGamemode() === 3 || $player->getGamemode() === 1) {
                            }
                            if($player->getLevel() === $this->plugin->getServer()->getLevelByName("hubuhc")){
        
                            }
                            else {
                                if($this->plugin->getLanguage($player) == "english"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET.TF::BLUE . ' You have reached the border!');
                                }
                                if($this->plugin->getLanguage($player) == "spanish"){
                                    $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET.TF::BLUE . ' Has llegado a la frontera!');
                                }
                                $player->knockBack($player, 0, -$player->x, -$player->z, 0.6);
        
                            }
                        }
                    }
                }
                $this->win();
                $this->sendHealth();
            }
            
            public function handleWaiting(){
                $players = $this->plugin->getServer()->getOnlinePlayers();
                foreach($players as $player){
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' There are '.TF::GOLD.count($this->plugin->queue).TF::GRAY." player(s) in this UHC!");
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Hay '.TF::GOLD.count($this->plugin->queue).TF::GRAY." jugadore(s) en esta UHC!");
                    }
                }
            }
        
            public function handleCountdown(){
                $this->countdown--;
                foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'     ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' The UHC will start in '.$this->seconds2string($this->countdown).' seconds.');
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'     ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' El UHC comenzará en '.$this->seconds2string($this->countdown).' segundos.');
                    }
                    if($this->countdown === 3){
                        $player->getLevel()->addSound(new Note(new Vector3($player->x, $player->y, $player->z)),$this->plugin->getServer()->getOnlinePlayers());
                    }
                    if($this->countdown === 2){
                        $player->getLevel()->addSound(new Note(new Vector3($player->x, $player->y, $player->z)),$this->plugin->getServer()->getOnlinePlayers());
                    }
                    if($this->countdown === 1){
                        $player->getLevel()->addSound(new Note(new Vector3($player->x, $player->y, $player->z)),$this->plugin->getServer()->getOnlinePlayers());
                    }
                    if($this->countdown === 0){
                        $this->plugin->status = UHC::STATUS_GRACE;
                        $player->getLevel()->addSound(new Fall(new Vector3($player->x, $player->y, $player->z)),$this->plugin->getServer()->getOnlinePlayers());
                        $this->countdown = 30;
        
                    }
        
                }
                return;
            }
        
            /**
             *
             */
            public function handleGrace(){
                $this->grace--;
                foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $x = round($player->x);
                    $y = round($player->y);
                    $z = round($player->z);
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'            ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Players Left: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n   Grace will end in ". TF::GOLD. $this->seconds2string($this->grace).TF::GRAY.' minute(s).');
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'            ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Jugadores Que Quedan: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n   El gracia terminará en ". TF::GOLD. $this->seconds2string($this->grace).TF::GRAY.' minuto(s).');
                    }
                    if($this->grace === 0){
                        $this->plugin->status = UHC::STATUS_PVP;
                        $this->grace = 60 * 15;
                        $this->config->set("mute", "false");
                        $this->config->save();
                        if($this->plugin->getLanguage($player) == "english"){
                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']' . TF::RESET . TF::GRAY . ' Global mute is now disabled.');
                        }
                        if($this->plugin->getLanguage($player) == "spanish"){
                            $player->sendMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']' . TF::RESET . TF::GRAY . ' Global mute ha sido desactivado.');
                        }
                    }
                }
            }
        
        
            public function handlePvP() {
                $this->pvp--;
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $x = round($player->x);
                    $y = round($player->y);
                    $z = round($player->z);
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'           ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'   X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Players Left: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n  Everything is normal for ". TF::GOLD. $this->seconds2string($this->pvp).TF::GRAY.' minute(s).');
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'			 ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'   X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Jugadores Que Quedan: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n  Todo es normal por ". TF::GOLD. $this->seconds2string($this->pvp).TF::GRAY.' minuto(s)');
                    }
                }
                if($this->pvp === 0){
                    $this->plugin->status = UHC::STATUS_TP_1;
                    $this->pvp = 20 * 15;
                    $this->getOwner()->getDataFolder();
                }
            }
        
            public function handleTP1(){
                $this->tp1--;
                foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                    $x = round($player->x);
                    $y = round($player->y);
                    $z = round($player->z);
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'              ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'      X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Players Left: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n The first teleport will happen in ".TF::GOLD.$this->seconds2string($this->tp1).TF::GRAY.' minute(s).');
                        
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'              ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n".TF::RESET.TF::GRAY.'      X: '.TF::GOLD.$x.TF::GRAY.' Y: '.TF::GOLD.$y.TF::GRAY.' Z: '. TF::GOLD.$z.TF::GRAY." Jugadores Que Quedan: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n El primer teletransporte empezara en ".TF::GOLD.$this->seconds2string($this->tp1).TF::GRAY.' minuto(s).');
                    }
                }
                if($this->tp1 === 0) {
                    $this->plugin->status = UHC::STATUS_TELE_1;
                    $this->tp1 = 60 * 15;
                }
            }
        
            public function handleTele1(){
                $this->tele1--;
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . 'The first teleport is commencing...');
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'. TF::RESET . TF::GRAY . 'El primer teletransporte esta comenzando...');
                    }
                }
            }
            public function handleLast()
            {
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $x = round($player->x);
                    $y = round($player->y);
                    $z = round($player->z);
                    if($this->plugin->getLanguage($player) == "english"){
                        $player->sendPopup(TF::DARK_GRAY.'         ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n". TF::RESET . TF::GRAY . ' X: ' . TF::GOLD . $x . TF::GRAY . ' Y: ' . TF::GOLD . $y . TF::GRAY . ' Z: ' . TF::GOLD . $z . TF::GRAY . " Players Left: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n The last phase has begun, good luck!");
                    }
                    if($this->plugin->getLanguage($player) == "spanish"){
                        $player->sendPopup(TF::DARK_GRAY.'         ['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET."\n". TF::RESET . TF::GRAY . ' X: ' . TF::GOLD . $x . TF::GRAY . ' Y: ' . TF::GOLD . $y . TF::GRAY . ' Z: ' . TF::GOLD . $z . TF::GRAY . " Jugadores Que Quedan: ".TF::GOLD.count($this->plugin->queue).TF::GRAY." Kills: ".TF::GOLD.$this->plugin->kills[$player->getName()].TF::GRAY."\n La ultima pelea a comenzado, buena suerte!");
                    }
                }
            }
        
            //thankstosavion
            public function seconds2string($int) {
                $m = floor($int / 60);
                $s = floor($int % 60);
                return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . $s);
            }
        
            /**
             *
             */
            public function finalHeal() {
                $players = $this->plugin->getServer()->getOnlinePlayers();
                foreach ($players as $player) {
                    $effects = $player->getEffects();
                    foreach($effects as $effect){
                        if($effect->isBad()){
                            $player->removeEffect($effect);
                        }
                    }
                    $full = $player->getMaxHealth();
                    $player->setHealth($full);
                    $hunger = $player->getMaxFood();
                    $player->setFood($hunger);
                    $player->extinguish();
                }                if($this->plugin->getLanguage($player) == "english"){
                    $this->plugin->getServer()->broadcastMessage(TTF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' All players have been healed!');
                }
                if($this->plugin->getLanguage($player) == "spanish"){
                    $this->plugin->getServer()->broadcastMessage(TF::DARK_GRAY.'['.TF::BLUE."Eon".TF::WHITE."UHC".TF::DARK_GRAY.']'.TF::RESET.TF::GRAY.' Todos los jugadores han sido regenerados!');
                }
            }
        
            public function isTeamMate(Player $p1, Player $p2){
                $this->player = new Config($this->plugin->getDataFolder()."players/".strtolower($p1->getName()).".yml", Config::YAML);
                if($this->player->get("teammate") === strtolower($p2->getName())){
                    return true;
                }
                else {
                    return false;
                }
            }
            
            public function sendHealth(){
                foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                    $name = $player->getDisplayName();
                    $mh = round($player->getMaxHealth());
                    $h = round($player->getHealth());
                    $player->setNameTag(TF::GRAY.$name."\n  ".TF::GOLD.$h.TF::GRAY."|".TF::GOLD.$mh);
                }
            }
            
            public function isAtBorder(Player $player){
                switch($this->status){
                    case UHC::STATUS_GRACE:
                        if($player->x >= $this->border || $player->x <= -$this->border || $player->z >= $this->border || $player->z <= -$this->border ){
                            
                        }
                }
            }
        
            public function win(){
                if(count($this->plugin->queue) === 1){
                    if($this->plugin->status === UHC::STATUS_GRACE || $this->plugin->status === UHC::STATUS_PVP || $this->plugin->status === UHC::STATUS_TP_1 || $this->plugin->status === UHC::STATUS_TELE_1 || $this->plugin->status === UHC::STATUS_TP_2 || $this->plugin->status === UHC::STATUS_LAST) {
                        foreach ($this->getOwner()->getServer()->getOnlinePlayers() as $player) {
                            $this->plugin->status = UHC::STATUS_WAITING;
                            $this->player = new Config($this->plugin->getDataFolder() . "players/" . strtolower($this->plugin->queue[$player->getName()]) . ".yml");
                            $wins = $this->player->get("Wins");
                            $this->player->set("Wins", $wins + 1);
                            $this->player->save();
                            $level = $this->getOwner()->getServer()->getLevelByName("hubuhc");
                            $spawn = $level->getSafeSpawn();
                            $player->teleport($spawn);
                            $player->removeAllEffects();
                            $player->getInventory()->clearAll();
                            $player->setGamemode(0);
                        }
                    }
                }
            }
}
