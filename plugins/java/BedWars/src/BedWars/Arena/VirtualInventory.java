package BedWars.Arena;

import cn.nukkit.inventory.CustomInventory;
import cn.nukkit.Player;
import cn.nukkit.inventory.InventoryType;
import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.item.Item;

public class VirtualInventory extends CustomInventory {

    public int[] hotbar;
    public Item[] armor;

    public VirtualInventory(Player p) {
        super(p, InventoryType.get(2));
        PlayerInventory inv = p.getInventory();
        this.setContents(inv.getContents());
        this.armor = inv.getArmorContents();
        for (int i = 0; i <= 9; i++) {
            this.hotbar[i] = inv.getHotbarSlotIndex(i);
        }
    }

}