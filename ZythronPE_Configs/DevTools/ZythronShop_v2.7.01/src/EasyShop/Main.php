<?php
namespace EasyShop;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\{Item, ItemBlock};
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use EasyShop\Modals\elements\{Dropdown, Input, Button, Label, Slider, StepSlider, Toggle};
use EasyShop\Modals\network\{GuiDataPickItemPacket, ModalFormRequestPacket, ModalFormResponsePacket, ServerSettingsRequestPacket, ServerSettingsResponsePacket};
use EasyShop\Modals\windows\{CustomForm, ModalWindow, SimpleForm};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor};

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
  public $shop;
  public $item;

  //documentation for setting up the items
  /*
  "Item name" => [item_id, item_damage, buy_price, sell_price]
  */
public $Blocks = [
    "ICON" => ["Blocks",2,0],
    "Grass" => [2,0,150,15],
    "TNT" => [46,0,2500,250],
    "Obsidian" => [49,0,5000,500],
    "Bedrock" => [7,0,10000,1000],
    "Chest" => [54,0,500,50],
    "Stone" => [1,0,150,15],
    "Wood" => [17,0,150,12],
    "Sand" => [12,0,250,25],
    "Redstone Block" => [152,0,2500,250],
    "Lapis Block" => [22,0,2500,250],
    "Iron Block" => [42,0,3500,350],
    "Gold Block" => [41,0,4500,450],
    "Diamond Block" => [57,0,6500,650],
    "Water Flowing" => [8,0,2500,250],
    "Lava Flowing" => [10,0,2500,250]
  ];

  public $Ores = [
    "ICON" => ["Ores",266,0],
    "Coal Ore" => [16,0,500,50],
    "Redstone Ore" => [73,0,1000,100],
    "Lapis Lazuli Ore" => [21,0,1000,100],
    "Iron Ore" => [15,0,3000,300],
    "Gold Ore" => [14,0,3500,350],
    "Diamond Ore" => [56,0,5000,500],
    "Coal" => [263,0,250,25],
    "Redstone" => [331,0,500,50],
    "Lapis Lazuli" => [331,0,500,50],
    "Iron Ingot" => [265,0,1500,150],
    "Gold Ingot" => [266,0,1750,175],
    "Diamond" => [264,0,2500,250]
  ];

  public $Tools = [
    "ICON" => ["Tools",278,0],
    "Wooden Pickaxe" => [270,0,250,10],
    "Wooden Shovel" => [269,0,250,10],
    "Wooden Axe" => [271,0,250,10],
    "Wooden Hoe" => [290,0,250,10],
    "Wooden Sword" => [268,0,500,20],
    "Stone Pickaxe" => [274,0,500,20],
    "Stone Shovel" => [273,0,500,20],
    "Stone Axe" => [275,0,500,20],
    "Stone Hoe" => [291,0,500,20],
    "Stone Sword" => [272,0,750,50],
    "Iron Pickaxe" => [257,0,1000,70],
    "Iron Shovel" => [256,0,1000,70],
    "Iron Axe" => [258,0,1000,70],
    "Iron Hoe" => [292,0,1000,70],
    "Iron Sword" => [267,0,1500,120],
    "Diamond Pickaxe" => [278,0,2500,250],
    "Diamond Shovel" => [277,0,2500,250],
    "Diamond Axe" => [279,0,2500,250],
    "Diamond Hoe" => [293,0,2500,220],
    "Diamond Sword" => [276,0,3000,270],
    "Bow" => [261,0,2500,100],
    "Arrow" => [262,0,125,5],
    "Flint & Steel" => [259,0,750,50]
  ];

  public $Armor = [
    "ICON" => ["Armor",311,0],
    "Leather Helmet" => [298,0,250,10],
    "Leather Chestplate" => [299,0,350,20],
    "Leather Leggings" => [300,0,350,20],
    "Leather Boots" => [301,0,250,10],
    "Gold Helmet" => [314,0,500,20],
    "Gold Chestplate" => [315,0,750,50],
    "Gold Leggings" => [316,0,750,50],
    "Gold Boots" => [317,0,500,20],
    "Chain Helmet" => [302,0,750,50],
    "Chain Chestplate" => [303,0,1000,70],
    "Chain Leggings" => [304,0,1000,75],
    "Chain Boots" => [305,0,750,50],
    "Iron Helmet" => [306,0,1250,100],
    "Iron Chestplate" => [307,0,1500,125],
    "Iron Leggings" => [308,0,1500,120],
    "Iron Boots" => [309,0,1250,100],
    "Diamond Helmet" => [310,0,7500,500],
    "Diamond Chestplate" => [311,0,10000,750],
    "Diamond Leggings" => [312,0,10000,750],
    "Diamond Boots" => [313,0,7500,500]
  ];

  public $Potions = [
    "ICON" => ["Potions",374,0],
    "Glass Bottle" => [374,0,500,0],
    "Water Bottle" => [373,0,750,0],
    "Night Vision [3:00]" => [373,5,1000,0],
    "Leaping [3:00]" => [373,9,1000,0],
    "Fire Resistance [3:00]" => [373,12,1000,0],
    "Swiftness [3:00]" => [373,14,1000,0],
    "Water Breathing [3:00]" => [373,19,1000,0],
    "Instant Health" => [373,21,1000,0],
    "Regeneration [0:45]" => [373,28,1000, 0],
    "Strength [3:00]" => [373,31,1000, 0]
  ];

  public $Food = [
    "ICON" => ["Food",364,0],
    "Steak" => [364,0,150,125],
    "Golden Apple" => [322,0,1500,1250],
    "Enchanted Golden Apple" => [466,0,12500,7500],
    "Carrot" => [391,0,100,50],
    "Chicken" => [365,0,100,50]
  ];

  public $Miscellaneous = [
    "ICON" => ["Miscellaneous",368,0],
    "Enderpearl" => [368,0,250,150],
    "String" => [287,0,150,125],
    "Feather" => [288,0,150,125],
    "Leather" => [334,0,250,200],
    "Spider Eye" => [375,0,250,200],
    "Bone" => [352,0,150,125],
    "Gunpowder" => [289,0,75,50],
    "Blaze Rod" => [369,0,150,125],
    "Rotten Flesh" => [367,0,75,50],
    "Pumpkin Seeds" => [361,0,250,175],
    "Melon Seeds" => [362,0,250,175],
    "Wheat Seeds" => [295,0,250,175],
    "Book & Quill" => [386,0,150,0]
  ];

  public $Spawners = [
    "ICON" => ["Spawners",52,0],
    "Chicken" => [52,10,10000,5000],
    "Cow" => [52,11,10000,5000],
    "Sheep" => [52,13,10000,5000],
    "Skeleton" => [52,34,15000,10000],
    "Zombie" => [52,32,25000,15000],
    "Blaze" => [52,43,50000,25000],
    "Iron Golem" => [52,20,1000000,500000],
    "Zombie Pigman" => [52,36,100000,50000]
  ];

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    PacketPool::registerPacket(new GuiDataPickItemPacket());
		PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
    $this->item = [$this->Blocks, $this->Ores, $this->Tools, $this->Armor, $this->Potions, $this->Food, $this->Miscellaneous, $this->Spawners];
  }

  public function sendMainShop(Player $player){
    $ui = new SimpleForm("§l§bZythron§6Shop","Made by Zadezter | v2.7.01");
    foreach($this->item as $category){
      if(isset($category["ICON"])){
        $rawitemdata = $category["ICON"];
        $button = new Button($rawitemdata[0]);
        $button->addImage('url', "http://avengetech.me/items/".$rawitemdata[1]."-".$rawitemdata[2].".png");
        $ui->addButton($button);
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 110;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendShop(Player $player, $id){
    $ui = new SimpleForm("§l§bZythron§6Shop","Made by Zadezter | v2.7.01");
    $ids = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $id){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            $button = new Button($name);
            $button->addImage('url', "http://avengetech.me/items/".$item[0]."-".$item[1].".png");
            $ui->addButton($button);
          }
        }
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 111;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendConfirm(Player $player, $id){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $id){
              $this->item[$player->getName()] = $id;
              $iname = $name;
              $cost = $item[2];
              $sell = $item[3];
              break;
            }
          }
          $idi++;
        }
      }
    }

    $ui = new CustomForm($iname);
    $slider = new Slider("Amount ",1,64,1);
    $toggle = new Toggle("Selling");
    if($sell == 0) $sell = "0";
    $label = new Label(TF::GREEN."Buy: $".TF::GREEN.$cost.TF::RED."\nSell: $".TF::RED.$sell);
    $ui->addElement($label);
    $ui->addElement($toggle);
    $ui->addElement($slider);
    $pk = new ModalFormRequestPacket();
    $pk->formId = 112;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sell(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if($sell == 0){
                $player->sendMessage(TF::RED."This Is Not Sellable!");
                return true;
              }
              if($player->getInventory()->contains(Item::get($id,$damage,$amount))){
                $player->getInventory()->removeItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->addMoney($player, $sell);
                $player->sendMessage(TF::GREEN."You Sold $amount $iname For $$sell");
              }else{
                $player->sendMessage(TF::RED."You Do Not Have $amount $iname!");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function purchase(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if(EconomyAPI::getInstance()->myMoney($player) > $cost){
                $player->getInventory()->addItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->reduceMoney($player, $cost);
                $player->sendMessage(TF::GREEN."You Purchased $amount $iname For $$cost");
              }else{
                $player->sendMessage(TF::RED."You Do Not Have Enough Money To Buy $amount $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if($packet instanceof ModalFormResponsePacket){
      $id = $packet->formId;
      $data = $packet->formData;
      $data = json_decode($data);
      if($data === Null) return true;
      if($id === 110){
        $this->shop[$player->getName()] = $data;
        $this->sendShop($player, $data);
        return true;
      }
      if($id === 111){
        //$this->shop[$player->getName()] = $data;
        $this->sendConfirm($player, $data);
        return true;
      }
      if($id === 112){
        $selling = $data[1];
        $amount = $data[2];
        if($selling){
          $this->sell($player, $data, $amount);
          return true;
        }
        $this->purchase($player, $data, $amount);
        return true;
      }
    }
    return true;
  }

  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
    switch(strtolower($command)){
      case "shop":
        $this->sendMainShop($player);
        return true;
    }
  }

}
