<?php

namespace Richen\DonateCase;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;
use pocketmine\Server;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\Level;

use pocketmine\utils\Config;

use Richen\Economy\Economy;

class DonateCase eXtEnDs PluginBase iMpLeMeNtS Listener
{
	public $opencase, $click;
	private $isOpen;
	
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		if(!is_dir($this->getDataFolder()))
			@mkdir($this->getDataFolder());
		
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
	}
	
	public function onDisable()
	{
		$this->config->save();
	}
	
	public function countCases($name)
	{
		if($name Instanceof Player)
			$name = strtolower($name->getName());
		
		$config = $this->config;
		
		if(!$config->exists(strtolower($name)))
			return $array = array(0, 0, 0);
		
		$array = $config->get(strtolower($name));
		$array = explode(":", $array);
		return $array;
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{	
		switch($command->getName())
		{
			case "dc":
			
			$player = $sender;
			$name = strtolower($player->getName());
				
			if(isset($args[0]))
			{
				switch(strtolower($args[0]))
				{
					case 'pay':
					if(!isset($args[1]) or !isset($args[2]) or !is_numeric($args[2]))
						return $player->sendMessage("§8[§cСервер§8] §6Используйте§8: §e/dc pay [игрок] [кол-во]");
					
					if($this->config->exists($name))
					{
						if($this->countCases($name)[0] > 0)
						{
							if($args[2] <= 0)
								return $player->sendMessage("§c[§6Донат§eКейсы§c] Ты не можешь передать менее одного кейса!");
							
							if($this->countCases($player)[0] < $args[2])
								return $player->sendMessage("§c[§6Донат§eКейсы§c] Ты не можешь передать §6{$args[2]} §cкейсов, у тебя есть только §6{$this->countCases($player)[0]} §cкейсов");
							
							$recipi = $this->getServer()->getPlayer($args[1]);
							
							if(!$recipi instanceof Player)
								return $player->sendMessage("§c[§6Донат§eКейсы§c] Игрок с ником §6{$args[1]} §cне онлайн");
							
							$nick = strtolower($recipi->getName());
							
							$this->config->set(strtolower($player->getName()), ($this->countCases($player)[0] - $args[2]) . ":" . $this->countCases($player)[1]);	
							$this->config->set(strtolower($recipi->getName()), ($this->countCases($recipi)[0] + $args[2]) . ":" . $this->countCases($recipi)[1]);
							
							$recipi->sendMessage("§c[§6Донат§eКейсы§c] §eИгрок §a{$name} §eпередал вам §b{$args[2]} §eдонат кейсов.");
							$player->sendMessage("§c[§6Донат§eКейсы§c] §eВы передали игроку §a{$args[1]} §eкейсы: §c{$args[2]} §eшт.");	
							return;
						}
					}
					$player->sendMessage("§6У тебя нет донат кейсов! Другие команды: §a/dc");
					$player->sendMessage("§6Приобрести кейсы: §ePay.MineScar.ru");
					return;
					
					case 'see':
						if(!isset($args[1]))
							return $player->sendMessage("§8[§cСервер§8] §6Используйте§8: §e/dc see [игрок]");
						
						$player->sendMessage("§c[§6Донат§eКейсы§c] Кейсы игрока $args[1]:");
						$count = $this->countCases($args[1]);
						$player->sendMessage("§9> §eКейсов: §b{$count[0]} кейсов");
						$player->sendMessage("§9> §eОткрыл(а): §b{$count[1]} кейсов");
					return;
					
					case 'add':
					case 'give':
					if(!$player->isOp() && !$player->hasPermission("dc.add") && $player Instanceof Player)
						return $player->sendMessage("§8[§cСервер§8] §6У Вас нет прав, для выполнения данной команды.");
					
					if(!isset($args[1]) or !isset($args[2]) or !is_numeric($args[2]))
						return $player->sendMessage("§8[§cСервер§8] §6Используйте§8: §e/dc add [ник] [кол-во]");
					
					$this->config->set(strtolower($args[1]), ($this->countCases(strtolower($args[1]))[0] + $args[2]) . ":" . $this->countCases(strtolower($args[1]))[1]);
					$player->sendMessage("§c[§6Донат§eКейсы§c] §eИгроку §a{$args[1]} §eдобавлено §c{$args[2]} §eкейсов");
					return;
				}
			}
			$player->sendMessage("§c[§6Донат§eКейсы§c] Помощь:");
			$player->sendMessage("§a- /dc see§f: смотреть кейсы игрока");
			$player->sendMessage("§a- /dc pay§f: передать кейс игроку");
			
			$player->sendMessage("§9> §eТвои кейсы: §b{$this->countCases($name)[0]} кейсов");
			$player->sendMessage("§9> §eТы открыл(а): §b{$this->countCases($name)[1]} кейсов");
			
			$player->sendMessage("§9> §eОткрыть §6кейс §eможно на спавне! (/spawn)");
			$player->sendMessage("§9> §eКупить кейсы: §aPay.MineScar.ru");
			break;
		}
	}
	
	public function onTouch(PlayerInteractEvent $event)
	{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		
		//if(!ServerAuth::getAPI()->isPlayerAuthenticated($player)) return;
		
		$config = $this->config;
		
		$counts = $this->countCases($player->getName());
		
		$name = strtolower($event->getPlayer()->getName());
		
		
		if($block->getX() == 25 && $block->getZ() == 358){
		
			$event->setCancelled();
		
			if($counts[0] <= 0)
				return $player->sendPopup("§cУ вас нет донат кейсов! §eИнформация§8: §a/dc");
			
			if(isset($this->isOpen) && $this->isOpen != strtolower($player->getName()))
				return $player->sendPopup("§cИгрок §6" . $this->isOpen . " §cуже открывает Кейс. Подождите.");
		
			if(isset($this->isOpen) && $this->isOpen == strtolower($player->getName()))
				return $player->sendPopup("§6Вы уже открываете кейс. Подождите.");
			
			if($counts[0] > 0)
			{
				$this->isOpen = $name;
				$this->openCase($player);
				$player->sendMessage("§c[§6Донат§eКейсы§c] §eОткрытие кейса! §aУ тебя осталось: §6" . ($counts[0] - 1) . " §aкейсов.");
				return;
			}
		}
	}
	
	public function openCase($player, $sec = 5)
	{
		$popsound = "pocketmine\\level\\sound\\PopSound";
		$level = $this->getServer()->getDefaultLevel();
		if($player instanceof Player){
			$player->sendTip("§eОткрытие кейса §c{$sec}§e...");
			$player->getLevel()->addSound(new $popsound($player));
		}
		
		if($sec == 5){
			$level->setBlock(new Vector3(25, 68, 358), Block::get(57, 0));
			$level->setBlock(new Vector3(25, 64, 358), Block::get(0, 0));
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "openCase"], [$player, 4]), 20 * 1);
			
			$text = new FloatingTextParticle(new Vector3(25.5, 64.5, 358.5), null, "§cКейс открывается!");
			$this->getServer()->getDefaultLevel()->addParticle($text);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 1);
		}
		if($sec == 4){
			$level->setBlock(new Vector3(25, 67, 358), Block::get(57, 0));
			$level->setBlock(new Vector3(25, 68, 358), Block::get(0, 0));
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "openCase"], [$player, 3]), 20 * 1);
			
			$text = new FloatingTextParticle(new Vector3(25.5, 64.5, 358.5), null, "§6Кейс открывается!");
			$this->getServer()->getDefaultLevel()->addParticle($text);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 1);
		}
		if($sec == 3){
			$level->setBlock(new Vector3(25, 66, 358), Block::get(57, 0));
			$level->setBlock(new Vector3(25, 67, 358), Block::get(0, 0));
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "openCase"], [$player, 2]), 20 * 1);
			
			$text = new FloatingTextParticle(new Vector3(25.5, 64.5, 358.5), null, "§eКейс открывается!");
			$this->getServer()->getDefaultLevel()->addParticle($text);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 1);
		}
		if($sec == 2){
			$level->setBlock(new Vector3(25, 65, 358), Block::get(57, 0));
			$level->setBlock(new Vector3(25, 66, 358), Block::get(0, 0));
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "openCase"], [$player, 1]), 20 * 1);

			$text = new FloatingTextParticle(new Vector3(25.5, 64.5, 358.5), null, "§aКейс открывается!");
			$this->getServer()->getDefaultLevel()->addParticle($text);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 1);
		}
		if($sec == 1){
			$level->setBlock(new Vector3(25, 64, 358), Block::get(57, 0));
			$level->setBlock(new Vector3(25, 65, 358), Block::get(0, 0));
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "openCase"], [$player, 0]), 20 * 1);
			
			$text = new FloatingTextParticle(new Vector3(25.5, 64.5, 358.5), null, "§bКейс открывается!");
			$this->getServer()->getDefaultLevel()->addParticle($text);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 1);
		}
		if($sec == 0){
			$level->setBlock(new Vector3(25, 64, 358), Block::get(120, 0));	
			unset($this->isOpen);
			$this->opensCase($player);
			$level->addSound(new FizzSound($player));
			$level->addParticle(new DestroyBlockParticle(new Vector3(25, 64, 358), Block::get(120,0)));
		}
	}
	
	public function opensCase($player)
	{
		$rand = mt_rand(0, 200);
		
		$name = strtolower($player->getName());
		
		if($player Instanceof Player && $player->isOnline())
		{
			$config = $this->config;
			
			$getcfg = $this->countCases($name);
			
			$config->set($name, ($getcfg[0] - 1) . ":" . ($getcfg[1] + 1));
			
			if($rand ==  0 && $rand ==  0) $group = "Gospodin";
			if($rand ==  1 || $rand ==  1) $group = "Creater";
			if($rand >=  2 && $rand <=  4) $group = "SuperAdmin";
			if($rand >=  5 && $rand <=  10) $group = "Moderator";
			if($rand >= 11 && $rand <= 20) $group = "Admin";
			if($rand >= 21 && $rand <= 35) $group = "Creative";
			if($rand >= 36 && $rand <= 70) $group = "Premium";
			if($rand >= 71 && $rand <= 120) $group = "Vip";
			if($rand >= 121 && $rand <= 170) $group = "Fly";
			
			if(!isset($group))
			{
				$rand = mt_rand(10000,20000);
				Economy::getInstance()->addMoney($name, $rand);
				$this->getServer()->broadcastMessage("§c[§6Донат§eКейсы§c] §fИгрок §e{$name} §fвыбил §a{$rand}\$§f, хочешь тоже испытать свою §6удачу§f? Напиши - §d/dc");	
			}
			else
			{
				if(!$player->hasPermission(strtolower($group)))
				{
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setgroup $name $group");
				}
				else
				{
					$player->sendMessage("§c[§6Донат§eКейсы§c] §6У тебя уже есть привилегия выше §e{$group}§6!");
				}
				$this->getServer()->broadcastMessage("§c[§6Донат§eКейсы§c] §fИгрок §e{$name} §fвыбил привилегию §a{$group}§f, хочешь тоже испытать свою §6удачу§f? Напиши - §d/dc");
				
				$text = new FloatingTextParticle(new Vector3(25, 65, 358), null, "§eИгрок §b{$name} §eвыбил привилегию §a{$group}");
				$this->getServer()->getDefaultLevel()->addParticle($text);
				$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "remParticle"], [$text, $player->getLevel()]), 20 * 5);
			}
		}
	}
	
	public function remParticle(FloatingTextParticle $particle, Level $level){
		$particle->setInvisible();
		$level->addParticle($particle);
	}
}