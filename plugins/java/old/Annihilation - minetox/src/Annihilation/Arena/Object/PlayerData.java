package Annihilation.Arena.Object;

import Annihilation.Arena.Inventory.EnderBrewingInventory;
import Annihilation.Arena.Inventory.EnderFurnaceInventory;
import Annihilation.Arena.Kits.Kit;
import Annihilation.Arena.VirtualInventory;
import Annihilation.Entity.FishingHook;
import cn.nukkit.inventory.ChestInventory;
import cn.nukkit.Player;

public class PlayerData{

    private String name;

    private Integer kit;

    private Team team = null;
    private boolean lobby = true;
    private Integer newKit;
    private PlayerData killer;
    private Integer time = 0;

    private ChestInventory chest = null;

    private EnderFurnaceInventory furnace = null;

    private EnderBrewingInventory brewing = null;
    private boolean wasInGame = false;

    private String rank;

    private VirtualInventory inventory;

    public FishingHook fishingHook;

    public PlayerData(String name){
        this(name, Kit.CIVILIAN, "hrac");
    }

    public PlayerData(String name, Integer kit){
        this(name, kit, "hrac");
    }

    public PlayerData(String name, Integer kit, String rank){
        this.name = name;
        this.newKit = kit;
        this.rank = rank;
    }

    public Team getTeam(){
        return team;
    }

    public void setTeam(Team team){
        this.team = team;
    }

    public ChestInventory getChest(){
        return chest;
    }

    public void setChest(ChestInventory inv){
        chest = inv;
    }

    public EnderFurnaceInventory getFurnace(){
        return furnace;
    }

    public void setFurnace(EnderFurnaceInventory inv){
        furnace = inv;
    }

    public EnderBrewingInventory getBrewing(){
        return brewing;
    }

    public void setBrewing(EnderBrewingInventory inv){
        brewing = inv;
    }

    public boolean wasInGame(){
        return wasInGame && team != null;
    }

    public void setInGame(){
        wasInGame = true;
    }

    public PlayerData wasKilled(){
        if((System.currentTimeMillis() - time) <= 10000 && killer != null){
            return killer;
        }
        return null;
    }

    public Integer getKit(){
        return kit;
    }

    public void setKit(Integer kit){
        this.kit = kit;
    }

    public Integer getNewKit(){
        return newKit;
    }

    public void setNewKit(Integer kit){
        this.newKit = kit;
    }

    public boolean isInLobby(){
        return lobby;
    }

    public VirtualInventory getSavedInventory(){
        return inventory;
    }

    public void saveInventory(Player p){
        inventory = new VirtualInventory(p);
    }

    public void removeInventory(){
        inventory = null;
    }

    public void setLobby(boolean value){
        lobby = value;
    }

    public void setKiller(PlayerData p){
        this.killer = p;
        this.time = (int) System.currentTimeMillis();
    }

    public String getName(){
        return name;
    }
}