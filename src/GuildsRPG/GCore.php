<?php

namespace GuildsRPG;

/*/
 * This Plugin Just For FUN.
/*/

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat as Z;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\block\Snow;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
//Using EconomyAPI
use onebone\economyapi\EconomyAPI;

class GCore extends PluginBase implements Listener {

    public $db;
    public $settings;
    public $war_req = [];
    public $wars = [];
    public $war_players = [];

    public function onLoad(){
        $loadingmsg = Z::GOLD . "Loading Database";
        $completemsg = Z::GREEN . "Complete!";
        $loadmsg = Z::AQUA . "GCore ONLINE!";
        $this->getLogger()->notice($loadingmsg);
        $this->getLogger()->warning($completemsg);
        $this->getLogger()->error($loadmsg);
    }
    public function onEnable(){
        @mkdir($this->getDataFolder());
        if (!file_exists($this->getDataFolder() . "DisableGuildsName.txt")) {
            $file = fopen($this->getDataFolder() . "DisableGuildsName.txt", "w");
            $txt = "Admin:admin:Staff:staff:Owner:owner:Builder:builder:Op:OP:op";
            fwrite($file, $txt);
        $this->api = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        }
        $this->getServer()->getPluginManager()->registerEvents(new GuildsEvents($this), $this);
        $this->guildsCommand = new GuildsCommands($this);
        $settingsname = "GuildsOptions.yml";
        $this->settings = new Config($this->getDataFolder() . $settingsname, CONFIG::YAML, array(
            "MaxGuildNameLength" => 15,
            "MaxPlayersPerguild" => 30,
            "OnlyGuildsMasterAndSecondInCommandsCanInvite" => true,
            "###Plots###",
		    "SecondInCommandsCanClaim" => false,
	    	"PlotSize" => 30,
            "PlayersNeededInGuildToClaimAPlot" => 5,
            "ClaimWorlds" => [],
            "###Guilds Point###",
            "PointNeededToClaimAPlot" => 1000,
            "PointNeededToSetOrUpdateAHome" => 250,
            "PointGainedPerPlayerInGuild" => 50,
            "PointGainedPerKillingAnEnemy" => 10,
    		"PointReducedPerDeathByAnEnemy" => 10,
            "PointGainedPerAlliance" => 100,
            "AllianceLimitPerGuild" => 5,
            "TheDefaultPointEveryGuildStartsWith" => 0,
            "###Economys###",
            "CreateCost" => 3000,
		    "ClaimCost" => 100000,
	    	"OverClaimCost" => 25000,
	    	"AllianceCost" => 5000,
	    	"AlliancePrice" => 5000,
	    	"SetHomeCost" => 150,
            "###GuildsMoneys###",
            "GuildsMoneyGainPerKill" => 10,
            "GuildsMoneyLostPerDeath" => 10,
        ));
        $this->db = new \SQLite3($this->getDataFolder() . "GuildsDatabase.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, rank TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS confirm (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, invitedby TEXT, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliance (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, requestedby TEXT, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS motdrcv (player TEXT PRIMARY KEY, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS motd (guild TEXT PRIMARY KEY, message TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS plots(guild TEXT PRIMARY KEY, x1 INT, z1 INT, x2 INT, z2 INT, world TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS home(guild TEXT PRIMARY KEY, x INT, y INT, z INT, world TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS gp(guild TEXT PRIMARY KEY, guildpoints INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliance(ID INT PRIMARY KEY, guild1 TEXT, guild2 TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS nemisys(ID INT PRIMARY KEY, guild1 TEXT, guild2 TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliancecountlimit(guild TEXT PRIMARY KEY, count INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS effects(guild TEXT PRIMARY KEY, effect TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS moneys(guild TEXT PRIMARY KEY, moneys INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS boosters(guild TEXT PRIMARY KEY, booster TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS wp(guild TEXT PRIMARY KEY, warpoints INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS nemisyscountlimit(guild TEXT PRIMARY KEY, count INT);");
    }
    public function onDisable() {
        $loadingoff = Z::GOLD . "Saving Database";
        $completeoff = Z::GREEN . "Complete!";
        $disablemsg = Z::RED . "GCore OFFLINE!";
        $this->getLogger()->notice($loadingoff);
        $this->getLogger()->critical($completeoff);
        $this->getLogger()->error($disablemsg);
        $this->db->close();
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        $this->guildsCommand->onCommand($sender, $command, $label, $args);
    }
///////////////////??EFFECTS??///////////////////
    public function addEffectTo($guild, $effect){
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO effects (guild, effect) VALUES (:guild, :effect);");  
        $stmt->bindValue(":guild", $guild);
		$stmt->bindValue(":effect", $effect);
		$result = $stmt->execute();
    }
    public function getEffectOf($guild){
        $result = $this->db->query("SELECT * FROM effects WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return "none";
        }
        return $resultArr['effect'];
    }
    public function updateTagsAndEffectsOf($f){
        $result = $this->db->query("SELECT * from master WHERE faction='$f';");
        $i = 0;
        while($resultArr = $result->fetchArray(SQLITE3_ASSOC)){
            if($this->getServer()->getPlayerExact($resultArr['player']) instanceof Player){
                $this->getServer()->getPlayerExact($resultArr['player'])->removeAllEffects();
                //$this->updateTag($resultArr['player']);
            }
            $i = $i + 1;
        } 
    }
/////??BOOSTER??/////
    public function addBoosterInto($guild, $effect){
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO boosters (guild, booster) VALUES (:guild, :booster);");  
        $stmt->bindValue(":guild", $guild);
		$stmt->bindValue(":booster", $booster);
		$result = $stmt->execute();
    }
    public function getBoosterFrom($guild){
        $result = $this->db->query("SELECT * FROM boosters WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return "none";
        }
        return $resultArr['booster'];
    }
/////??NEMISYS??/////
    public function setNemisys($guild1, $guild2) {
        $stmt = $this->db->prepare("INSERT INTO nemisys (guild1, guild2) VALUES (:guild1, :guild2);");
        $stmt->bindValue(":guild1", $guild1);
        $stmt->bindValue(":guild2", $guild2);
        $result = $stmt->execute();
    }
    public function isNemisys($guild1, $guild2) {
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if (empty($resultArr) == false) {
            return true;
        }
    }
    public function updateNemisys($guild) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO nemisyscountlimit (guild, count) VALUES (:guild, :count);");
        $stmt->bindValue(":guild", $guild);
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1='$guild';");
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $i = $i + 1;
        }
        $stmt->bindValue(":count", (int) $i);
        $result = $stmt->execute();
    }
    public function getNemisysCount($guild) {
        $result = $this->db->query("SELECT * FROM nemisyscountlimit WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["count"];
    }
    public function getNemisysLimit() {
        return (int) $this->prefs->get("NemisysLimitPerguild");
    }
    public function deleteNemisys($guild1, $guild2) {
        $stmt = $this->db->prepare("DELETE FROM nemisys WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $result = $stmt->execute();
    }
    public function getAllNemisys($sender, $guild) {
        $team = "";
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1 ='$guild';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['guild2'] = $resultArr['guild2'];
            $team .= TextFormat::ITALIC . TextFormat::RED . $row[$i]['guild2'] . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            $i = $i + 1;
        }

        $sender->sendMessage($this->formatMessage("§l§b»§r§e Nemisys of $guild §l§b«", true));
        $sender->sendMessage($team);
    }
/////??Guilds??/////
    public function isInGuilds($player) {
        $result = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function getGuilds($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["guild"];
    }
    public function isGuildsMaster($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "GuildsMaster";
    }

    public function isSecondInCommands($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "SecondInCommands";
    }

    public function isMember($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "Member";
    }
    public function getPlayersInGuildsByRank($sender, $guild, $rank) {
        if ($rank != "GuildsMaster") {
            $rankname = $rank . 's';
        } else {
            $rankname = $rank;
        }
        $team = "";
        $result = $this->db->query("SELECT * FROM master WHERE guild = '$guild' AND rank = '$rank';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['player'] = $resultArr['player'];
            if ($this->getServer()->getPlayerExact($row[$i]['player']) instanceof Player) {
                $team .= TextFormat::ITALIC . TextFormat::AQUA . $row[$i]['player'] . TextFormat::GREEN . "[ON]" . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            } else {
                $team .= TextFormat::ITALIC . TextFormat::AQUA . $row[$i]['player'] . TextFormat::RED . "[OFF]" . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            }
            $i = $i + 1;
        }
        $sender->sendMessage($this->formatMessage("~ *<$rankname> of |$guild|* ~", true));
        $sender->sendMessage($team);
    }
    public function getPlayerGuild($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["guild"];
    }
    public function getGuildsMaster($guild) {
        $gm = $this->db->query("SELECT * FROM master WHERE guild = '$guild' AND rank = 'GuildsMaster';");
        $gmArray = $gm->fetchArray(SQLITE3_ASSOC);
        return $gmArray['player'];
    }
    public function guildsExists($guild) {
        $result = $this->db->query("SELECT * FROM master WHERE guild = '$guild';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function sameGuild($player1, $player2) {
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player1';");
        $player1Guild = $guild->fetchArray(SQLITE3_ASSOC);
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player2';");
        $player2Guild = $guild->fetchArray(SQLITE3_ASSOC);
        return $player1Guild["guild"] == $player2Guild["guild"];
    }
    public function getNumberOfPlayers($guild) {
        $query = $this->db->query("SELECT COUNT(*) as count FROM master WHERE guild = '$guild';");
        $number = $query->fetchArray();
        return $number['count'];
    }
    public function isGuildFull($guild) {
        return $this->getNumberOfPlayers($guild) >= $this->prefs->get("MaxPlayersPerGuild");
    }
/////??GUILDSPOINTS??/////
    public function setGuildsPoints($guild, $gp) {
        if ($gp < 0) {
            $gp = 0;
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoints", $gp);
        $result = $stmt->execute();
    }
    public function getGuildsPoints($guild) {
        $result = $this->db->query("SELECT * FROM gp WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["guildpoints"];
    }
    public function addGuildsPoints($guild, $gp) {
        if ($this->getGuildsPoints($guild) + $gp < 0) {
            $gp = $this->getGuildsPoints($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoints", $this->getGuildsPoints($guild) + $gp);
        $result = $stmt->execute();
    }
    public function subtractGuildsPoints($guild, $gp) {
        if ($this->getGuildsPoints($guild) - $gp < 0) {
            $gp = $this->getGuildsPoints($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoint);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoint", $this->getGuildsPoints($guild) - $gp);
        $result = $stmt->execute();
    }
/////??ALLIANCE??/////
    public function setAlliance($guild1, $guild2) {
        $stmt = $this->db->prepare("INSERT INTO alliance (guild1, guild2) VALUES (:guild1, :guild2);");
        $stmt->bindValue(":guild1", $guild1);
        $stmt->bindValue(":guild2", $guild2);
        $result = $stmt->execute();
    }

    public function areAlliance($guild1, $guild2) {
        $result = $this->db->query("SELECT * FROM alliance WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if (empty($resultArr) == false) {
            return true;
        }
    }
    public function updateAlliance($guild) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO alliancecountlimit(guild, count) VALUES (:guild, :count);");
        $stmt->bindValue(":guild", $guild);
        $result = $this->db->query("SELECT * FROM alliance WHERE guild1= '$guild';");
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $i = $i + 1;
        }
        $stmt->bindValue(":count", (int) $i);
        $result = $stmt->execute();
    }
    public function getAllianceCount($guild) {
        $result = $this->db->query("SELECT * FROM alliancecountlimit WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["count"];
    }
    public function getAllianceLimit() {
        return (int) $this->prefs->get("AllianceLimitPerGuilds");
    }
    public function deleteAlliance($guild1, $guild2) {
        $stmt = $this->db->prepare("DELETE FROM alliance WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $result = $stmt->execute();
    }
    public function getAllAlliance($sender, $guild) {
        $team = "";
        $result = $this->db->query("SELECT * FROM alliance WHERE guild1 = '$guild';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['guild2'] = $resultArr['guild2'];
            $team .= TextFormat::ITALIC . TextFormat::RED . $row[$i]['guild2'] . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            $i = $i + 1;
        }
        $sender->sendMessage($this->formatMessage("§l§b»§r§e Alliance of $guild §l§b«", true));
        $sender->sendMessage($team);
    }
/////??ETC??/////
    public function isNameBanned($name) {
        $banName = "DisableGuildsName.txt";
        $bannedNames = file_get_contents($this->getDataFolder() . $banName);
        return (strpos(strtolower($bannedNames), strtolower($name)));
    }
    public function leaderboards($sender) {
        $tf = "";
        $result = $this->db->query("SELECT guild FROM gp ORDER BY guildpoints DESC LIMIT 10;");
        $row = array();
        $i = 0;
        $sender->sendMessage($this->formatMessage("§l§b»§r§e-=§f[§aTop 10 Leading Guildz§f]§e=-§l§b«", true));
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $j = $i + 1;
            $cf = $resultArr['guild'];
            $pf = $this->getGuildsPoints($cf);
            $df = $this->getNumberOfPlayers($cf);
            $sender->sendMessage(TextFormat::BLUE . TextFormat::GOLD . "§l§b» §r§e$j §f:  " . TextFormat::GREEN . "$cf" . TextFormat::GOLD . " with " . TextFormat::RED . "$pf GuildsPoints" . TextFormat::GOLD . " and " . TextFormat::LIGHT_PURPLE . "have $df PLAYERS" . TextFormat::RESET);
            $i = $i + 1;
        }
    }
    public function formatMessage($string, $confirm = false) {
        if ($confirm) {
            return TextFormat::GREEN . "$string";
        } else {
            return TextFormat::YELLOW . "$string";
        }
    }
    public function updateTag($player) {
        $p = $this->getServer()->getPlayer($player);
        $f = $this->getPlayerGuild($player);
        $n = $this->getNumberOfPlayers($f);
        if (!$this->isInGuilds($player)) {
            $p->setNameTag(TextFormat::ITALIC . TextFormat::YELLOW . "<$player>");
        } else {
            $p->setNameTag(TextFormat::ITALIC . TextFormat::GOLD . "<$f> " .
                    TextFormat::ITALIC . TextFormat::YELLOW . "<$player>");
        }
    }
/////??PLOT??/////
    public function newPlot($guild, $x1, $z1, $x2, $z2) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO plots (guild, x1, z1, x2, z2) VALUES (:guild, :x1, :z1, :x2, :z2);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":x1", $x1);
        $stmt->bindValue(":z1", $z1);
        $stmt->bindValue(":x2", $x2);
        $stmt->bindValue(":z2", $z2);
        $result = $stmt->execute();
    }
    public function drawPlot($sender, $guild, $x, $y, $z, $level, $size) {
        $arm = ($size - 1) / 2;
        //$block = new Snow();
        if ($this->cornerIsInPlot($x + $arm, $z + $arm, $x - $arm, $z - $arm)) {
            $claimedBy = $this->guildFromPoint($x, $z);
            $gp_claimedBy = $this->getGuildsPoints($claimedBy);
            $gp_sender = $this->getGuildsPoints($guild);
            if ($this->prefs->get("EnableOverClaim")) {
                if ($gp_sender < $gp_claimedBy) {
                    $sender->sendMessage($this->formatMessage("This area is aleady claimed by $claimedBy with $gp_claimedBy STR. Your guild has $gp_sender power. You don't have enough power to overclaim this plot."));
                } else {
                    $sender->sendMessage($this->formatMessage("This area is aleady claimed by $claimedBy with $gp_claimedBy STR. Your guild has $gp_sender power. Type /guilds overclaim to overclaim this plot if you want."));
                }
                return false;
            } else {
                $sender->sendMessage($this->formatMessage("Overclaiming is disabled."));
                return false;
            }
        }
        $level->setBlock(new Vector3($x + $arm, $y, $z + $arm));//, $block
        $level->setBlock(new Vector3($x - $arm, $y, $z - $arm));//, $block
        $this->newPlot($guild, $x + $arm, $z + $arm, $x - $arm, $z - $arm);
        return true;
    }
    public function isInPlot($player) {
        $x = $player->getFloorX();
        $z = $player->getFloorZ();
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function guildFromPoint($x, $z) {
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return $array["guild"];
    }
    public function inOwnPlot($player) {
        $playerName = $player->getName();
        $x = $player->getFloorX();
        $z = $player->getFloorZ();
        return $this->getPlayerGuild($playerName) == $this->guildFromPoint($x, $z);
    }
    public function pointIsInPlot($x, $z) {
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return !empty($array);
    }
    public function cornerIsInPlot($x1, $z1, $x2, $z2) {
        return($this->pointIsInPlot($x1, $z1) || $this->pointIsInPlot($x1, $z2) || $this->pointIsInPlot($x2, $z1) || $this->pointIsInPlot($x2, $z2));
    }
/////??MOTD??/////
    public function motdWaiting($player) {
        $stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
        $array = $stmt->fetchArray(SQLITE3_ASSOC);
        return !empty($array);
    }
    public function getMOTDTime($player) {
        $stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
        $array = $stmt->fetchArray(SQLITE3_ASSOC);
        return $array['timestamp'];
    }
    public function setMOTD($guild, $player, $msg) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO motd (guild, message) VALUES (:guild, :message);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":message", $msg);
        $result = $stmt->execute();
        $this->db->query("DELETE FROM motdrcv WHERE player='$player';");
    }
/////??GUILDMONEY??/////
    public function getGuildMoney($guild) {
        $result = $this->db->query("SELECT * FROM moneys WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["moneys"];
    }

    public function addGuildMoney($guild, $money) {
        if ($this->getGuildMoney($guild) + $money < 0) {
            $money = $this->getGuildMoney($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO moneys (guild, moneys) VALUES (:guild, :moneys);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":moneys", $this->getGuildMoney($guild) + $money);
        $result = $stmt->execute();
    }

    public function subtractGuildMoney($guild, $money) {
        if ($this->getGuildMoney($guild) - $money < 0) {
            $money = $this->getGuildMoney($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO moneys (guild, moneys) VALUES (:guild, :moneys);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":moneys", $this->getGuildMoney($guild) - $money);
        $result = $stmt->execute();
    }
/////??WarPoints??/////
    public function getGuildWPoint($guild) {
        $result = $this->db->query("SELECT * FROM wp WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["warpoints"];
    }

    public function addGuildWPoint($guild, $wp) {
        if ($this->getGuildWPoint($guild) + $wp < 0) {
            $wp = $this->getGuildWPoint($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO wp (guild, warpoints) VALUES (:guild, :warpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":warpoints", $this->getGuildWPoint($guild) + $wp);
        $result = $stmt->execute();
    }

    public function subtractGuildWPoint($guild, $wp) {
        if ($this->getGuildWPoint($guild) - $wp < 0) {
            $wp = $this->getGuildWPoint($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO wp (guild, warpoints) VALUES (:guild, :warpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":warpoints", $this->getGuildWPoint($guild) - $wp);
        $result = $stmt->execute();
    }
}

        $this->guildsCommand = new GuildsCommands($this);
        $settingsname = "GuildsOptions.yml";
        $this->settings = new Config($this->getDataFolder() . $settingsname, CONFIG::YAML, array(
            "MaxGuildNameLength" => 15,
            "MaxPlayersPerguild" => 30,
            "OnlyLeadersAndOfficersCanInvite" => true,
            "###Plots###",
		    "OfficersCanClaim" => false,
	    	"PlotSize" => 30,
            "PlayersNeededInguildToClaimAPlot" => 5,
            "ClaimWorlds" => [],
            "###Guilds Power###",
            "PowerNeededToClaimAPlot" => 1000,
            "PowerNeededToSetOrUpdateAHome" => 250,
            "PowerGainedPerPlayerInguild" => 50,
            "PowerGainedPerKillingAnEnemy" => 10,
    		"PowerReducedPerDeathByAnEnemy" => 10,
            "PowerGainedPerAlly" => 100,
            "AllyLimitPerguild" => 5,
            "TheDefaultPowerEveryguildStartsWith" => 0,
            "###Economys###",
            "CreateCost" => 3000,
		    "ClaimCost" => 100000,
	    	"OverClaimCost" => 25000,
	    	"AllyCost" => 5000,
	    	"AllyPrice" => 5000,
	    	"SetHomeCost" => 150,
            "###GuildsMoneys###",
            "GuildsMoneyGainPerKill" => 10,
            "GuildsMoneyLostPerDeath" => 10,
        ));
        $databasefile = "GuildsDatabase.db";
        $this->db = new \SQLite3($this->getDataFolder() . $databasefile);
        $this->db->exec("CREATE TABLE IF NOT EXISTS master (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, rank TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS confirm (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, invitedby TEXT, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliance (player TEXT PRIMARY KEY COLLATE NOCASE, guild TEXT, requestedby TEXT, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS motdrcv (player TEXT PRIMARY KEY, timestamp INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS motd (guild TEXT PRIMARY KEY, message TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS plots (guild TEXT PRIMARY KEY, x1 INT, z1 INT, x2 INT, z2 INT, world TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS home (guild TEXT PRIMARY KEY, x INT, y INT, z INT, world TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS gp (guild TEXT PRIMARY KEY, guildpoints INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliance (ID INT PRIMARY KEY, guild1 TEXT, guild2 TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS nemisys (ID INT PRIMARY KEY, guild1 TEXT, guild2 TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS alliancecountlimit (guild TEXT PRIMARY KEY, count INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS effects (guild TEXT PRIMARY KEY, effect TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS moneys (guild TEXT PRIMARY KEY, moneys INT);");
		$this->db->exec("CREATE TABLE IF NOT EXISTS boosters (guild TEXT PRIMARY KEY, booster TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS wp (guild TEXT PRIMARY KEY, warpoints INT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS nemisyscountlimit (guild TEXT PRIMARY KEY, count INT);");
    }
    public function onDisable() {
        $loadingoff = Z::GOLD . "Saving Database";
        $completeoff = Z::GREEN . "Complete!";
        $disablemsg = Z::RED . "GCore OFFLINE!";
        $this->getLogger()->notice($loadingoff);
        $this->getLogger()->critical($completeoff);
        $this->getLogger()->error($disablemsg);
        $this->db->close();
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        $this->guildsCommand->onCommand($sender, $command, $label, $args);
    }
///////////////////??EFFECTS??///////////////////
    public function addEffectTo($guild, $effect){
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO effects (guild, effect) VALUES (:guild, :effect);");  
        $stmt->bindValue(":guild", $guild);
		$stmt->bindValue(":effect", $effect);
		$result = $stmt->execute();
    }
    public function getEffectOf($guild){
        $result = $this->db->query("SELECT * FROM effects WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return "none";
        }
        return $resultArr['effect'];
    }
/////??BOOSTER??/////
    public function addBoosterInto($guild, $effect){
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO boosters (guild, booster) VALUES (:guild, :booster);");  
        $stmt->bindValue(":guild", $guild);
		$stmt->bindValue(":booster", $booster);
		$result = $stmt->execute();
    }
    public function getBoosterFrom($guild){
        $result = $this->db->query("SELECT * FROM boosters WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if(empty($resultArr)){
            return "none";
        }
        return $resultArr['booster'];
    }
/////??NEMISYS??/////
    public function setNemisys($guild1, $guild2) {
        $stmt = $this->db->prepare("INSERT INTO nemisys (guild1, guild2) VALUES (:guild1, :guild2);");
        $stmt->bindValue(":guild1", $guild1);
        $stmt->bindValue(":guild2", $guild2);
        $result = $stmt->execute();
    }
    public function isNemisys($guild1, $guild2) {
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if (empty($resultArr) == false) {
            return true;
        }
    }
    public function updateNemisys($guild) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO nemisyscountlimit (guild, count) VALUES (:guild, :count);");
        $stmt->bindValue(":guild", $guild);
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1='$guild';");
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $i = $i + 1;
        }
        $stmt->bindValue(":count", (int) $i);
        $result = $stmt->execute();
    }
    public function getNemisysCount($guild) {
        $result = $this->db->query("SELECT * FROM nemisyscountlimit WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["count"];
    }
    public function getNemisysLimit() {
        return (int) $this->prefs->get("NemisysLimitPerguild");
    }
    public function deleteNemisys($guild1, $guild2) {
        $stmt = $this->db->prepare("DELETE FROM nemisys WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $result = $stmt->execute();
    }
    public function getAllNemisys($sender, $guild) {
        $team = "";
        $result = $this->db->query("SELECT * FROM nemisys WHERE guild1 ='$guild';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['guild2'] = $resultArr['guild2'];
            $team .= TextFormat::ITALIC . TextFormat::RED . $row[$i]['guild2'] . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            $i = $i + 1;
        }

        $sender->sendMessage($this->formatMessage("§l§b»§r§e Nemisys of $guild §l§b«", true));
        $sender->sendMessage($team);
    }
/////??Guilds??/////
    public function isInGuilds($player) {
        $result = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function getGuilds($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["guild"];
    }
    public function isGuildsMaster($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "GuildsMaster";
    }

    public function isSecondInCommands($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "SecondInCommands";
    }

    public function isMember($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player='$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["rank"] == "Member";
    }
    public function getPlayersInGuildsByRank($sender, $guild, $rank) {
        if ($rank != "GuildsMaster") {
            $rankname = $rank . 's';
        } else {
            $rankname = $rank;
        }
        $team = "";
        $result = $this->db->query("SELECT * FROM master WHERE guild = '$guild' AND rank = '$rank';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['player'] = $resultArr['player'];
            if ($this->getServer()->getPlayerExact($row[$i]['player']) instanceof Player) {
                $team .= TextFormat::ITALIC . TextFormat::AQUA . $row[$i]['player'] . TextFormat::GREEN . "[ON]" . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            } else {
                $team .= TextFormat::ITALIC . TextFormat::AQUA . $row[$i]['player'] . TextFormat::RED . "[OFF]" . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            }
            $i = $i + 1;
        }
        $sender->sendMessage($this->formatMessage("~ *<$rankname> of |$guild|* ~", true));
        $sender->sendMessage($team);
    }
    public function getPlayerGuild($player) {
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player';");
        $guildArray = $guild->fetchArray(SQLITE3_ASSOC);
        return $guildArray["guild"];
    }
    public function getGuildsMaster($guild) {
        $gm = $this->db->query("SELECT * FROM master WHERE guild = '$guild' AND rank = 'GuildsMaster';");
        $gmArray = $gm->fetchArray(SQLITE3_ASSOC);
        return $gmArray['player'];
    }
    public function guildsExists($guild) {
        $result = $this->db->query("SELECT * FROM master WHERE guild = '$guild';");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function sameGuild($player1, $player2) {
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player1';");
        $player1Guild = $guild->fetchArray(SQLITE3_ASSOC);
        $guild = $this->db->query("SELECT * FROM master WHERE player = '$player2';");
        $player2Guild = $guild->fetchArray(SQLITE3_ASSOC);
        return $player1Guild["guild"] == $player2Guild["guild"];
    }
    public function getNumberOfPlayers($guild) {
        $query = $this->db->query("SELECT COUNT(*) as count FROM master WHERE guild = '$guild';");
        $number = $query->fetchArray();
        return $number['count'];
    }
    public function isGuildFull($guild) {
        return $this->getNumberOfPlayers($guild) >= $this->prefs->get("MaxPlayersPerGuild");
    }
/////??GUILDSPOINTS??/////
    public function setGuildsPoints($guild, $gp) {
        if ($gp < 0) {
            $gp = 0;
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoints", $gp);
        $result = $stmt->execute();
    }
    public function getGuildsPoints($guild) {
        $result = $this->db->query("SELECT * FROM gp WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["guildpoints"];
    }
    public function addGuildsPoints($guild, $gp) {
        if ($this->getGuildsPoints($guild) + $gp < 0) {
            $gp = $this->getGuildsPoints($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoints", $this->getGuildsPoints($guild) + $gp);
        $result = $stmt->execute();
    }
    public function subtractGuildsPoints($guild, $gp) {
        if ($this->getGuildsPoints($guild) - $gp < 0) {
            $gp = $this->getGuildsPoints($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO gp (guild, guildpoints) VALUES (:guild, :guildpoint);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":guildpoint", $this->getGuildsPoints($guild) - $gp);
        $result = $stmt->execute();
    }
/////??ALLIANCE??/////
    public function setAlliance($guild1, $guild2) {
        $stmt = $this->db->prepare("INSERT INTO alliance (guild1, guild2) VALUES (:guild1, :guild2);");
        $stmt->bindValue(":guild1", $guild1);
        $stmt->bindValue(":guild2", $guild2);
        $result = $stmt->execute();
    }

    public function areAlliance($guild1, $guild2) {
        $result = $this->db->query("SELECT * FROM allies WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        if (empty($resultArr) == false) {
            return true;
        }
    }
    public function updateAlliance($guild) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO alliancecountlimit (guild, count) VALUES (:guild, :count);");
        $stmt->bindValue(":guild", $guild);
        $result = $this->db->query("SELECT * FROM alliance WHERE guild1 = '$guild';");
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $i = $i + 1;
        }
        $stmt->bindValue(":count", (int) $i);
        $result = $stmt->execute();
    }
    public function getAllianceCount($guild) {
        $result = $this->db->query("SELECT * FROM alliancecountlimit WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["count"];
    }
    public function getAllianceLimit() {
        return (int) $this->prefs->get("AllianceLimitPerGuilds");
    }
    public function deleteAlliance($guild1, $guild2) {
        $stmt = $this->db->prepare("DELETE FROM alliance WHERE guild1 = '$guild1' AND guild2 = '$guild2';");
        $result = $stmt->execute();
    }
    public function getAllAlliance($sender, $guild) {
        $team = "";
        $result = $this->db->query("SELECT * FROM alliance WHERE guild1 = '$guild';");
        $row = array();
        $i = 0;
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $row[$i]['guild2'] = $resultArr['guild2'];
            $team .= TextFormat::ITALIC . TextFormat::RED . $row[$i]['guild2'] . TextFormat::RESET . TextFormat::WHITE . "||" . TextFormat::RESET;
            $i = $i + 1;
        }
        $sender->sendMessage($this->formatMessage("§l§b»§r§e Alliance of $guild §l§b«", true));
        $sender->sendMessage($team);
    }
/////??ETC??/////
    public function isNameBanned($name) {
        $banName = "DisableGuildsName.txt";
        $bannedNames = file_get_contents($this->getDataFolder() . $banName);
        return (strpos(strtolower($bannedNames), strtolower($name)));
    }
    public function leaderboards($sender) {
        $tf = "";
        $result = $this->db->query("SELECT guild FROM gp ORDER BY guildpoints DESC LIMIT 10;");
        $row = array();
        $i = 0;
        $sender->sendMessage($this->formatMessage("§l§b»§r§e-=§f[§aTop 10 Leading Guildz§f]§e=-§l§b«", true));
        while ($resultArr = $result->fetchArray(SQLITE3_ASSOC)) {
            $j = $i + 1;
            $cf = $resultArr['guild'];
            $pf = $this->getGuildsPoints($cf);
            $df = $this->getNumberOfPlayers($cf);
            $sender->sendMessage(TextFormat::BLUE . TextFormat::GOLD . "§l§b» §r§e$j §f:  " . TextFormat::GREEN . "$cf" . TextFormat::GOLD . " with " . TextFormat::RED . "$pf GuildsPoints" . TextFormat::GOLD . " and " . TextFormat::LIGHT_PURPLE . "have $df PLAYERS" . TextFormat::RESET);
            $i = $i + 1;
        }
    }
    public function formatMessage($string, $confirm = false) {
        if ($confirm) {
            return TextFormat::GREEN . "$string";
        } else {
            return TextFormat::YELLOW . "$string";
        }
    }
    public function updateTag($player) {
        $p = $this->getServer()->getPlayer($player);
        $f = $this->getPlayerGuild($player);
        $n = $this->getNumberOfPlayers($f);
        if (!$this->isInGuilds($player)) {
            $p->setNameTag(TextFormat::ITALIC . TextFormat::YELLOW . "<$player>");
        } else {
            $p->setNameTag(TextFormat::ITALIC . TextFormat::GOLD . "<$f> " .
                    TextFormat::ITALIC . TextFormat::YELLOW . "<$player>");
        }
    }
/////??PLOT??/////
    public function newPlot($guild, $x1, $z1, $x2, $z2) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO plots (guild, x1, z1, x2, z2) VALUES (:guild, :x1, :z1, :x2, :z2);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":x1", $x1);
        $stmt->bindValue(":z1", $z1);
        $stmt->bindValue(":x2", $x2);
        $stmt->bindValue(":z2", $z2);
        $result = $stmt->execute();
    }
    public function drawPlot($sender, $guild, $x, $y, $z, $level, $size) {
        $arm = ($size - 1) / 2;
        //$block = new Snow();
        if ($this->cornerIsInPlot($x + $arm, $z + $arm, $x - $arm, $z - $arm)) {
            $claimedBy = $this->guildFromPoint($x, $z);
            $gp_claimedBy = $this->getGuildsPoints($claimedBy);
            $gp_sender = $this->getGuildsPoints($guild);
            if ($this->prefs->get("EnableOverClaim")) {
                if ($gp_sender < $gp_claimedBy) {
                    $sender->sendMessage($this->formatMessage("This area is aleady claimed by $claimedBy with $gp_claimedBy STR. Your guild has $gp_sender power. You don't have enough power to overclaim this plot."));
                } else {
                    $sender->sendMessage($this->formatMessage("This area is aleady claimed by $claimedBy with $gp_claimedBy STR. Your guild has $gp_sender power. Type /guilds overclaim to overclaim this plot if you want."));
                }
                return false;
            } else {
                $sender->sendMessage($this->formatMessage("Overclaiming is disabled."));
                return false;
            }
        }
        $level->setBlock(new Vector3($x + $arm, $y, $z + $arm));//, $block
        $level->setBlock(new Vector3($x - $arm, $y, $z - $arm));//, $block
        $this->newPlot($guild, $x + $arm, $z + $arm, $x - $arm, $z - $arm);
        return true;
    }
    public function isInPlot($player) {
        $x = $player->getFloorX();
        $z = $player->getFloorZ();
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return empty($array) == false;
    }
    public function guildFromPoint($x, $z) {
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return $array["guild"];
    }
    public function inOwnPlot($player) {
        $playerName = $player->getName();
        $x = $player->getFloorX();
        $z = $player->getFloorZ();
        return $this->getPlayerGuild($playerName) == $this->guildFromPoint($x, $z);
    }
    public function pointIsInPlot($x, $z) {
        $result = $this->db->query("SELECT * FROM plots WHERE $x <= x1 AND $x >= x2 AND $z <= z1 AND $z >= z2;");
        $array = $result->fetchArray(SQLITE3_ASSOC);
        return !empty($array);
    }
    public function cornerIsInPlot($x1, $z1, $x2, $z2) {
        return($this->pointIsInPlot($x1, $z1) || $this->pointIsInPlot($x1, $z2) || $this->pointIsInPlot($x2, $z1) || $this->pointIsInPlot($x2, $z2));
    }
/////??MOTD??/////
    public function motdWaiting($player) {
        $stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
        $array = $stmt->fetchArray(SQLITE3_ASSOC);
        return !empty($array);
    }
    public function getMOTDTime($player) {
        $stmt = $this->db->query("SELECT * FROM motdrcv WHERE player='$player';");
        $array = $stmt->fetchArray(SQLITE3_ASSOC);
        return $array['timestamp'];
    }
    public function setMOTD($guild, $player, $msg) {
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO motd (guild, message) VALUES (:guild, :message);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":message", $msg);
        $result = $stmt->execute();
        $this->db->query("DELETE FROM motdrcv WHERE player='$player';");
    }
/////??GUILDMONEY??/////
    public function getGuildMoney($guild) {
        $result = $this->db->query("SELECT * FROM moneys WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["moneys"];
    }

    public function addGuildMoney($guild, $money) {
        if ($this->getGuildMoney($guild) + $money < 0) {
            $money = $this->getGuildMoney($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO moneys (guild, moneys) VALUES (:guild, :moneys);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":moneys", $this->getGuildMoney($guild) + $money);
        $result = $stmt->execute();
    }

    public function subtractGuildMoney($guild, $money) {
        if ($this->getGuildMoney($guild) - $money < 0) {
            $money = $this->getGuildMoney($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO moneys (guild, moneys) VALUES (:guild, :moneys);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":moneys", $this->getGuildMoney($guild) - $money);
        $result = $stmt->execute();
    }
/////??WarPoints??/////
    public function getGuildWPoint($guild) {
        $result = $this->db->query("SELECT * FROM wp WHERE guild = '$guild';");
        $resultArr = $result->fetchArray(SQLITE3_ASSOC);
        return (int) $resultArr["warpoints"];
    }

    public function addGuildWPoint($guild, $wp) {
        if ($this->getGuildWPoint($guild) + $wp < 0) {
            $wp = $this->getGuildWPoint($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO wp (guild, warpoints) VALUES (:guild, :warpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":warpoints", $this->getGuildWPoint($guild) + $wp);
        $result = $stmt->execute();
    }

    public function subtractGuildWPoint($guild, $wp) {
        if ($this->getGuildWPoint($guild) - $wp < 0) {
            $wp = $this->getGuildWPoint($guild);
        }
        $stmt = $this->db->prepare("INSERT OR REPLACE INTO wp (guild, warpoints) VALUES (:guild, :warpoints);");
        $stmt->bindValue(":guild", $guild);
        $stmt->bindValue(":warpoints", $this->getGuildWPoint($guild) - $wp);
        $result = $stmt->execute();
    }
}
