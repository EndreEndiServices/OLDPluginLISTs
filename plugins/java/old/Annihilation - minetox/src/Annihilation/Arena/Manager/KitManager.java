package Annihilation.Arena.Manager;

import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Object.PlayerData;
import Annihilation.MySQL.KitSelectQuery;
import cn.nukkit.inventory.PlayerInventory;
import cn.nukkit.Player;
import cn.nukkit.item.*;
import Annihilation.Arena.Kits.*;
import cn.nukkit.nbt.tag.CompoundTag;
import cn.nukkit.utils.TextFormat;

public class KitManager {

    public Arena plugin;

    public KitManager(Arena plugin) {
        this.plugin = plugin;
        Kit.init();
    }

    public void onKitChange(Player p, Integer kit) {
        /*if (!this.hasKit(p, kit) && !p.isOp()) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "You have not bought this kit");
            return;
        }
        this.plugin.getPlayerData(p).setNewKit(kit);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GREEN + "Selected class " + TextFormat.BLUE + Kit.getKit(kit).getName());*/
        new KitSelectQuery(Annihilation.getInstance(), p.getName().toLowerCase(), Kit.getKit(kit));
    }

    public void giveKit(Player p) {
        PlayerData data = this.plugin.getPlayerData(p);

        Integer kit = data.getNewKit();

        data.setKit(kit);

        PlayerInventory inv = p.getInventory();
        inv.clearAll();

        /*foreach($this->kits[$this->plugin->getPlayerData($p)->getKit()]->give($p) as $item) {
            $nbt = new Compound("", []);
            $nbt->Soulbound = new Byte("Soulbound", 1);

            $item->setNamedTag($nbt);
        }*/

        Kit kitIns = Kit.getKit(kit);
        kitIns.give(p);

        CompoundTag nbt = new CompoundTag()
                .putInt("customColor", data.getTeam().getDecimalColor())
                .putCompound("display", new CompoundTag().putString("Name", TextFormat.RESET + TextFormat.GOLD + "SoulBound"));

        ItemHelmetLeather helmet = new ItemHelmetLeather();
        helmet.setCompoundTag(nbt);

        ItemChestplateLeather chestplate = new ItemChestplateLeather();
        chestplate.setCompoundTag(nbt);

        ItemLeggingsLeather leggings = new ItemLeggingsLeather();
        leggings.setCompoundTag(nbt);

        ItemBootsLeather boots = new ItemBootsLeather();
        boots.setCompoundTag(nbt);

        inv.setHelmet(helmet);
        inv.setChestplate(chestplate);
        inv.setLeggings(leggings);
        inv.setBoots(boots);

        inv.sendContents(p);
        inv.sendArmorContents(p);
    }

    public void addKitWindow(Player p) {
        PlayerInventory inv = p.getInventory();

        for(int i = 0; i < 12; i++){

            Kit kit = Kit.getKit(i);

            if(kit == null){
                return;
            }

            inv.setItem(i, kit.getItem());
            inv.setHotbarSlotIndex(i, 35);
        }

        inv.sendContents(p);
    }
    
    /*public function onItemTrans(InventoryTransactionEvent $e){
        if($e->getTransaction() instanceof Transaction){
        $inv = $e->getTransaction()->getInventory();
        if($inv instanceof PlayerInventory){
            $p = $inv->getHolder();
            if($this->plugin->getPlayerTeam($p) !== 0){
                return;
            }
            $e->setCancelled();
        }
        }
    }*/

    public void itemHeld(Player p, int slot) {
        if(slot < 12) {
            this.onKitChange(p, slot);
        }
    }

    /*public boolean hasKit(Player p, Integer kitId) {
        String kit = Kit.getKit(kitId).getName();

        String[] ranks = new String[]{"vip", "vip+", "sponzor", "youtuber", "owner", "builder", "extra"};

        return Arrays.asList(ranks).contains(this.plugin.mtcore.mysqlmgr.getRank(p.getName())) || this.plugin.mysql.getKits(p.getName()).contains(kit);
    }*/

    /*public void buyKit(Player p, Integer kit) {
        if (this.hasKit(p, kit) || kit == Kit.HANDYMAN) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.YELLOW + "You have already purchased this kit.");
            return;
        }

        if (this.plugin.mtcore.mysqlmgr.getTokens(p.getName()) < 5000) {
            p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "You need 5 000 tokens to buy this kit");
            return;
        }

        this.plugin.mysql.addKit(p.getName(), Kit.getKit(kit).getName());
        this.plugin.mtcore.mysqlmgr.takeTokens(p.getName(), 5000);
        p.sendMessage(Annihilation.getPrefix() + TextFormat.GREEN + "Purchased kit " + kit);
    }*/
}