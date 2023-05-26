<?php
namespace CosmicCore;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;

class Reward
{

    public function __construct(CosmicCore $plugin)
    {
        $this->plugin = $plugin;
    }

    public function giveRandomTitleTo($p)
    {
        $titles = $this->plugin->titles();
        unset($titles[0]);// Remove "Noob" title.
        $title = $this->getRandomKey($titles);
        $this->plugin->titleRetrieve($p, $title);
    }

    public function strike($pos)
    {
        return $this->plugin->strike($pos);
    }

    public function getRandomKey($array)
    {
        for ($i = 0; $i < count($array); $i++) {
            shuffle($array);
        }
        $random = $array[mt_rand(0, count($array) - 1)];
        return $random;
    }

    public function randomize($array)
    {
        for ($i = 0; $i < count($array); $i++) {
            shuffle($array);
        }
        $max = mt_rand(2, 4);
        $range = range(0, $max);
        $rand = [];
        foreach ($range as $r) {
            $random = $array[mt_rand(0, count($array) - 1)];
            array_push($rand, $random);
        }
        return array_unique($rand);
    }

    public function getVanillaEnchants($item)
    {
        if ($item == "sword") $array = array(9, 10, 11, 12, 13, 14);
        if ($item == "helmet") $array = array(0, 1, 3, 4, 5, 6, 7);
        if ($item == "chestplate") $array = array(0, 1, 3, 4, 5);
        if ($item == "leggings") $array = array(0, 1, 3, 4, 5);
        if ($item == "boots") $array = array(0, 1, 3, 4, 5, 7, 8);
        $randomized = $this->randomize($array);
        return $randomized;
    }

    public function getRandomEnchants($type)
    {
        $array = array();
        if ($type === "bow") $array = array(19, 20, 21, 22, 106, 107, 110, 118, 119, -1);
        if ($type === "sword") $array = array(9, 10, 11, 12, 13, 14, 105, 107, 108, 110, 106, 113, 114, 118, 119, -1);
        if ($type === "helmet") $array = array(0, 1, 3, 4, 5, 6, 7, 101, 109, 111, 122, 124, -1);
        if ($type === "chestplate") $array = array(0, 1, 3, 4, 5, 102, 111, 122, 124, -1);
        if ($type === "leggings") $array = array(0, 1, 3, 4, 5, 111, 122, 124, -1);
        if ($type === "boots") $array = array(0, 1, 3, 4, 5, 7, 8, 103, 104, 111, 122, 124, -1);
        return $this->randomize($array);
    }

    public function getRandomName($item)
    {
        if ($item == "sword") $names = array("Sol Edge", "Arc Edge", "Dark Drinker", "The Universal Remote", "No Time To Explain", "The OP Banana", "A Strange Weapon", "Secret Handshake", "Heaven Can Wait", "A Cosmonaut's Word");
        if ($item == "bow") $names = array("Time On Target", "Wireless Hotspot", "Plan C", "Time And Patience", "420 Blaze It", "Supreme Vision", "Judgement XIX");
        if ($item == "helmet") $names = array("Obsidian Mind", "A Safety Precaution", "An Unordinary Skull", "Protection From The Noob's Touch", "The Veteran Crown");
        if ($item == "chestplate") $names = array("The Undying Heart", "Starfire Protocol", "Fear Eater", "A Human Plate");
        if ($item == "leggings") $names = array("Party Pants", "Hollow Bones", "Tireless Striders", "These Legs Don't Hurt", "Immanent War Legs");
        if ($item == "boots") $names = array("Boots Of No Tomorrow", "Exodus Boots", "All On Spikes");
        $randName = $this->getRandomKey($names);
        return $randName;
    }

    public function giveOPBlockTo($p)
    {
        $blocks = array(49, 246, 247);
        $rand_block = $blocks[mt_rand(0, count($blocks) - 1)];
        $counts = array(16, 32, 48, 64);
        $rand_count = $counts[mt_rand(0, count($counts) - 1)];
        $final = Item::get($rand_block, 0, $rand_count);
        $p->getInventory()->addItem($final);
    }

    public function giveRandomPotionTo($p)
    {
        switch (mt_rand(1, 2)) {
            case 1:
                $type = 373;
                break;
            case 2:
                $type = 438;
                break;
        }
        $rand_meta = mt_rand(5, 35);
        $rand_count = mt_rand(2, 6);
        if ($rand_meta = 23 || $rand_meta = 24) $rand_meta = 22;
        $final = Item::get($type, $rand_meta, $rand_count);
        $p->getInventory()->addItem($final);
    }

    public function giveVanillaSwordTo($p)
    {
        $swords = array(258, 279, 267, 276);
        $swordR = $this->getRandomKey($swords);
        $vanillaSword = Item::get($swordR, 0, 1);
        $names = array("Mighty Sword Of Poofless", "Mighty Weapon Of Preston", "Pleb Weapon Of Poofless", "Pleb Weapon Of Preston", "Preston's Weapon", "Poofless's Weapon", "A Vanilla Weapon");
        $name = $this->getRandomKey($names);
        $vanillaSword->setCustomName(TF::YELLOW . $name);
        foreach ($this->getVanillaEnchants("sword") as $se) {
            $this->ce($vanillaSword, $se);
        }
        $p->getInventory()->addItem($vanillaSword);
    }

    public function giveVanillaArmorTo($p)
    {
        $armor = array(306, 307, 308, 309, 310, 311, 312, 313);
        $armorR = $this->getRandomKey($armor);
        $vanillaArmor = Item::get($armorR, 0, 1);
        $names = array("Mighty Armor Of Poofless", "Mighty Armor Of Preston", "Pleb Armor Of Poofless", "Pleb Armor Of Preston", "Preston's Armor", "Poofless's Armor", "Pleb Gear", "The Vanilla Gear");
        $name = $this->getRandomKey($names);
        $vanillaArmor->setCustomName(TF::YELLOW . $name);
        if ($armorR == 306 || $armorR == 310) $armorType = "helmet";
        if ($armorR == 307 || $armorR == 311) $armorType = "chestplate";
        if ($armorR == 308 || $armorR == 312) $armorType = "leggings";
        if ($armorR === 309 || $armorR == 313) $armorType = "boots";
        foreach ($this->getVanillaEnchants($armorType) as $ae) {
            $this->ce($vanillaArmor, $ae);
        }
        $p->getInventory()->addItem($vanillaArmor);
    }

    public function giveOpSwordTo($p)
    {
        $swords = array(258, 279, 267, 276, 258, 279, 267);
        $swordR = $this->getRandomKey($swords);
        $legendarySword = Item::get($swordR, 0, 1);
        $legendarySword->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("sword"));
        foreach ($this->getRandomEnchants("sword") as $se) {
            $this->ce($legendarySword, $se);
        }
        $p->getInventory()->addItem($legendarySword);
    }

    public function giveOpBowTo($p)
    {
        $bow = Item::get(261, 0, 1);
        $bow->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("bow"));
        foreach ($this->getRandomEnchants("bow") as $be) {
            $this->ce($bow, $be);
        }
        $p->getInventory()->addItem($bow);
    }

    public function giveOpHelmetTo($p)
    {
        $helmets = array(302, 306, 310);
        $helmetR = $this->getRandomKey($helmets);
        $helmet = Item::get($helmetR, 0, 1);
        $helmet->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("helmet"));
        foreach ($this->getRandomEnchants("helmet") as $he) {
            $this->ce($helmet, $he);
        }
        $p->getInventory()->addItem($helmet);
    }

    public function giveOpChestplateTo($p)
    {
        $chestplates = array(303, 307, 311);
        $chestplateR = $this->getRandomKey($chestplates);
        $chestplate = Item::get($chestplateR, 0, 1);
        $chestplate->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("chestplate"));
        foreach ($this->getRandomEnchants("chestplate") as $che) {
            $this->ce($chestplate, $che);
        }
        $p->getInventory()->addItem($chestplate);
    }

    public function giveOpLeggingsTo($p)
    {
        $leggings = array(304, 308, 312);
        $leggingR = $this->getRandomKey($leggings);
        $legging = Item::get($leggingR, 0, 1);
        $legging->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("leggings"));
        foreach ($this->getRandomEnchants("leggings") as $lege) {
            $this->ce($legging, $lege);
        }
        $p->getInventory()->addItem($legging);
    }

    public function giveOpBootsTo($p)
    {
        $bootss = array(305, 309, 313);
        $bootsR = $this->getRandomKey($bootss);
        $boots = Item::get($bootsR, 0, 1);
        $boots->setCustomName(TF::LIGHT_PURPLE . $this->getRandomName("boots"));
        foreach ($this->getRandomEnchants("boots") as $boe) {
            $this->ce($boots, $boe);
        }
        $p->getInventory()->addItem($boots);
    }

    public function ce($i, $e, $levelE = null)
    {
        $cn = $i->getCustomName();
        $enchant = Enchantment::getEnchantment($e);
        if (isset($levelE)) {
            $enchant->setLevel($levelE);
        } else {
            if ($e !== 111) $enchant->setLevel(mt_rand(1, 5));
        }
        $level = $enchant->getLevel();
        $l = $this->useLevelSystem($level);
        if ($e === 100) {
            $name = "Blindness";
        } elseif ($e === 101) {
            $name = "Glowing";
        } elseif ($e === 102) {
            $name = "Obsidian Shield";
        } elseif ($e === 103) {
            $name = "Gears";
        } elseif ($e === 104) {
            $name = "Springs";
        } elseif ($e === 105) {
            $name = "Confusion";
        } elseif ($e === 106) {
            $name = "Lightning";
        } elseif ($e === 107) {
            $name = "Poison";
        } elseif ($e === 108) {
            $name = "Frozen";
        } elseif ($e === 109) {
            $name = "Aquatic";
        } elseif ($e === 110) {
            $name = "Double Damage";
        } elseif ($e === 111) {
            $name = TF::RED . "Overload";
        } elseif ($e === 113) {
            $name = "Demonforge";
        } elseif ($e === 114) {
            $name = "Featherweight";
        } elseif ($e === 115) {
            $name = "Disappearer";
        } elseif ($e === 116) {
            $name = "Lifesteal";
        } elseif ($e === 117) {
            $name = "Disarmor";
        } elseif ($e === 118) {
            $name = "Obliteration";
        } elseif ($e === 119) {
            $name = "Wither";
        } elseif ($e === 120) {
            $name = "VeinGlory";
        } elseif ($e === 122) {
            $name = "Nutrition";
        } elseif ($e === 124) {
            $name = "Armoured";
        }
        if ($e === 111) $enchant->setLevel(1);
        if ($e > 99) {
            $i->setCustomName($cn . "\n" . TF::GRAY . $name . " " . $l);
        }
        $i->addEnchantment($enchant);
    }

    public function useLevelSystem($level)
    {
        if ($level === 1) {
            return "I";
        } elseif ($level === 2) {
            return "II";
        } elseif ($level === 3) {
            return "III";
        } elseif ($level === 4) {
            return "IV";
        } else {
            return "V";
        }
    }

    public function rewardPlayer($player)
    {
        switch (mt_rand(1, 5)) {
            case 1:
                $this->giveOpHelmetTo($player);
                break;
            case 2:
                $this->giveOpChestplateTo($player);
                break;
            case 3:
                $this->giveOpLeggingsTo($player);
                break;
            case 4:
                $this->giveOpBootsTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 2)) {
            case 1:
                $this->giveOpSwordTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 2)) {
            case 1:
                $this->giveVanillaSwordTo($player);
                break;
            case 2:
                $this->giveVanillaArmorTo($player);
                break;
        }

        $pickArray = array(278, 257, 0, 0);
        $randomPick = array_rand($pickArray);
        $pickId = $pickArray[$randomPick];
        $pickaxe = Item::get($pickId, 0, 1);
        $pickaxe->setCustomName(TF::RED . "Against All Odds");
        $this->ce($pickaxe, 120);
        $player->getInventory()->addItem($pickaxe);

        switch (mt_rand(1, 3)) {
            case 1:
                $this->giveOpBowTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 5)) {
            case 1:
                $food = Item::get(322, 1, mt_rand(3, 6));
                break;
            case 2:
                $food = Item::get(322, 0, mt_rand(6, 12));
                break;
            case 3:
                $food = Item::get(320, 0, 64);
                break;
            case 4:
                $food = Item::get(364, 0, 64);
                break;
            default:
                $food = Item::get(0);
                break;
        }

        switch (mt_rand(1, 3)) {
            case 1:
                $this->giveRandomPotionTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 3)) {
            case 1:
                $this->giveOPBlockTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 18)) {
            case 1:
                $this->giveRandomTitleTo($player);
                break;
            default:
                break;
        }

        switch (mt_rand(1, 5)) {
            case 1:
                $spawnEgg = Item::get(383, mt_rand(32, 35), 1);
                break;
            case 2:
                $spawnEgg = Item::get(383, mt_rand(10, 13), 1);
                break;
            default:
                $spawnEgg = Item::get(0);
                break;
        }

        $paperMoney = Item::get(339, mt_rand(500, 22500), 1);

        $food->setCustomName(TF::ITALIC . TF::GREEN . "Free food, cant complain!");
        $player->getInventory()->addItem($food);
        $spawnEgg->setCustomName(TF::LIGHT_PURPLE . "Envoic Monster");
        $player->getInventory()->addItem($spawnEgg);
        $paperMoney->setCustomName(TF::GREEN . "Maths Exam Paper");
        switch (mt_rand(1, 5)) {
            case 1:
                $player->getInventory()->addItem($paperMoney);
                break;
            default:
                break;
        }
    }
}
