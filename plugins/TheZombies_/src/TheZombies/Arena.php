<?php

namespace TheZombies;

use pocketmine\level\Position;
use pocketmine\entity\Effect;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\{LevelEventPacket, LevelSoundEventPacket};
use pocketmine\item\Item;

class Arena
{
	const STATUS_WAITING = 0x01;
	const STATUS_START = 0x02;
	const STATUS_GAME = 0x03;
	const STATUS_RELOAD = 0x05;
	
	public $plugin, $status = self::STATUS_WAITING, $survivors = [], $zombies = [], $countdown = 30, $start = 20, $time = 300;
	
	public function __construct(Main $plugin, array $data){
		$this->plugin = $plugin;
		$this->arena = $data["arena"];
		$this->server = $data["server"];
		$this->min_players = $data["min_players"];
		$this->max_players = $data["max_players"];
		$this->lobby = $data["lobby"];
		$this->spawn = $data["spawn"];
		$this->world = $this->plugin->getServer()->getLevelByName($this->arena);
	}
	
	public function tick(){
		switch($this->status){
			case self::STATUS_WAITING:
			if(count($this->survivors) == 1){
				foreach($this->world->getPlayers() as $players){
					$right = "                                                                             ";
					$players->sendTip($right ."  §l§eTheZombies§r\n{$right}§7". date("d/m/y") ." §8{$this->server}\n\n{$right}§fИгроков: §a". count($this->survivors) ."/{$this->max_players}\n{$right}§cОжидание игроков\n\n{$right}§fАрена: §a{$this->arena}\n\n{$right}§eshop.gamewix.ru \n\n\n\n\n\n\n\n\n\n\n\n\n");
					$players->sendPopup("§eШанс стать §cЗомби §e- §7". $this->getChance());
					$players->setFood(20);
				}
				$this->countdown = 30;
			}elseif(count($this->survivors) >= $this->min_players){
				$this->countdown--;
				foreach($this->world->getPlayers() as $players){
					$right = "                                                                             ";
					$players->sendTip($right ."  §l§eTheZombies§r\n{$right}§7". date("d/m/y") ." §8{$this->server}\n\n{$right}§fИгроков: §a". count($this->survivors) ."/{$this->max_players}\n{$right}§fНачало через: §a". date("i:s", ($this->countdown)) ."\n\n{$right}§fАрена: §a{$this->arena}\n\n{$right}§eshop.gamewix.ru \n\n\n\n\n\n\n\n\n\n\n\n\n");
					$players->sendPopup("§eШанс стать §cЗомби §e- §7". $this->getChance());
					$players->setFood(20);
					if($this->countdown <= 30 and $this->countdown >= 6){
						$this->addSound($players, 71);
					}
					if($this->countdown <= 5 and $this->countdown >= 1){
						$players->addTitle("§c{$this->countdown}", "§eПриготовьтесь к битве!");
						$this->addSound($players, 72);
					}
					if($this->countdown == 0){
						$players->getInventory()->clearAll();
						$players->teleport(new Position($this->spawn["x"]+0.5, $this->spawn["y"], $this->spawn["z"]+0.5, $this->world), $this->spawn["yaw"], $this->spawn["pitch"]);
						$players->sendMessage("§aБитва началась! §7Скорее скройтесь от зомби!");
						$players->addTitle("§l§eTheZombies§r", "§7Битва началась!");
						$this->addGuardian($players);
						
						$this->status = self::STATUS_START;
					}
				}
			}
			break;
			case self::STATUS_START:
			if(count($this->survivors) == 0){
				foreach($this->world->getPlayers() as $players){
					$players->addTitle("§l§eTheZombies§r", "§fПобедила команда §cЗомби§f!");
					$players->sendMessage("§cБитва окончена! §aПобедила команда: §7Зомби§a!");
					$this->addGuardian($players);
				}
				$this->status = self::STATUS_RELOAD;
			}
			
			$this->start--;
			
			foreach($this->world->getPlayers() as $players){
				$right = "                                                                             ";
				$players->sendTip($right ."  §l§eTheZombies§r\n{$right}§7". date("d/m/y") ." §8{$this->server}\n\n{$right}§fВыживших: §a". count($this->survivors) ."\n{$right}§fЗомби: §c". count($this->zombies) ."\n\n{$right}§fАрена: §a{$this->arena}\n\n{$right}§fКонец через: §c". date("i:s", ($this->time)) ."\n\n{$right}§eshop.gamewix.ru \n\n\n\n\n\n\n\n\n\n\n\n\n");
				$players->sendPopup("§cСкорее скройтесь от зомби! (". date("i:s", ($this->start)) .")");
				$players->setFood(20);
				if($this->start == 15 or $this->start == 10 or $this->start <= 5 and $this->start >= 1){
					$this->addSound($players, 71);
					$players->sendMessage("§eЗомби появится через: §a{$this->start}с.");
				}
				if($this->start == 0){
					$this->start = 20;
					
					$this->addSound($players, 45);
					
					$key = array_rand($this->survivors);
					$this->zombies[$this->survivors[$key]] = $this->survivors[$key];
					unset($this->survivors[$key]);
					
					$players->sendMessage("§eВ зомби превратился игрок §c{$key}§e!");
					foreach($this->zombies as $name){
						$zombie = $this->plugin->getServer()->getPlayer($name);
						$zombie->setNameTag("§c". $zombie->getName());
						$zombie->addTitle("§l§eTheZombies§r", "§7Вы превратились в §сЗомби§7!");
						$zombie->sendMessage("§eВы превратились в §cЗомби§e!");
						$zombie->addEffect(Effect::getEffect(1)->setDuration(9999999)->setVisible(false));
						$this->addGuardian($zombie);
					}
					$this->status = self::STATUS_GAME;
				}
			}
			break;
			case self::STATUS_GAME:
			if(count($this->survivors) == 0){
				foreach($this->world->getPlayers() as $players){
					$players->addTitle("§l§eTheZombies§r", "§fПобедила команда §cЗомби§f!");
					$players->sendMessage("§cИгра окончена! §aПобедила команда: §7Зомби§a!");
					$this->addGuardian($players);
				}
				$this->status = self::STATUS_RELOAD;
			}elseif(count($this->zombies) == 0){
				foreach($this->world->getPlayers() as $players){
					$players->addTitle("§l§eTheZombies§r", "§fПобедила команда §aВыживших§f!");
					$players->sendMessage("§cИгра окончена! §aПобедила команда: §7Выживших§a!");
					$this->addGuardian($players);
				}
				$this->status = self::STATUS_RELOAD;
			}elseif($this->time == 0 && count($this->survivors) <= 1){
				foreach($this->world->getPlayers() as $players){
					$players->addTitle("§l§eTheZombies§r", "§fПобедила команда §aВыживших§f!");
					$players->sendMessage("§cИгра окончена! §aПобедила команда: §7Выживших§a!");
					$this->addGuardian($players);
				}
				$this->status = self::STATUS_RELOAD;
			}
			
			$this->time--;
			
			foreach($this->world->getPlayers() as $players){
				$right = "                                                                             ";
				$players->sendTip($right ."  §l§eTheZombies§r\n{$right}§7". date("d/m/y") ." §8{$this->server}\n\n{$right}§fВыживших: §a". count($this->survivors) ."\n{$right}§fЗомби: §c". count($this->zombies) ."\n\n{$right}§fАрена: §a{$this->arena}\n\n{$right}§fКонец через: §c". date("i:s", ($this->time)) ."\n\n{$right}§eshop.gamewix.ru \n\n\n\n\n\n\n\n\n\n\n\n\n");
				$players->setFood(20);
			}
			break;
			case self::STATUS_RELOAD:
			foreach($this->world->getPlayers() as $players){
				$this->removePlayer($players, "respawn");
			}
			
			$this->plugin->loadArena($this->arena);
			break;
		}
	}
	
	public function addPlayer(Player $player){
		$this->survivors[$player->getName()] = $player->getName();
		
		$player->addTitle("§l§eTheZombies§r", "§fАрена: §a". $this->arena);
		$player->sendMessage("§aПодключение к серверу §7{$this->server}§a!");
		
		$player->setFood(20);
		$player->setMaxHealth(1);
		$player->setHealth(1);
		$player->removeAllEffects();
		$player->getInventory()->clearAll();
		$player->setGamemode(Player::ADVENTURE);
		$player->setNameTag("§a". $player->getName());
		$player->getInventory()->setItem(8, Item::get(120, 0, 1)->setCustomName("§r§bВыйти в лобби"));
		$player->addEffect(Effect::getEffect(16)->setDuration(9999999)->setVisible(false));
		
		$player->teleport(new Position($this->lobby["x"]+0.5, $this->lobby["y"], $this->lobby["z"]+0.5, $this->world), $this->lobby["yaw"], $this->lobby["pitch"]);
		
		$this->addSound($player, 71);
		
		foreach($this->world->getPlayers() as $players){
			$players->sendMessage("§eИгрок §7{$player->getName()} §eприсоединился к битве! §e(§b". count($this->survivors) ."§e/§b12§e)!");
		}
	}
	
	public function removePlayer(Player $player, string $reason){
		if(array_key_exists($player->getName(), $this->survivors)){
			unset($this->survivors[$player->getName()]);
		}elseif(array_key_exists($player->getName(), $this->zombies)){
			unset($this->zombies[$player->getName()]);
		}
		
		if(!is_null($player)){
			$player->setFood(20);
			$player->setMaxHealth(20);
			$player->setHealth(20);
			$player->removeAllEffects();
			$player->getInventory()->clearAll();
			$player->setGamemode(Player::ADVENTURE);
			$player->setNameTag("§f". $player->getName());
			$player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
		}
		
		if($reason == "quit"){
			foreach($this->world->getPlayers() as $players){
				$players->sendMessage("§eИгрок §7{$player->getName()} §eпокинул битву! §e(§b". count($this->survivors) ."§e/§b12§e)!");
			}
		}
	}
	
	public function inArena(Player $player){
		if(array_key_exists($player->getName(), $this->survivors)){
			return 1;
		}elseif(array_key_exists($player->getName(), $this->zombies)){
			return 2;
		}else{
			return 0;
		}
	}
	
	public function addSound(Player $player, int $id){
		$pk = new LevelSoundEventPacket();
		$pk->sound = $id;
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$player->dataPacket($pk);
	}
	
	public function addGuardian(Player $player){
		$pk = new LevelEventPacket();
		$pk->evid = LevelEventPacket::EVENT_GUARDIAN_CURSE;
		$pk->data = 0;
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$player->dataPacket($pk);
	}
	
	public function getChance(){
		$result = round((100 / count($this->survivors)), 1);
		
		return $result;
	}
}
