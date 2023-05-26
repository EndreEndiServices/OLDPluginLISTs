package Annihilation.Arena.Shop;

import cn.nukkit.inventory.InventoryHolder;

public class FakeHolder implements InventoryHolder {

    @Override
    public ShopInventory getInventory() {
        return null;
    }
}
