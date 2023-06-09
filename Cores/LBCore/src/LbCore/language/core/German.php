<?php
namespace LbCore\language\core;

use pocketmine\utils\TextFormat;
use LbCore\language\Translate;

class German
{

    public $translates = array(
        "ERR_PLAYER_NOT_FOUND" => TextFormat::RED . "Es konnte kein Spieler mit diesem Namen gefunden werden.",
        "YOUR_COINS" => TextFormat::GOLD . "Deine Punkte: " . TextFormat::WHITE . "arg1",
        "PLAYER_HAS_COINS" => TextFormat::WHITE . "arg1 " . TextFormat::DARK_AQUA . "hat " . TextFormat::WHITE . "arg2 " . TextFormat::DARK_AQUA . "Punkte.",
        "SENT_COINS" => TextFormat::DARK_AQUA . "Du hast " . TextFormat::WHITE . "arg1 " . TextFormat::DARK_AQUA . "Punkte an " . TextFormat::WHITE . "arg2 " . TextFormat::DARK_AQUA . "geschickt.",
        "ERR_INSUFFICENT_COINS" => TextFormat::RED . "Du hast nicht genug Punkte.",
        "NOT_REGISTERED" => TextFormat::RED . "Registriere dich, um dies zu tun",
        "WELCOME_MESSAGE_REGISTERED" => TextFormat::DARK_AQUA . "Willkommen auf " . TextFormat::AQUA . "Life" . TextFormat::RED . "boat" . TextFormat::DARK_AQUA . "!\n" . TextFormat::YELLOW . "Dieser Benutzername ist schon registriert, bitte melde dich an\n" . TextFormat::YELLOW . "oder änder deinen Benutzernamen in den Einstellungen\n" . TextFormat::YELLOW . "und benutze /register um dich zu registrieren.",
        "PASSWORD_CHANGED" => TextFormat::GREEN . "Dein Passwort wurde erfolgreich geändert.",
        "PASSWORD_NOT_CHANGED" => TextFormat::RED . "Ein Fehler ist aufgetreten, dein Passwort konnte nicht geändert werden.",
        "NEEDS_LOGIN" => TextFormat::RED . "Bitte melde dich erst an.",
        "ACCOUNT_LOCKED" => TextFormat::RED . "Account gesperrt.",
        "WELCOME_MESSAGE_UNREGISTERED" => TextFormat::DARK_AQUA . "Willkommen auf " . TextFormat::AQUA . "Life" . TextFormat::RED . "boat" . TextFormat::DARK_AQUA . "!\n" . TextFormat::YELLOW . "Dieser Account ist noch nicht registriert, du kannst ihn\n" . TextFormat::YELLOW . "mit " . TextFormat::LIGHT_PURPLE . "/register" . TextFormat::YELLOW . " registrieren.",
        "REGISTRATION_SUCCESS" => TextFormat::GREEN . "Du bist nun registriert.",
        "ON_LOGIN" => TextFormat::GREEN . "Du bist nun angemeldet.",
        "CONFIRM_PASS" => TextFormat::YELLOW . "Bitte schreibe dein gewünschtes Passwort noch einmal.",
        "SHORT_PASS" => TextFormat::RED . "Das Passwort ist zu kurz, bitte wähle ein längeres.",
        "PASS_NOT_MATCH" => TextFormat::RED . "Die Passwörter stimmen nicht überein, versuche es noch einmal.",
        "FINISH_REGISTRATION" => TextFormat::YELLOW . "Du registriest den Account: " . TextFormat::WHITE . "arg1\n" . TextFormat::YELLOW . "Mit dem Passwort: " . TextFormat::WHITE . "arg2\n" . TextFormat::YELLOW . "Bitte schreibe deine E-Mail in den Chat um die Registrierung abzuschließen.",
        "NEW_PASS" => TextFormat::YELLOW . "Bitte schreibe dein gewünschtes neues Passwort in den Chat.",
        "INVALID_EMAIL" => TextFormat::YELLOW . "Bitte schreibe eine gültige E-Mail.",
        "INCORRECT_PASSWORD" => TextFormat::DARK_AQUA . "Dieser Account ist registriert, und das ist nicht das\n" . TextFormat::DARK_AQUA . "richtige Passwort. Falls du das Passwort nicht kennst,\n" . TextFormat::DARK_AQUA . "benutze bitte einen anderen MCPE Benutzernamen.",
        "SESSION_IN_USE" => TextFormat::DARK_AQUA . "Da dein Account auf einem anderen Lifeboat Server benutzt wird, wurdest du abgemeldet.",
        "DEFAULT_USER" => TextFormat::LIGHT_PURPLE . "Du benutzt den Standart-Benutzernamen.\n" . TextFormat::LIGHT_PURPLE . "Bitte änder ihn in deinen Einstellungen.",
        "WAITING_FOR_LOGIN" => TextFormat::LIGHT_PURPLE . "Warte auf Login-Informationen für diesen Account...",
        "VIP_GIVAWAY_WINNER" => TextFormat::DARK_PURPLE . "Du hast VIP+ bis zu deiner nächsten Abmeldung gewonnen!\o/\n" . TextFormat::DARK_PURPLE . "Bitte melde dich an.",
        "REGISTRATION_FAILED" => TextFormat::RED . "Registrierung fehlgeschlagen, versuche es später erneut.",
        "LOGIN_DB_ERROR" => TextFormat::BOLD . TextFormat::DARK_GRAY . "." . TextFormat::DARK_AQUA . "Willkommen auf " . TextFormat::AQUA . "Life" . TextFormat::RED . "boat" . TextFormat::DARK_AQUA . "!\n" . TextFormat::RED . "Es ist ein Fehler in unserer Datenbank aufgetreten. Falls du registriert oder ein VIP+ Spieler bist, bitte verlasse den Server und versuche es erneut." . TextFormat::BOLD . TextFormat::DARK_GRAY . ".",
        "STATISTIC_HEADER" => TextFormat::DARK_AQUA . "Zeige Statistiken von: " . TextFormat::WHITE . "arg1",
        "KILL_DEATH_COUNTS" => TextFormat::DARK_AQUA . "Kills: " . TextFormat::WHITE . "arg1" . TextFormat::DARK_AQUA . ", Tode: " . TextFormat::WHITE . "arg2",
        "LAST_SEEN" => TextFormat::DARK_AQUA . "Letztes mal gesehen auf: " . TextFormat::WHITE,
        "IDLE_TIMEOUT" => TextFormat::YELLOW . "Du hast schon länger kein Match mehr betreten. Verbindung getrennt.",
        "IP_LIMITED" => TextFormat::YELLOW . "Zu viele Spieler benutzen diese IP, bitte versuche es später erneut.",
        "ALREADY_AUTHENTICATED" => TextFormat::YELLOW . "Du bist auf diesem Server schon angemeldet.",
        "MOD_WARNING" => TextFormat::RED . "Mods sind auf LBSG nicht erlaubt, bitte schalte diese aus.",
        "LOW_PLAYER_COUNT" => TextFormat::DARK_PURPLE . "Da zu wenige Spieler online sind, wird der Server neu gestartet.",
        "ABOUT_TO_RESTART" => TextFormat::DARK_AQUA . "Der Server startet in arg1 Sekunde(n) neu...",
        "RESTARTING" => TextFormat::DARK_AQUA . "Server wird neu gestartet...",
        "VIP_ENABLED" => TextFormat::YELLOW . "VIP Items " . TextFormat::GREEN . "aktiviert" . TextFormat::YELLOW . ".",
        "VIP_DISABLED" => TextFormat::YELLOW . "VIP Items " . TextFormat::RED . "deaktiviert" . TextFormat::YELLOW . ".",
        "VIP_USAGE" => TextFormat::YELLOW . "Benutze /vip",
        "RANKS_RETRIEVING_START" => TextFormat::DARK_PURPLE . "Rufe Ränge von der Datenbank ab.",
        "RANKS_RETRIEVING_ERROR" => TextFormat::YELLOW . "Es gab einen Fehler beim Abfragen der Ränge von der Datebank, versuche es erneut.",
        "CANNOT_SAY_PASSWORD" => TextFormat::RED . "Nachricht geblockt " . TextFormat::YELLOW . "(dein Passwort anderen zu geben\n" . TextFormat::YELLOW . "ist eine schlechte Idee und wird deinen Account eventuell sperren).",
        "MSG_INAPPROPRIATE" => TextFormat::RED . "Deine Nachricht wurde vom Chat-Filter geblockt.",
        "NO_DATING" => TextFormat::RED . "Bitte benutze LBSG nicht als Dating-Seite.",
        "NO_ADVERTISING" => TextFormat::RED . "Bitte mache keine Werbung.",
        "CIRCUMVENT_WARNING" => TextFormat::RED . "Bitte versuche nicht, den Chat-Filter zu umgehen.",
        "CHAT_WHEN_MUTED" => TextFormat::DARK_BLUE . "- " . TextFormat::DARK_PURPLE . "Du kannst keine Nachrichten schicken, während du gemutet bist.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::DARK_PURPLE . "Benutze /unmute um wieder schreiben zu können.",
        "COMMAND_WHEN_MUTED" => TextFormat::RED . "Du kannst keine Befehle benutzen, während du gemutet bist.\n" . TextFormat::RED . "Benutze /unmute um wieder Befehle zu benutzen.",
        "RATE_LIMITED" => TextFormat::RED . "Bitte warte eine Weile, bevor du etwas schreibst.",
		"MSG_SHORT" => TextFormat::RED . "Bitte schreibe eine längere Nachricht.",
        "MSG_REPEATED" => TextFormat::RED . "Warte, bevor du dich wiederholst.",
        "KITS_HEADER" => TextFormat::YELLOW . "Verfügbare Kits (Seite arg1 von arg2):",
        "HELP_TOO_FAR" => TextFormat::GRAY . "Diese Seite existiert nicht.",
        "CMD_DEFAULT_ACCOUNT" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du kannst das nicht auf diesem Account machen.",
        "CMD_REQUIRE_LOGIN" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du musst dich erst anmelden oder registrieren.",
        "MUTE_NO_MORE_CHAT" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Du wirst nun keine Nachrichten von Spielern mehr sehen.\n " . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Um dies wieder zu tun, benutze " . TextFormat::LIGHT_PURPLE . "/unmute" . TextFormat::YELLOW . ".",
        "UNMUTE_RECEIVE_ALL_CHAT" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Du wirst nun wieder alle Nachrichten sehen.",
        "LBMSG_SPECIFY" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Bitte gebe einen Spieler an.",
        "LBMSG_INVALID" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Bitte gebe einen gültigen Spieler an.",
        "LBMSG_NO_PARTY" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Das Party-System wird auf diesem Server nicht benutzt.",
        "REGISTER_ALREADY_REGISTERED" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Dieser Account wurde schon registriert.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Um deinen eigenen zu registrieren, änder deinen Benutzernamen, \n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "betrete den Server und benutze diesen Befehl erneut.",
        "REGISTER_PASSWORD_TOO_SHORT" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Dein Passwort ist zu kurz." . TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Account nicht registriert.",
        "REGISTER_REGISTER_PASSWORD" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Du registerierst den folgenden Account: arg1\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Bitte schreibe dein gewünschtes Passwort in den Chat.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Es wird anderen Spielern nicht angezeigt. Du musst das\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Passwort vor jedem Spiel eingeben.",
        "REGISTER_REGISTER_EMAIL" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Du registrierst den folgenden Account: arg1\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Mit dem Passwort: arg2\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Bitte gebe nun deine E-Mail in den Chat ein.",
        "LOGIN_ALREADY_LOGGED_IN" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du bist schon angemeldet.",
        "LOGIN_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /login <passwort>",
        "PARTY_HELP" => TextFormat::DARK_BLUE . "- " . TextFormat::AQUA . TextFormat::BOLD . "Party System:\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "/party" . TextFormat::GOLD . " <spielername>" . TextFormat::WHITE . " | Schicke eine Party-Anfrage.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "/party" . TextFormat::GOLD . " accept <spielername>" . TextFormat::WHITE . " | Akzeptiere eine Party-Anfrage.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "/party" . TextFormat::GOLD . " leave" . TextFormat::WHITE . " | Verlasse eine Party.",
        "PARTY_ACCEPT" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Betrete arg1's Party...",
        "PARTY_ERROR" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Das Party-System wird auf diesem Server nicht benutzt.",
        "PARTY_LEAVE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Verlasse Party...",
        "PARTY_CREATE" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Erstelle Party...\n" . TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Sende Party-Anfrage an arg1...",
        "PARTY_INVITE" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Sende Party-Anfrage an arg1...",
        "PARTY_INVITE_NO_PERMISSION" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Nur Party-Inhaber können Spieler hinzufügen.",
        "CHAT_GLOBAL" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Zum globalen Chat gewechselt.",
        "CHAT_PARTY" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Zum Party-Chat gewechselt.",
        "CHAT_NO_PARTY" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du bist in keiner Party!",
        "PCHAT_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /pchat <nachricht>",
        "IGNORE_ADD" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "arg1 wird nun ignoriert.",
        "IGNORE_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /ignore <spielername>",
        "UNIGNORE_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /unignore <spielername>",
        "UNIGNORE_REMOVE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "arg1 wird nun nicht mehr ignoriert.",
        "UNIGNORE_ERROR" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "arg1 wird nicht ignoriert.",
        "BLOCK_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /block <spielername>",
        "NOPE" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Dieser Befehl ist auf diesem Server nicht verfügbar.",
        "PAY_INVALID" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Bitte gebe eine gültige Anzahl an Punkten an.",
        "PAY_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /pay <spielername> <anzahl>",
        "FRIEND_HELP" => TextFormat::AQUA . TextFormat::BOLD . "Freunde-System:\n" . TextFormat::YELLOW . "/friend" . TextFormat::GOLD . " list" . TextFormat::WHITE . " | Zeigt eine Liste deiner Freunde.\n" . TextFormat::YELLOW . "/friend" . TextFormat::GOLD . " <spielername>" . TextFormat::WHITE . " | Schickt eine Freundschaftsanfrage.\n" . TextFormat::YELLOW . "/friend" . TextFormat::GOLD . " accept <spielername>" . TextFormat::WHITE . " | Akzeptiert eine Freundschaftsanfrage.\n" . TextFormat::YELLOW . "/friend" . TextFormat::GOLD . " deny <spielername>" . TextFormat::WHITE . " | Lehnt eine Freundschaftsanfrage ab.\n" . TextFormat::YELLOW . "/friend" . TextFormat::GOLD . " remove <spielername>" . TextFormat::WHITE . " | Entferne einen Freund von deiner Liste.",
        "CHANGEPW_CHANGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Ändere das Passwort für: arg1\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Bitte schreibe dein jetziges Passwort in den Chat.",
        "CHANGEPW_ERROR" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du bist schon dabei, dein Passwort zu ändern.",
        "LANG_CHANGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Sprache auf Deutsch geändert.",
        "LANG_USAGE" => TextFormat::GRAY . "Benutze: /lang <en|es|du|de>",
        "RANK_SHOW" => TextFormat::GRAY . "Dein Tag wird nun angezeigt.",
        "RANK_HIDE" => TextFormat::GRAY . "Dein Tag wird nun versteckt.",
        "STATS_IS_EMPTY" => TextFormat::RED . "Tut uns Leid, es konnten keine Statistiken für diesen Spieler gefunden werden.",
        "REPLY_USAGE" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze: /reply <nachricht>",
        "REPLY_TO_NOBODY" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Dir hat noch kein Spieler geschrieben.",
        "YOU_IGNORED" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Du kannst keine Nachrichten an arg1 senden.",
        "TELL_USAGE" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Benutze /tell <spieler> <nachricht>",
        "ME_USAGE" => TextFormat::DARK_PURPLE . "- " . TextFormat::RED . "Benutze: /me <aktion...>",
        "ONLY_FOR_VIP" => TextFormat::RED . "Diese Aktion ist nur für VIP Spieler verfügbar.\n" . TextFormat::RED . "Du kannst den VIP Rang in der Lifeboat+ App kaufen.",
        "WON_KIT" => TextFormat::DARK_AQUA . "Dir wurde zufällig das Kit " . TextFormat::DARK_PURPLE . "arg1" . TextFormat::DARK_AQUA . " gegeben.",
        "VIP_CHANGE_KIT" => TextFormat::DARK_AQUA . "VIP's können sich ein neues Kit aussuchen.",
        "HAVE_KIT" => TextFormat::RED . "Du hast dieses Kit schon ausgewählt.",
        "VIP_SELECT_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Du hast das Kit " . TextFormat::DARK_PURPLE . "arg1" . TextFormat::GREEN . " ausgewählt.",
        "TAP_TO_SELECT_KIT" => TextFormat::AQUA . "Tippe noch einmal auf das Schild, um das Kit auszuwählen.",
        "GOT_SAVED_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Dein letztes Kit (" . TextFormat::GOLD . "arg1" . TextFormat::GREEN . ") wurde wiederhergestellt.",
        "NO_KITS_FOUND" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Tut uns Leid, es wurden keine verfügbaren Kits gefunden.",
        "UNKNOWN_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Dieses Kit existiert nicht.",
        "CHOOSE_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Benutze: /kits <info> <kitname>",
        "KITS_HELP" => TextFormat::AQUA . TextFormat::BOLD . "Kits-System:\n" . TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " list" . TextFormat::WHITE . " | Zeigt eine Liste der verfügbaren Kits.\n" . TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " <kit name>" . TextFormat::WHITE . " | Wähle ein Kit aus.\n" . TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " info <kit name>" . TextFormat::WHITE . " | Zeigt die Kit-Beschreibung an.",
        "GOT_FOOD" => TextFormat::YELLOW . "Du hast arg1 arg2 " . TextFormat::YELLOW . "bekommen.",
        "GOT_ENOUGH_FOOD" => TextFormat::RED . "Du hast noch genug arg1" . TextFormat::RED . "!",
        "MAGIC_DRINK_WARNING" => TextFormat::RED . "Bist du schon 18? Wir sind uns nicht sicher..",
        "ENOUGH_CURE" => TextFormat::RED . "Du hast genug Elixier der Vitalität für heute. Geh ein bisschen spielen und hab Spaß!",
        "COFFEE_EFFECT" => TextFormat::GREEN . "Cool! Du bist ausgeruht und wieder voller Stärke.",
        "VIP_LOUNGE_ERROR" => ["Sorry, aber, nur VIP!", " Nur VIPs ist es erlaubt hier zu sein.", " Mein Chef sagte mir, ich sollte Penner abweisen. Bist du ein Penner?", " Das ist eine respektable Einrichtung. Nur VIPs.", " Die VIP-Lounge ist für VIPs.", " Bist du VIP?", " Das ist die VIP-Lounge.", " Du musst jemanden kennen, um hier rein zu kommen, oder VIP sein.", " Die VIP-Lounge ist nur für VIPs.", " Du kommst hier ohne VIP nicht rein.", " Ich bin größer als du und du bist kein VIP.", " Das hier ist die VIP-Lounge."],
        "PROFILE_NOT_FOUND" => TextFormat::RED . "Dein Spieler-Profil konnte nicht gefunden werden.",
        "PLAYER_NOT_EXIST" => TextFormat::RED . "Ein Spieler mit diesem Namen konnte nicht gefunden werden.",
        "TRYING_ADD_YOURSELF" => TextFormat::RED . "Tut uns Leid, aber imaginäre Freunde zählen nicht. Du kannst dich nicht selber als Freund hinzufügen.",
        "INVALID_REQUEST_DATA" => TextFormat::RED . "Du hast von diesem Spieler keine Freundschaftsanfrage.",
        "INCORRECT_HASH" => TextFormat::RED . "Dein Account konnte nicht authentifiziert werden.",
        "KNOWN_ERROR_PREFIX" => TextFormat::RED . "Ein Fehler ist aufgetreten:",
        "UNKNOWN_ERROR" => TextFormat::RED . "Ein unbekannter Fehler ist aufgetreten.",
        "FRIEND_ACCEPTED" => TextFormat::YELLOW . "Freundschaftsanfrage von arg1 angenommen.",
        "FRIEND_DENIED" => TextFormat::YELLOW . "Freundschaftsanfrage von arg1 abgelehnt.",
        "FRIEND_LIST_REQUEST" => TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Du hast arg1 Freundschaftsanfragen.\n" . TextFormat::DARK_BLUE . "- " . TextFormat::YELLOW . "Benutze /friend list um sie anzuzeigen.",
        "YOUR_FRIENDS" => TextFormat::BOLD . TextFormat::YELLOW . "Deine Freunde: ",
        "YOU_HAVE_NO_FRIENDS" => TextFormat::GRAY . "Du hast noch keine Freunde. Benutze /friend um Freundschaftsanfragen zu senden.",
        "YOUR_REQUESTS" => TextFormat::BOLD . TextFormat::YELLOW . "Deine Freundschaftsanfragen:",
        "YOU_HAVE_NO_REQUESTS" => TextFormat::GRAY . "Du hast keine Freundschaftsanfragen.",
        "FRIEND_REMOVED" => TextFormat::YELLOW . "arg1 wurde als Freund entfernt.",
        "FRIEND_REQUEST_SENT" => TextFormat::YELLOW . "Freundschaftsanfrage an arg1 gesendet.",
        "ALREADY_FRIENDS" => TextFormat::RED . "Ihr seid schon Freunde.",
        "DUPLICATE_REQUEST" => TextFormat::RED . "Du hast von diesem Spieler schon eine Freundschaftsanfrage.",
        "TOO_MANY_FRIENDS" => TextFormat::RED . "Du hast zu viele Freunde.",
        "MUST_UPDATE_PASS" => TextFormat::RED . "Sicherheits-Update.\n" . TextFormat::DARK_PURPLE . "Entschuldigung für die Unannehmlichkeiten, aber du musst dein Passwort ändern.",
        "PARTICLE_FOR_VIP" => "Sorry, Partikeleffekte sind für VIPs reserviert.",
        "PARTICLES" => TextFormat::BOLD . "arg1" . TextFormat::GREEN . " Partikel.",
        "CANT_TELEPORT_IN_DEATHMATCH" => TextFormat::RED . "Du kannst den Teleporter im Deathmatch nicht benutzen.",
        "CANT_PLACE_BLOCK" => TextFormat::RED . "Du kannst hier keine Blöcke platzieren.",
        "GIVE_USAGE" => Translate::PREFIX_PLAYER_ACTION . "Benutze: /give <item_name | item_id>",
        "WARN_USAGE" => TextFormat::RED . "Benutze: /warn <spielername>",
        "WARNING_BEFORE_MUTE" => Translate::PREFIX_ACTION_FAILED . "ACHTUNG: Wir haben bei dir ein unangemessenes Verhalten erkannt. Falls du nicht aufhörst, wirst du gemutet.",
        "WARN_NO_TARGET" => TextFormat::RED . "Der Spieler arg1 wurde nicht gefunden.",
        "PET_ONLY_LOBBY" => TextFormat::GRAY . "Du kannst nur ein Haustier haben.",
        "PET_WELCOME" => array(
			"Willkommen zurück!",
			"Ich habe dich vermisst.",
			"Ich bin so froh, hier zu sein.",
			"Danke, dass du mich hergerufen hast!",
			"Wir sehen uns wieder...",
			"Hi.  Was gibt's?",
			"Hallo, wie geht's dir so?",
			"Hallo, Herrchen!",
			"Howdy!",
			"Hey!",
			"Guten Tag, mein Freund.",
			"Was machen wir heute?",
			"Morgen, Herrchen! Zu ihren Diensten!",
			"Ist ein bisschen her, bin froh dich zu sehen!",
			"Was geht?!",
			"Ich freue mich, dich wieder zu haben, Herrchen!",
			"Es ist Zeit, dich wieder zu sehen!",
			"Ich bin froh, mit dir zu arbeiten.",
			"Ich bin froh, dich wieder zu haben!",
			"Ich bin glücklich, dass ich dich begleiten darf!",
			"Ich bin froh, dass du mich gebrauchen kannst.",
			"Ich bin froh, mit dir zu arbeiten, Herrchen."
		),
		"PET_BYE" => array(
			"Okay, bye",
			"Naja, dann such ich mir jemand anderes.",
			"/human....../human.... geht nicht.",
			"Oink.  Oder moo.  Oder was auch immer ich sagen soll.",
			"Naja, ich wollte mir eh was zu Essen holen.",
			"Chao! (Ich bin italienisch)",
			"Bis dann!",
			"Ich glaube ich sehe ein Eichhörnchen, mit dem ich spielen kann.",
			"War lustig, danke!",
			"Zeit für ein Nickerchen!",
			"Zeit zu essen.",
			"Tschüss."
		),
		"PET_OWNER_DEAD" => array(
			"Da arg1 dich getötet hat... soll ich ihm ins Bein beißen?",
			"Ich mag arg1 nicht.",
			"Wie arg1 er wäre es.  Nächstes mal bekommen wir ihn.",
			"Pech gehabt, Meister.",
			"Ich wette arg1 hat kein Haustier!",
			"Oh, mist, ich hab gedacht den bekommst du.",
			"Gewinne paar, verliere paar.",
			"Ich habe keine Ahnung, wie eine Legende wie du sterben konnte.",
			"Du hast dich nur aufgewärmt.",
			"Sei nicht traurig, das war nur das Aufwärmen.",
			"arg1 wünscht sich, er hätte so ein cooles Haustier wie mich!"
		),
		"PET_OWNER_SOON_BACK" => array(
			"Schon wieder da?",
			"Das ging schnell!!",
			"Ich wusste, du würdest nicht so lange weg sein.",
			"Ich habe geblinzelt und du warst schon wieder da.",
			"Hast du was vergessen?"
		),
		"PET_OWNER_LONG_BACK" => array(
			"Du warst Ewigkeiten weg!",
			"Warum warst du so lange weg??",
			"Can you just leave me like that!",
			"Das war 'ne halbe Ewigkeit!",
			"Endlich bist du wieder da.",
			"Bitte verlass mich nicht nochmal!",
			"Ich hab dich vermisst!",
			"Ich habe schon gedacht du kommst gar nicht mehr zurück...",
			"Paar Leute brauchen auch mal eine Pause von Lifeboat.. manchmal zumindest",
			"It's been a long daaay, without you my friend.",
			"Das hat sich wie eine Ewigkeit angefühlt!",
			"Was erwartest du von mir, wie lange ich warte?",
			"Sei nächstes Mal nicht so lange weg!",
			"Ich bin schon hungrig geworden."
		),
		"PET_OWNER_RETURN" => array(
			"Willkommen zurück!",
			"Hallo nochmal!",
			"So schön, dich wieder zu sehen",
			"Oh wie viel Spaß wir haben, oder?!",
			"Hallo Hallo Hallo!",
			"Schön dich wieder zu sehen, Herrchen!",
			"Hail, Herrchen, was ein Zufall!",
			"Hast du mich vermisst?",
			"Ich habe dich vermisst.",
			"Da sind wir wieder in der Lobby.",
			"Sollen wir in die VIP Lounge?  Ich habe gehört die haben Leckerchen",
			"Und, wie war das Spiel?",
			"Hast du paar Kisten geöffnet?",
			"Wie komisch.. Du hast auf ein Schild geklickt und warst weg!",
			"Hmm, was ist hinter der Schilderwand?"
		),
		"PET_LOBBY_RANDOM" => array(
			"Hast du was zu essen?",
			"Sollen wir in dein Match?",
			"Schönes Wetter haben wir.",
			"Ist das ein Eichhörnchen da hinten?",
			"Na los, ich will ein Spiel spielen!",
			"Schlaf nicht ein, es ist Zeit zu spielen!",
			"What does the fox say?",
			"Irgendwelche guten Filme gesehen?"
		),
		"PET_CHAT_FILTER" => array(
			"Soll ich auch so reden?",
			"Das Wort kenne ich nicht.",
			"Die Meinungen meines Herrchen sind nicht immer die gleichen wie meine.",
			"Ich habe sowas auch schonmal gesagt, aber meine Mama hat gesagt ich darf das nicht.",
			"Es gibt 'ne Menge andere, gute Wörter da draußen.",
			"Naja, das war nicht gerade nett.",
			"Ich fühle mich auch manchmal so, aber ich haöte mich zurück.",
			"Lass die Wut raus."
		),
		"PET_OWNER_WINS" => array(
			"Du hast gewonnen!  Wow!  Ich kanns gar nicht glauben!",
			"Glückwunsch, Herrchen!",
			"Ein weiterer Sieg!",
			"Gut gemacht!",
			"Das war genial! Wollen wir noch eine Runde spielen?",
			"Wow, du sahst aus da draußen wie Katniss!"
		),
        "HACKER_USAGE" => Translate::PREFIX_PLAYER_ACTION . "Benutze: /hacker <report>",
        "REPORT_SEND" => "Danke für deinen Report!",
        "STATUE_DIRECT_CONNECT" => TextFormat::RED . "Bitte joine auf " . TextFormat::GREEN . "arg1" . TextFormat::RED . ".",
        "STATUE_WALLS" => TextFormat::RED . "Tut uns Leid, aber dieser Spielmodus ist momentan offline.",
        "STATUE_COMING_SOON" => TextFormat::YELLOW . "Dieser Spielmodus ist noch nicht fertig, du Dummkopf.",
        "TELL_ONESELF" => Translate::PREFIX_ACTION_FAILED . "Du kannst dich nicht selber anschreiben.",
        "PLAYER_NOT_ONLINE" => Translate::PREFIX_ACTION_FAILED . "Es ist kein Spieler mit diesem Namen online.",
		"CLASSIC_SG_VIP_KIT" => "Diamantaxt, Lederkappe, Kettenhemd, Kettenhose, Kettenstiefel",
		"MIDAS_KIT" => "Goldhelm, Goldharnisch, Goldbeinschutz und Goldstiefel",
		"ARCHER_KIT" => "1 Bogen, 16 Pfeile",
		"TELEPORTER_KIT" => "1 werfbares Ei pro Minute, teleportiert dich dort hin, wo es aufprallt",
		"BRAWLER_KIT" => "Gib mehr Rückstoß, bekomme weniger.",
		"ATHLETE_KIT" => "Sprungkraft I",
		"PROSPECTOR_KIT" => "Diamantspitzhacke, Eisenhelm, extra Items in Kisten",
		"CREEPER_KIT" => "Werfbares TNT (1x per 90s)",
		"TANK_KIT" => "Diamantbrustpanzer",
		"ASSASSIN_KIT" => "Eisenschwert",
		"PARTICLES_OFF" => "Partikel aus",
		"BOUNCER_NPC" => "Türsteher[NPC]",
		"PET_NPS" => "[NPC] Dein Haustier",
		"BAD_USERNAME" => TextFormat::RED . "Dieser Benutzername entspricht nicht der Lifeboat Regeln. Bitte benutze einen anderen.",
		"SAME_PASS" => TextFormat::RED . "Dein jetziges Passwort ist das gleiche. Bitte benutze verschiedene Passwörter.",
    );

}
