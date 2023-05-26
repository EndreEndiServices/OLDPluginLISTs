package Annihilation.Arena.Object;

import Annihilation.Arena.Inventory.EnderBrewingInventory;
import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.VirtualInventory;
import Annihilation.Entity.FishingHook;
import cn.nukkit.inventory.ChestInventory;
import cn.nukkit.Player;
import lombok.Getter;
import lombok.Setter;

public class PlayerData {

    @Getter
    private String name;

    @Getter
    @Setter
    private Kit kit;

    @Getter
    @Setter
    private Team team = null;

    @Setter
    @Getter
    private boolean inLobby = true;

    @Getter
    @Setter
    private Kit newKit;

    private PlayerData killer;

    @Getter
    @Setter
    private Integer time = 0;

    @Getter
    @Setter
    private ChestInventory chest = null;

    @Getter
    @Setter
    private EnderFurnaceInventory furnace = null;

    @Getter
    @Setter
    private EnderBrewingInventory brewing = null;

    private boolean wasInGame = false;

    private VirtualInventory inventory;

    @Getter
    @Setter
    private FishingHook fishingHook;

    @Getter
    @Setter
    private MTCore.Object.PlayerData baseData;

    public PlayerData(String name, MTCore.Object.PlayerData pdata) {
        this.name = name;
        this.newKit = Kit.CIVILIAN;
        this.baseData = pdata;
    }

    public boolean wasInGame() {
        return wasInGame && team != null;
    }

    public void setInGame() {
        wasInGame = true;
    }

    public PlayerData wasKilled() {
        if ((System.currentTimeMillis() - time) <= 10000 && killer != null) {
            return killer;
        }
        return null;
    }

    public VirtualInventory getSavedInventory() {
        return inventory;
    }

    public void saveInventory(Player p) {
        inventory = new VirtualInventory(p);
    }

    public void removeInventory() {
        inventory = null;
    }

    public void setKiller(PlayerData p) {
        this.killer = p;
        this.time = (int) System.currentTimeMillis();
    }
}