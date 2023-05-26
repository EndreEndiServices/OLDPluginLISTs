<?php

namespace PrestigeSociety\Core;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use PrestigeSociety\Core\Task\LSDUpdateTask;
use PrestigeSociety\Core\Utils\RandomUtils;

class FunBox {

	/** @var array */
	public $lsdEnabled = [];
	/** @var array */
	public $godModeEnabled = [];
	/** @var int[] */
	public $generatedColors = [];
	/** @var PrestigeSocietyCore */
	protected $plugin;

	/**
	 *
	 * FunThings constructor.
	 *
	 * @param PrestigeSocietyCore $core
	 *
	 */
	public function __construct(PrestigeSocietyCore $core){
		$this->plugin = $core;
	}


	public function generateColors(){

		$this->generatedColors = [];

		$colors = 1000;
		$k = mt_rand(0xFFF, 0xFFFFFF);

		$f = 0;

		$div = $colors / 2;

		for($i = 0; $i <= $colors; ++$i){

			if($i > $div){

				$k = $k - $f;

				$f--;

			}else{

				$k = $k + $f;

				$f++;

			}

			$this->generatedColors[$i] = $k;
		}

		return $this->generatedColors;
	}

	/**
	 *
	 * @param Player $sender
	 *
	 * @return bool
	 *
	 */
	public function isLSDEnabled(Player $sender){
		return isset($this->lsdEnabled[$sender->getXuid()]);
	}


	/**
	 *
	 * @param Player $sender
	 *
	 */
	public function toggleLSD(Player $sender){
		$xuid = $sender->getXuid();

		if(isset($this->lsdEnabled[$xuid])){

			$id = $this->lsdEnabled[$xuid]['taskId'];

			$this->plugin->getScheduler()->cancelTask($id);
			$sender->removeEffect(Effect::NIGHT_VISION);

			$sender->getInventory()->setContents($this->lsdEnabled[$xuid]['inventory']);
			$sender->getArmorInventory()->setContents($this->lsdEnabled[$xuid]['armor']);

			unset($this->lsdEnabled[$xuid]);

			$message = $this->plugin->getMessage('LSD', 'disabled');
			$sender->sendMessage(RandomUtils::colorMessage($message));

		}else{

			$lsdUT = new LSDUpdateTask($this->plugin, $sender);

			$id = $this->plugin->getScheduler()->scheduleRepeatingTask($lsdUT, 5);

			$sender->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 0x7fffffff, 50));

			$this->lsdEnabled[$xuid]['player'] = $sender;
			$this->lsdEnabled[$xuid]['taskId'] = $id->getTaskId();
			$this->lsdEnabled[$xuid]['inventory'] = $sender->getInventory()->getContents();
			$this->lsdEnabled[$xuid]['armor'] = $sender->getArmorInventory()->getContents(true);

			$sender->getInventory()->clearAll();

			$message = $this->plugin->getMessage('LSD', 'enabled');

			$sender->level->broadcastLevelSoundEvent($sender, LevelSoundEventPacket::SOUND_PORTAL);
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}
	}

	/**
	 *
	 * @param Player $sender
	 *
	 */
	public function toggleGod(Player $sender){
		$xuid = $sender->getXuid();

		if($this->isGodEnabled($sender)){

			unset($this->godModeEnabled[$xuid]);

			$message = $this->plugin->getMessage('God', 'disabled');
			$sender->sendMessage(RandomUtils::colorMessage($message));

		}else{

			$this->godModeEnabled[$xuid] = $sender;

			$message = $this->plugin->getMessage('God', 'enabled');

			$sender->level->broadcastLevelSoundEvent($sender, LevelSoundEventPacket::SOUND_BLOCK_END_PORTAL_SPAWN);
			$sender->sendMessage(RandomUtils::colorMessage($message));
		}
	}

	/**
	 *
	 * @param Player $sender
	 *
	 * @return bool
	 *
	 */
	public function isGodEnabled(Player $sender){
		return isset($this->godModeEnabled[$sender->getXuid()]);
	}

}