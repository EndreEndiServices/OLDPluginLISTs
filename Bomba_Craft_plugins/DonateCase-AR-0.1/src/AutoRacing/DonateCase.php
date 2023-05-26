<?php
namespace AutoRacing;

use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\entity\Item as EI;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;
use pocketmine\scheduler\PluginTask;
use pocketmine\level\Position;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\DustParticle;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class DonateCase extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("DonateCase-AR Загружен!");
		if(!is_dir($this->getDataFolder()))
			@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, ['x' => 0, 'y' => 0, 'z' => 0]);
		$this->list = new Config($this->getDataFolder()."list.yml", Config::YAML);
		$this->check = true;
		$this->stop = false;
		$this->step = [0.75, 1.5, 2.25, 3, 3.75, 4.5, 5.25];
		$this->PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if(strtolower($command) == "givecase") {
			if(!$sender->isOp()) {
				return;
			}
			if(isset($args[0])) {
				if(isset($args[1])){
					$c = $args[1];
				}
				if(empty($this->list->get(strtolower($args[0])))) {
					$count = 0 + $c;
				}else{
					$count = $this->list->get(strtolower($args[0])) + $c;
				}
				$this->list->set(strtolower($args[0]), $count);
				$this->list->save();
				$sender->sendMessage('Добавлен кейс игроку '.$args[0]);
			}
		}
	}

	public function onInteract(PlayerInteractEvent $e) {
		$b = $e->getBlock();
		$p = $e->getPlayer();
		$coord = $this->config->getAll();
		if($b->x == $coord['x'] && $b->y == $coord['y'] && $b->z == $coord['z']) {
			$e->setCancelled(true);
			if(!$this->check) {
				$p->sendMessage('§7[§aДонат-кейс§7] §fКейс занят! Дождитесь своей очереди!');
				return;
			}
			if(empty($this->list->get(strtolower($p->getName())))) {
				$p->sendMessage('§7[§aДонат-кейс§7] §fУ вас нет кейсов для открытия!');
				return;
			}
			$this->check = false;
			$count = $this->list->get(strtolower($p->getName())) - 1;
			$this->list->set(strtolower($p->getName()), $count);
			$this->list->save();
			$p->sendPopup('§fОткрываем!');
			$this->stop = false;
			$color = [1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f'];
			shuffle($color);
			$wins = ['Вип','Премиум','Креатив','Админ','Модератор','Оператор','100к$','Основатель','Владелец'];
			shuffle($wins);
			$this->win = $wins;
			array_shift($this->win);
			foreach ($wins as $key => $value) {
				$wins[$key] = '§'.$color[$key].$wins[$key];
			}
			$this->wins = $wins;
			$this->createFloatingText(array_shift($this->wins), Entity::$entityCount++, $b->x, $b->y, $b->z, $p->getLevel(), $p, $b, true);
		}
	}

	public function sendPacket($packet, $level = false, $player = false) {
		if($level != false) {
			foreach($level->getPlayers() as $player){
				$player->dataPacket($packet);
			}
			return;
		}
		elseif($player) {
			if(is_string($player))
				$player = $this->getServer()->getPlayer($player);
			if($player instanceof Player) {
				$player->dataPacket($packet);
				return;
			}
		}
	}

	public function createFloatingText($text, $eid, $x, $y, $z, $level = false, $player = false, $b, $one) {
		$pk = new AddEntityPacket();
		$pk->eid = $eid;
		$pk->type = EI::NETWORK_ID;
		$pk->x = $x + 0.6;
		$pk->y = $y - 1;
		$pk->z = $z + 0.5;
		$pk->speedX = 0; 
		$pk->speedY = 0; 
		$pk->speedZ = 0; 
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->item = 0;
		$pk->meta = 0;
		$flags  = 0;
		$flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
		$flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
		$pk->metadata = [
		Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
		Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]
		];
		$this->sendPacket($pk, $level, $player);
		$this->getServer()->getScheduler()->scheduleDelayedTask(new Rotat($this, $pk, $player, 0, $b, $x, $y, $z, $one), 1);
	}
}

class Rotat extends PluginTask{

	public function __construct($plugin, $pk, $player, $i, $b, $x, $y, $z, $one){
		$this->plugin = $plugin;
		parent::__construct($plugin, $pk, $player, $i, $b, $x, $y, $z, $one);
		$this->pk = $pk;
		$this->player = $player;
		$this->i = $i;
		$this->b = $b;
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->one = $one;
	}

	public function onRun($tick){
		if($this->i == 30) {
			$this->plugin->stop = true;
			$this->player->sendMessage('§7[§aДонат-кейс§7] §fВы выиграли '.$this->plugin->wins[1].'§f. Спасибо за игру!');
			$center = new Vector3($this->x, $this->y, $this->z);
			$radius = 2.0;
			$count = 300;
			$r = mt_rand(0, 300);
			$g = mt_rand(0, 300);
			$b = mt_rand(0, 300);
			$center = new Vector3($this->x + 0.5, $this->y + 1, $this->z + 0.5);
			$particle = new DustParticle($center, $r, $g, $b);
			for ($i = 0; $i < $count; $i++) { 
				$pitch = (mt_rand() / mt_getrandmax() - 0.5) * M_PI;
				$yaw = mt_rand() / mt_getrandmax() * 2 * M_PI;
				$y = -sin($pitch);
				$delta = cos($pitch);
				$x = -sin($yaw) * $delta;
				$z = cos($yaw) * $delta;
				$v = new Vector3($x, $y + 0.5, $z);
				$p = $center->add($v->normalize()->multiply($radius));
				$particle->setComponents($p->x, $p->y, $p->z);
				$this->player->getLevel()->addParticle($particle);
			}
			$this->plugin->check = true;
			if($this->plugin->win[1] == '100к$') {
				EconomyAPI::getInstance()->addMoney($this->player, 100000);
			}else{
				$this->plugin->PurePerms->getUserDataMgr()->setGroup($this->player, $this->plugin->PurePerms->getGroup($this->plugin->win[1]), null);
			}
		}
		if($this->plugin->stop) {
			$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new Delete($this->plugin, $this->pk, $this->player), 20 * mt_rand(2.1,2.9));
			return;
		}
		if($this->one) {
			foreach ($this->plugin->step as $key => $value) {
				if($this->i == $value) {
					$this->plugin->createFloatingText($this->plugin->wins[$key], Entity::$entityCount++, $this->x, $this->y, $this->z, $this->player->getLevel(), $this->player, $this->b, false);
				}else{
					continue;
				}
			}
		}
		if(($this->i - floor($this->i)) == 0) {
			$center = new Vector3($this->x + 0.5, $this->y + 2, $this->z + 0.5);
			$particle = new HeartParticle($center);
			$this->player->getLevel()->addParticle($particle);
		}
		$k = 0.25;
		$this->i += 0.25;
		$pk = new MoveEntityPacket();
		$pk->eid = $this->pk->eid;
		$pk->x = $this->pk->x;
		$pk->y = $this->pk->y + sin($this->i)*($k);
		$pk->z = $this->pk->z + cos($this->i)*($k);
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->headYaw = 0;
		$this->plugin->sendPacket($this->pk, $this->player->getLevel(), $this->player);
		$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new Rotat($this->plugin, $pk, $this->player, $this->i, $this->b, $this->x, $this->y, $this->z, $this->one), 1);
	}
}

class Delete extends PluginTask{

	public function __construct($plugin, $pk, $player){
		$this->plugin = $plugin;
		parent::__construct($plugin, $pk, $player);
		$this->pk = $pk;
		$this->player = $player;
	}

	public function onRun($tick){
		$pk = new RemoveEntityPacket();
		$pk->eid = $this->pk->eid;
		$this->plugin->sendPacket($pk, $this->player->getLevel(), $this->player);
	}
}