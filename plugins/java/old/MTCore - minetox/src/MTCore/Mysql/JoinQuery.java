package MTCore.Mysql;

import MTCore.MTCore;
import cn.nukkit.item.Item;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class JoinQuery extends AsyncQuery
{

    private String ip;
    private String uuid;



    private boolean kick = false;
    private String expireMessage = null;
    private String displayName = null;
    private String joinMsg = "";
    private boolean auth = false;
    private String chatColor = null;

    public JoinQuery(MTCore plugin, String player, String ip, String uuid)
    {
        this.player = player;
        this.ip = ip;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onQuery(HashMap<String, Object> data){


        if(data == null){
            kick = true;
            return;
        }

        chatColor = MTCore.getChatColor((String) data.get("rank"));

        String displayName = MTCore.getDisplayRank((String) data.get("rank")) + " " + this.player;

        long currentTime = System.currentTimeMillis() * 1000;

        switch ((String) data.get("rank")) {
            case "VIP":
                if (!(currentTime * 1000 >= ((int) data.get("doba")))) {
                    int time = Math.round(((int) data.get("doba") - currentTime) / 86400);

                    expireMessage = TextFormat.GOLD + "[MTCore] " + TextFormat.GREEN + "VIP rank expires in " + time + " days";
                    break;
                }
                expireMessage = TextFormat.GOLD + "[MTCore] " + TextFormat.GREEN + "VIP rank expired";
                this.setRank(this.player, "hrac");
                break;
            case "VIP+":
                if (!(currentTime * 1000 >= ((int) data.get("doba")))) {
                    int time = Math.round(((int) data.get("doba") - currentTime) / 86400);

                    expireMessage = TextFormat.GOLD + "[MTCore] " + TextFormat.GREEN + "VIP+ rank expires in " + time + " days";
                    break;
                }
                expireMessage = TextFormat.GOLD + "[MTCore] " + TextFormat.GREEN + "VIP+ rank expired";
                this.setRank(this.player, "hrac");
                break;
        }

        this.displayName = displayName;

        joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
            TextFormat.YELLOW + ">> Welcome to " + TextFormat.GOLD + "Minetox" + TextFormat.WHITE + ", " + displayName + "\n" +
            TextFormat.YELLOW + ">> This account is already registered" + "\n" +
            TextFormat.YELLOW + ">> Login with " + TextFormat.RED + "/login " + TextFormat.YELLOW + "or change" + "\n" +
            TextFormat.YELLOW + ">> your name in the MCPE settings." + "\n" +
            TextFormat.GRAY + "==========================================";

        /*if (((String) data.get("heslo")).length() < 4) {
            //this.registerPlayer(this.player);
            //result["register"] = true;
            joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
                TextFormat.YELLOW + ">> Welcome to " + TextFormat.GOLD + "Minetox" + TextFormat.YELLOW + ", " + displayName + "\n" +
                TextFormat.YELLOW + ">> The account has not been registered\n" +
                TextFormat.YELLOW + ">> You can register him with " + TextFormat.RED + "/register\n" +
                TextFormat.GRAY + "==========================================";

        } else*/ if (data.get("ip") == this.ip && data.get("id") == this.uuid){
            auth = true;
            joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
                TextFormat.YELLOW + ">> Welcome to " + TextFormat.GOLD + "Minetox" + TextFormat.WHITE + ", " + displayName + "\n" +
                TextFormat.GRAY + "==========================================";

        } else if (((String) data.get("heslo")).length() < 4) {
            //$result["register"] = true;
            joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
                TextFormat.YELLOW + ">> Welcome to " + TextFormat.GOLD + "Minetox" + TextFormat.WHITE + ", " + displayName + "\n" +
                TextFormat.YELLOW + ">> The account has not been registered\n" +
                TextFormat.YELLOW + ">> You can register him with " + TextFormat.RED + "/register\n" +
                TextFormat.GRAY + "==========================================";
        }

        //$pl->setDisplayName($name = (MTCore::getDisplayRank($pl, $data["rank"])." ".$pl->getName()));
        //$pl->setNameTag($name);
    }

    @Override
    public void onCompletion(Server server)
    {
        Player p = server.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        if(kick){
            p.kick(TextFormat.GREEN + "Your profile was successfully created. You can connect again");
            return;
        }

        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        if (auth) {
            plugin.unauthed.remove(this.player.toLowerCase());

            /*for(Player pl : new ArrayList<>(server.getOnlinePlayers().values())){
                p.showPlayer(pl);
            }*/

            p.removeAllEffects();
            p.setDisplayName(displayName);
            p.setNameTag(displayName);

            if(chatColor != null && !chatColor.equals("§3")) {
                plugin.chatColors.put(this.player, chatColor);
            }
        }

        p.teleport(plugin.lobby);
        p.setGamemode(0);
        p.setRotation(270, 0);
        p.setHealth(20);
        if(p.getInventory() != null) {
                p.getInventory().clearAll();
                p.getInventory().setItem(0, Item.get(Item.CLOCK, 0, 1).setCustomName("§r§eDisplay players"));
                p.getInventory().setItem(1, Item.get(Item.GOLD_INGOT, 0, 1));
                p.getInventory().sendContents(p);
        }

        p.sendMessage(joinMsg);

        if (expireMessage != null) {
            p.sendMessage(expireMessage);
        }

    }

}