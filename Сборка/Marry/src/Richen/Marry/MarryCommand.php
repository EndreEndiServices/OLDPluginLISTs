<?php

namespace Richen\Marry;

use Richen\Marry\Marry;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\level\particle\HeartParticle;
use pocketmine\math\Vector3;

class MarryCommand extends Marry
{
	private $request = [];

	public function __construct(Marry $plugin){
		$this->plugin = $plugin;
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(strtolower($command->getName()) == "marry") 
		{
			$config = $this->plugin->config;
			
			switch(count($args))
			{
				case 0:
					$sender->sendMessage("§8[§cСвадьбы§8] §6Доступные команды:");
					$sender->sendMessage("§a- /marry <ник>§f: предложить игроку семейную жизнь");
					$sender->sendMessage("§a- /marry yes/no§f: принять/отклонить предложение");
					$sender->sendMessage("§a- /marry kiss§f: поцеловать супруга(у)");
					$sender->sendMessage("§a- /marry love§f: проявлять свою любовь");
					$sender->sendMessage("§a- /marry tp§f: телепортация к супругу(е)");
					$sender->sendMessage("§a- /marry sethome§f: установить общий дом");
					$sender->sendMessage("§a- /marry home§f: телепортация в супружеский дом");
					$sender->sendMessage("§a- /marry divorce§f: расстаться с игроком");
				break;
				
				case 1:
				$name = strtolower($sender->getName());
				switch(strtolower($args[0])){

				case 'help':
					$sender->sendMessage("§8[§cСвадьбы§8] §6Доступные команды:");
					$sender->sendMessage("§a- /marry <ник>§f: предложить игроку семейную жизнь");
					$sender->sendMessage("§a- /marry yes/no§f: принять/отклонить предложение");
					$sender->sendMessage("§a- /marry kiss§f: поцеловать супруга(у)");
					$sender->sendMessage("§a- /marry love§f: проявлять свою любовь");
					$sender->sendMessage("§a- /marry tp§f: телепортация к супругу(е)");
					$sender->sendMessage("§a- /marry sethome§f: установить общий дом");
					$sender->sendMessage("§a- /marry home§f: телепортация в супружеский дом");
					$sender->sendMessage("§a- /marry divorce§f: расстаться с игроком");
				break;
				
				case "love":
				case "l":
					for($i = 1; $i <= 15; $i++) {
					$particle = new HeartParticle(new Vector3($sender->getX() + mt_rand(0, 0.55), $sender->getY() + 2, $sender->getZ() + mt_rand(0, 0.55)), 0);
					$sender->getLevel()->addParticle($particle);
				}
				break;
					case "sethome":
					foreach($this->plugin->families as $id => $family)
					{
						if($name == $family["first"] || $name == $family["second"]) 
						{
							$this->plugin->families[$id]["home"] = [
								"x" => round($sender->getX()),
								"y" => round($sender->getY()),
								"z" => round($sender->getZ()),
								"level" => $sender->getLevel()->getName()
							];
							$this->plugin->save();
							$sender->sendMessage("§8[§cСвадьбы§8] §f§dОбщий дом создан");
							$p = $name == $family["first"]? $family["second"] : $family["first"];
							$p = $this->plugin->getServer()->getPlayer($p);
							if($p !== null)
								$p->sendMessage("§8[§cСвадьбы§8] §f§dОбщий дом создан");
							return true;
						}
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§7Ты одинок :C");
				break;
				
				case "home":
					foreach($this->plugin->families as $family)
					{
						if($name == $family["first"] || $name == $family["second"])
						{
							if($family["home"] !== false)
							{
								$sender->teleport(new Position($family["home"]["x"], $family["home"]["y"], $family["home"]["z"], $this->plugin->getServer()->getLevelByName($family["home"]["level"])));
								$sender->sendMessage("§8[§cСвадьбы§8] §f§aТы телепортирован в общий дом");
							}
							else $sender->sendMessage("§8[§cСвадьбы§8] §f§eУ вас нет общего дома...установите его: §d/marry sethome §e!");
							return true;
						}
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§7Ты одинок :C");
				break;
					case "tp":
					foreach($this->plugin->families as $family)
					{
						if($name == $family["first"] || $name == $family["second"])
						{
							$p = $name == $family["first"]? $family["second"] : $family["first"];
							$p = $this->plugin->getServer()->getPlayer($p);
							if($p !== null)
							{
								$x = round($p->getX());
								$y = round($p->getY());
								$z = round($p->getZ());
								$sender->teleport(new Position($x, $y, $z, $p->getLevel()));
								$sender->sendMessage("§8[§cСвадьбы§8] §f§aТы телепортированы к своей половинке :3");
								$p->sendMessage("§8[§cСвадьбы§8] §f§aК тебе телепортировалась твоя вторая половинка :3");
							} else $sender->sendMessage("§8[§cСвадьбы§8] §f§cИгрока нет в сети");
							return true;
						}
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§7Ты одинок :C");
				break;
				
				case "kiss":
					foreach($this->plugin->families as $family)
					{
						if($name == $family["first"] || $name == $family["second"])
						{
							$p = $name == $family["first"]? $family["second"] : $family["first"];
							$p = $this->plugin->getServer()->getPlayer($p);
							if($p !== null)
							{
								$x = round($p->getX());
								$y = round($p->getY());
								$z = round($p->getZ());
								$sender->teleport(new Position($x, $y, $z, $p->getLevel()));
								$sender->sendMessage("§8[§cСвадьбы§8] §fВы §aпоцеловали §fсупругу(а)!");
								$p->sendMessage("§8[§cСвадьбы§8] §fВаш(а) супруг(а) §aпоцеловал(а) §fВас!");
								for($i = 1; $i <= 15; $i++) {
									$particle = new HeartParticle(new Vector3($sender->getX() + mt_rand(0, 0.55), $sender->getY() + 2, $sender->getZ() + mt_rand(0, 0.55)), 0);
									$sender->getLevel()->addParticle($particle);
								}
							} else $sender->sendMessage("§8[§cСвадьбы§8] §f§cИгрока нет в сети");
							return true;
						}
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§7Ты одинок :C");
				break;
				
				case "divorce":
					foreach($this->plugin->families as $id => $family)
					{
						if($name == $family["first"] || $name == $family["second"])
						{
							unset($this->plugin->families[$id]);
							$p = $name == $family["first"] ? $family["second"] : $family["first"];
							$sender->sendMessage(str_replace("{player}", $p, "§8[§cСвадьбы§8] §f§4Ты развелся с игроком {player}"));
							$p = $this->plugin->getServer()->getPlayer($p);
							if($p instanceof Player)
								$p->sendMessage(str_replace("{player}", $sender->getName(), "§8[§cСвадьбы§8] §f§4Игрок {player} бросил тебя D':"));
							$this->plugin->save();
							return true;
						} 
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§7Ты одинок :C");
				break;
				
				case "accept":
				case "yes":
				case "ac":
					foreach($this->request as $player => $req)
					{
						if($name == $player)
						{
							$p = $this->plugin->getServer()->getPlayer($req);
							/*if($this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($p) < $config["MoneyToMarry"])
							{
								if($p !== null)
									$p->sendMessage("§8[§cСвадьбы§8] §f§cУ тебя недостаточно денег :с");
								$sender->sendMessage(str_replace("{player}", $req, "§8[§cСвадьбы§8] §f§eУ {player} недостаточно денег :("));
								return true;
							}*/
							//$this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->reduceMoney($config["MoneyToMarry"]);
							$this->plugin->families[] = [
								"first" => $req,
								"second" => $name,
								"home" => false
							];
							$sender->sendMessage(str_replace("{player}", $p->getName(), "§8[§cСвадьбы§8] §f§aТеперь Ты и §d{player} §a- семья :3 §c♥"));
							if($p !== null)
								$p->sendMessage(str_replace("{player}", $sender->getName(), "§8[§cСвадьбы§8] §f§aТеперь Ты и §d{player} §a- семья :3 §c♥"));
							unset($this->request[$name]);
							$this->plugin->save();
							$this->plugin->getServer()->broadcastMessage(str_replace(["{first}", "{second}"], [$sender->getName(), $p->getName()], "§8[§cСвадьбы§8] §f§aИгроки §d{first} §aи §d{second} §aтеперь пара! §lПоздравим их! §c♥"));
							return true;
						}
					}
					$sender->sendMessage("§8[§cСвадьбы§8] §f§eУ тебя нет входящих предложений");
				break;

				case "deny":
				case "d":
				case "no":
					if(isset($this->request[$name]))
					{
						$p = $this->plugin->getServer()->getPlayer($this->request[$name]);
						$sender->sendMessage("§8[§cСвадьбы§8] §f§cТы отклонил/а предложение");
						if($p !== null)
							$p->sendMessage("§8[§cСвадьбы§8] §f§cИгрок отклонил твое предложение :(");
					} else $sender->sendMessage("§8[§cСвадьбы§8] §f§eУ тебя нет входящих предложений");
				break;

				default:
					foreach($this->plugin->families as $family)
					if($name == $family["first"] || $name == $family["second"])
					{
						$sender->sendMessage("§8[§cСвадьбы§8] §f§cУ вас уже есть вторая половинка >:(");
						return true;
					}
					foreach($this->plugin->families as $family)
						if(strtolower($args[0]) == $family["first"] || strtolower($args[0]) == $family["second"]){
							$sender->sendMessage("§8[§cСвадьбы§8] §f§cУ игрока уже есть пара :с");
							return true;
						}

						/*if($this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($sender) < $config["MoneyToMarry"]) {
							$sender->sendMessage("§8[§cСвадьбы§8] §f§cУ тебя недостаточно денег :с");
							return true;
						}*/

						if($name == strtolower($args[0])) {
							$sender->sendMessage("§8[§cСвадьбы§8] §cТы ебанько, нельзя жениться на себе!");
							return true;
						}

						$player = $this->plugin->getServer()->getPlayer($args[0]);
						if($player !== null){
							$this->request[strtolower($player->getName())] = $name;
							$sender->sendMessage(str_replace("{player}", $player->getName(), "§8[§cСвадьбы§8] §f§aТы отправил запрос на свадьбу игроку §d{player}"));
							if($player->isOnline())
								$player->sendMessage(str_replace("{player}", $sender->getName(), "§8[§cСвадьбы§8] §f§aТебе пришел запрос на свадьбу от §d{player}"));
						} else $sender->sendMessage("§8[§cСвадьбы§8] §f§cИгрока нет в сети");
					break;
				}
			}
		}
	}
}