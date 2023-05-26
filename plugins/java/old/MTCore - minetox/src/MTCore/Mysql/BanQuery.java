package MTCore.Mysql;

import MTCore.MTCore;
import MTCore.MySQLManager;
import cn.nukkit.command.CommandSender;
import cn.nukkit.Player;
import cn.nukkit.Server;
import cn.nukkit.utils.TextFormat;

import java.util.HashMap;

public class BanQuery extends AsyncQuery
{

    private String reason;
    private String sender;
    private String permMsg;
    private boolean isOp;

    private String msg;
    private boolean success = false;

    public BanQuery(MTCore plugin, String player, String reason, String sender, boolean op, String permMsg)
    {
        this.player = player;
        this.reason = reason;
        this.sender = sender;
        this.permMsg = permMsg;
        this.isOp = op;


    }

    public void onQuery(HashMap<String, Object> data)
    {
        /*HashMap<String, Object> senderData = getPlayer(this.sender);

        if (!isOp && data == null && !senderData.get("rank").equals("banner")) {
            msg = permMsg;
        } else {
            $id = (is_array($data) && trim($data["id"]) != "") ? $data["id"] : null;

            if ($id != null) {
                $banData = $this->getPlayer($data["id"], "banlist");

                $this->ban($id, $this->reason, $this->sender, $banData);
                $result["msg"] = MTCore.getPrefix() + TextFormat::GREEN . "banned player " . TextFormat::YELLOW . $this->player;
                $result["success"] = true;
            } else {
                $result["msg"] = MTCore.getPrefix() + TextFormat.RED + "can not ban this player";
            }
        }

        $this->setResult($result);  */
    }

    public void onCompletion(Server server)
    {
        /*$result = $this->getResult();

        if ($result["success"]) {

            $p = $server->getPlayerExact($this->player);

            if ($p instanceof Player && $p->isOnline()) {
                $p->kick(TextFormat::RED . "You are banned. \n " . TextFormat::RED . "Reason: " . TextFormat::AQUA . $this->reason, false);
            }

            $server->getLogger()->info($result["msg"]);
        }

        $pl = $server->getPlayer($this->sender);

        if ($pl instanceof Player && $pl->isOnline()) {
            $pl->sendMessage($result["msg"]);
        }   */

    }

    public void ban(String p, String reason, String who, HashMap<String, Object> banData){
        /*if($banData != null){
            $this->getMysqli()->query
            (
                "UPDATE banlist SET reason = '".$reason."', expiration = 0, forever = 1, amount = amount+1, banner = '".$who."' WHERE name = '".$this->getMysqli()->escape_string(trim(strtolower($p)))."'"
            );
        } else{
            $name = trim(strtolower($p));
            $data =
                [
                    "name" => $name,
                    "reason" => $reason,
                    "expiration" => MTCore::getTime() + 1209600,
                    "forever" => 0,
                    "amount" => 1
                ];

            $this->getMysqli()->query
            (
                "INSERT INTO banlist (
            name, reason, expiration, forever, amount, banner)
            VALUES
            ('".$this->getMysqli()->escape_string($name)."', '".$data["reason"]."', '".$data["expiration"]."', '".$data["forever"]."', '".$data["amount"]."', '".$who."')"
            );
        }*/
    }
}