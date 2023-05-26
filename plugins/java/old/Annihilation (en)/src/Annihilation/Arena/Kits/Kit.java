package Annihilation.Arena.Kits;

import cn.nukkit.Player;
import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemPotion;
import cn.nukkit.item.enchantment.Enchantment;
import cn.nukkit.utils.TextFormat;

public enum Kit {
    ACROBAT(Item.get(Item.FEATHER), 0, "", 10000, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.CRAFTING_TABLE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.BOW, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.ARROW, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    ARCHER(Item.get(Item.BOW), 1, "", 10000, new Item[]{
            Item.get(Item.BOW, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.POTION, 21, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.ARROW, 0, 16).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    BERSERKER(Item.get(Item.CHAIN_CHESTPLATE), 2, "", 10000, new Item[]{
            Item.get(Item.STONE_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.POTION, ItemPotion.INSTANT_HEALTH, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    CIVILIAN(Item.get(Item.CRAFTING_TABLE), 3, "", 0, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.STONE_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.STONE_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.CRAFTING_TABLE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    HANDYMAN(Item.get(Item.ANVIL), 4, "", 0, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.CRAFTING_TABLE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    LUMBERJACK(Item.get(Item.STONE_AXE), 5, "", 5000, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.STONE_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.CRAFTING_TABLE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    MINER(Item.get(Item.STONE_PICKAXE), 6, "", 10000, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            addEnchantment(Item.get(Item.STONE_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"), Enchantment.getEnchantment(Enchantment.ID_EFFICIENCY)),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.CRAFTING_TABLE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    OPERATIVE(Item.get(Item.SOUL_SAND), 7, "", 10000, new Item[]{
            Item.get(Item.WOODEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.SOUL_SAND, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    SCOUT(Item.get(346), 8, "", 10000, new Item[]{
            Item.get(Item.GOLDEN_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.FISHING_ROD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    }),
    /*SPY("spy", Item.get(Item.POTION, ItemPotion.INVISIBLE_LONG), 9, "", new Item[]{
            Item.get(Item.GOLDEN_SWORD, 0, 1),
            Item.get(Item.WOODEN_PICKAXE, 0, 1),
            Item.get(Item.WOODEN_AXE, 0, 1),
            Item.get(Item.CRAFTING_TABLE, 0, 1),
            Item.get(Item.COMPASS, 0, 1),
    }),
    THOR("thor", Item.get(Item.GOLDEN_AXE), 10, "", new Item[]{
            Item.get(Item.GOLDEN_AXE, 0, 1),
            Item.get(Item.WOODEN_PICKAXE, 0, 1),
            Item.get(Item.WOODEN_AXE, 0, 1),
            Item.get(Item.CRAFTING_TABLE, 0, 1),
            Item.get(Item.COMPASS, 0, 1),
    }),*/
    WARRIOR(Item.get(Item.STONE_SWORD), 11, "", 5000, new Item[]{
            Item.get(Item.STONE_SWORD, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_PICKAXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.WOODEN_AXE, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.POTION, ItemPotion.INSTANT_HEALTH, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
            Item.get(Item.COMPASS, 0, 1).setCustomName(TextFormat.RESET + TextFormat.GOLD + "SoulBound"),
    });

    private Item item;
    private Item[] items;
    private Integer id;
    private String message;
    private Integer cost;

    Kit(Item item, Integer id, String message, Integer cost, Item[] items) {
        this.item = item;
        this.id = id;
        this.message = message;
        this.items = items;
        this.cost = cost;
    }

    public Integer getId() {
        return id;
    }

    public String getName() {
        return name().toLowerCase();
    }

    public Item[] getItems() {
        return items.clone();
    }

    public Integer getCost() {
        return cost;
    }

    public String getMessage() {
        return message;
    }

    public Item getItem() {
        return item.clone();
    }

    public boolean isFree() {
        return cost <= 0;
    }

    public void give(Player p) {
        PlayerInventory inv = p.getInventory();
        Item[] items = getItems();

        for (int i = 0; i < items.length; i++) {
            inv.setItem(i, this.items[i]);
            inv.setHotbarSlotIndex(i, i);
        }
    }

    private static Item addEnchantment(Item item, Enchantment ench) {
        ench.setLevel(1);
        item.addEnchantment(ench);

        return item;
    }
}
