package GTCore.Mysql;

import GTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;

public class LoginDataQuery extends AsyncQuery {

    private String uuid;

    //private HashMap<String, Object> data;
    //private HashMap<String, Object> banData;

    public LoginDataQuery(MTCore plugin, String player, String uuid) {
        this.player = player;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private String prefix;
    private String rank;
    private int level;

    @Override
    public void onRun() {
        prefix = getApi().getPrefix(getPlayer());

        //HashMap<String, Object> banData = this.getPlayer(this.uuid, "banlist");


        //this.data = data;
        //this.banData = banData;
    }

    @Override
    public void onCompletion(Server server) {
        Player p = server.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        MTCore plugin = MTCore.getInstance();
        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        //String rank = (String) data.get("rank");
        //HashSet<String> ranks = new HashSet<>();

        //ranks.addAll(Arrays.asList("vip", "vip+", "sponzor", "youtuber", "owner", "builder", "extra", "banner"));

        //TODO: full server checks
        /*if(server.getOnlinePlayers().size() >= 100 && !p.isOp() && !ranks.contains(rank)){
            if(p.loggedIn) {
                p.kick(GTCore.serverFullMessage, false);
            } else{
                p.close(GTCore.serverFullMessage, GTCore.serverFullMessage);
            }
            return;
        }*/

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