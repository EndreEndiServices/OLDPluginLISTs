<?php
namespace CosmicCore;

use pocketmine\entity\Effect;
use pocketmine\utils\TextFormat as TF;

class CustomEnchantments
{

    public function __construct(CosmicCore $plugin)
    {
        $this->plugin = $plugin;
    }

    /* $this->ce->callEnchant("Blindness", $damager, $victim, $it->getEnchantment(100)->getLevel(), $event); */
    public function callEnchant($enchant, $damager, $victim, $level, $event)
    {
        $it = $damager->getItemInHand();
        $enchant = strtolower($enchant);
        $blindness = Effect::getEffect(15)->setVisible(false);
        $confusion = Effect::getEffect(9)->setVisible(false);
        $poison = Effect::getEffect(19)->setVisible(false)->setAmplifier(1);
        $weakness = Effect::getEffect(18)->setVisible(false)->setAmplifier(1);
        $slowness = Effect::getEffect(2)->setVisible(false)->setAmplifier(2);
        $haste = Effect::getEffect(3)->setVisible(false)->setAmplifier(2);
        $speed = Effect::getEffect(1)->setVisible(false)->setAmplifier(1);
        $wither = Effect::getEffect(20)->setVisible(false)->setAmplifier(1);
        $strength = Effect::getEffect(5)->setVisible(false)->setAmplifier(1);
        switch ($enchant) {
            case "natureswrath":
                $event->setKnockback(7);
                $nwEffects = array(9, 20, 4, 2);
                $randDuration = mt_rand(500, 750);
                foreach ($nwEffects as $nwEffect) {
                    $victim->addEffect(Effect::getEffect($nwEffect)->setDuration($randDuration)->setVisible(false));
                }
                for ($i = 10; $i <= 50; $i += 10) {
                    $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new \CosmicCore\CE\NaturesWrath($this->plugin, $event->getEntity()), $i);
                }
                break;
            case "disarmor":
                $armr = $victim->getInventory()->getArmorContents();
                $randArmr = $armr[mt_rand(0, 3)];
                $victim->getInventory()->addItem($randArmr);
                $victim->getInventory()->remove($randArmr);
                break;
            case "blindness":
                switch (mt_rand(1, 10) == 1) {
                    case 4:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "ENEMY BLIND!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $blindness->setDuration(200 + 10 * $level);
                        $victim->addEffect($blindness);
                        break;
                }
                break;
            case "confusion":
                switch (mt_rand(1, 10) == 1) {
                    case 4:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "ENEMY CONFUSED!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $confusion->setDuration(200 + 10 * $level);
                        $victim->addEffect($confusion);
                        break;
                }
                break;
            case "disappearer":
                switch (mt_rand(1, 12) == 1) {
                    case 6:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . $victim->getName() . " DISAPPEARED!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $pX = $victim->getX();
                        $pY = $victim->getY() + 10;
                        $pZ = $victim->getZ();
                        $level = $victim->getLevel();
                        $pos = new Position($pX, $pY + $level, $pZ, $level);
                        $victim->teleport($pos);
                        break;
                }
                break;
            case "lightning":
                switch (mt_rand(1, 8) == 1) {
                    case 1:
                    case 2:
                    case 7:
                    case 8:
                        $this->plugin->strike($victim);
                        break;
                    case 6:
                        $d = mt_rand(2, 6);
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "THUNDER STRUCK " . $victim->getName() . " HARD!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "*STRUCK BY LIGHTNING*");
                        $pH = $victim->getHealth();
                        $victim->setHealth($pH - $ld);
                        break;
                }
                break;
            case "poison":
                $rand = 12;
                if ($it->getId() == 261) $rand = 6;
                switch (mt_rand(1, $rand) == 1) {
                    case 6:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . $victim->getName() . " IS POISONED!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $poison->setDuration(200 + $level * 20);
                        $victim->addEffect($poison);
                        break;
                }
                break;
            case "frozen":
                switch (mt_rand(1, 12) == 1) {
                    case 6:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "ENEMY FROZEN!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $weakness->setDuration(200 + $level * 20);
                        $victim->addEffect($weakness);
                        $slowness->setDuration(200 + $level * 20);
                        $victim->addEffect($slowness);
                        break;
                }
                break;
            case "lifesteal":
                switch (mt_rand(1, 12) == 1) {
                    case 6:
                        $damager->sendMessage(TF::GREEN . "+2 HP (Lifesteal)");
                        $plH = $damager->getHealth();
                        $damager->setHealth($plH + 2);
                        $victim->sendMessage(TF::RED . "-2 HP (ENEMY USED LIFESTEAL)");
                        $pH = $victim->getHealth();
                        $victim->setHealth($pH - 2);
                        break;
                }
                break;
            case "doubledamage":
                switch (mt_rand(1, 12) == 1) {
                    case 6:
                        $damager->sendMessage(TF::BOLD . TF::RED . "DAMAGE DOUBLED!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Struck by a greater force!");
                        $event->setDamage($event->getDamage() * 2);
                        break;
                }
                break;
            case "featherweight":
                switch (mt_rand(1, 12) == 1) {
                    case 6:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "Featherweight ACTIVATED!");
                        $victim->sendMessage(TF::BOLD . TF::RED . "Enemy's featherweight is now activated!");
                        $haste->setDuration(200 + 10 * $level);
                        $damager->addEffect($haste);
                        $speed->setDuration(200 + 10 * $level);
                        $damager->addEffect($speed);
                        break;
                }
                break;
            case "obliteration":
                switch (mt_rand(1, 6) == 1) {
                    case 2:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "*OBLITERATION ACTIVATED*");
                        $victim->sendMessage(TF::BOLD . TF::RED . "*OBLITERATED*");
                        $event->setKnockback(1.25);
                        break;
                }
                break;
            case "wither":
                $rand = 8;
                if ($it->getId() == 261) $rand = 6;
                switch (mt_rand(1, $rand) == 1) {
                    case 2:
                        $damager->sendMessage(TF::BOLD . TF::GREEN . "*ENEMY WITHERED*");
                        $victim->sendMessage(TF::BOLD . TF::RED . "*WITHERED*");
                        $wither->setDuration(200 + 10 * $level);
                        $victim->addEffect($wither);
                        break;
                }
                break;
        }
    }

    public function updateArmorEffects($item, $p)
    {
        foreach ($item->getEnchantments() as $en) {
            if (!$item->hasEnchantments()) return;
            if ($this->getEffectByEnchantment($en->getId()) === 0 || $this->getEffectByEnchantment($en->getId()) === null) return;
            if ($this->getEffectByEnchantment($en->getId()) !== 36 && $this->getEffectByEnchantment($en->getId()) !== 111) {
                $p->addEffect(Effect::getEffect($this->getEffectByEnchantment($en->getId()))->setDuration(999999999)->setVisible(false)->setAmplifier(1));
            }
        }
    }

    public function getEffectByEnchantment($en)
    {
        $effect = 0;
        $range = range(-2, 40);
        $effectors = array(101, 102, 103, 104, 109);
        if (!in_array($en, $effectors)) $effect = 36;
        switch ($en) {
            case 101:
                $effect = 16;
                break;
            case 102:
                $effect = 12;
                break;
            case 103:
                $effect = 1;
                break;
            case 104:
                $effect = 8;
                break;
            case 109:
                $effect = 13;
                break;
        }
        return (int)$effect;
    }

    public function nutritionize($p)
    {
        if ($p instanceof Human) {
            if ($p->getFood() <=16) {
                $p->setFood($p->getFood() + 4);
            } else if ($p->getFood() > 16 && $p->getFood() < 20) {
                $p->setFood($p->getFood() + 1);
            }
        }
    }

    public function updateGlobalEnchants($p){
        if ($p->getInventory()->getHelmet()->hasEnchantment(122)) {
            $this->nutritionize($p);
        } elseif ($p->getInventory()->getChestplate()->hasEnchantment(122)) {
            $this->nutritionize($p);
        } elseif ($p->getInventory()->getLeggings()->hasEnchantment(122)) {
            $this->nutritionize($p);
        } elseif ($p->getInventory()->getBoots()->hasEnchantment(122)) {
            $this->nutritionize($p);
        }
    }
}
