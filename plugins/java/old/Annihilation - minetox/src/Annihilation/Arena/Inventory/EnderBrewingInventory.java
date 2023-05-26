package Annihilation.Arena.Inventory;

import Annihilation.Arena.BlockEntity.EnderBrewing;
import cn.nukkit.inventory.ContainerInventory;
import cn.nukkit.inventory.InventoryType;
import cn.nukkit.item.Item;

public class EnderBrewingInventory extends ContainerInventory {

    public int brewTime = EnderBrewing.MAX_BREW_TIME;

    public EnderBrewingInventory(EnderBrewing brewingStand) {
        super(brewingStand, InventoryType.get(InventoryType.BREWING_STAND));
    }

    @Override
    public EnderBrewing getHolder() {
        return (EnderBrewing) this.holder;
    }

    public Item getIngredient() {
        return getItem(0);
    }

    public void setIngredient(Item item) {
        setItem(0, item);
    }

    @Override
    public void onSlotChange(int index, Item before) {
        super.onSlotChange(index, before);

        this.getHolder().inventoryUpdate(this);
    }
}
