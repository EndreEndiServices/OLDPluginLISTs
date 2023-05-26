package Annihilation.Arena.Kits;

import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.item.Item;
import cn.nukkit.Player;
import cn.nukkit.item.ItemPotion;
import cn.nukkit.item.enchantment.Enchantment;
import cn.nukkit.utils.TextFormat;

public class Kit {

    public static final int ACROBAT = 0;
    public static final int ARCHER = 1;
    public static final int BERSERKER = 2;
    public static final int CIVILIAN = 3;
    public static final int HANDYMAN = 4;
    public static final int LUMBERJACK = 5;
    public static final int MINER = 6;
    public static final int OPERATIVE = 7;
    public static final int SCOUT = 8;
    public static final int SPY = 9;
    public static final int THOR = 10;
    public static final int WARRIOR = 11;

    protected String name;

    public static Kit[] kits;

    protected Item item;

    protected Item[] items;

    protected int id;

    public Kit(String name, Item item, Integer id, Item[] items) {
        this.items = items;
        this.name = name;
        this.item = item;
        this.id = id;
    }

    public static void init() {
        Kit.kits = new Kit[20];

        kits[ACROBAT] = new Kit("acrobat", Item.get(Item.FEATHER), ACROBAT, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
                Item.get(Item.BOW, 0, 1),
                Item.get(Item.ARROW, 0, 1),
        });
        kits[ARCHER] = new Kit("archer", Item.get(Item.BOW), ARCHER, new Item[]{
                Item.get(Item.BOW, 0, 1),
                Item.get(270, 0, 1),
                Item.get(271, 0, 1),
                Item.get(Item.POTION, 21, 1),
                Item.get(Item.COMPASS, 0, 1),
                Item.get(Item.ARROW, 0, 16),
        });
        kits[BERSERKER] = new Kit("berserker", Item.get(Item.CHAIN_CHESTPLATE), BERSERKER, new Item[]{
                Item.get(Item.STONE_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.POTION, ItemPotion.INSTANT_HEALTH, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[CIVILIAN] = new Kit("civilian", Item.get(Item.CRAFTING_TABLE), CIVILIAN, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.STONE_PICKAXE, 0, 1),
                Item.get(Item.STONE_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[HANDYMAN] = new Kit("handyman", Item.get(Item.ANVIL), HANDYMAN, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[LUMBERJACK] = new Kit("lumberjack", Item.get(Item.STONE_AXE), LUMBERJACK, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.STONE_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[MINER] = new Kit("miner", Item.get(Item.STONE_PICKAXE), MINER, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.STONE_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[OPERATIVE] = new Kit("operative", Item.get(Item.SOUL_SAND), OPERATIVE, new Item[]{
                Item.get(Item.WOODEN_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.SOUL_SAND, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[SCOUT] = new Kit("scout", Item.get(346), SCOUT, new Item[]{
                Item.get(Item.GOLDEN_SWORD, 0, 1),
                Item.get(Item.FISHING_ROD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        /*kits[SPY] = new Kit("spy", Item.get(Item.POTION, ItemPotion.INVISIBLE_LONG), SPY, new Item[]{
                Item.get(Item.GOLDEN_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });
        kits[THOR] = new Kit("thor", Item.get(Item.GOLDEN_AXE), THOR, new Item[]{
                Item.get(Item.GOLDEN_AXE, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.CRAFTING_TABLE, 0, 1),
                Item.get(Item.COMPASS, 0, 1),
        });*/
        kits[WARRIOR] = new Kit("warrior", Item.get(Item.STONE_SWORD), WARRIOR, new Item[]{
                Item.get(Item.STONE_SWORD, 0, 1),
                Item.get(Item.WOODEN_PICKAXE, 0, 1),
                Item.get(Item.WOODEN_AXE, 0, 1),
                Item.get(Item.POTION, ItemPotion.INSTANT_HEALTH, 1),
                Item.get(Item.COMPASS, 0, 1),
        });

        kits[MINER].items[1].addEnchantment(Enchantment.getEnchantment(Enchantment.ID_EFFICIENCY));
        kits[LUMBERJACK].items[2].addEnchantment(Enchantment.getEnchantment(Enchantment.ID_EFFICIENCY));
        kits[WARRIOR].items[1].addEnchantment(Enchantment.getEnchantment(Enchantment.ID_DURABILITY));
        kits[ARCHER].items[0].addEnchantment(Enchantment.getEnchantment(Enchantment.ID_BOW_KNOCKBACK));

        for (Kit kit : kits) {
            if(kit == null){
                continue;
            }
            for (Item item : kit.items) {
                item.setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound");
            }
        }
    }

    public String getName() {
        return this.name;
    }

    public int getId() {
        return id;
    }

    public void give(Player p) {
        PlayerInventory inv = p.getInventory();

        for (int i = 0; i < this.items.length; i++) {


            inv.setItem(i, this.items[i].clone());
            inv.setHotbarSlotIndex(i, i);
        }

        //return items.clone();
    }

    public Item getItem() {
        return this.item.clone();
    }

    public static Kit getKit(int id) {
        try {
            if (kits[id] != null) {
                return kits[id];
            }
        } catch (Exception e) {
            return kits[CIVILIAN];
        }
        return kits[CIVILIAN];
    }

    public static Kit getKitByName(String name) {
        switch (name.toLowerCase()) {
            case "civilian":
                return kits[CIVILIAN].clone();
            case "miner":
                return kits[MINER].clone();
            case "lumberjack":
                return kits[LUMBERJACK].clone();
            case "berserker":
                return kits[BERSERKER].clone();
            case "scout":
                return kits[SCOUT].clone();
            case "handyman":
                return kits[HANDYMAN].clone();
            case "acrobat":
                return kits[ACROBAT].clone();
            case "archer":
                return kits[ARCHER].clone();
            case "operative":
                return kits[OPERATIVE].clone();
        }

        return kits[CIVILIAN];
    }

    @Override
    public Kit clone() {
        try {
            return (Kit) super.clone();
        } catch (CloneNotSupportedException e) {
            return null;
        }
    }
}