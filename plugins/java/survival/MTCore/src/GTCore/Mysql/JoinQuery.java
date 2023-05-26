package GTCore.Mysql;

import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.Server;

public class JoinQuery extends AsyncQuery {

    private String ip;
    private String uuid;


    private boolean kick = false;
    private String expireMessage = null;
    private String joinMsg = "";
    private boolean auth = false;
    private String chatColor = null;
    private String originalPlayer;

    private String[] perms = new String[0];

    public JoinQuery(MTCore plugin, String player, String ip, String uuid) {
        originalPlayer = player;
        this.player = player;
        this.ip = ip;
        this.uuid = uuid;

        plugin.getServer().getScheduler().scheduleAsyncTask(this);
    }

    private String prefix;
    private String suffix;
    private int level;
    private int tokens;

    @Override
    public void onRun() {
        prefix = getApi().getPrefix(getPlayer()).replace("&", "§");
        suffix = getApi().getSuffix(getPlayer()).replace("&", "§");
        level = getApi().getLevel(getPlayer());
        tokens = getApi().getTokens(getPlayer());

        perms = getApi().hasPerm(getPlayer(), "gameteam.vip", "gameteam.ban");
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

        PlayerData data = plugin.getPlayerData(this.player);
        //data.setTokens(tokens);
        data.setPrefix(prefix);
        data.setSuffix(suffix);
        data.setLevel(level);
        data.setChatColor(suffix.trim().substring(Math.max(0, suffix.length() - 3)).replaceAll("&", "§"));
        data.setMoney(tokens);

        for (String perm : perms) {
            p.addAttachment(plugin, perm);
        }
    }
}