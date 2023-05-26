package BedWars.Utils;


import cn.nukkit.block.BlockAir;
import cn.nukkit.inventory.Inventory;
import cn.nukkit.item.Item;
import cn.nukkit.item.ItemBlock;

public class Items {

    public static final Item BRONZE = Item.get(Item.BRICK).setCustomName("§r§6Bronz");
    public static final Item IRON = Item.get(Item.IRON_INGOT).setCustomName("§r§7Iron");
    public static final Item GOLD = Item.get(Item.GOLD_INGOT).setCustomName("§r§eGold");

    public static boolean containsItem(Inventory inventory, Item item) {
        int count = Math.max(1, item.getCount());

        for (int i = 0; i < inventory.getSize(); i++) {
            Item item2 = inventory.getItem(i);

            if (item2.equals(item, true, false) && item2.getCount() > 0) {
                count -= item2.getCount();
            }

            if (count <= 0) {
                return true;
            }
        }

        return false;
    }

    public static void removeItem(Inventory inv, Item item) {
        int count = item.getCount();

        for (int i = 0; i < inv.getSize(); i++) {
            Item item1 = inv.getItem(i);

            if (item1.equals(item, true, false)) {
                if (count <= item1.count) {
                    item1.count -= count;
                    inv.setItem(i, item1);
                    return;
                } else {
                    count -= item1.getCount();
                    inv.setItem(i, new ItemBlock(new BlockAir()));
                }
            }
        }
    }
}
