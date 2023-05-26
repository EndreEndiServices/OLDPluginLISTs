<?php

namespace PrestigeSociety\Core;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;
use PrestigeSociety\Core\Utils\RandomUtils;

class InfoParticles {

	/** @var Human[] */
	protected $loadedParticles = [];

	/** @var PrestigeSocietyCore */
	protected $core;

	/**
	 *
	 * HUD constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->core = $core;
	}

	public function spawnToAll(){
		$this->getAllInfoParticles();
	}

	/**
	 *
	 * @return array
	 *
	 */
	public function getAllInfoParticles(){
		$info = $this->core->getConfig()->get('info');

		$out = [];

		$p = null;

		foreach($info as $page => $value){
			if(($p = $this->getInfoParticle($page)) !== null){
				$out[$page] = $p;
			}
		}

		return $out;
	}

	/**
	 *
	 * @param string $page
	 *
	 * @return null|Entity
	 *
	 */
	public function getInfoParticle(string $page){
		$info = $this->core->getConfig()->get('info');
		$title = "";
		$text = "";

		if(isset($info[$page])){

			$infopage = $info[$page];

			$pgtitle = $infopage["title"][0];

			$title .= RandomUtils::colorMessage($pgtitle) . "\n";

			$title = str_replace("\\n", "\n", $title);

			$pgtext = $infopage["text"][0];

			$text .= RandomUtils::colorMessage($pgtext) . "\n";

			$text = str_replace("\\n", "\n", $text);

			$this->formatKills($text);
			$this->formatDeaths($text);

//			$level = $this->core->getServer()->getLevelByName($infopage['level']);
//			$pos = new Position($infopage['x'], $infopage['y'], $infopage['z'], $level ?? Server::getInstance()->getDefaultLevel());
//
//			$skinData = str_repeat("\x00", 64 * 64 * 2);
//
//			$nbt = Human::createBaseNBT($pos);
//			$nbt->setTag(new CompoundTag("Skin",
//				["Data" => new StringTag("Data", $skinData),
//				 "Name" => new StringTag("Name", "Standard_Custom")]));
//			$nbt->setByte("text", 1);
//			$p = Human::createEntity("Human", $level, $nbt);
//			if($p instanceof Human){
//				$p->setSkin(new Skin("Standard_Custom", $skinData));
//			}
//			$p->namedtag->setByte("text", 1);
//			$p->setNameTag($title . $text);
//			$p->setNameTagAlwaysVisible(true);
//			$p->setScale(0.01);
//			if(!$p->isImmobile()){
//				$p->setImmobile();
//			}
//			$p->setGenericFlag(Entity::DATA_FLAG_NO_AI, true);
//			$p->setGenericFlag(Entity::DATA_FLAG_AFFECTED_BY_GRAVITY, false);
//			$p->setGenericFlag(Entity::DATA_FLAG_MOVING, false);
//			$p->spawnToAll();

			return null;
		}

		return null;
	}

	/**
	 *
	 * @param string $message
	 *
	 */
	private function formatKills(string &$message){
		$kills = $this->core->PrestigeSocietyLevels()->getTopKills(10);

		foreach($kills as $index => $data){
			$message = str_replace(["@top" . ($index + 1) . "playerWithKills", "@top" . ($index + 1) . "kills"], [$data['name'], $data['kills']], $message);
		}
	}

	/**
	 *
	 * @param string $message
	 *
	 */
	private function formatDeaths(string &$message){
		$deaths = $this->core->PrestigeSocietyLevels()->getTopDeaths(10);

		foreach($deaths as $index => $data){
			$message = str_replace(["@top" . ($index + 1) . "playerWithDeaths", "@top" . ($index + 1) . "deaths"], [$data['name'], $data['deaths']], $message);
		}
	}

	/**
	 *
	 * @param Player $player
	 *
	 */
	public function spawnTo(Player $player){
		$this->loadedParticles = $this->getAllInfoParticles();
		$this->updateParticles();
		foreach($this->loadedParticles as $particle){
			$particle->spawnTo($player);
		}
	}

	public function updateParticles(){
		$this->loadedParticles = $this->getAllInfoParticles();
		foreach($this->loadedParticles as $info => $particle){
			$particle->setNameTag($this->getInfoParticleText($info));
			foreach($this->core->getServer()->getOnlinePlayers() as $player){
				$particle->sendData($player);
			}
		}
	}

	/**
	 *
	 * @param string $page
	 *
	 * @return null|string
	 *
	 */
	public function getInfoParticleText(string $page){
		$info = $this->core->getConfig()->get('info');
		$title = '';
		$text = '';

		if(isset($info[$page])){

			$info = $info[$page];

			foreach($info['title'] as $pg){
				$title .= RandomUtils::colorMessage($pg) . "\n";
			}
			foreach($info['text'] as $pg){
				$text .= RandomUtils::colorMessage($pg) . "\n";
			}

			$this->formatKills($text);
			$this->formatDeaths($text);

			return $title . $text;
		}

		return null;
	}

	/**
	 *
	 * @param string $page
	 * @param Position $position
	 *
	 */
	public function saveInfoParticle(string $page, Position $position){
		$info = $this->core->getConfig()->get('info');
		$info[$page] = [
			'title' => [
				(string)'This is your particle title',
			],
			'text'  => [
				(string)'This is your particle text',
			],
			'x'     => $position->x,
			'y'     => $position->y,
			'z'     => $position->z,
			'level' => $position->level->getName(),
		];
		$this->core->getConfig()->set('info', $info);
		$this->core->getConfig()->save();
	}

	/**
	 *
	 * @return array
	 *
	 */
	public function spawnAllInfoParticles(){
		$info = $this->core->getConfig()->get('info');

		$out = [];

		foreach($info as $page => $value){
			if(($p = $this->spawnInfoParticle($page)) !== null){
				$out[$page] = $p;
			}
		}

		return $out;
	}

	/**
	 *
	 * @param string $page
	 *
	 * @return null|Entity
	 *
	 */
	public function spawnInfoParticle(string $page){
		$info = $this->core->getConfig()->get('info');

		if(isset($info[$page])){

			$p = $this->getInfoParticle($page);

			return $p;
		}

		return null;
	}

}