package Annihilation.Arena.Shop;

import cn.nukkit.item.Item;

public class ShopWindow extends ShopInventory {

    public ShopWindow(Item[] items) {
        for (int i = 0; i < items.length; i++) {
            setItem(i, items[i]);
        }
    }
}
