<?php

namespace mcg76\plugin\worldedit;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\utils\Utils;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\block\Block;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;

/**
 * MCPE World Edit - Made by minecraftgenius76
 *
 * You're allowed to use for own usage only "as-is".
 * you're not allowed to republish or resell or for any commercial purpose.
 *
 * Thanks for your cooperate!
 *
 * Copyright (C) 2015 minecraftgenius76
 *
 * Web site: http://www.minecraftgenius76.com/
 * YouTube : http://www.youtube.com/user/minecraftgenius76
 *
 * @author minecraftgenius76
 *        
 */
class WorldEditCommand {
	private $pgin;
	public function __construct(WorldEditPlugIn $pg) {
		$this->pgin = $pg;
	}
	public function &session(Player $sender) {
		if (! isset ( $this->getPlugIn ()->sessions [$sender->getName ()] )) {
			$this->getPlugIn ()->sessions [$sender->getName ()] = array (
					"selection" => array (
							false,
							false 
					),
					"clipboard" => false,
					"block-limit" => - 1,
					"wand-usage" => false,
					"paste-pos" => false 
			);
		}
		return $this->getPlugIn ()->sessions [$sender->getName ()];
	}
	public function setPosition1(&$session, Position $position, &$output) {
		$session ["selection"] [0] = array (
				round ( $position->x ),
				round ( $position->y ),
				round ( $position->z ),
				$position->level 
		);
		$count = $this->countBlocks ( $session ["selection"] );
		if ($count === false) {
			$count = "";
		} else {
			$count = " ($count)";
		}
		$output .= "[WE] Position #1 set to (" . $session ["selection"] [0] [0] . ", " . $session ["selection"] [0] [1] . ", " . $session ["selection"] [0] [2] . ")$count.\n";
		return true;
	}
	public function setPosition2(&$session, Position $position, &$output) {
		$session ["selection"] [1] = array (
				round ( $position->x ),
				round ( $position->y ),
				round ( $position->z ),
				$position->level 
		);
		$count = $this->countBlocks ( $session ["selection"] );
		if ($count === false) {
			$count = "";
		} else {
			$count = " ($count)";
		}
		$output .= "[WE] Position #2 set to (" . $session ["selection"] [1] [0] . ", " . $session ["selection"] [1] [1] . ", " . $session ["selection"] [1] [2] . ")$count.\n";
		return true;
	}
	
	/**
	 * Handle plugin commands
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$output = "";
		if ((strtolower ( $command->getName () ) == "/") && isset ( $args [0] )) {
			switch ($args [0]) {
				case "blockon" :
					$this->pgin->pos_display_flag = 1;
					$sender->sendMessage ( "[WE] display block on" );
					break;
				case "blockoff" :
					$this->pgin->pos_display_flag = 0;
					$sender->sendMessage ( "[WE] display block off" );
					break;
				case "paste" :
					$this->handlePasteCommand ( $sender, $args );
					break;
				case "cylinder" :
					$this->handleCylinderCommand ( $sender, $args );
					break;
				case "overlay" :
					$this->handleOverlay ( $sender, $args );
					break;
				case "hcylinder" :
					$this->handleHoloCylinderCommand ( $sender, $args );
					break;
				case "wall" :
					$this->handleWallCommand ( $sender, $args );
					break;
				case "plant" :
					$this->handlePlantCommand ( $sender, $args );
					break;
				case "copy" :
					$this->handleCopyCommand ( $sender, $args );
					break;
				case "cut" :
					$this->handleCutCommand ( $sender, $args );
					break;
				case "remove" :
					$this->handleRemoveCommand ( $sender, $args );
					break;
				case "expand" :
					$this->handleExpandCommand ( "expand", $sender, $args );
					break;
				case "grow" :
					$this->handleGrowCommand ( "grow", $sender, $args );
					break;
				case "toggleeditwand" :
					if (! ($sender instanceof Player)) {
						$output .= "[WE] Please run this command in-game.\n";
						$sender->sendMessage ( $output );
						break;
					}
					$session = & $this->session ( $sender );
					$session ["wand-usage"] = $session ["wand-usage"] == true ? false : true;
					$output = "Wand Item is now " . ($session ["wand-usage"] === true ? "enabled" : "disabled") . ".\n";
					if (isset ( $session ["wand-usage"] ) && $session ["wand-usage"] == true) {
						$this->handleWandCommand ( $sender->getPlayer () );
					}
					$sender->sendMessage ( $output );
					break;
				case "wand" :
					if (! ($sender instanceof Player)) {
						$output .= "[WE]Please run this command in-game.\n";
						$sender->sendMessage ( $output );
						break;
					}
					$this->handleWandCommand ( $sender->getPlayer () );
					break;
				case "limit" :
					$this->handleLimitCommand ( "limit", $sender, $args );
					break;
				case "pos1" :
					$this->handlePos1Command ( $sender, $args );
					break;
				case "pos2" :
					$this->handlePos2Command ( $sender, $args );
					break;
				case "hsphere" :
					$this->handleSphereCommand ( "hsphere", $sender, $args, false );
					break;
				case "sphere" :
					$this->handleSphereCommand ( "sphere", $sender, $args, true );
					break;
				case "movedown" :
					$this->handleMoveDownCommand ( "movedown", $sender, $args );
					break;
				case "set" :
					$this->handleSetCommand ( "set", $sender, $args );
					break;
				case "replace" :
					$this->handleReplaceCommand ( "replace", $sender, $args );
					break;
				case "desel" :
					$this->handleDeSelCommand ( $sender );
					break;
				default :
				case "help" :
					$output = "Minecraft PE WorldEdit 0.3.0\n";
					$output .= "Commands: // cut, // copy, // paste, // sphere, // hsphere, // remove, // cylinder, // hcylinder, // plant, // overlay, // expand, //grow,  // desel, // limit, // pos1, // pos2, // set, // replace, // help, // wand, // toggleeditwand\n";
					$sender->sendMessage ( $output );
					break;
			}
		}
	}
	public function handleDeSelCommand($sender) {
		$session = & $this->session ( $sender );
		$session ["selection"] = array (
				false,
				false 
		);
		unset ( $session ["wand-pos1"] );
		unset ( $session ["wand-pos2"] );
		unset ( $session ["paste-pos"] );
		$output = "[WE] Selection cleared.\n";
		$sender->sendMessage ( $output );
	}
	public function handlePlantCommand(Player $sender, $args) {
		$session = & $this->session ( $sender );
		$output = "";
		if (count ( $args ) < 3) {
			$sender->sendMessage ( "[WE] Usage: // plant [type] [radius] [spaces]" );
			return;
		}
		
		$pos = $sender->getPosition ();
		$planttype = $args [1];
		$radius = $args [2];
		$spaces = 0;
		if (count ( $args ) == 4) {
			$spaces = $args [3];
		}
		$sender->sendMessage ( "[WE] planting " . $planttype );
		$this->getBuilder ()->W_plants ( $sender->getLevel (), $pos, $planttype, $radius, $spaces, $output );
		$sender->sendMessage ( "[WE] plant " . $output );
		if ($output != null) {
			$sender->teleport ( new Position ( $pos->x + $radius + 1, $pos->y + $radius, $pos->z ) );
		}
	}
	public function handleCylinderCommand(Player $sender, $args) {
		$session = & $this->session ( $sender );
		$output = "";
		if (count ( $args ) != 4) {
			$sender->sendMessage ( "[WE] Usage: // cylinder [block id] [radius] [height] " );
			return;
		}
		$pos = $sender->getPosition ();
		// $blockid = $args [1];
		$radius = $args [2];
		$height = $args [3];
		// if (Item::get ( $blockid )->getBlock () == null) {
		// $sender->sendMessage ( "[WE] Invalid id [" . $blockid . "]" );
		// return;
		// }
		$values = explode ( ":", $args [1] );
		$bid = null;
		$bmeta = null;
		$item = null;
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$sender->sendMessage ( "[WE] building cynlinder using block " . $item );
		$this->getBuilder ()->W_cylinder ( $pos, $item->getBlock (), $radius, $height, $output );
		$sender->sendMessage ( "[WE] cynlinder created " . $output );
		if ($output != null) {
			$sender->teleport ( new Position ( $pos->x, $pos->y + $height + 1, $pos->z ) );
		}
	}
	public function handleOverlay(Player $sender, $args) {
		$output = "";
		if (count ( $args ) != 2) {
			$sender->sendMessage ( "[WE] Usage: // overlay [block id:meta]" );
			return;
		}
		// $blockid = $args [1];
		// if (Item::get ( $blockid )->getBlock () == null) {
		// $sender->sendMessage ( "[WE] Invalid id [" . $blockid . "]" );
		// return;
		// }
		$values = explode ( ":", $args [1] );
		$bid = null;
		$bmeta = null;
		$item = null;
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$sender->sendMessage ( "[WE] building overlay using block " . $item );
		$session = & $this->session ( $sender );
		$this->getBuilder ()->W_overlay ( $sender->getLevel (), $session ["selection"], $item->getBlock (), $output );
		$sender->sendMessage ( "[WE] overlay created " . $output );
	}
	public function handleHoloCylinderCommand(Player $sender, $args) {
		$session = & $this->session ( $sender );
		$output = "";
		if (count ( $args ) != 4) {
			$sender->sendMessage ( "[WE] Usage: // hcylinder [block id] [radius] [height] " );
			return;
		}
		$pos = $sender->getPosition ();
		// $blockid = $args [1];
		$radius = $args [2];
		$height = $args [3];
		// if (Item::get ( $blockid )->getBlock () == null) {
		// $sender->sendMessage ( "[WE] Invalid id [" . $blockid . "]" );
		// return;
		// }
		$values = explode ( ":", $args [1] );
		$bid = null;
		$bmeta = null;
		$item = null;
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$sender->sendMessage ( "[WE] building holo cylinder " );
		$this->getBuilder ()->W_holocylinder ( $pos, $item->getBlock (), $radius, $height, $output );
		$sender->sendMessage ( "[WE] holo cylinder created " . $output );
		if ($output != null) {
			$sender->teleport ( new Position ( $pos->x, $pos->y + $height + 1, $pos->z ) );
		}
	}
	public function handleWallCommand(Player $sender, $args) {
		$output = "";
		if (count ( $args ) != 2) {
			$sender->sendMessage ( "[WE] Usage: // wall [block id]" );
			return;
		}
		// $blockid = $args [1];
		// if (Item::get ( $blockid )->getBlock () == null) {
		// $sender->sendMessage ( "[WE] Invalid id [" . $blockid . "]" );
		// return;
		// }
		$values = explode ( ":", $args [1] );
		$bid = null;
		$bmeta = null;
		$item = null;
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$sender->sendMessage ( "[WE] building wall using block " . $item );
		$this->getBuilder ()->W_wall ( $sender->getLevel (), $session ["selection"], $item->getBlock (), $output );
		$sender->sendMessage ( "[WE] wall created " . $output );
	}
	public function handlePasteCommand($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$pastePos = $sender->getPosition ();
		if (isset ( $session ["paste-pos"] )) {
			if ($session ["paste-pos"] instanceof Position) {
				$pastePos = $session ["paste-pos"];
			}
		}
		$this->getBuilder ()->W_paste ( $session ["clipboard"], $pastePos, $output );
		$sender->sendMessage ( "[WE] " . $output . " Pasted." );
	}
	public function handleCopyCommand($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"], $startX, $startY, $startZ );
		// disable restriction
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output .= "[WE] Block limit of " . $session ["block-limit"] . " exceeded, tried to copy $count block(s).\n";
			$sender->sendMessage ( "[WE]  " . $output );
			return;
		}
		// @FIXME - avoid liquid
		$blocks = $this->getBuilder ()->W_copy ( $session ["selection"], $output );
		if (count ( $blocks ) > 0) {
			$offset = array (
					$startX - $sender->x - 0.5,
					$startY - $sender->y,
					$startZ - $sender->z - 0.5 
			);
			$session ["clipboard"] = array (
					$offset,
					$blocks 
			);
		}
		$sender->sendMessage ( $output );
	}
	public function handleLimitCommand($cmd, $sender, $args) {
		$output = "";
		if (! isset ( $args [1] ) or trim ( $args [1] ) === "") {
			$output .= "[WE] Usage: //limit <limit>\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! is_numeric ( $args [1] )) {
			$output .= "[WE] Usage: //" . $cmd . " < # of blocks MUST be a numeric value>\n";
			$sender->sendMessage ( $output );
			return;
		}
		$limit = $args [1];
		if ($limit < 0) {
			$limit = - 1;
		}
		// @DISABLE
		// if ($this->getPlugIn ()->getConfig ()->get ( "block-limit" ) > 0) {
		// $limit = $limit == - 1 ? $this->getPlugIn ()->getConfig ()->get ( "block-limit" ) : min ( $this->getPlugIn ()->getConfig ()->get ( "block-limit" ), $limit );
		// }
		$session = & $this->session ( $sender );
		$session ["block-limit"] = $limit;
		$output = "[WE] Block limit set to " . ($limit == - 1 ? "infinite" : $limit) . " block(s).\n";
		$sender->sendMessage ( $output );
	}
	public function handleCutCommand($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"], $startX, $startY, $startZ );
		// @FIXME AVOID LIQUID
		// remove restriction, takes more blocks in-memory
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output .= "[WE] Block limit of " . $session ["block-limit"] . " exceeded, tried to cut $count block(s).\n";
			$sender->sendMessage ( $output );
			return;
		}
		$blocks = $this->getBuilder ()->W_cut ( $session ["selection"], $output );
		if (count ( $blocks ) > 0) {
			$offset = array (
					$startX - $sender->x - 0.5,
					$startY - $sender->y,
					$startZ - $sender->z - 0.5 
			);
			$session ["clipboard"] = array (
					$offset,
					$blocks 
			);
		}
		$sender->sendMessage ( $output );
	}
	public function handleMoveDownCommand($cmd, $sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		
		if (! isset ( $args [1] ) or $args [1] == "") {
			$output .= "[WE] Usage: //" . $cmd . " <number of blocks>\n";
			$sender->sendMessage ( $output );
			return;
		}
		$delta = $args [1];
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"], $startX, $startY, $startZ );
		// remove restriction, takes more blocks in-memory
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output .= "[WE] Block limit of " . $session ["block-limit"] . " exceeded, tried to cut $count block(s).\n";
			$sender->sendMessage ( $output );
			return;
		}
		$this->getBuilder ()->W_moveDown ( $session ["selection"], $session ["clipboard"], $delta, $output );
		// update selection
		// startY
		$session ["selection"] [0] [1] = $session ["selection"] [0] [1] - $delta;
		// endY
		$session ["selection"] [0] [1] = $session ["selection"] [1] [1] - $delta;
		$sender->sendMessage ( $output );
	}
	public function handleExpandCommand($cmd, $sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! isset ( $args [1] ) or $args [1] == "") {
			$output .= "[WE] Usage: //" . $cmd . " <size in # times>\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! is_numeric ( $args [1] )) {
			$output .= "[WE] Usage: //" . $cmd . " <size MUST be a numeric value>\n";
			$sender->sendMessage ( $output );
			return;
		}
		$size = intval ( $args [1] );
		$session = & $this->session ( $sender );
		
		$startX = min ( $session ["selection"] [0] [0], $session ["selection"] [1] [0] );
		$endX = max ( $session ["selection"] [0] [0], $session ["selection"] [1] [0] );
		$startZ = min ( $session ["selection"] [0] [2], $session ["selection"] [1] [2] );
		$endZ = max ( $session ["selection"] [0] [2], $session ["selection"] [1] [2] );
		if ($startX < 0) {
			$session ["selection"] [1] [0] = $session ["selection"] [1] [0] - ($endX - $startX + 1) * $size;
		} else {
			$session ["selection"] [1] [0] = $session ["selection"] [1] [0] + ($endX - $startX + 1) * $size;
		}
		if ($startZ < 0) {
			$session ["selection"] [1] [2] = $session ["selection"] [1] [2] - ($endZ - $startZ + 1) * $size;
		} else {
			$session ["selection"] [1] [2] = $session ["selection"] [1] [2] + ($endZ - $startZ + 1) * $size;
		}
		$count = $this->countBlocks ( $session ["selection"] );
		// remove restriction, takes more blocks in-memory
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output .= "Block limit of " . $session ["block-limit"] . " exceeded, tried to cut $count block(s).\n";
			$sender->sendMessage ( $output );
			break;
		}
		$output = "[WE] Expanded $size times with total blocks " . $count;
		$sender->sendMessage ( $output );
	}
	public function handleGrowCommand($cmd, $sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! isset ( $args [1] ) or $args [1] == "") {
			$output .= "[WE] Usage: //" . $cmd . " <size in # times>\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! is_numeric ( $args [1] )) {
			$output .= "[WE] Usage: //" . $cmd . " <size MUST be a numeric value>\n";
			$sender->sendMessage ( $output );
			return;
		}
		$size = intval ( $args [1] );
		$session = & $this->session ( $sender );
		
		$startX = min ( $session ["selection"] [0] [0], $session ["selection"] [1] [0] );
		$endX = max ( $session ["selection"] [0] [0], $session ["selection"] [1] [0] );
		
		$startY = min ( $session ["selection"] [0] [1], $session ["selection"] [1] [1] );
		$endY = max ( $session ["selection"] [0] [1], $session ["selection"] [1] [1] );
		
		$startZ = min ( $session ["selection"] [0] [2], $session ["selection"] [1] [2] );
		$endZ = max ( $session ["selection"] [0] [2], $session ["selection"] [1] [2] );
		
		if ($startX < 0) {
			$session ["selection"] [1] [0] = $session ["selection"] [1] [0] - ($endX - $startX + 1) * $size;
		} else {
			$session ["selection"] [1] [0] = $session ["selection"] [1] [0] + ($endX - $startX + 1) * $size;
		}
		if ($startZ < 0) {
			$session ["selection"] [1] [2] = $session ["selection"] [1] [2] - ($endZ - $startZ + 1) * $size;
		} else {
			$session ["selection"] [1] [2] = $session ["selection"] [1] [2] + ($endZ - $startZ + 1) * $size;
		}
		
		$session ["selection"] [1] [1] = $session ["selection"] [1] [1] + ($endY - $startY + 1) * $size;
		
		$count = $this->countBlocks ( $session ["selection"] );
		// remove restriction, takes more blocks in-memory
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output .= "[WE] Block limit of " . $session ["block-limit"] . " exceeded, tried to cut $count block(s).\n";
			$sender->sendMessage ( $output );
			break;
		}
		$output = "[WE] Grow $size times with total blocks " . $count;
		$sender->sendMessage ( $output );
	}
	public function handleRemoveCommand($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"], $startX, $startY, $startZ );
		$blocks = $this->getBuilder ()->W_remove ( $session ["selection"], $output );
		if (count ( $blocks ) > 0) {
			$offset = array (
					$startX - $sender->x - 0.5,
					$startY - $sender->y,
					$startZ - $sender->z - 0.5 
			);
			$session ["clipboard"] = array (
					$offset,
					$blocks 
			);
		}
		$sender->sendMessage ( $output );
	}
	public function handlePos1Command($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$this->setPosition1 ( $session, new Position ( $sender->x - 0.5, $sender->y, $sender->z - 0.5, $sender->getLevel () ), $output );
		$sender->sendMessage ( $output );
	}
	public function handlePos2Command($sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$session = & $this->session ( $sender );
		$this->setPosition2 ( $session, new Position ( $sender->x - 0.5, $sender->y, $sender->z - 0.5, $sender->getLevel () ), $output );
		$sender->sendMessage ( $output );
	}
	public function handleWandCommand(Player $player) {
		$session = & $this->session ( $player );
		if ($session ["wand-usage"]) {
			$session ["wand-usage"] = false;
			$player->sendMessage ( "Wand Deselected" );
			$this->handleDeSelCommand ( $player );
		} else {
			$session ["wand-usage"] = true;
			$player->sendMessage ( "Wand selected" );
			if ($player->getInventory ()->getItemInHand ()->getId () != 292) {
				$player->getInventory ()->setItemInHand ( new Item ( 292 ) );
			}
			$player->sendMessage ( "[WE] Break a block to set the #1 position.\n" );
		}
	}
	public function handleSetPastePositionCommand(Player $player) {
		$session = & $this->session ( $player );
		$session ["paste-pos"] = true;
		$player->sendMessage ( "[WE] Set Paste Location selected" );
		$player->sendMessage ( "[WE] Break a block to set Paste position.\n" );
	}
	public function handleReplaceCommand($cmd, $sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			return;
		}
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"] );
		
		if (! isset ( $args [1] ) or $args [1] == "") {
			$output .= "Usage: //" . $cmd . " <block1> <block2>.\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (! isset ( $args [2] ) or $args [2] == "") {
			$output .= "Usage: //" . $cmd . " <block1> <block2>.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$bid = null;
		$bmeta = null;
		$item1 = null;
		$item2 = null;
		$values = [ ];
		$values = explode ( ":", $args [1] );
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item1 = Item::get ( $args [1], $bmeta );
		} else {
			$item1 = Item::get ( $args [1] );
		}
		if ($item1 == null) {
			$output .= "[WE] Incorrect block #1.\n";
			$sender->sendMessage ( $output );
			return;
		}
		
		$values = [ ];
		$values = explode ( ":", $args [2] );
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item2 = Item::get ( $args [2], $bmeta );
		} else {
			$item2 = Item::get ( $args [2] );
		}
		if ($item2 == null) {
			$output .= "[WE] Incorrect block #2.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$this->getBuilder ()->W_replace ( $session ["selection"], $item1->getBlock (), $item2->getBlock (), $output );
		$sender->sendMessage ( $output );
	}
	public function handleSetCommand($cmd, $sender, $args) {
		$output = "";
		if (! ($sender instanceof Player)) {
			$output = "[WE] Please run this command in-game.\n";
			$sender->sendMessage ( $output );
			return;
		}
		if (count ( $args ) != 2) {
			$output = "Usage: // set <block id:meta>.\n";
			$sender->sendMessage ( $output );
			return;
		}
		
		$session = & $this->session ( $sender );
		$count = $this->countBlocks ( $session ["selection"] );
		// remove restriction
		if ($count > $session ["block-limit"] and $session ["block-limit"] > 0) {
			$output = "[WE] Block limit of " . $session ["block-limit"] . " exceeded, tried to change $count block(s).\n";
			$sender->sendMessage ( $output );
			return;
		}
		$values = explode ( ":", $args [1] );
		$bid = null;
		$bmeta = null;
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$output = "[WE] setting block " . $item;
		$sender->sendMessage ( $output );
		
		$this->getBuilder ()->W_set ( $session ["selection"], $item->getBlock (), $output );
		$sender->sendMessage ( $output );
	}
	public function handleSphereCommand($cmd, $sender, $args, $filled = true) {
		$output = " ";
		if (! ($sender instanceof Player)) {
			$output .= "[WE] Please run this command in-game.\n";
			return;
		}
		
		if (count ( $args ) != 3) {
			$output .= "[WE] Usage: sphere <block id:meta> <radius>.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$radius = abs ( floatval ( $args [2] ) );
		$session = & $this->session ( $sender );
		
		$bid = null;
		$bmeta = null;
		$values = explode ( ":", $args [1] );
		if (count ( $values ) == 1) {
			$bid = $values [0];
		} elseif (count ( $values ) == 2) {
			$bid = $values [0];
			$bmeta = $values [1];
		}
		if ($bmeta != null) {
			$item = Item::get ( $args [1], $bmeta );
		} else {
			$item = Item::get ( $args [1] );
		}
		if ($item == null) {
			$output .= "[WE] Incorrect block.\n";
			$sender->sendMessage ( $output );
			return;
		}
		$pos = $sender->getPosition ();
		$pos->x = $pos->x - 0.5;
		$pos->y = $pos->y - 0.5;
		$pos->z = $pos->z - 0.5;
		
		$sender->sendMessage ( "[WE] Building Sphere - Radius " . $radius . " at " . $pos->x . " " . $pos->y . " " . $pos->z . " using block " . $item . "\n" );
		$this->getBuilder ()->W_sphere ( $pos, $item->getBlock (), $radius, $radius, $radius, $filled, $output );
		$sender->sendMessage ( $output );
		
		// move player away from block
		$pos->x = $pos->x + $radius + 3;
		$pos->y = $pos->y + $radius + 2;
		$sender->teleport ( new Vector3 ( $pos->x, $pos->y, $pos->z ) );
	}
	public function countBlocks($selection, &$startX = null, &$startY = null, &$startZ = null) {
		if (! is_array ( $selection ) or $selection [0] === false or $selection [1] === false or $selection [0] [3] !== $selection [1] [3]) {
			return false;
		}
		$startX = min ( $selection [0] [0], $selection [1] [0] );
		$endX = max ( $selection [0] [0], $selection [1] [0] );
		$startY = min ( $selection [0] [1], $selection [1] [1] );
		$endY = max ( $selection [0] [1], $selection [1] [1] );
		$startZ = min ( $selection [0] [2], $selection [1] [2] );
		$endZ = max ( $selection [0] [2], $selection [1] [2] );
		return ($endX - $startX + 1) * ($endY - $startY + 1) * ($endZ - $startZ + 1);
	}
	public static function lengthSq($x, $y, $z) {
		return ($x * $x) + ($y * $y) + ($z * $z);
	}
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
	protected function getBuilder() {
		return new WorldEditBuilder ( $this->pgin );
	}
	protected function getPlugIn() {
		return $this->pgin;
	}
}
