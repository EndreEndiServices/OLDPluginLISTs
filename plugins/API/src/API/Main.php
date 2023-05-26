<?php

namespace API;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\event\block\{BlockBreakEvent, BlockPlaceEvent};
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\{PlayerInteractEvent, PlayerJoinEvent, PlayerQuitEvent, PlayerChatEvent};
use pocketmine\event\inventory\{InventoryClickEvent, InventoryCloseEvent};
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\inventory\WindowInventory;
use pocketmine\network\mcpe\protocol\{AddEntityPacket, RemoveEntityPacket, LevelSoundEventPacket};
use pocketmine\entity\{Entity, Vindicator};
use pocketmine\level\sound\{MinecraftSound, BlazeShootSound};
use pocketmine\nbt\tag\{CompoundTag, ListTag, DoubleTag, FloatTag};

use pocketmine\scheduler\PluginTask;

use pocketmine\command\{Command, CommandSender};

class Main extends PluginBase implements Listener
{
	public $until = 0, $msgcount = 0;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		foreach($this->getServer()->getLevels() as $level){
			$level->setTime(3000);
			$level->stopTime();
		}

		$this->main1 = new FloatingTextParticle(new Vector3(995.5, 35, 868.5), "", "");
		$this->main2 = new FloatingTextParticle(new Vector3(995.5, 34.5, 868.5), "", "");
		$this->main3 = new FloatingTextParticle(new Vector3(995.5, 34, 868.5), "", "");
		$this->main4 = new FloatingTextParticle(new Vector3(995.5, 33.5, 868.5), "", "");

		$this->play = new FloatingTextParticle(new Vector3(995.5, 36.5, 851.5), "", "");
		$this->play_online = new FloatingTextParticle(new Vector3(995.5, 36, 851.5), "", "");

		$this->getServer()->getScheduler()->scheduleRepeatingTask(new FloatingTexts($this), 65);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new Players($this), 18);

		$this->getServer()->getCommandMap()->register("npc", new NpcCommand($this));
	}

	public function onBreak(BlockBreakEvent $ev){
		if(!($ev->getPlayer()->isOp())){
			$ev->setCancelled();
		}
	}

	public function onPlace(BlockPlaceEvent $ev){
		if(!($ev->getPlayer()->isOp())){
			$ev->setCancelled();
		}
	}

	public function onDamage(EntityDamageEvent $ev){
		$ev->setCancelled();
		if($ev->getCause() == EntityDamageEvent::CAUSE_VOID){
			$ev->getEntity()->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
		}
	}

	public function onClick(PlayerInteractEvent $ev){
		$ev->setCancelled();
	}

	public function onInventory(InventoryClickEvent $ev){
		if(!$ev->getPlayer()->isOp() && $ev->getPlayer()->getGamemode() != 1){
			$ev->setCancelled();
		}
	}

	public function onJoin(PlayerJoinEvent $ev){
		$ev->setJoinMessage(null);

		$p = $ev->getPlayer();

		$p->setFood(20);
		$p->setHealth(20);
		$p->removeAllEffects();
		$p->setAllowFlight(false);
		$p->getInventory()->clearAll();
		$p->setGamemode(Player::ADVENTURE);

		$p->getInventory()->setItem(1, Item::get(137, 0, 1)->setCustomName("§r§aИграть!"));
		$p->getInventory()->setItem(4, Item::get(288, 0, 1)->setCustomName("§r§bВолшебное перо"));
		$p->getInventory()->setItem(7, Item::get(351, 8, 1)->setCustomName("§r§cСкрыть игроков"));
		$p->getInventory()->setItem(8, Item::get(399, 0, 1)->setCustomName("§r§ecorbium.net"));

		$colors = array("§b", "§a", "§6", "§c", "§d");

		$p->addTitle($colors[array_rand($colors)] ."Corbium Network§r", "§aДобро пожаловать в §bHUB§a!");

		$p->getLevel()->addSound((new MinecraftSound(new Vector3($p->x, $p->y, $p->z), "mob.wither.ambient")), [$p]);

		if($p->isOp()){
			$p->setNameTag("§l§cOWNER §r§7". $p->getName());
			$p->setDisplayName("§c". $p->getName());

			$p->setAllowFlight(true);

			$this->getServer()->broadcastMessage("§l§cOWNER §r§7". $p->getName() ." §eприсоединился к серверу!");
		}else{
			$p->setNameTag("§7". $p->getName());
			$p->setDisplayName("§7". $p->getName());

			$this->getServer()->broadcastPopup("§a+ §7". $p->getName());
		}
	}

	public function onQuit(PlayerQuitEvent $ev){
		$ev->setQuitMessage(null);

		if($ev->getPlayer()->isOp()){
			$this->getServer()->broadcastMessage("§l§cOWNER §r§7". $ev->getPlayer()->getName() ." §eпокинул сервер!");
		}else{
			$this->getServer()->broadcastPopup("§c- §7". $ev->getPlayer()->getName());
		}
	}

	public function onChat(PlayerChatEvent $ev){
		return $ev->setFormat($ev->getPlayer()->getNameTag() ."§7: §f". $ev->getMessage());
	}

	public function inPlay(Player $p){
		$window = new class($p, "Куда пойдем играть? :D") extends WindowInventory{
			function __construct($p, string $customName = ""){
				parent::__construct($p, $customName);

				$this->p = $p;

				$pk = new AddEntityPacket();
				$pk->entityRuntimeId = $p->getId() * 8;
				$pk->type = 15;
				$pk->metadata = [Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]];
				$pk->x = $this->p->x; $pk->y = $this->p->y; $pk->z = $this->p->z;

				$this->p->dataPacket($pk);
			}
		};

		$window->setItem(0, Item::get(368, 0, 1)->setCustomName("§r§eSkyWars")->setLore(["", "§r§7Захватывающая битва на островах", "§r§7где вам нужно выжить последним", "§r§7чтобы выиграть игру!"]));
		$p->addWindow($window);

		$p->getLevel()->broadcastLevelSoundEvent(new Vector3($p->x, $p->y, $p->z), LevelSoundEventPacket::SOUND_DROP_SLOT);
	}

	public function inMenu(InventoryClickEvent $ev){
		if($ev->getInventory() instanceof WindowInventory){
			$ev->setCancelled();
			$ev->getInventory()->close($ev->getPlayer());
			if($ev->getItem()->getId() == 368 && $ev->getItem()->getCustomName() == "§r§eSkyWars"){
				$ev->getPlayer()->sendMessage("§cСервер находится в разработке!");
				$ev->getPlayer()->getLevel()->broadcastLevelSoundEvent(new Vector3($ev->getPlayer()->x, $ev->getPlayer()->y, $ev->getPlayer()->z), LevelSoundEventPacket::SOUND_CHEST_CLOSED);
			}
		}
	}

	public function closeMenu(InventoryCloseEvent $ev){
		if($ev->getInventory() instanceof WindowInventory){
			$pk = new RemoveEntityPacket();
			$pk->entityUniqueId = $ev->getPlayer()->getId() * 8;
			$ev->getPlayer()->dataPacket($pk);
		}
	}

	public function onClicked(PlayerInteractEvent $ev){
		$p = $ev->getPlayer();

		if($ev->getItem()->getId() == 137 && $ev->getItem()->getCustomName() == "§r§aИграть!"){
			$this->inPlay($p);
		}

		if($ev->getItem()->getId() == 288 && $ev->getItem()->getCustomName() == "§r§bВолшебное перо"){
			if($p->getLevel()->getBlock($p->floor()->subtract(0, 1))->getId() != 0){
				$p->setMotion($p->getDirectionVector()->multiply(0.8));
				$p->getLevel()->addSound((new BlazeShootSound($p)), [$p]);
			}
		}

		if($ev->getItem()->getId() == 351 && $ev->getItem()->getCustomName() == "§r§cСкрыть игроков"){
			foreach($this->getServer()->getOnlinePlayers() as $pl){
				$p->hidePlayer($pl);
			}

			$p->getInventory()->setItemInHand(Item::get(351, 10, 1)->setCustomName("§r§bПоказать игроков"));

			$p->sendMessage("§aВы успешно скрыли игроков!");

			$p->getLevel()->broadcastLevelSoundEvent(new Vector3($p->x, $p->y, $p->z), LevelSoundEventPacket::SOUND_DROP_SLOT);
		}

		if($ev->getItem()->getId() == 351 && $ev->getItem()->getCustomName() == "§r§bПоказать игроков"){
			foreach($this->getServer()->getOnlinePlayers() as $pl){
				$p->showPlayer($pl);
			}

			$p->getInventory()->setItemInHand(Item::get(351, 8, 1)->setCustomName("§r§cСкрыть игроков"));

			$p->sendMessage("§aВы успешно показали игроков!");

			$p->getLevel()->broadcastLevelSoundEvent(new Vector3($p->x, $p->y, $p->z), LevelSoundEventPacket::SOUND_DROP_SLOT);
		}

		if($ev->getItem()->getId() == 399 && $ev->getItem()->getCustomName() == "§r§ecorbium.net"){
			$p->sendMessage("§eПокупка рангов, монеток, и прочее только на сайте §7- §6shop.corbium.net");
		}
	}
}

class FloatingTexts extends PluginTask
{
	public $pg;

	public function __construct(Main $pg){
		$this->pg = $pg;
		parent::__construct($pg);
	}

	public function onRun($tick){
		$colors = array("§b", "§a", "§6", "§c", "§d");

		$this->pg->main1->setTitle($colors[array_rand($colors)] ."§lCorbium Network");
		$this->pg->main2->setTitle("§eДобро пожаловать в §aHUB§e!");
		$this->pg->main3->setTitle("§eОфициальный сайт - §bcorbium.net");
		$this->pg->main4->setTitle("§aЖелаем удачи в победах!");

		$this->pg->play->setTitle("§eSkyWars");
		$this->pg->play_online->setTitle("§fСтатус: §cВ разработке");

		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->main1);
		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->main2);
		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->main3);
		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->main4);

		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->play);
		$this->pg->getServer()->getDefaultLevel()->addParticle($this->pg->play_online);
	}
}

class Players extends PluginTask
{
	public $pg;

	public function __construct(Main $pg){
		$this->pg = $pg;
		parent::__construct($pg);
	}

	public function until(){
		$this->pg->until++;

		if($this->pg->until == 13){ 
			$this->pg->until = 0;
		}

		if($this->pg->until == 0) return "§l§eCorbium§r";
		if($this->pg->until == 1) return "§l§6C§eorbium§r";
		if($this->pg->until == 2) return "§l§aC§6o§erbium§r";
		if($this->pg->until == 3) return "§l§6C§ao§6r§ebium§r";
		if($this->pg->until == 4) return "§l§eC§6o§ar§6b§eium§r";
		if($this->pg->until == 5) return "§l§eCo§6r§ab§6i§eum§r";
		if($this->pg->until == 6) return "§l§eCor§6b§ai§6u§em§r";
		if($this->pg->until == 7) return "§l§eCorb§6i§au§6m§r";
		if($this->pg->until == 8) return "§l§eCorbi§6u§am§r";
		if($this->pg->until == 9) return "§l§eCorbiu§6m§r";
		if($this->pg->until == 10) return "§l§eCorbium§r";
		if($this->pg->until == 11) return "§l§6Corbium§r";
		if($this->pg->until == 12) return "§l§aCorbium§r";
	}

	public function onRun($tick){
		foreach($this->pg->getServer()->getOnlinePlayers() as $p){
			$p->setFood(20);

			//hb
			$right = "                                                                      ";
			$p->sendTip($right ."  ". $this->until() ."\n". $right ."§7". date("d/m/y") ." §aHUB-1\n\n". $right ."§bВыберите сервер!\n\n". $right ."§fОнлайн в хабе: §a". count($this->pg->getServer()->getOnlinePlayers()) ."\n\n". $right ."§ewww.corbium.net \n\n\n\n\n\n\n\n\n\n\n\n\n");
		}
	}
}

class NpcCommand extends Command
{
	public $pg;

	public function __construct(Main $pg){
		$this->pg = $pg;
		parent::__construct("npc", "создать существо");
	}

	public function execute(CommandSender $p, $alias, array $args){
		if($p->isOp()){
			$nbt = new CompoundTag("", [
				"Pos" => new ListTag("Pos", [
					new DoubleTag("", $p->getX()),
					new DoubleTag("", $p->getY()),
					new DoubleTag("", $p->getZ())
				]),
				"Motion" => new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				"Rotation" => new ListTag("Rotation", [
					new FloatTag("", $p->getYaw()),
					new FloatTag("", $p->getPitch())
				])
			]);

			$mob = Entity::createEntity(Vindicator::NETWORK_ID, $p->level, $nbt);
			$mob->setNameTag("sw");

			$mob->spawnToAll();

			$p->sendMessage("§aУспешно заспавнен!");
		}else{
			$p->sendMessage("§cНедостаточно прав!");
		}
	}
}
