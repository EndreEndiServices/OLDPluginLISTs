package Annihilation.MySQL;

import Annihilation.Annihilation;
import Annihilation.Arena.Kits.Kit;
import GTCore.MTCore;
import GTCore.Mysql.AsyncQuery;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class BuyKitQuery extends AsyncQuery {

    public static final int ACTION_BUY = 0;
    public static final int ACTION_INFO = 1;

    private Kit kit;
    private Integer action;
    private String originalPlayer = "";
    //private Integer cost;

    private String msg = "";

    public BuyKitQuery(Annihilation plugin, String player, Kit kit, int action) {
        this.player = player.toLowerCase();
        this.originalPlayer = player;
        this.kit = kit;
        this.action = action;
        this.table = "annihilation";
        //$this->cost = (int) $cost;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    public void onQuery(HashMap<String, Object> data) {

        //HashMap<String, Integer> prices = new HashMap<>();

        if (action == ACTION_BUY) {
            /*prices.put("civilian", 0);
            prices.put("miner", 10000);
            prices.put("lumberjack", 5000);
            prices.put("warrior", 5000);
            prices.put("berserker", 10000);
            prices.put("acrobat", 10000);
            prices.put("archer", 10000);
            prices.put("operative", 10000);
            prices.put("handyman", 0);
            prices.put("scout", 10000);*/

            int cost = kit.getCost();

            if (((String) data.get("kits")).contains(kit.getName())) {
                msg = TextFormat.GREEN + "◆ Tento kit mas jiz koupeny";
            } else if (getApi().getTokens(getPlayer()) < cost) {
                msg = TextFormat.RED + "◆ Nemas dostatek tokenu" + "\n" + TextFormat.ITALIC + TextFormat.GRAY + "http://mc.gameteam.cz/vip-a-kredit/";
            } else {
                msg = Annihilation.getPrefix() + TextFormat.GREEN + "Koupil sis " + kit + " za " + TextFormat.AQUA + cost + TextFormat.GREEN + " tokenu";

                addKit(this.player, kit.getName());
                getApi().subtractTokens(originalPlayer, cost);

            }
        } else {
            String purchaseMessage = ((String) data.get("kits")).contains(kit.getName()) ? TextFormat.GREEN + "◆ Tento kit mas jiz koupeny" : TextFormat.YELLOW + "◆ Pro zakoupeni tohoto kitu klikni na NPC gold ingotem";

            String infoMessage = kit.getMessage();

            msg = purchaseMessage + "\n" + infoMessage;
        }
    }

    public void onCompletion(Server server) {
        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        Player p = plugin.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        p.sendMessage(msg);
    }
}