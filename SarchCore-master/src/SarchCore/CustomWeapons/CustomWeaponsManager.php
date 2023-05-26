<?php

namespace SarchCore\CustomWeapons;

use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use SarchCore\SarchCore;

class CustomWeaponsManager implements Listener {

	private $plugin;

	public function __construct(SarchCore $plugin) {
		$this->plugin = $plugin;
		$galadhrim = Item::get(Item::BOW, 0, 1);
		$galadhrim->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Galadhrim");
		$galadhrim->addEnchantment(Enchantment::getEnchantment(Enchantment::TYPE_BOW_INFINITY));
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_BOW_POWER);
		$ench->setLevel(4);
		$galadhrim->addEnchantment($ench);
		$recipe = new ShapedRecipe($galadhrim, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::STICK, 0, 1));
		$recipe->addIngredient(0, 1, Item::get(Item::DIAMOND, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::STICK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::GLOWSTONE, 0, 1));
		$recipe->addIngredient(1, 1, Item::get(Item::BOW, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::GLOWSTONE, 0, 1));
		$recipe->addIngredient(2, 0, Item::get(Item::STICK, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::DIAMOND, 0, 1));
		$recipe->addIngredient(2, 2, Item::get(Item::STICK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$stt = Item::get(Item::DIAMOND_SWORD, 0, 1);
		$stt->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Sword of a Thousand Truths");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_SHARPNESS);
		$ench->setLevel(2);
		$stt->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_ARTHROPODS);
		$ench->setLevel(3);
		$stt->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_LOOTING);
		$ench->setLevel(2);
		$stt->addEnchantment($ench);
		$recipe = new ShapedRecipe($stt, 3, 3);
		$recipe->addIngredient(0, 1, Item::get(Item::NETHER_STAR, 0, 1));
		$recipe->addIngredient(1, 1, Item::get(Item::IRON_SWORD, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::SPIDER_EYE, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$zaxe = Item::get(Item::STONE_AXE, 0, 1);
		$zaxe->setCustomName(TextFormat::RESET, TextFormat::LIGHT_PURPLE . "Zeus' Axe");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_SMITE);
		$ench->setLevel(4);
		$zaxe->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_SHARPNESS);
		$ench->setLevel(5);
		$zaxe->addEnchantment($ench);
		$zaxe->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_MINING_EFFICIENCY);
		$ench->setLevel(5);
		$zaxe->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_ARTHROPODS);
		$ench->setLevel(5);
		$zaxe->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_MINING_SILK_TOUCH);
		$ench->setLevel(1);
		$zaxe->addEnchantment($ench);
		$recipe = new ShapedRecipe($zaxe, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::GOLD_BLOCK, 0, 1));
		$recipe->addIngredient(0, 1, Item::get(Item::NETHER_STAR, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::GOLD_BLOCK, 0, 1));
		$recipe->addIngredient(1, 1, Item::get(Item::DIAMOND_AXE, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$mjolnir = Item::get(Item::IRON_AXE, 0, 1);
		$mjolnir->setCustomName(TextFormat::RESET . TextFormat::RED . "Mjolnir");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_SHARPNESS);
		$ench->setLevel(3);
		$mjolnir->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_SMITE);
		$ench->setLevel(5);
		$mjolnir->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_WEAPON_ARTHROPODS);
		$ench->setLevel(4);
		$mjolnir->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_MINING_EFFICIENCY);
		$ench->setLevel(4);
		$mjolnir->addEnchantment($ench);
		$recipe = new ShapedRecipe($mjolnir, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::IRON_BLOCK, 0, 1));
		$recipe->addIngredient(0, 1, Item::get(Item::IRON_BLOCK, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::IRON_BLOCK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::LEATHER, 0, 1));
		$recipe->addIngredient(1, 1, Item::get(Item::BLAZE_ROD, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::LEATHER, 0, 1));
		$recipe->addIngredient(2, 0, Item::get(Item::LEATHER, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::BLAZE_ROD, 0, 1));
		$recipe->addIngredient(2, 2, Item::get(Item::LEATHER, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$heavyhelm = Item::get(Item::DIAMOND_HELMET, 0, 1);
		$heavyhelm->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heavy Diamond Helmet");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROTECTION);
		$ench->setLevel(2);
		$heavyhelm->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION);
		$ench->setLevel(4);
		$heavyhelm->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION);
		$ench->setLevel(4);
		$heavyhelm->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_THORNS);
		$ench->setLevel(2);
		$heavyhelm->addEnchantment($ench);
		$recipe = new ShapedRecipe($heavyhelm, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(0, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$heavyboot = Item::get(Item::DIAMOND_BOOTS, 0, 1);
		$heavyboot->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heavy Diamond Boots");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROTECTION);
		$ench->setLevel(2);
		$heavyboot->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_FALL_PROTECTION);
		$ench->setLevel(1);
		$heavyboot->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION);
		$ench->setLevel(3);
		$heavyboot->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_THORNS);
		$ench->setLevel(2);
		$heavyboot->addEnchantment($ench);
		$recipe = new ShapedRecipe($heavyboot, 3, 3);
		$recipe->addIngredient(2, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$heavychest = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
		$heavychest->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heavy Diamond Chestplate");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROTECTION);
		$ench->setLevel(4);
		$heavychest->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_FIRE_PROTECTION);
		$ench->setLevel(4);
		$heavychest->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION);
		$ench->setLevel(3);
		$heavychest->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION);
		$ench->setLevel(4);
		$heavychest->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_THORNS);
		$ench->setLevel(2);
		$heavychest->addEnchantment($ench);
		$recipe = new ShapedRecipe($heavychest, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
		$heavylegs = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
		$heavylegs->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heavy Diamond Leggings");
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROTECTION);
		$ench->setLevel(3);
		$heavylegs->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_FIRE_PROTECTION);
		$ench->setLevel(4);
		$heavylegs->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION);
		$ench->setLevel(3);
		$heavylegs->addEnchantment($ench);
		$ench = Enchantment::getEnchantment(Enchantment::TYPE_ARMOR_PROJECTILE_PROTECTION);
		$ench->setLevel(3);
		$heavylegs->addEnchantment($ench);
		$recipe = new ShapedRecipe($heavylegs, 3, 3);
		$recipe->addIngredient(0, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(0, 1, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(0, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(1, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 0, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$recipe->addIngredient(2, 2, Item::get(Item::DIAMOND_BLOCK, 0, 1));
		$this->plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
	}

	public function onChange(EntityArmorChangeEvent $ev) {
		$p = $ev->getEntity();
		if(!$p instanceof Player) {
			return;
		}
		$old = $ev->getOldItem();
		$new = $ev->getNewItem();
		$effect = Effect::getEffect(Effect::SLOWNESS)->setAmplifier(1)->setDuration(2147483648);
		if($old->getId() === Item::DIAMOND_HELMET && $old->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $old->getName()) {
			$p->removeEffect($effect->getId());
			return;
		} elseif($old->getId() === Item::DIAMOND_CHESTPLATE && $old->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $old->getName()) {
			$p->removeEffect($effect->getId());
			return;
		} elseif($old->getId() === Item::DIAMOND_LEGGINGS && $old->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $old->getName()) {
			$p->removeEffect($effect->getId());
			return;
		} elseif($old->getId() === Item::DIAMOND_BOOTS && $old->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $old->getName()) {
			$p->removeEffect($effect->getId());
			return;
		}
		if($new->getId() === Item::DIAMOND_HELMET && $new->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $new->getName()) {
			$p->addEffect($effect);
			return;
		} elseif($new->getId() === Item::DIAMOND_CHESTPLATE && $new->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $new->getName()) {
			$p->addEffect($effect);
			return;
		} elseif($new->getId() === Item::DIAMOND_LEGGINGS && $new->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $new->getName()) {
			$p->addEffect($effect);
			return;
		} elseif($new->getId() === Item::DIAMOND_BOOTS && $new->getCustomName() === TextFormat::RESET . TextFormat::GOLD . "Heavy " . $new->getName()) {
			$p->addEffect($effect);
			return;
		}
	}
}