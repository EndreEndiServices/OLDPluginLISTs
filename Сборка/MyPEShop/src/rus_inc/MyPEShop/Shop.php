<?php

namespace rus_inc\MyPEShop;

/*
  ##~~~##~##~~##~~#####~~~#####~~~##~~##~~~~~~~######~~~####
  ###~###~~####~~~##~~##~~##~~~~~~~####~~~~~~~~##~~##~~##~~##
  ##~#~##~~~##~~~~#####~~~####~~~~~~##~~~~~~~~~##~~##~~##~~##
  ##~~~##~~~##~~~~##~~~~~~##~~~~~~~####~~~~~~~~##~~##~~##~~##
  ##~~~##~~~##~~~~##~~~~~~#####~~~##~~##~~~~~~~##~~##~~~####

  #####~~~##~~##~~~~~#####~~~##~~##~~~####~~~~~~~~~~~~~######~~##~~##~~~####
  ##~~##~~~####~~~~~~##~~##~~##~~##~~##~~~~~~~~~~~~~~~~~##~~~~###~##~~##~~##
  #####~~~~~##~~~~~~~#####~~~##~~##~~~####~~~~~~~~~~~~~~##~~~~##~###~~##
  ##~~##~~~~##~~~~~~~##~~##~~##~~##~~ ~~~##~~~~~~~~~~~~~##~~~~##~~##~~##~~##
  #####~~~~~##~~~~~~~##~~##~~~####~~~~####~~~#######~~######~~##~~##~~~####
  Эксклюзивное ПО для MyPEX.ru
 */

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use Richen\Economy\Economy;
use pocketmine\network\protocol\BlockEntityDataPacket;
use pocketmine\nbt\NBT;
use pocketmine\utils\Config;

class Shop extends PluginBase implements Listener {

    public $shops = array();
    public $data;

    public function onEnable() {
        $this->cfg=new Config($this->getDataFolder()."Shop.yml", Config::YAML);
        if($this->cfg->getAll()==[]){
            $this->cfg->setAll(
                    
                    
                    
["FOOD"=>[
[413,0,"§7[§3RABBIT STEW§7]",4,1],
[400,0,"§7[§3PIE§7]",1,1],
[366,0,"§7[§3COOKED CHICKEN§7]",4,1],
[364,0,"§7[§3STEAK§7]",4,1],
[360,0,"§7[§3MELON§7]",8,1],
[357,0,"§7[§3COOKIE§7]",8,1],
[354,0,"§7[§3CAKE§7]",1,3],
[322,0,"§7[§3GOLDEN APPLE§7]",1,2],
[320,0,"§7[§3COOKED PORKCHOP§7]",1,2],
[103,0,"§7[§3MELON BLOCK§7]",16,30],
[86,0,"§7[§3PUMPKIN§7]",16,40]



],
"BUILD"=>[
[1,0,"§7[§3STONE§7]",16,2],
[45,0,"§7[§3BRICKS§7]",16,2],
[2,0,"§7[§3GRASS§7]",16,2],
[5,0,"§7[§3OAK WOOD PLANKS§7]",16,2],
[5,1,"§7[§3SPRUCE WOOD PLANKS§7]",16,10],
[5,2,"§7[§3BIRCH WOOD PLANKS§7]",16,10],
[5,3,"§7[§3JUNGLE WOOD PLANKS§7]",16,10],
[5,4,"§7[§3ACACIA WOOD PLANKS§7]",16,10],
[5,5,"§7[§3DARK OAK WOOD PLANKS§7]",16,10],
[17,0,"§7[§3OAK WOOD§7]",16,10],
[17,1,"§7[§3SPRUCE WOOD§7]",16,40],
[17,2,"§7[§3BIRCH WOOD§7]",16,40],
[17,3,"§7[§3JUNGLE WOOD§7]",16,40],
[85,0,"§7[§3OAK FENCE§7]",16,5],
[85,1,"§7[§3SPRUCE FENCE§7]",16,20],
[85,2,"§7[§3BIRCH FENCE§7]",16,20],
[85,3,"§7[§3JUNGLE FENCE§7]",16,20],
[85,4,"§7[§3ACACIA FENCE§7]",16,20],
[85,5,"§7[§3DARK OAK FENCE§7]",16,20],
[1,1,"§7[§3GRANITE§7]",16,4],
[1,2,"§7[§3POLISHED GRANITE§7]",16,5],
[1,3,"§7[§3DIORITE§7]",16,4],
[1,4,"§7[§3POLISHED DIORITE§7]",16,4],
[1,5,"§7[§3ANDESITE§7]",16,4],
[1,6,"§7[§3POLISHED ANDESITE§7]",16,5],
[3,0,"§7[§3DIRT§7]",16,2],
[4,0,"§7[§3COBBLESTONE§7]",16,2],
[12,0,"§7[§3SAND§7]",16,2],
[12,1,"§7[§3RED SAND§7]",16,2],
[13,0,"§7[§3GRAVEL§7]",1,2],
[24,0,"§7[§3SANDSTONE§7]",16,10],
[24,1,"§7[§3CHISELED SANDSTONE§7]",16,10],
[24,2,"§7[§3SMOOTH SANDSTONE§7]",16,10],
[98,0,"§7[§3STONE BRICKS§7]",16,30],
[98,1,"§7[§3MOSSY STONE BRICKS§7]",16,40],
[98,2,"§7[§3CRACKED STONE BRICKS§7]",16,40],
[98,3,"§7[§3CHISELED STONE BRICKS§7]",16,50],
[48,0,"§7[§3MOSS STONE§7]",16,30],
[107,0,"§7[§3OAK FENCE GATE§7]",16,2]






],
"DECORATION"=>[
[110,0,"§7[§3MYCELIUM§7]",16,60],
[111,0,"§7[§3LILY PAD§7]",8,40],
[116,0,"§7[§3ENCHANTING TABLE§7]",16,50],
[121,0,"§7[§3END STONE§7]",16,30],
[123,1,"§7[§3REDSTONE LAMP§7]",16,30],
[101,0,"§7[§3IRON BARS§7]",8,10],
[102,0,"§7[§3GLASS PANE§7]",16,1],
[106,0,"§7[§3VINES§7]",16,20],
[131,0,"§7[§3TRIPWIRE HOOK§7]",16,20],
[133,0,"§7[§3EMERALD BLOCK§7]",1,40],
[145,0,"§7[§3ANVIL§7]",1,20],
[151,0,"§7[§3DAYLIGHT SENSOR§7]",16,20],
[155,0,"§7[§3QUARTZ BLOCK§7]",16,30],
[155,1,"§7[§3CHISELED QUARTZ BLOCK§7]",16,20],
[155,2,"§7[§3QUARTZ PILLAR§7]",16,30],
[165,0,"§7[§3SLIME BLOCK§7]",16,50],
[170,0,"§7[§3HAY BALE§7]",16,30],
[172,0,"§7[§3HARDENED CLAY§7]",16,20],
[91,0,"§7[§3JACK O'LANTERN§7]",16,80],
[87,0,"§7[§3NETHERRACK§7]",16,40],
[88,0,"§7[§3SOUL SAND§7]",16,40],
[89,0,"§7[§3GLOWSTONE§7]",16,20],
[69,0,"§7[§3LEVER§7]",8,5],
[70,0,"§7[§3STONE PRESSURE PLATE§7]",8,10],
[72,0,"§7[§3WOODEN PRESSURE PLATE§7]",8,10],
[78,0,"§7[§3SNOW LAYER§7]",16,5],
[79,0,"§7[§3ICE§7]",16,20],
[80,0,"§7[§3SNOW BLOCK§7]",16,20],
[81,0,"§7[§3CACTUS§7]",16,2],
[82,0,"§7[§3CLAY BLOCK§7]",16,5],
[30,0,"§7[§3COBWEB§7]",1,10],
[32,0,"§7[§3DEAD BUSH§7]",16,30],
[37,0,"§7[§3DANDELION§7]",8,20],
[38,0,"§7[§3POPPY§7]",8,20],
[38,1,"§7[§3BLUE ORCHID§7]",8,20],
[38,2,"§7[§3ALLIUM§7]",8,20],
[38,3,"§7[§3AZURE BLUET§7]",8,20],
[38,4,"§7[§3RED TULIP§7]",8,20],
[38,5,"§7[§3ORANGE TULIP§7]",8,20],
[38,6,"§7[§3WHITE TULIP§7]",8,20],
[38,7,"§7[§3PINK TULIP§7]",8,20],
[38,8,"§7[§3OXEYE DAISY§7]",8,20],
[39,0,"§7[§3BROWN MUSHROOM§7]",8,20],
[40,0,"§7[§3RED MUSHROOM§7]",8,20],
[25,0,"§7[§3NOTEBLOCK§7]",1,50],
[27,0,"§7[§3POWEREDRAIL§7]",16,10],
[28,0,"§7[§3DETECTOR RAIL§7]",16,10],
[18,0,"§7[§3OAK LEAVES§7]",16,40],
[18,1,"§7[§3SPRUCE LEAVES§7]",16,4],
[18,2,"§7[§3BIRCH LEAVES§7]",16,4],
[18,3,"§7[§3JUNGLE LEAVES§7]",16,4],
[19,0,"§7[§3SPONGE§7]",16,10],
[20,0,"§7[§3GLASS§7]",16,2],
[44,0,"§7[§3STONE SLAB§7]",16,20],
[44,1,"§7[§3SANDSTONE SLAB§7]",16,20],
[47,0,"§7[§3BOOKSHELF§7]",16,30],
[49,0,"§7[§3OBSIDIAN§7]",16,30],
[50,0,"§7[§3TORCH§7]",16,1],
[58,0,"§7[§3CRAFTING TABLE§7]",1,1],
[61,0,"§7[§3FURNACE§7]",1,1],
[65,0,"§7[§3LADDER§7]",8,1],
[174,0,"§7[§3PACKED ICE§7]",16,20],
[175,0,"§7[§3SUNFLOWER§7]",16,30],
[175,1,"§7[§3LILAC§7]",16,20],
[175,2,"§7[§3TALLGRASS§7]",16,20],
[175,3,"§7[§3TALLGRASS§7]",16,20],
[175,4,"§7[§3TALLGRASS§7]",16,20],
[355,0,"§7[§3BED§7]",1,10],
[390,0,"§7[§3FLOWER POT§7]",1,5]


],

"COLORED"=>[
[159,0,"§7[§3WHITE STAINED CLAY§7]",16,30],
[159,1,"§7[§3ORANGE STAINED CLAY§7]",16,40],
[159,2,"§7[§3MAGENTA STAINED CLAY§7]",16,40],
[159,3,"§7[§3LIGHT BLUE STAINED CLAY§7]",16,40],
[159,4,"§7[§3YELLOW STAINED CLAY§7]",16,40],
[159,5,"§7[§3LIME STAINED CLAY§7]",16,40],
[159,6,"§7[§3PINK STAINED CLAY§7]",16,40],
[159,7,"§7[§3GRAY STAINED CLAY§7]",16,40],
[159,8,"§7[§3LIGHT GRAY STAINED CLAY§7]",16,40],
[159,9,"§7[§3CYAN STAINED CLAY§7]",16,40],
[159,10,"§7[§3PURPLE STAINED CLAY§7]",16,40],
[159,11,"§7[§3BLUE STAINED CLAY§7]",16,40],
[159,12,"§7[§3BROWN STAINED CLAY§7]",16,40],
[159,13,"§7[§3GREEN STAINED CLAY§7]",16,40],
[159,14,"§7[§3RED STAINED CLAY§7]",16,40],
[159,15,"§7[§3BLACK STAINED CLAY§7]",16,40],
[172,0,"§7[§3HARDENED CLAY§7]",16,20],
[32,0,"§7[§3DEAD BUSH§7]",16,30],
[35,0,"§7[§3WHITE WOOL§7]",16,30],
[35,1,"§7[§3ORANGE WOOL§7]",16,30],
[35,2,"§7[§3MAGENTA WOOL§7]",16,30],
[35,3,"§7[§3LIGHT BLUE WOOL§7]",16,30],
[35,4,"§7[§3YELLOW WOOL§7]",16,30],
[35,5,"§7[§3LIME WOOL§7]",16,30],
[35,6,"§7[§3PINK WOOL§7]",16,30],
[35,7,"§7[§3GRAY WOOL§7]",16,30],
[35,8,"§7[§3LIGHT GRAY WOOL§7]",16,30],
[35,9,"§7[§3CYAN WOOL§7]",16,30],
[35,10,"§7[§3PURPLE WOOL§7]",16,30],
[35,11,"§7[§3BLUE WOOL§7]",16,30],
[35,12,"§7[§3BROWN WOOL§7]",16,30],
[35,13,"§7[§3GREEN WOOL§7]",16,30],
[35,14,"§7[§3RED WOOL§7]",16,30],
[35,15,"§7[§3BLACK WOOL§7]",16,30]


]

]

                    
                    
                    
                    );
            $this->cfg->save();
        }
        $this->assort = $this->cfg->getAll(); //[[1, 0, "§7[§3STONE§7]", 10, 1], [2, 0, "§7[§3TPABA§7]", 10, 1], [5, 0, "§7[§3WOOD§7]", 10, 10], [5, 1, "§7[§3WOOD§7]", 10, 10]]; //[ID,DAMAGE,NAME,КОЛ-ВО ПРИБАВЛЕНИЯ.УБАВЛЕНИЯ ЗА НАЖАТИЕ КНОПКИ +/-,ЦЕНА]

        $this->debug = false;
        $this->Bbuy = new Vector3(31, 64, 362);
        $this->Bimage = new Vector3(31, 65, 362);
        $this->Btext = new Vector3(31, 66, 362);
        $this->Bnext = new Vector3(31, 65, 363);
        $this->Bprev = new Vector3(31, 65, 361);
        $this->Bplus = new Vector3(31, 66, 363);
        $this->Bminus = new Vector3(31, 66, 361);
        $this->BCategory=new Vector3(31, 65, 362);

        $this->sign = [//@i - имя предмета, @c - выбранное кол-во, @p - получившаяся цена @catN - имя категории, @catC - количество вещей в категории (X/Y) (выбрано/всего)
            "@i\n\n\n\n\n\n\n@catN\n@catC",
            "\n§6 -                                              +\n\n\n\n\n\n\n\n\n<<                                              >>",
            "§6@c шт.",
            "§a@p$"
        ];
//                       @catN                       \n                       @catC                       
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function updateShopSign($player) {
        $sign = $player->getLevel()->getTile($this->Btext);
        if ($sign === null)
            return;
        $b = $this->shops[$player->getName()];
        $item = $this->assort[$b["Category"]][$b["Item"]][2];
        $price = ($this->assort[$b["Category"]][$b["Item"]][4]) * (($b["Count"]) / ($this->assort[$b["Category"]][$b["Item"]][3]));
        $count = $b["Count"];

        $data = $sign->getSpawnCompound();
//        str_replace("@catN",$b["Category"], str_replace("@catC",$b["Item"]."/".count($this->assort[$b["Category"]]), $price, $this->sign[0]));
        $data["Text1"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, str_replace("@catN",$b["Category"], str_replace("@catC",1+$b["Item"]."/".count($this->assort[$b["Category"]]), $this->sign[0])))));
        $data["Text2"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, str_replace("@catN",$b["Category"], str_replace("@catC",1+$b["Item"]."/".count($this->assort[$b["Category"]]), $this->sign[1])))));
        $data["Text3"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, str_replace("@catN",$b["Category"], str_replace("@catC",1+$b["Item"]."/".count($this->assort[$b["Category"]]), $this->sign[2])))));
        $data["Text4"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, str_replace("@catN",$b["Category"], str_replace("@catC",1+$b["Item"]."/".count($this->assort[$b["Category"]]), $this->sign[3])))));
//        $data["Text2"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, $this->sign[1])));
//        $data["Text3"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, $this->sign[2])));
//        $data["Text4"] = str_replace("@i", $item, str_replace("@c", $count, str_replace("@p", $price, $this->sign[3])));

        $nbt = new NBT(NBT::LITTLE_ENDIAN);
        $nbt->setData($data);
        $pk = new BlockEntityDataPacket();
        $pk->x = $this->Btext->getX();
        $pk->y = $this->Btext->getY();
        $pk->z = $this->Btext->getZ();
        $pk->namedtag = $nbt->write(true);
        $player->dataPacket($pk);
    }

    public function updateShopImage($player) {
        $image = $player->getLevel()->getTile($this->Bimage);
        if ($image === null)
            return;
        $b = $this->shops[$player->getName()];
        $i = $this->assort[$b["Category"]][$b["Item"]];
        $image->setItem(Item::get($i[0], $i[1], 1), false);
        $image->spawnTo($player);
    }

    public function Join(PlayerJoinEvent $e) {
        foreach($this->cfg->getAll() as $category=>$items){
            $cat=$category;
            break;
        }
        if (@$this->shops[$e->getPlayer()->getName()] == null) {
            $this->shops[$e->getPlayer()->getName()] = [
                "Category"=>$cat,
                "Item" => 0,
                "Count" => $this->assort[$cat][0][3]
            ];
        }
        $this->updateShopImage($e->getPlayer());
        $this->updateShopSign($e->getPlayer());
    }

    public function Tap(PlayerInteractEvent $e) {
        $player = $e->getPlayer();
        $name = $player->getName();
        $b = $e->getBlock();
		
        $cat=$this->shops[$name]["Category"];
        if ($this->debug)
            $player->sendMessage($b->getX() . " " . $b->getY() . " " . $b->getZ());

        if ($b->getX() == $this->Bprev->getX() && $b->getY() == $this->Bprev->getY() && $b->getZ() == $this->Bprev->getZ()) {
            $e->setCancelled();
            if ($this->shops[$name]["Item"] == 0) {
                $this->shops[$name]["Item"] = count($this->assort[$cat]) - 1;
            } else {
                $this->shops[$name]["Item"] -= 1;
            }
            $this->shops[$name]["Count"] = $this->assort[$cat][$this->shops[$name]["Item"]][3];
            $this->updateShopSign($player);
            $this->updateShopImage($player);
        }
        if ($b->getX() == $this->Bnext->getX() && $b->getY() == $this->Bnext->getY() && $b->getZ() == $this->Bnext->getZ()) {
            $e->setCancelled();
            if ($this->shops[$name]["Item"] == count($this->assort[$cat]) - 1) {
                $this->shops[$name]["Item"] = 0;
            } else {
                $this->shops[$name]["Item"] += 1;
            }
            $this->shops[$name]["Count"] = $this->assort[$cat][$this->shops[$name]["Item"]][3];
            $this->updateShopSign($player);
            $this->updateShopImage($player);
        }
        if ($b->getX() == $this->Bminus->getX() && $b->getY() == $this->Bminus->getY() && $b->getZ() == $this->Bminus->getZ()) {
            $e->setCancelled();
            if ($this->shops[$name]["Count"] == $this->assort[$cat][$this->shops[$name]["Item"]][3]) {
                $player->sendPopup("§7[§cSHOP§7] §fДостигнут минимум количества");
            } else {
                $this->shops[$name]["Count"] -= $this->assort[$cat][$this->shops[$name]["Item"]][3];
            }
            $this->updateShopSign($player);
        }
        if ($b->getX() == $this->Bplus->getX() && $b->getY() == $this->Bplus->getY() && $b->getZ() == $this->Bplus->getZ()) {
            $e->setCancelled();
            $this->shops[$name]["Count"] += $this->assort[$cat][$this->shops[$name]["Item"]][3];
            $this->updateShopSign($player);
        }
        if ($b->getX() == $this->Bbuy->getX() && $b->getY() == $this->Bbuy->getY() && $b->getZ() == $this->Bbuy->getZ()) {
            $e->setCancelled();
            $price = ($this->assort[$cat][$this->shops[$name]["Item"]][4]) * (($this->shops[$name]["Count"]) / ($this->assort[$cat][$this->shops[$name]["Item"]][3]));
            $item = Item::get($this->assort[$cat][$this->shops[$name]["Item"]][0], $this->assort[$cat][$this->shops[$name]["Item"]][1], $this->shops[$name]["Count"]);
            $economy = Economy::getInstance();
            if ($economy->myMoney($name) < $price) {
                $player->sendMessage("§7[§cSHOP§7] §fНедостаточно денег для покупки!");
            } elseif (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage("§7[§cSHOP§7] §fНедостаточно места в инвентаре!");
            } else {
                $economy->remMoney($name, $price);
                $player->getInventory()->addItem($item);
                $player->sendMessage("§7[§cSHOP§7] §fСпасибо за покупку!");
            }
        }
        if ($b->getX() == $this->BCategory->getX() && $b->getY() == $this->BCategory->getY() && $b->getZ() == $this->BCategory->getZ()) {
            $e->setCancelled();
            $next=false;
            foreach($this->cfg->getAll() as $category=>$items){
                if($category==$this->shops[$name]["Category"]){
                    $next=true;
                    continue;
                }
                if($next){
                    $this->shops[$name]["Category"]=$category;
                    $next=false;
                    break;
                }
            }
            if($next){
                foreach($this->cfg->getAll() as $category=>$items){
                    $this->shops[$name]["Category"]=$category;
                    $next=false;
                    break;
                 }
            }
            $this->shops[$name]["Item"] = 0;
            $this->shops[$name]["Count"] = $this->assort[$this->shops[$name]["Category"]][$this->shops[$name]["Item"]][3];
            $this->updateShopSign($player);
            $this->updateShopImage($player);
        }
    }

}

?>
