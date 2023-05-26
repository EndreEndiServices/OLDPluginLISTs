package MTCore.Mysql;


import MTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class RegisterQuery extends AsyncQuery{

    private String password;
    private String ip;
    private String uuid;

    public RegisterQuery(MTCore plugin, String player, String pass, String ip, String uuid){
        this.player = player;
        this.password = pass;
        this.ip = ip;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private boolean already = false;

    @Override
    public void onQuery(HashMap<String, Object> data){

        if (data != null && ((String) data.get("heslo")).length() >= 4) {
            already = true;
        } else {

            this.setPassword(this.player, this.password);
            this.setIP(this.player, this.ip);
            this.setUUID(this.player, this.uuid);
        }
    }

    @Override
    public void onCompletion(Server server){
        Player p = server.getPlayerExact(this.player);

        if (p == null || !p.isOnline()) {
            return;
        }

        MTCore plugin = MTCore.getInstance();

        if (plugin == null || !plugin.isEnabled()) {
            return;
        }

        if(already) {
            p.sendMessage(MTCore.getPrefix() + TextFormat.GOLD + "You are already registered");
            return;
        }

        if(!plugin.unauthed.containsKey(p.getName().toLowerCase())){
            plugin.unauthed.remove(p.getName().toLowerCase());
        }
        p.removeAllEffects();
        p.sendMessage(MTCore.getPrefix() + TextFormat.GREEN + "You have been successfully registered");
    }

}