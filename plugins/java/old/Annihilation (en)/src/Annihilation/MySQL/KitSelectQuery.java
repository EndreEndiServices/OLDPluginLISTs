package Annihilation.MySQL;


import Annihilation.Annihilation;
import Annihilation.Arena.Arena;
import Annihilation.Arena.Kits.Kit;
import MTCore.MTCore;
import MTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;

public class KitSelectQuery extends AsyncQuery {

    private Kit kit;

    private boolean purchased = false;

    public KitSelectQuery(Annihilation plugin, String player, Kit kit) {
        this.player = player;
        this.table = "annihilation";
        this.kit = kit;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {
        String kits = (String) data.get("kits");

        purchased = kits.toLowerCase().contains(kit.getName()) || kit.isFree();
    }

    public void onCompletion(Server server) {
        MTCore plugin2 = MTCore.getInstance();

        if (plugin2 == null || !plugin2.isEnabled()) {
            return;
        }

        Player p = plugin2.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        Annihilation plugin = Annihilation.getInstance();

        if (plugin != null && plugin.isEnabled()) {
            Arena arena = plugin.getPlayerArena(p);

            if (arena != null) {
                if (!purchased && !p.isOp() && !p.hasPermission("gameteam.vip")) {
                    p.sendMessage(Annihilation.getPrefix() + TextFormat.RED + "You have not purchased this kit");
                } else {
                    //$arena->kitManager->onKitChange($p, $this->kit);
                    arena.getPlayerData(p).setNewKit(kit);
                    p.sendMessage(Annihilation.getPrefix() + TextFormat.GREEN + "Selected class " + TextFormat.BLUE + kit.getName());
                }
            }
        }
    }
}