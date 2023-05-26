package Annihilation.Arena;

import cn.nukkit.inventory.CustomInventory;
import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.item.Item;
import cn.nukkit.Player;

import java.util.Map;

public class VirtualInventory {

    public int[] hotbar = new int[9];

    public Item[] armor = new Item[4];

    public Map<Integer, Item> contents;

    public int heldItemIndex;

    public int xp = 0;
    public int xplevel = 0;
    public int hunger = 0;
    public int health = 0;

    public VirtualInventory(Player p) {
        PlayerInventory inv = p.getInventory();


        contents = inv.getContents();
        /*for(Map.Entry<Integer, Item> entry : inv.getContents().entrySet()){
            contents.put(entry.getKey(), entry.getValue());
        }*/

        for (int i = 0; i < 4; i++) {
            armor[i] = inv.getArmorContents()[i];
        }

        for (int i = 0; i < 9; i++) {
            hotbar[i] = inv.getHotbarSlotIndex(i);
        }

        hunger = p.getFoodData().getLevel();
        xp = p.getExperience();
        xplevel = p.getExperienceLevel();
        health = p.getHealth();
    }

    public Map<Integer, Item> getContents() {
        return contents;
    }
}