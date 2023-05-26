<?php
namespace FaigerSYS\VeryShortSex;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat as CLR;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\entity\Effect;

use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\level\particle\Particle;

use pocketmine\level\particle\PortalParticle;

class VeryShortSex extends PluginBase {
	
	const PREFIX = CLR::BOLD . CLR::WHITE . '(' . CLR::GOLD . 'Sex' . CLR::WHITE . ')' . CLR::RESET . CLR::GRAY . ' ';
	
	const WAIT_TIMEOUT = 60;
	
	const SPHERE_RADIUS = 0.5;
	const SPHERE_PARTICLES_COUNT = 600;
	
	/** @var ClearRequestsTask */
	private $task_cache;
	
	/** @var Particle */
	private $particle;
	
	/** @var Effect[] */
	private $good_effects;
	private $bad_effects;
	
	/** @var array */
	private $requests = [];
	
	/** @var string */
	private $alredy_sex = [];
	
	public function onEnable() {
		$this->getLogger()->info(CLR::GOLD . 'VeryShortSex загружается...');
		
		$this->task_cache = new ClearRequestsTask($this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask($this->task_cache, 20 * 30);
		
		$this->particle = new PortalParticle(new Vector3(0, 0, 0));
		
		$this->good_effects = [
			Effect::getEffect(Effect::SPEED)->setDuration(20 * 60),
			Effect::getEffect(Effect::JUMP)->setDuration(20 * 60)
		];
		$this->bad_effects = [
			Effect::getEffect(Effect::SLOWNESS)->setDuration(20 * 60),
			Effect::getEffect(Effect::BLINDNESS)->setDuration(20 * 60)
		];
		
		# $this->prepareData();
		
		# $this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->getLogger()->info(CLR::GOLD . 'VeryShortSex загружен!');
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if ($sender instanceof ConsoleCommandSender)
			return;
		
		$root_cmd = $command->getName();
		switch ($root_cmd) {
			case 'sex':
				$partner_name = array_shift($args);
				if (empty($partner_name)) {
					$sender->sendMessage(self::PREFIX . 'Использование: /' . $root_cmd . ' <ник>');
					return;
				}
				
				$partner = $this->getServer()->getPlayer($partner_name);
				if (!$partner) {
					$sender->sendMessage(self::PREFIX . 'Партнёр ' . CLR::WHITE . $partner_name . CLR::GRAY . ' не найден!');
					return;
				}
				
				$partner_name = $partner->getName();
				$sender_name = $sender->getName();
				if ($partner_name === $sender_name) {
					$sender->sendMessage(self::PREFIX . 'Вы не можете дрочить себе!');
					return;
				}
				
				$s_partner_name = strtolower($partner_name);
				if (isset($this->alredy_sex[$s_partner_name])) {
					$sender->sendMessage(self::PREFIX . 'Этот партнёр уже занимался сексом сегодня!');
					return;
				}
				
				$s_sender_name = strtolower($sender_name);
				if (isset($this->alredy_sex[$s_sender_name])) {
					$sender->sendMessage(self::PREFIX . 'Вы уже занимались сексом сегодня!');
					return;
				}
				
				$this->requests[$s_partner_name] = [$sender_name, time()];
				
				$sender->sendMessage(self::PREFIX . 'Вы отправили приглашение на секс с игроком ' . CLR::WHITE . $partner_name . CLR::GRAY . '!');
				
				$partner->sendMessage(self::PREFIX . 'Вам пришло приглашение на секс от ' . CLR::WHITE . $sender_name . CLR::GRAY . '! Оно будет действовать ' . self::WAIT_TIMEOUT . ' сек.');
				$partner->sendMessage(self::PREFIX . CLR::YELLOW . '/sexyes' . CLR::RED . ' - ' . CLR::GRAY . 'согласться' . CLR::RED . ' | ' . CLR::YELLOW . '/sexno' . CLR::RED . ' - ' . CLR::GRAY . 'отказаться');
				
				break;
			
			case 'sexyes':
				$partner = $sender;
				
				$partner_name = $partner->getName();
				$s_partner_name = strtolower($partner_name);
				
				$this->clearInactiveRequests($s_partner_name);
				if (!isset($this->requests[$s_partner_name])) {
					$partner->sendMessage(self::PREFIX . 'У вас нет приглашений на секс');
					return;
				}
				
				list($sender_name) = $this->requests[$s_partner_name];
				$s_sender_name = strtolower($sender_name);
				
				$sender = $this->getServer()->getPlayerExact($sender_name);
				if (!$sender) {
					$partner->sendMessage(self::PREFIX . 'Отправитель приглашения ещё не в сети!');
					return;
				}
				
				$sender->teleport($partner);
				
				$sender->sendMessage(self::PREFIX . 'Вы занимаетесь сексом с ' . CLR::WHITE . $partner_name . CLR::GRAY . '!');
				$partner->sendMessage(self::PREFIX . 'Вы занимаетесь сексом с ' . CLR::WHITE . $sender_name . CLR::GRAY . '!');
				
				$center = $sender->add(0, $sender->getEyeHeight() - 1, 0);
				$level = $sender->getLevel();
				$this->spawnParticleSphere($this->particle, $center, $level);
				
				if (mt_rand(0, 1) === 0) {
					$bad_player = $partner;
					$good_player = $sender;
				} else {
					$bad_player = $sender;
					$good_player = $partner;
				}
				
				foreach ($this->good_effects as $effect) {
					$good_player->addEffect($effect);
				}
				$good_player->sendMessage(self::PREFIX . CLR::GREEN . 'Ничего себе! Вы занялись сексом и стали сильнее!');
				
				foreach ($this->bad_effects as $effect) {
					$bad_player->addEffect($effect);
				}
				$bad_player->sendMessage(self::PREFIX . CLR::RED . 'Сегодня видимо вы устали на работе... Отдохните');
				
				unset($this->requests[$s_partner_name]);
				
				$this->alredy_sex[$s_sender_name] = true;
				$this->alredy_sex[$s_partner_name] = true;
				
				break;
			
			case 'sexno':
				$partner = $sender;
				$partner_name = $partner->getName();
				$s_partner_name = strtolower($partner_name);
				$this->clearInactiveRequests($s_partner_name);
				
				if (!isset($this->requests[$s_partner_name])) {
					$partner->sendMessage(self::PREFIX . 'У вас нет приглашений на секс');
					return;
				}
				
				list($sender_name) = $this->requests[$s_partner_name];
				$sender = $this->getServer()->getPlayerExact($sender_name);
				if ($sender) {
					$sender->sendMessage(self::PREFIX . 'Партнёр ' . CLR::WHITE . $partner_name . CLR::GRAY . ' отказался от секса с вами!');
				}
				
				$partner->sendMessage(self::PREFIX . 'Вы отказались от секса с игроком ' . CLR::WHITE . $sender_name . CLR::GRAY . '!');
				
				unset($this->requests[$s_partner_name]);
				
				break;
		}
	}
	
	public function spawnParticleSphere(Particle $particle, Vector3 $center, Level $level, float $radius = self::SPHERE_RADIUS, int $particles_count = self::SPHERE_PARTICLES_COUNT) {
		$players = $level->getChunkPlayers($center->getX() >> 4, $center->getZ() >> 4);
		$packets = [];
		
		while ($particles_count-- > 0) {
			$x = (mt_rand() / mt_getrandmax()) * 2 - 1;
			$y = (mt_rand() / mt_getrandmax()) * 2 - 1;
			$z = (mt_rand() / mt_getrandmax()) * 2 - 1;
			$pos = $center->add((new Vector3($x, $y, $z))->normalize()->multiply($radius));
			
			$particle->setComponents($pos->getX(), $pos->getY(), $pos->getZ());
			
			$packets[] = $particle->encode();
		}
		
		$this->getServer()->batchPackets($players, $packets);
	}
	
	public function clearInactiveRequests(string $partner_name = null) {
		$now = time();
		
		if ($partner_name) {
			$partner_name = strtolower($partner_name);
			if (isset($this->requests[$partner_name])) {
				list($sender_name, $time) = $this->requests[$partner_name];
				if (($time + self::WAIT_TIMEOUT) < $now || isset($this->alredy_sex[$partner_name]) || isset($this->alredy_sex[strtolower($sender_name)])) {
					unset($this->requests[$partner_name]);
				}
			}
		} else {
			foreach ($this->requests as $partner_name => $request) {
				list($sender_name, $time) = $request;
				if (($time + self::WAIT_TIMEOUT) < $now || isset($this->alredy_sex[$partner_name]) || isset($this->alredy_sex[strtolower($sender_name)])) {
					unset($this->requests[$partner_name]);
				}
			}
		}
	}
	
	private function prepareData() {
		@mkdir($path = $this->getDataFolder());
		$defaultConfig = stream_get_contents($this->getResource($file = 'settings.yml'));
		$defaultData = yaml_parse($defaultConfig);
		if (!file_exists($path .= $file)) {
			file_put_contents($path, $defaultConfig);
			return $defaultData;
		} else {
			$newData = @yaml_parse(file_get_contents($path));
			if (!is_array($newData) || empty($newData)) {
				$this->getLogger()->warning('Файл с настройками был повреждён. Он будет ввостановлен в начальный вид');
				file_put_contents($path, $defaultConfig);
				return $defaultData;
			} else {
				return array_replace_recursive($defaultData, $newData);
			}
		}
	}
	
}
