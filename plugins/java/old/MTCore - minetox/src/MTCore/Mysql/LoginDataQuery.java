package MTCore.Mysql;

import MTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;

import java.util.Arrays;
import java.util.HashMap;
import java.util.HashSet;

public class LoginDataQuery extends AsyncQuery{

    private String uuid;

    private HashMap<String, Object> data;
    private HashMap<String, Object> banData;

    public LoginDataQuery(MTCore plugin, String player, String uuid){
        this.player = player;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onQuery(HashMap<String, Object> data){
        if(data == null){
            data = this.registerPlayer(this.player);
        }

        HashMap<String, Object> banData = this.getPlayer(this.uuid, "banlist");

        this.data = data;
        this.banData = banData;
    }

    @Override
    public void onCompletion(Server server){
        Player p = server.getPlayerExact(this.player);

        if(p == null || !p.isOnline()){
            return;
        }

        MTCore plugin = MTCore.getInstance();
        if (plugin == null || !plugin.isEnabled()){
            return;
        }

        int count = 60;

        if (plugin.anni != null){
            count = 60;
        }
        else if (plugin.bedwars != null){
            count = 25;
        }

        String rank = (String) data.get("rank");
        HashSet<String> ranks = new HashSet<>();

        ranks.addAll(Arrays.asList("vip", "vip+", "sponzor", "youtuber", "owner", "builder", "extra", "banner"));

        if(server.getOnlinePlayers().size() >= count && !p.isOp() && !ranks.contains(rank)){
            if(p.loggedIn) {
                p.kick(MTCore.serverFullMessage, false);
            } else{
                p.close(MTCore.serverFullMessage, MTCore.serverFullMessage);
            }
            return;
        }

        /*String msg = this.checkBan(banData);

        if(msg != null){
            if(p.loggedIn) {
                p.kick(msg, false);
            } else {
                p.close(msg, msg);
            }
        }*/
    }
}