package BedWars.Shop;

import cn.nukkit.Player;
import cn.nukkit.block.BlockWool;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;
import lombok.Getter;
import lombok.NonNull;
import lombok.experimental.NonFinal;

public class ShopWindow extends Window {

    @Getter
    private ItemWindow previousWindow;

    public ShopWindow(Item item, Item cost, ItemWindow previous) {
        setItem(0, item);
        setItem(3, cost);
        setItem(getSize() - 1, new ItemBlock(new BlockWool(), 14));

        this.previousWindow = previous;
    }

    public Item getItem() {
        return getItem(0);
    }

    public Item getCost() {
        return getItem(3);
    }

    @Override
    public Window getWindow(int slot) {
        if (slot == getSize() - 1) {
            return previousWindow;
        }

        return null;
    }
}
