<?php

namespace Richen\Economy;

use pocketmine\scheduler\CallbackTask;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\tile\Sign;

class Economy extends PluginBase implements Listener{
	
	private static $instance;
	
	public function onEnable()
	{	
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
		
		$this->config = new Config($this->getDataFolder() . "ecomoney.json", Config::JSON);
		$this->eshops = new Config($this->getDataFolder() . "ecoshops.json", Config::JSON);
		
		self::$instance = $this;
		
		/*foreach($this->eshops->getAll() as $k => $b){
			$this->eshops->remove($k);
		}*/
	}
	
	public function onDisable(){
		$this->config->save();
		$this->eshops->save();
	}
	
	public static function getInstance(){
		return self::$instance;
	}
	
	public function myMoney($name){
		$money = $this->config->get(strtolower($name));
		return $money;
	}
	
	public function addMoney($name, $add){
		if(($this->myMoney($name) + (int) $add) > 999999)
			$this->config->set(strtolower($name), 999999);
		else{
			$this->config->set(strtolower($name), $this->myMoney($name) + (int) $add);
		}
	}
	
	public function remMoney($name, $rem = 0){
		$this->config->set(strtolower($name), $this->myMoney($name) - (int) $rem);
	}
	
	public function onPreLogin(PlayerPreLoginEvent $event){
		$name = strtolower($event->getPlayer()->getName());
		
		if(!$this->config->exists($name))
			$this->config->set($name, 0);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		switch($cmd->getName()){
			case "money":
				if($sender instanceof Player)
					$sender->sendMessage("§8(§6Экономика§8) §eВаш баланс: §a{$this->myMoney($sender->getName())} $");
				break;
			
			case "pay":
				if(!$sender instanceof Player)
					return $sender->sendMessage("§cКомманда вводится только в игре");
				if(count($args) < 2)
					return $sender->sendMessage("§8(§6Экономика§8) §cИспользуйте: /pay [ник] [сумма]");
				if(!is_numeric($args[1]))
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма может быть только числом");
				if($args[1] <= 0)
					return $sender->sendMessage("§8(§6Экономика§8) §cИспользуйте только положительные числа не больше 9999999.");
				if(strtolower($sender->getName()) === strtolower($args[0]))
					return $sender->sendMessage("§8(§6Экономика§8) §cВы не можете отправить деньги себе");
				if(($player = $this->getServer()->getPlayer($args[0])) != null){
					if($this->myMoney($sender->getName()) < $args[1]){
						$sender->sendMessage("§8(§6Экономика§8) §cУ вас нету столько денег (§6{$args[1]}§c) для отправки!");
						$sender->sendMessage("§8(§6Экономика§8) §6Чтобы проверить баланс, используйте: §e/money.");
						return;
					}
					$sender->sendMessage("§8(§6Экономика§8) §eВы успешно отправили игроку §b{$player->getName()} §6{$args[1]} $");
					$player->sendMessage("§8(§6Экономика§8) §eИгрок §b{$sender->getName()} §eотправил вам §6{$args[1]} $");
					$this->addMoney($player->getName(), $args[1]);
					$this->remMoney($sender->getName(), $args[1]);
				}else{
					$sender->sendMessage("§8(§6Экономика§8) §cИгрока с ником §6{$args[0]} §cнет онлайн.");
				}
				break;
			
			case "addmoney":
				if(!$sender->isOp())
					return $sender->sendMessage("§8(§cСервер§8) §6У Вас нет прав, для выполнения данной команды.");
				
				if(count($args) < 2)
					return $sender->sendMessage("§8(§6Экономика§8) §cИспользуйте: /addmoney [ник] [сумма]");
				
				if(!is_numeric($args[1]))
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма может быть только числом");
				
				if($args[1] <= 0)
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма не может быть равна нулю или быть меньше нуля");
					
				if($args[1] > 999999)
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма не может быть больше 999999");
				
				if(strtolower($sender->getName()) === strtolower($args[0]))
					return $sender->sendMessage("§8(§6Экономика§8) §cВы не можете выдать деньги себе");
				
				if(($player = $this->getServer()->getPlayer($args[0])) != null){
					$sender->sendMessage("§8(§6Экономика§8) §eВы успешно выдали игроку §b{$player->getName()} §6{$args[1]} $");
					$player->sendMessage("§8(§6Экономика§8) §eВам было выдано §6{$args[1]} $");
					$this->addMoney($player->getName(), $args[1]);
				}else{
					$sender->sendMessage("§8(§6Экономика§8) §eВы успешно выдали игроку §b{$args[0]} §6{$args[1]} $");
					$this->addMoney($args[0], $args[1]);
				}
				break;
			
			case "seemoney":
				if(count($args) < 1)
					return $sender->sendMessage("§8(§6Экономика§8) §cИспользуйте: /seemoney [ник]");
				
				if(($player = $this->getServer()->getPlayer($args[0])) != null)
					$sender->sendMessage("§8(§6Экономика§8) §bБаланс игрока §e{$player->getName()}: §6{$this->myMoney($player->getName())} $");
				else{
					if($this->config->exists(strtolower($args[0])))
						$sender->sendMessage("§8(§6Экономика§8) §bБаланс игрока §e{$args[0]}: §6{$this->myMoney($args[0])} $");
					else{
						$sender->sendMessage("§8(§6Экономика§8) §cИгрок с ником §6{$args[0]} §cникогда не был на сервере");
					}
				}
				break;
				
			case "takemoney":
				if(!$sender->isOp())
					return $sender->sendMessage("§8(§cСервер§8) §6У Вас нет прав, для выполнения данной команды.");
				
				if(count($args) < 2)
					return $sender->sendMessage("§8(§6Экономика§8) §cИспользуйте: /takemoney [ник] [сумма]");
					
				if(!is_numeric($args[1]))
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма может быть только числом");
					
				if($args[1] <= 0)
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма не может быть равна нулю или быть меньше нуля");
				
				if($args[1] > 999999)
					return $sender->sendMessage("§8(§6Экономика§8) §cСумма не может быть больше 999999");
					
				if(strtolower($sender->getName()) === strtolower($args[0]))
					return $sender->sendMessage("§8(§6Экономика§8) §cВы не можете забрать деньги у себя");
				
				if(($player = $this->getServer()->getPlayer($args[0])) != null){
					if($this->myMoney($player->getName()) < $args[1]) $args[1] = $this->myMoney($player->getName());
					$sender->sendMessage("§8(§6Экономика§8) §eВы успешно забрали у игрока §b{$player->getName()} §6{$args[1]} $");
					$player->sendMessage("§8(§6Экономика§8) §eУ вас забрали §6{$args[1]} $");
					$this->remMoney($player->getName(), $args[1]);
				}else{
					if($this->myMoney($args[0]) < $args[1]) $args[1] = $this->myMoney($args[0]);
					$sender->sendMessage("§8(§6Экономика§8) §eВы успешно забрали у игрока §b{$args[0]} §6{$args[1]} $");
					$this->remMoney($args[0], $args[1]);
				}
				break;
			
			case "topmoney":
				$money = (new Config($this->getDataFolder() . "ecomoney.json", Config::JSON))->getAll();
				arsort($money);
				$p = [];
				$n = 0;
				foreach($money as $name => $moneys){
					$n++;
					$p[$n]["name"] = $name;
					$p[$n]["money"] = $moneys;
				}
				$sender->sendMessage(
					"§8[§6Экономика§8] §6Топ богатых по Кредитам:\n" .
					"§c#1 §e{$p[1]["name"]}§6: §d{$p[1]["money"]} §6кр. ¢\n" .
					"§c#2 §e{$p[2]["name"]}§6: §d{$p[2]["money"]} §6кр. ¢\n" .
					"§6#3 §e{$p[3]["name"]}§6: §d{$p[3]["money"]} §6кр. ¢\n" .
					"§6#4 §e{$p[4]["name"]}§6: §d{$p[4]["money"]} §6кр. ¢\n" .
					"§e#5 §e{$p[5]["name"]}§6: §d{$p[5]["money"]} §6кр. ¢\n" .
					"§e#6 §e{$p[6]["name"]}§6: §d{$p[6]["money"]} §6кр. ¢\n" .
					"§a#7 §e{$p[7]["name"]}§6: §d{$p[7]["money"]} §6кр. ¢\n"
				);
				break;
		}
	}
	
	public function createShop($coord, $data)
	{
		$this->eshops->set($coord['x'] . ":" . $coord['y'] . ":" . $coord['z'], $data);
	}
	
	public function isShop($coord)
	{
		$data = $this->eshops->get($coord['x'] . ":" . $coord['y'] . ":" . $coord['z']);
		
		if($data == null)
			return false;
		return true;
	}
	
	public function buyItem($player, $coord)
	{
		$data = $this->eshops->get($coord['x'] . ":" . $coord['y'] . ":" . $coord['z']);
		
		$price = $data['price'];
		$count = $data['count'];
		$id = $data['id'];
		$damage = $data['dmg'];
		
		if($this->myMoney($player->getName()) < $price)
			return $player->sendMessage("§8(§6Экономика§8) §cУ Вас недостаточно денег.");
		
		$item = Item::get($id, $damage, $count);
		$player->getInventory()->addItem($item);
		
		$this->remMoney($player->getName(), $price);
		$player->sendMessage("§8(§6Экономика§8) §eУспешная покупка предмета §6{$item->getName()} ({$count} шт.)");
	}
	
	public function removeShop($coord)
	{
		$this->eshops->remove($coord['x'] . ":" . $coord['y'] . ":" . $coord['z']);
	}
	
	public function makingShop(SignChangeEvent $event)
	{
		$player = $event->getPlayer();
		$lines = $event->getLines();
		
		if($lines[0] == "shop")
		{
			if(!$player->isOp() && !$player->hasPermission("shop.create")){
				return $player->sendMessage("§8(§6Экономика§8) §cВы не можете создавать магазин");
			}
			
			$item = explode(":", $lines[1]);
				$id    = $item[0];
				$dmg   = $item[1];
				$count = $item[2];
			$price = $lines[2];
			$name = $lines[3];
			$block = $event->getBlock();
			$coord = array(
				"x" => $block->getX(),
				"y" => $block->getY(),
				"z" => $block->getZ());
			$data = array(
				"id" => $id,
				"dmg" => $dmg,
				"count" => $count,
				"price" => $price
			);
			
			$this->createShop($coord, $data);
				
			$event->setLine(0,"§6• §eПокупка предмета §6•");
			$event->setLine(1,"§6".$lines[3]);
			$event->setLine(2,"§eЦена: §a".$lines[2]);
			$event->setLine(3,"§6• §e========== §6•");
			
			$player->sendMessage("§8(§6Экономика§8) §aВы создали магазин.");
		}
	}
	
	public function buyProcess(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		
		//if(!ServerAuth::getAPI()->isPlayerAuthenticated($player)) return;
		
		$coord = array(
			"x" => $block->getX(),
			"y" => $block->getY(),
			"z" => $block->getZ()
		);
		
		if(!$this->isShop($coord)) return;
		
		$this->buyItem($player, $coord);
	}
	
	public function onRemoveShop(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		
		//if(!ServerAuth::getAPI()->isPlayerAuthenticated($player)) return;
		
		$coord = array(
			"x" => $block->getX(),
			"y" => $block->getY(),
			"z" => $block->getZ()
		);
		
		if(!$this->isShop($coord)) return;
		
		if(!$player->isOp() && !$player->hasPermission("shop.remove")) return $event->setCancelled();
		
		$this->removeShop($coord);
		$player->sendMessage("§8(§6Экономика§8) §eМагазин удален.");
	}
}
