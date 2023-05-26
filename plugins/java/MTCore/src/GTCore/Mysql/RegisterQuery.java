package GTCore.Mysql;


import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;
import com.gmail.holubvojtech.gthttpapi.api.ApiResponse;

public class RegisterQuery extends AsyncQuery {

    private String password;
    private String ip;
    private String uuid;

    private String originalPlayer;

    public RegisterQuery(MTCore plugin, String player, String pass, String ip, String uuid) {
        this.originalPlayer = player;
        this.player = player;
        this.password = pass;
        this.ip = ip;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private boolean success = false;
    private String msg = "";

    @Override
    public void onRun() {
        //nameTag = getApi().getPrefix(getPlayer()) + originalPlayer;
        //displayName = TextFormat.GRAY + "[" + TextFormat.GREEN + getApi().getLevel(getPlayer()) + TextFormat.GRAY + "]" + getApi().getPrefix(getPlayer()) + TextFormat.WHITE + TextFormat.RESET + originalPlayer + getApi().getSuffix(getPlayer()) + ":";
        ApiResponse.Register result = getApi().registerPlayer(getPlayer(), password);

        switch (result) {
            case SUCCESS:
                success = true;
                msg = MTCore.getPrefix() + TextFormat.GREEN + "Byl jsi uspesne zaregistrovan";
                break;
            case ALREADY_REGISTERED:
                msg = MTCore.getPrefix() + TextFormat.GOLD + "Tento ucet je jiz registrovany";
                break;
            case INVALID:
            case ERROR:
                msg = MTCore.getPrefix() + TextFormat.RED + "Nastala chyba behem procesu";
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

        if (success) {
            PlayerData data = plugin.getPlayerData(p);

            data.setLoggedIn(true);

            p.removeAllEffects();
            p.setDisplayName(TextFormat.GRAY + "[" + TextFormat.GREEN + data.getLevel() + TextFormat.GRAY + "] " + data.getPrefix() + TextFormat.WHITE + TextFormat.RESET + data.getPlayer().getName() + data.getSuffix());
            p.setNameTag(data.getPrefix() + " " + data.getPlayer().getName());
        }
    }
}