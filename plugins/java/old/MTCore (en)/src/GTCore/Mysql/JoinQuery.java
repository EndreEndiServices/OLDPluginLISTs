package GTCore.Mysql;

import GTCore.MTCore;
import GTCore.Object.PlayerData;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

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
        this.player = player.toLowerCase();
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
        //chatColor = GTCore.getChatColor((String) data.get("rank"));

        prefix = getApi().getPrefix(getPlayer()).replace("&", "§");
        suffix = getApi().getSuffix(getPlayer()).replace("&", "§");
        level = getApi().getLevel(getPlayer());
        tokens = getApi().getTokens(getPlayer());

        //System.out.println("\nsuffix: " + suffix);

        String displayName = prefix + TextFormat.WHITE + TextFormat.RESET + " " + originalPlayer + suffix;

        if (!getApi().isRegistered(getPlayer())) {
            joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
                    TextFormat.YELLOW + ">> Welcome to " + MTCore.getPrefix() + TextFormat.WHITE + ", " + displayName + "\n" +
                    TextFormat.YELLOW + ">> The account has not been registered\n" +
                    TextFormat.YELLOW + ">> You can register with " + TextFormat.RED + "/register\n" +
                    TextFormat.GRAY + "==========================================";
        } else {
            joinMsg = TextFormat.GRAY + "==========================================" + "\n" +
                    TextFormat.YELLOW + ">> Welcome to " + MTCore.getPrefix() + TextFormat.WHITE + ", " + displayName + "\n" +
                    TextFormat.YELLOW + ">> This account is already registered" + "\n" +
                    TextFormat.YELLOW + ">> Login with " + TextFormat.RED + "/login " + TextFormat.YELLOW + "or change" + "\n" +
                    TextFormat.YELLOW + ">> your name in the MCPE settings." + "\n" +
                    TextFormat.GRAY + "==========================================";
        }

        perms = getApi().hasPerm(getPlayer(), "gameteam.vip", "gameteam.ban");

        //$pl->setDisplayName($name = (GTCore::getDisplayRank($pl, $data["rank"])." ".$pl->getName()));
        //$pl->setNameTag($name);
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

        //System.out.println("suffix: "+suffix+"   trim: "+suffix.trim().substring(Math.max(0, suffix.length() - 3)).replaceAll("&", "§"));

        /*if (auth) {
            plugin.unauthed.remove(this.player.toLowerCase());

            for(Player pl : new ArrayList<>(server.getOnlinePlayers().values())){
                p.showPlayer(pl);
            }

            p.removeAllEffects();
            p.setDisplayName(displayName);
            p.setNameTag(displayName);

            if(chatColor != null && !chatColor.equals("§3")) {
                plugin.chatColors.put(this.player, chatColor);
            }
        }*/

        /*p.teleport(plugin.lobby);
        p.setGamemode(0);
        p.setRotation(270, 0);
        p.setHealth(20);

        if(p.getInventory() != null) {
                p.getInventory().clearAll();
                p.getInventory().setItem(0, Item.get(Item.CLOCK, 0, 1).setCustomName("§r§eDisplay players"));
                p.getInventory().setItem(1, Item.get(Item.GOLD_INGOT, 0, 1));
                p.getInventory().sendContents(p);
        }*/

        for (String perm : perms) {
            p.addAttachment(plugin, perm);
        }

        p.sendMessage(joinMsg);

        /*if (expireMessage != null) {
            p.sendMessage(expireMessage);
        }*/

    }
}