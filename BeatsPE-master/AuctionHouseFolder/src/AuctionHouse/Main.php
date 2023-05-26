<?php

namespace AuctionHouse;

use AuctionHouse\Chest\{CustomChest, CustomChestInventory};

use pocketmine\command\{Command, CommandSender};
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, IntTag, ListTag, StringTag, IntArrayTag};
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat as TF;

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase{

	const PREFIX = TF::BOLD.TF::RED.'' . TF::YELLOW .'§l§dBeats§bAH'.TF::RED.' §8» '.TF::RESET;

	public $inChestShop, $clicks = [];
	private static $instance = null;
	protected $shops = [];
	
	public function onEnable(){
		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		@mkdir($this->getDataFolder());
		if(!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());

		$shops = yaml_parse_file($this->getDataFolder().'shops.yml');
		if(!empty($shops)) foreach($shops as $key => $val) $this->shops[$key] = $val;
		
		Tile::registerTile(CustomChest::class);
	}

	public static function getInstance(){
		return self::$instance;
	}

	public function onDisable(){
		yaml_emit_file($this->getDataFolder().'shops.yml', $this->shops);
	}

	public function sendChestShop(Player $player){

		$tile = Tile::createTile("CustomChest", $player->getLevel(), new CompoundTag("", [

				new StringTag("id", Tile::CHEST),
				new StringTag("CustomName", "Auction House"),
				new IntTag("x", floor($player->x)),
				new IntTag("y", floor($player->y) - 4),
				new IntTag("z", floor($player->z))
			
		]));
		$block = Block::get(Block::CHEST);
		$block->x = $tile->x;
		$block->y = $tile->y;
		$block->z = $tile->z;
		$block->level = $tile->getLevel();
		$block->level->sendBlocks([$player], [$block]);
		$this->fillInventoryWithShop($inventory = $tile->getInventory());
		$player->addWindow($inventory);
	}

	public function getItemFromShop(int $id): Item {
		$data = $this->shops[$id] ?? null;
		$item = null;
		if($data !== null){
			$item = Item::get($data[0], $data[1], $data[2]);
			$item->setNamedTag(unserialize($data[6]));
			$seller = $this->getServer()->getPlayer($data[4]);
			EconomyAPI::getInstance()->addMoney($seller, $data[5]);
			unset($item->getNamedTag()->ChestShop);
			unset($this->shops[$id]);
		}
		return $item ?? Item::get(0);
	}

	public function fillInventoryWithShop(CustomChestInventory $inventory, int $page = 0){
		$inventory->clearAll();
		if(!empty($this->shops)) {
			$chunked = array_chunk($this->shops, 18, true);
			if($page < 0){
				$page = count($chunked) - 1;
			}
			$page = isset($chunked[$page]) ? $page : 0;
			foreach($chunked[$page] as $data){
				$item = Item::get($data[0], $data[1], $data[2]);
				if($data[6] === null) break;
				$item->setNamedTag(unserialize($data[6]));
				$item->setCustomName(
				"§bItem: §f" . $data[3] .
				"\n \n§bSeller: §f" . $data[4] .
				"\n§bPrice: §f" . $data[5] .
				"\n§b Expired Items: §f" . $data[0]
				);
				$inventory->addItem($item);
			}
		}
        // Expired Items
		$expired = Item::get(Item::CHEST);
		$expired->setCustomName(TF::RESET.TF::YELLOW.TF::BOLD.' Expired Items'.TF::RESET."\n".TF::GRAY.'View Expired Items.');
		
		// LEFT PAGE
		$turnleft = Item::get(Item::PAPER);
		$turnleft->setCustomName(TF::RESET.TF::YELLOW.TF::BOLD.'<< Previous Page'.TF::RESET."\n".TF::GRAY.'View the previous page of auctions.');
		$nbtleft = $turnleft->getNamedTag();
		$nbtleft->turner = new IntArrayTag('turner', [0, $page]);
		$turnleft->setNamedTag($nbtleft);

		// RIGHT PAGE
		$turnright = Item::get(Item::PAPER);
		$turnright->setCustomName(TF::RESET.TF::YELLOW.TF::BOLD.'Next Page >>'.TF::RESET."\n".TF::GRAY.'View the next page of auctions.');
		$nbtright = $turnright->getNamedTag();
		$nbtright->turner = new IntArrayTag('turner', [1, $page]);
		$turnright->setNamedTag($nbtright);

		// HOW TO SELL
		$howToSell = Item::get(266, 0, 1);
		$howToSell->setCustomName(TF::RESET.TF::YELLOW.TF::BOLD.'How to Sell'.TF::RESET."\n".TF::GRAY.'To sell an item to auction house, just hold'.TF::RESET."\n".TF::GRAY.'the item in your hand and type '.TF::YELLOW.'/ah sell <price>');
		
			$inventory->setItem(18, $expired);
			$inventory->setItem(21, $turnleft);
			$inventory->setItem(23, $turnright);
			$inventory->setItem(26, $howToSell);
	}

	public function addToChestShop($player, $item, $price) {
        $key = rand();
		
        $name = strtolower($player->getName());
        $cloned = Item::get($item->getId(), $item->getDamage());
        $itemname = $cloned->getName();
        $itemcount = $item->getCount();

		if(isset($this->shops[$key])){
			while(isset($this->shops[$key])){
				$key = rand();
			}
		}
		
		$nbt = $item->getNamedTag() ?? new CompoundTag("", []);
		$nbt->ChestShop = new IntArrayTag('ChestShop', [$price, $key]);
		$nbt->CSKey = $key;
		$item->setNamedTag($nbt);
		
        $this->shops[$key] = [
			$item->getId(),
			$item->getDamage(),
			$item->getCount(),
			$itemname,
			$name,
			$price,
			serialize($nbt)
		];
        $player->sendMessage(self::PREFIX.TF::GREEN.'You have successfully placed your '.TF::BOLD.$itemname.TF::RESET.TF::GRAY.' (x'.$itemcount.')'.TF::GREEN.' for $'.$price.' on auction.');
    }

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		
		if (strtolower($cmd->getName()) === 'ah') {
            if (count($args) === 0) {
                $this->sendChestShop($sender);
            } else {
                switch (strtolower($args[0])) {
                    case 'sell':
						if($sender instanceof Player){
							$item = $sender->getInventory()->getItemInHand();
							if($item->getId() === 0){
								$sender->sendMessage(self::PREFIX.TF::RED.'Please hold an item in your hand.');
								return true;
							} else{
								if(isset($args[1]) && is_numeric($args[1]) && $args[1] >= 0) {
									$sender->getInventory()->remove($item);
									$this->addToChestShop($sender, $item, $args[1]);
								} else $sender->sendMessage(TF::RED.'Please enter a valid number.');
							}
						}
                    break;
                }
            }
        }
		
		return true;
	}
}