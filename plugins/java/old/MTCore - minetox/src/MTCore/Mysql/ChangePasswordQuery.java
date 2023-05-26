package MTCore.Mysql;


import MTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class ChangePasswordQuery extends AsyncQuery
{

    private String password;
    private String oldPass;

    private String msg;

    public ChangePasswordQuery(MTCore plugin, String player, String oldPass, String pass)
    {
        this.player = player;
        this.password = pass;
        this.oldPass = oldPass;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onQuery(HashMap<String, Object> data)
    {
        if(!oldPass.equals(data.get("heslo"))){
            msg = MTCore.getPrefix() + TextFormat.RED + "Wrong password";
        } else{
            msg = MTCore.getPrefix() + TextFormat.GREEN + "Your password has been successfully changed";
            this.setPassword(this.player, this.password);
        }
    }

    @Override
    public void onCompletion(Server server)
    {
        Player p = server.getPlayerExact(player);

        if(p == null || !p.isOnline()){
            return;
        }

        p.sendMessage(msg);
    }
}