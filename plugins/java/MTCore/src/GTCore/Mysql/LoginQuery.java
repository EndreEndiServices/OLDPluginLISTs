package GTCore.Mysql;

import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;
import com.gmail.holubvojtech.gthttpapi.api.ApiResponse;

public class LoginQuery extends AsyncQuery {

    private String password;
    private String ip;
    private String uuid;

    private String originialPlayer;

    public LoginQuery(MTCore plugin, String player, String pass, String ip, String uuid) {
        this.password = pass;
        this.ip = ip;
        this.uuid = uuid;
        this.originialPlayer = player;
        this.player = player;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private String msg = "";
    private boolean success = false;

    @Override
    public void onRun() {

        ApiResponse.Login result = getApi().loginPlayer(originialPlayer, password);

        if (result == ApiResponse.Login.WRONG_PASSWORD) {
            msg = MTCore.getPrefix() + TextFormat.RED + "Spatne heslo";
        } else if (result == ApiResponse.Login.NOT_REGISTERED) {
            msg = MTCore.getPrefix() + TextFormat.RED + "Jeste nejsi zaregistrovany\n§cPouzij /register <heslo> <heslo znova>";
        } else if (result == ApiResponse.Login.SUCCESS) {
            msg = MTCore.getPrefix() + TextFormat.GREEN + "Byl si uspesne prihlasen";
            success = true;
        } else {
            msg = MTCore.getPrefix() + TextFormat.RED + "Nastala chyba behem procesu";
            //this.setIP(this.player, this.ip);
            //this.setUUID(this.player, this.uuid);
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

        if (success) {
            PlayerData data = plugin.getPlayerData(p);

            data.setLoggedIn(true);

            p.setDisplayName(TextFormat.GRAY + "[" + TextFormat.GREEN + data.getLevel() + TextFormat.GRAY + "] " + data.getPrefix() + TextFormat.WHITE + TextFormat.RESET + data.getPlayer().getName() + data.getSuffix());
            p.setNameTag(data.getPrefix() + " " + data.getPlayer().getName());

            p.removeAllEffects();
        }

        p.sendMessage(msg);
    }
}