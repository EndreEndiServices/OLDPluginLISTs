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
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "Tvuj inventar je plny");
            return;
        }

        if (item.getId() == 377 && phase < 4) {
            p.sendMessage(TextFormat.RED + "Tento item muzes koupit az od faze IV");
            return;
        }

        Item cost = inv.getItem(slot + 1);

        if (!p.getInventory().contains(cost)) {
            p.sendMessage(TextFormat.RED + "Nemas dostatek zlata");
            return;
        }

        p.sendMessage(TextFormat.GRAY + "Koupil sis " + TextFormat.YELLOW + item.getName());
        p.getInventory().removeItem(cost);
        p.getInventory().addItem(item);
        p.getInventory().sendContents(p);
    }
}