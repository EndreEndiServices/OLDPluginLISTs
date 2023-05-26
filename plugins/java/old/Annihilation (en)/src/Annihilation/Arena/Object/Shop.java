package Annihilation.Arena.Object;

import Annihilation.Annihilation;
import cn.nukkit.inventory.ChestInventory;
import cn.nukkit.item.Item;
import cn.nukkit.Player;
import cn.nukkit.utils.TextFormat;

public class Shop {

    public void onTransaction(Player p, int slot, Item item, ChestInventory inv, int phase) {
        if (item.getId() == Item.GOLD_INGOT) {
            return;
        }

        if (!p.getInventory().canAddItem(item)) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "Your inventory is full");
            return;
        }

        if (item.getId() == 377 && phase < 4) {
            p.sendMessage(TextFormat.RED + "You can not buy this until phase IV");
            return;
        }

        Item cost = inv.getItem(slot + 1);

        if (!p.getInventory().contains(cost)) {
            p.sendMessage(TextFormat.RED + "You haven't enough gold");
            return;
        }

        p.sendMessage(TextFormat.GRAY + "Purchased " + TextFormat.YELLOW + item.getName());
        p.getInventory().removeItem(cost);
        p.getInventory().addItem(item);
        p.getInventory().sendContents(p);
    }
}