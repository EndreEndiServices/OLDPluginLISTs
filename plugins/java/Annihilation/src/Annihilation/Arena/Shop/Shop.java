package Annihilation.Arena.Shop;

import cn.nukkit.item.Item;
import lombok.Getter;

public enum Shop {
    BREWING("Brewing Shop", new Item[]{Item.get(Item.BREWING_STAND), Item.get(Item.GOLD_INGOT, 0, 10), Item.get(374, 0, 3), Item.get(Item.GOLD_INGOT, 0, 1), Item.get(372), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(331), Item.get(Item.GOLD_INGOT, 0, 3), Item.get(376), Item.get(Item.GOLD_INGOT, 0, 3), Item.get(378), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(353), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(382), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(370), Item.get(Item.GOLD_INGOT, 0, 15), Item.get(396), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(375), Item.get(Item.GOLD_INGOT, 0, 2), Item.get(377), Item.get(Item.GOLD_INGOT, 0, 15), Item.get(Item.GUNPOWDER), Item.get(Item.GOLD_INGOT, 0, 30)}),
    WEAPONS("Weapon Shop", new Item[]{Item.get(Item.IRON_HELMET), Item.get(Item.GOLD_INGOT, 0, 10), Item.get(Item.IRON_CHESTPLATE), Item.get(Item.GOLD_INGOT, 0, 18), Item.get(Item.IRON_LEGGINGS), Item.get(Item.GOLD_INGOT, 0, 14), Item.get(Item.IRON_BOOTS), Item.get(Item.GOLD_INGOT, 0, 8), Item.get(Item.IRON_SWORD), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.BOW), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.ARROW, 0, 16), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.CAKE), Item.get(Item.GOLD_INGOT, 0, 5), Item.get(Item.MELON, 0, 16), Item.get(Item.GOLD_INGOT, 0, 1)});

    String customName;

    @Getter
    ShopWindow window;

    Shop(String customName, Item[] items) {
        this.customName = customName;
        this.window = new ShopWindow(items);
        this.window.setCustomName(customName);
    }
}
