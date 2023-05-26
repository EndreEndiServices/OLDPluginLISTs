package GTCore.Mysql;


import GTCore.MTCore;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;
import com.gmail.holubvojtech.gthttpapi.api.ApiResponse;

public class ChangePasswordQuery extends AsyncQuery {

    private String password;
    private String oldPass;

    private String msg;

    public ChangePasswordQuery(MTCore plugin, String player, String oldPass, String pass) {
        this.player = player;
        this.password = pass;
        this.oldPass = oldPass;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    @Override
    public void onRun() {
        ApiResponse.ChangePass result = getApi().changePassword(getPlayer(), oldPass, password);

        switch (result) {
            case SUCCESS:
                msg = MTCore.getPrefix() + TextFormat.GREEN + "Password successfully changed";
                break;
            case ERROR:
            case INVALID:
                msg = MTCore.getPrefix() + TextFormat.RED + "An error occurred during authentication";
                break;
            case WRONG_PASSWORD:
                msg = MTCore.getPrefix() + TextFormat.RED + "Wrong password";
                break;
            case NOT_REGISTERED:
                msg = MTCore.getPrefix() + TextFormat.RED + "You are not registered\n" + TextFormat.RED + "Use /register [password] [password]";
                break;
        }
    }

    @Override
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