package Annihilation.MySQL;

import Annihilation.Annihilation;
import MTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class BuyKitQuery extends AsyncQuery {

    public static final int ACTION_BUY = 0;
    public static final int ACTION_INFO = 1;

    private String kit;
    private Integer action;
    //private Integer cost;

    private String msg = "";

    public BuyKitQuery(Annihilation plugin, String player, String kit, int action) {
        this.player = player;
        this.kit = kit;
        this.action = action;
        //$this->cost = (int) $cost;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {
        HashMap<String, Object> anniData = getPlayer(this.player, "annihilation");

        HashMap<String, Integer> prices = new HashMap<>();

        if (action == ACTION_BUY) {
            prices.put("civilian", 0);
            prices.put("miner", 10000);
            prices.put("lumberjack", 5000);
            prices.put("warrior", 5000);
            prices.put("berserker", 10000);
            prices.put("acrobat", 10000);
            prices.put("archer", 10000);
            prices.put("operative", 10000);
            prices.put("handyman", 0);
            prices.put("scout", 10000);

            int cost = prices.get(kit);

            if (((String) anniData.get("kits")).contains(kit)) {
                msg = TextFormat.GREEN + "� You have already purchased this kit";
            } else if ((int) data.get("tokens") < cost) {
                msg = TextFormat.RED + "� You haven't enough money" + "\n" + TextFormat.ITALIC + TextFormat.GRAY + "Buy some credits at " + TextFormat.RESET + TextFormat.GREEN + "bit.ly/mtBUY" + TextFormat.ITALIC + TextFormat.GRAY + " in section Ranks & Tokens";
            } else {
                msg = Annihilation.getPrefix() + TextFormat.GREEN + "Purchased kit " + kit + " for " + TextFormat.AQUA + cost + TextFormat.GREEN + " tokens";

                addKit(this.player, kit);
                addTokens(this.player, -cost);

            }
        } else {
            String purchaseMessage = ((String) anniData.get("kits")).contains(kit) ? TextFormat.GREEN + "� You have already purchased this kit" : TextFormat.YELLOW + "� To buy this kit use a gold ingot";

            String infoMessage = Annihilation.kitMessages.get(kit);

            msg = purchaseMessage + "\n" + infoMessage;
        }
    }

    public void onCompletion(Server server) {
        Player p = server.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        p.sendMessage(msg);
    }
}