<?php

namespace NetherManiacsKingdom\Block-Hunt\tasks;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use pocketmine\tile\Tile;
use pocketmine\tile\Sign;
use pocketmine\tile\Chest;
use pocketmine\math\Vector3;
use pocketmine\Server;
use mcg76\game\blockhunt\BlockHuntPlugIn;
use mcg76\game\blockhunt\arenas\ArenaModel;
use mcg76\game\blockhunt\utils\LevelUtil;


class PlayArenaRepairTask extends PluginTask {
	private $plugin;
	
	/**
	 *
	 * @param PrivatePlotPlugIn $plugin        	
	 */
	public function __construct(BlockHuntPlugIn $plugin) {
		$this->plugin = $plugin;
		parent::__construct ( $plugin );
	}
	
	/**
	 *
	 * @param
	 *        	$ticks
	 */
	public function onRun($ticks) {
		try {
			foreach ( $this->plugin->arenaManager->playArenas as $arena ) {
				if ($arena instanceof ArenaModel) {
					// if ($arena->lockdownOn && $arena->status != ArenaModel::ARENA_STATUS_PLAYING) {
					if ($arena->lockdownOn) {
						LevelUtil::resetArenaBlocks ( $this->plugin, $arena );
					}
				}
			}
		} catch ( \Exception $e ) {
			$message = $e->getCode () . "|" . $e->getLine () . "|" . $e->getMessage () . "|" . $e->getTraceAsString ();
			$this->plugin->getLogger ()->error ( $message );
		}
	}
	public function onCancel() {
	}
}
