<?php
namespace LbCore\language\core;

use pocketmine\utils\TextFormat;
use LbCore\language\Translate;

class Spanish {
	
	public $translates = array(
			
		/* Errors */
		"ERR_PLAYER_NOT_FOUND" => TextFormat::RED."Jugador no encontrado.",

		/* Economy */
		"YOUR_COINS" => TextFormat::GOLD."Mis monedas: " . TextFormat::WHITE . "arg1",
		"PLAYER_HAS_COINS" => TextFormat::WHITE."arg1 ".	TextFormat::DARK_AQUA."tiene ".
							  TextFormat::WHITE."arg2 ".TextFormat::DARK_AQUA."monedas.",
		"SENT_COINS" => TextFormat::DARK_AQUA."Enviadas ".TextFormat::WHITE."arg1 ".
						TextFormat::DARK_AQUA."monedas a ".TextFormat::WHITE."arg2".TextFormat::DARK_AQUA.".",
		"ERR_INSUFFICENT_COINS" => TextFormat::RED."No tienes suficientes monedas.",
		"NOT_REGISTERED" => TextFormat::RED."Registrarse para hacer esto",

		/* Login & Registration */
		"WELCOME_MESSAGE_REGISTERED" => TextFormat::DARK_AQUA."¡Bienvenido a ".
										TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!\n".
										TextFormat::YELLOW."Esta cuenta está registrada, escribe la\n".
										TextFormat::YELLOW."contraseña o cambia de nombre.",
		"PASSWORD_CHANGED" => TextFormat::GREEN."Contraseña cambiada.",
		"NEEDS_LOGIN" => TextFormat::RED."Por favor, accede primero.",
		"ACCOUNT_LOCKED" => TextFormat::RED."Cuenta Bloqueada",
		"WELCOME_MESSAGE_UNREGISTERED" => TextFormat::DARK_AQUA."Bienvenido a ".
										  TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!\n".
										  TextFormat::YELLOW."Esta cuenta no está registrada, puedes\n".
										  TextFormat::YELLOW."registrarla con ".TextFormat::LIGHT_PURPLE."/register".TextFormat::YELLOW.".",
		"REGISTRATION_SUCCESS" => TextFormat::GREEN."Registro completado.",
		"ON_LOGIN" => TextFormat::GREEN."Accesso correcto.",
		"CONFIRM_PASS" => TextFormat::YELLOW."Por favor, escribe la nueva contraseña de nuevo.",
		"SHORT_PASS" => TextFormat::RED."Por favor, escoge una contraseña mas larga.",
		"PASS_NOT_MATCH" => TextFormat::RED."Las contraseñas no coinciden, prueba de nuevo.",
		"FINISH_REGISTRATION" => TextFormat::YELLOW."Estas registrando la cuenta: ".TextFormat::WHITE."arg1\n".
								 TextFormat::YELLOW."Con la contraseña: ".TextFormat::WHITE."arg2\n".
								 TextFormat::YELLOW."Por favor, escribe tu correo electrónico para acabar el registro.",
		"NEW_PASS" => TextFormat::YELLOW."Por favor, escribe la nueva contraseña deseada.",
		"INVALID_EMAIL" => TextFormat::YELLOW."Por favor, escriba un correo electrónico valido.",
		"INCORRECT_PASSWORD" => TextFormat::DARK_AQUA."Esta cuenta está registrada, y la contraseña\n".
								TextFormat::DARK_AQUA."no es válida. Si no conoces la contraseña,\n".
								TextFormat::DARK_AQUA."escoge un nombre diferente.",
		"SESSION_IN_USE" => TextFormat::DARK_AQUA."Están usando tu cuenta en otro lugar, por eso te sacamos del sistema.",
		"DEFAULT_USER" => TextFormat::LIGHT_PURPLE."Estas usando el nombre predeterminado de MCPE.\n".
						  TextFormat::LIGHT_PURPLE."Por favor cámbielo en la página de configuración.",
		"WAITING_FOR_LOGIN" => TextFormat::LIGHT_PURPLE."Esperando información de inicio de sesión de esta cuenta...",
		"VIP_GIVAWAY_WINNER" => TextFormat::DARK_PURPLE."Has ganado VIP + gratis para esta sesión!\\o/\n".
								TextFormat::DARK_PURPLE."Por favor Iniciar sesión.",
		"REGISTRATION_FAILED" => TextFormat::RED."Error en el registro, por favor, inténtelo de nuevo más tarde.",
		"LOGIN_DB_ERROR" => TextFormat::BOLD.TextFormat::DARK_GRAY."." .
			TextFormat::DARK_AQUA."Bienvenido a ".TextFormat::AQUA."Life".TextFormat::RED."boat".TextFormat::DARK_AQUA."!" .
			TextFormat::RED."Hubo un error con la base de datos. Si usted esta registrado o es usuario VIP+, por favor salga y vuelva a entrar para obtener sus ventajas." .
			TextFormat::BOLD.TextFormat::DARK_GRAY.".",

		/* Statistics */
		"STATISTIC_HEADER" => TextFormat::DARK_AQUA."Muestra estadísticas para: ".TextFormat::WHITE."arg1",
		"KILL_DEATH_COUNTS" =>  TextFormat::DARK_AQUA."Asesinatos: ".TextFormat::WHITE."arg1 ".
								TextFormat::DARK_AQUA."Muertes: ".TextFormat::WHITE."arg2",
		"LAST_SEEN" => TextFormat::DARK_AQUA."Visto por última vez jugando en: ".TextFormat::WHITE,

		/* Antihacks/Maitnence/Server QoS */
		"IDLE_TIMEOUT" => TextFormat::YELLOW."Has estado inactivo en el servidor por demasiado tiempo.",
		"IP_LIMITED" => TextFormat::YELLOW."Demasiados jugadores están usando esta IP, prueba de nuevo después.",
		"ALREADY_AUTHENTICATED" => TextFormat::YELLOW."Ya has accedido correctamente en este servidor.",
		"MOD_WARNING" => TextFormat::RED."Desactiva los mods, no son permitidos en LBSG.",
		"LOW_PLAYER_COUNT" => TextFormat::LIGHT_PURPLE."El servidor esta teniendo dificultades.",
		"ABOUT_TO_RESTART" => TextFormat::DARK_AQUA."El servidor se reiniciará en arg1 segundosarg2...",
		"RESTARTING" => TextFormat::DARK_AQUA."El servidor se está reiniciando...",

		/* Ranks */
		"VIP_ENABLED" => TextFormat::YELLOW."VIP ".TextFormat::GREEN."esta activado".TextFormat::YELLOW.".",
		"VIP_DISABLED" => TextFormat::YELLOW."VIP ".TextFormat::RED."no está activado".TextFormat::YELLOW.".",
		"VIP_USAGE" => TextFormat::YELLOW."Utilice /vip para volver a habilitar",
		"RANKS_RETRIEVING_START" => TextFormat::LIGHT_PURPLE."Obteniendo estadisticas de la base de datos.",
		"RANKS_RETRIEVING_ERROR" => TextFormat::YELLOW."Ocurrio un error intentando obtener las estadisticas de la base de datos, intentando de nuevo",

		/* Chat Filter */
		"CANNOT_SAY_PASSWORD" => TextFormat::RED."Mensaje bloqueado. ".
								 TextFormat::YELLOW."(Revelar tu contraseña\n".
								 TextFormat::YELLOW."es una mala idea y puede desactivar tu cuenta).",
		"MSG_INAPPROPRIATE" => TextFormat::RED."Mensaje bloqueado por filtro del chat.",
		"NO_DATING" => TextFormat::RED."Por favor no usen LBSG como servicio de buscar parejas.",
		"NO_ADVERTISING" => TextFormat::RED."Por favor no pongan propaganda.",
		"CIRCUMVENT_WARNING" => TextFormat::RED."No traten de engañar al filtro del chat.",
		"CHAT_WHEN_MUTED" => Translate::PREFIX_PLAYER_ACTION.
							 TextFormat::LIGHT_PURPLE."No puedes mandar mensajes de chat mientras estas mudo.\n".
							 Translate::PREFIX_PLAYER_ACTION.
							 TextFormat::LIGHT_PURPLE."Use /unmute para poder usar el chat otra vez.",
		"COMMAND_WHEN_MUTED" => TextFormat::RED."Usted no puede utilizar los comandos mientras se silencia.\n".
								TextFormat::RED."Use /unmute para usar los comandos de nuevo.",
		"RATE_LIMITED" => TextFormat::RED."¡Despacio, no chatees tan rápido!",
		"MSG_SHORT" => TextFormat::RED."Por favor escriba un mensaje más largo.",
		"MSG_REPEATED" => TextFormat::RED."Por favor espera antes de repetirte.",

		/* Commands */
		"KITS_HEADER" => TextFormat::YELLOW."Kits disponibles (página arg1 de arg2):",
		"HELP_TOO_FAR" => TextFormat::GRAY . "Esta pagina no existe.",


		"CMD_DEFAULT_ACCOUNT" => Translate::PREFIX_ACTION_FAILED."No puedes hacer eso en esta cuenta.",
		"CMD_REQUIRE_LOGIN" => Translate::PREFIX_ACTION_FAILED."Debes ingresar o registrarte primero.",

		"MUTE_NO_MORE_CHAT" =>  Translate::PREFIX_PLAYER_ACTION."No recibiras mas mensajes de chat de jugadores.\n" . 
								Translate::PREFIX_PLAYER_ACTION."Para habilitarlos de nuevo, ejecuta ".
								TextFormat::LIGHT_PURPLE."/unmute".TextFormat::YELLOW.".",
		"UNMUTE_RECEIVE_ALL_CHAT" => Translate::PREFIX_PLAYER_ACTION."Recibiras todos los mensajes de chat de nuevo.",
		"LBMSG_SPECIFY" => Translate::PREFIX_ACTION_FAILED."Por favor indique un jugador.",
		"LBMSG_INVALID" => Translate::PREFIX_ACTION_FAILED."Por favor escoja un jugador valido para enviar el mensaje.",
		"LBMSG_NO_PARTY" => Translate::PREFIX_PLAYER_ACTION."El sistema de partidos no esta ejecutandose en este servidor.",
		"REGISTER_ALREADY_REGISTERED" => Translate::PREFIX_PLAYER_ACTION."Esta cuenta ya fue registrada.\n" . 
										 Translate::PREFIX_PLAYER_ACTION."Para registrarte, cambia tu nombre de usuario en MCPE, \n" .
										 Translate::PREFIX_PLAYER_ACTION."ingresa al servidor y ejecuta este comando nuevamente.",
		"REGISTER_PASSWORD_TOO_SHORT" => Translate::PREFIX_ACTION_FAILED."Tu contraseña es demasiado corta." . 
										 Translate::PREFIX_ACTION_FAILED."Cuenta no registrada.",
		"REGISTER_REGISTER_PASSWORD" => Translate::PREFIX_PLAYER_ACTION."Estas registrando la cuenta: arg1\n" . 
										Translate::PREFIX_PLAYER_ACTION."Por favor ingresa la contraseña que quieres usar en el chat.\n" . 
										Translate::PREFIX_PLAYER_ACTION."No se mostrará a otros jugadores. Deberas\n" . 
										Translate::PREFIX_PLAYER_ACTION."ingresar esta contraseña antes de cada juego.",
		"REGISTER_REGISTER_EMAIL" => Translate::PREFIX_PLAYER_ACTION."Estas registrando la cuenta: arg1\n".
									 Translate::PREFIX_PLAYER_ACTION."Con la contraseña: arg2\n".
									 Translate::PREFIX_PLAYER_ACTION."Por favor ingresa tu direccion de correo electronico para completar la registracion.",
		"LOGIN_ALREADY_LOGGED_IN" => Translate::PREFIX_ACTION_FAILED."Ya has iniciado sesion.",
		"LOGIN_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /login <password>",
		"PARTY_HELP" => Translate::PREFIX_PLAYER_ACTION.
						TextFormat::AQUA.TextFormat::BOLD."Sistema de partidos:\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." <player name>".TextFormat::WHITE." | Envia una invitacion a un partido.\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." accept <player name>".TextFormat::WHITE." | Acepta una invitacion a un partido.\n".
						Translate::PREFIX_PLAYER_ACTION.TextFormat::YELLOW."/party".
						TextFormat::GOLD." leave".TextFormat::WHITE." | Abandona un partido.",
		"PARTY_ACCEPT" => Translate::PREFIX_PLAYER_ACTION . TextFormat::GREEN . "Uniendose al partido de arg1...",
		"PARTY_ERROR" => Translate::PREFIX_PLAYER_ACTION."El sistema de partidos no se esta ejecutando en este servidor.",
		"PARTY_LEAVE" => Translate::PREFIX_PLAYER_ACTION."Abandonando el partido...",
		"PARTY_CREATE" => Translate::PREFIX_PLAYER_ACTION.
						  TextFormat::GREEN."Creando el partido...\n" . 
						  Translate::PREFIX_PLAYER_ACTION.
						  TextFormat::GREEN."Sending party invite to arg1...",
		"PARTY_INVITE" => Translate::PREFIX_PLAYER_ACTION.TextFormat::GREEN."Enviando la invitacion al partido para arg1...",
		"PARTY_INVITE_NO_PERMISSION" => Translate::PREFIX_ACTION_FAILED."Solo los lideres del partido pueden agregar jugadores.",
		"CHAT_GLOBAL" => Translate::PREFIX_PLAYER_ACTION."Cambiado al chat global.",
		"CHAT_PARTY" => Translate::PREFIX_PLAYER_ACTION."Cambiado al chat del partido.",
		"CHAT_NO_PARTY" => Translate::PREFIX_ACTION_FAILED."No estas en un partido!",
		"PCHAT_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /pchat <message>",
		"IGNORE_ADD" => Translate::PREFIX_PLAYER_ACTION."arg1 agregado a la lista a ignorar",
		"IGNORE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /ignore <player name>",
		"UNIGNORE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /unignore <player name>",
		"UNIGNORE_REMOVE" => Translate::PREFIX_PLAYER_ACTION."arg1 quitado de la lista a ignorar",
		"UNIGNORE_ERROR" => Translate::PREFIX_ACTION_FAILED."arg1 no esta en la lista a ignorar",
		"BLOCK_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /block <player name>",
		"NOPE" => Translate::PREFIX_ACTION_FAILED."Ese comando no esta disponible en este servidor.",
		"PAY_INVALID" => Translate::PREFIX_ACTION_FAILED."Por favor ingrese un numero valido de monedas a enviar.",
		"PAY_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /pay <username> <amount>",
		"FRIEND_HELP" => TextFormat::AQUA.TextFormat::BOLD."Sistema de amigos:\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." list".
						 TextFormat::WHITE." | Muestra la lista de tus amigos.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." <player name>".
						 TextFormat::WHITE." | Envia una solicitud de amistad.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." accept <player name>".
						 TextFormat::WHITE." | Acepta una solicitud de amistad.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." deny <player name>".
						 TextFormat::WHITE." | Rechaza una solicitud de amistad.\n".
						 TextFormat::YELLOW."/friend".TextFormat::GOLD." remove <player name>".
						 TextFormat::WHITE." | Quita a un jugador de tu lista de amigos.",
		"CHANGEPW_CHANGE" => Translate::PREFIX_PLAYER_ACTION."Cambiando contraseña a: arg1\n".
							 Translate::PREFIX_PLAYER_ACTION."Por favor ingrese su contraseña actual.",
		"CHANGEPW_ERROR" => Translate::PREFIX_ACTION_FAILED."Ya estas cambiando tu contraseña.",
		"LANG_CHANGE" => Translate::PREFIX_PLAYER_ACTION."Lenguaje puesto en español.",
		"LANG_USAGE" => TextFormat::GRAY . "Uso: /lang <en|es|du|de>",
		"RANK_SHOW" => TextFormat::GRAY . "Tu etiqueta esta mostrada",
		"RANK_HIDE" => TextFormat::GRAY . "Yu etiqueta esta ocultada",
		"STATS_IS_EMPTY" => TextFormat::RED . "Disculpe, no se encontraron estadisticas para este jugador",
		"REPLY_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso /reply <message>",
		"REPLY_TO_NOBODY" => Translate::PREFIX_ACTION_FAILED."No puede responder a nadie",
		"YOU_IGNORED" => Translate::PREFIX_ACTION_FAILED."No se pueden enviar mensajes a arg1",
		"TELL_USAGE" => Translate::PREFIX_ACTION_FAILED."Uso /tell <player> <message>",
		"ME_USAGE" => Translate::PREFIX_ACTION_FAILED."Uso: /me <action...>",
		"GIVE_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /give <item_name | item_id>",
		"WARN_USAGE" => TextFormat::RED . "Uso: /warn <player_name>",
		"WARNING_BEFORE_MUTE" => Translate::PREFIX_ACTION_FAILED."ADVERTENCIA: Hemos detectado un comportamiento inapropiado. Por favor desista o se lo silenciará",
		"WARN_NO_TARGET" => TextFormat::RED . "Jugador con nombre arg1 no se encontró en línea",
		
		/*Kit logic*/
		"ONLY_FOR_VIP" => TextFormat::RED . "Esta acción sólo está disponible para jugadores VIP.\n" . 
						  TextFormat::RED . "Usted puede comprar el rango VIP en la aplicación LifeBoat.",
		"WON_KIT" => TextFormat::DARK_AQUA.'Se le ha dado el kit '
					. TextFormat::DARK_PURPLE . 'arg1',
		"VIP_CHANGE_KIT" => TextFormat::DARK_AQUA."VIP puede seleccionar un nuevo kit",
		"HAVE_KIT" => TextFormat::RED . "Ya tiene este kit.",
		"VIP_SELECT_KIT" => TextFormat::DARK_BLUE . "-" .
							TextFormat::GREEN . 'Ha seleccionado el kit ' .
							TextFormat::DARK_PURPLE . 'arg1',
		"TAP_TO_SELECT_KIT" => TextFormat::AQUA . 'Toca Iniciar sesión de nuevo para elegir el kit.',
		"GOT_SAVED_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::GREEN . "Tienes tu kit guardado " 
					. TextFormat::GOLD . 'arg1' . TextFormat::GREEN . " para hoy",
		"NO_KITS_FOUND" => TextFormat::DARK_BLUE . "- " 
					. TextFormat::RED . "Lo sentimos, no hay kits disponibles encontrados",
		"UNKNOWN_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Ese kit no existe",
		"CHOOSE_KIT" => TextFormat::DARK_BLUE . "- " . TextFormat::RED . "Uso: /kits <info> <kit name>",
		"KITS_HELP" => TextFormat::AQUA.TextFormat::BOLD."Sistema de kits:\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " list".
						TextFormat::WHITE . " | Mostrar una lista de kits disponibles.\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " <kit name>".
						TextFormat::WHITE . " | Aplicar un kit.\n".
						TextFormat::YELLOW . "/kits" . TextFormat::GOLD . " info <kit name>".
						TextFormat::WHITE . " | Mostrar una descripción del kit.",
		

		/*VIP Lounge*/
		"GOT_FOOD" => TextFormat::YELLOW . "Tienes arg1 arg2",
		"GOT_ENOUGH_FOOD" => TextFormat::RED . "Ya tiene suficientes arg1!",
		"MAGIC_DRINK_WARNING" => TextFormat::RED . "¿Tienes 21? No estamos seguros",
		"ENOUGH_CURE" => TextFormat::RED . "Tienes suficiente elixir de la vitalidad para hoy. Ve a los mapas y diviertete!",
		"COFFEE_EFFECT" => TextFormat::GREEN . "¡Guay! Usted está descansado y con fuerzas renovadas",
		
		"VIP_LOUNGE_ERROR" => array(		
			"Disculpa amigo, solo para usuarios VIP!",
			"Solo usuarios VIP estan permitidos aqui.",
			"Me dijeron que me cuide de la gente extraña. Eres tu de esa clase?",
			"Este es un establecimiento respetble. Solo usuarios VIP son admitidos.",
			"El area VIP es solo para usuarios VIP",
			"Eres tu un usuario VIP?",
			"Este es el area VIP.",
			"Tienes que conocer a alguien mas para estar aqui, o ser un usuario VIP",
			"El area VIP es solo para usuarios VIP.",
			"Tu no puedes entrar aqui si no eres un usuario VIP",
			"Soy mayor que ti y tu no eres un usuario VIP",
			"Esta es un area VIP"
		),
		
		/*Friend requests*/
		"PROFILE_NOT_FOUND" => TextFormat::RED."No pudimos encontrar tu perfil de jugador.",
		"PLAYER_NOT_EXIST" => TextFormat::RED."No existe un jugador con ese nombre.",
		"TRYING_ADD_YOURSELF" => TextFormat::RED."Disculpe, pero los amigos imaginarios no sirven. No puedes agregarte a ti mismo como amigo.",
		"INVALID_REQUEST_DATA" => TextFormat::RED."No tienes una solicitud enviada por ese jugador.",
		"INCORRECT_HASH" => TextFormat::RED."No se pudo autenticar tu cuenta.",
		"KNOWN_ERROR_PREFIX" => TextFormat::RED."Ha ocurrido un error:",
		"UNKNOWN_ERROR" => TextFormat::RED."Ha ocurrido un error desconocido.",
		"FRIEND_ACCEPTED" => TextFormat::YELLOW."Se acepto la solicitud de amistad de arg1.",
		"FRIEND_DENIED" => TextFormat::YELLOW."Se rechazo la solicitud de amistad de arg1.",
		"FRIEND_LIST_REQUEST" => Translate::PREFIX_PLAYER_ACTION . "Tienes arg1 solicitudes de amistad.\n" . 
								 Translate::PREFIX_PLAYER_ACTION . "Utilize /friend list para verlos.",
		"YOUR_FRIENDS" => TextFormat::BOLD.TextFormat::YELLOW."Tus amigos:",
		"YOU_HAVE_NO_FRIENDS" => TextFormat::GRAY . "No hay amigos para mostrar. Utilize /friend para invitar amigos.",
		"YOUR_REQUESTS" => TextFormat::BOLD.TextFormat::YELLOW."Tus solicitudes:",
		"YOU_HAVE_NO_REQUESTS" => TextFormat::GRAY . "No hay solicitudes para mostrar.",
		"FRIEND_REMOVED" => TextFormat::YELLOW . "Se ha quitado a arg1 como amigo.",
		"FRIEND_REQUEST_SENT" => TextFormat::YELLOW . "Se envio una solicitud de amistad a arg1.",
		"ALREADY_FRIENDS" => TextFormat::RED . "Ya eres amigo de este jugador.",
		"DUPLICATE_REQUEST" => TextFormat::RED . "Ya tienes una solicitud de amistad de este jugador.",
		"TOO_MANY_FRIENDS" => TextFormat::RED . "Alguien tiene demasiados amigos.",
		"MUST_UPDATE_PASS" => TextFormat::RED."Mejora nuestra seguridad.\n".TextFormat::DARK_PURPLE. "Sentimos los inconvenientes, pero debe cambiar su contraseña.",
                "PARTICLE_FOR_VIP" => "Disculpe, el uso de particulas solo esta permitido para usuarios VIP.",
                "PARTICLES" => TextFormat::BOLD . "arg1" . TextFormat::GREEN . " Partículas",
                "CANT_TELEPORT_IN_DEATHMATCH" => TextFormat::RED."No esta permitido usar Teletransportacion durante un partido mortal",
                "CANT_PLACE_BLOCK" => TextFormat::RED."No se pueden colocar bloques aquí",
		"PET_ONLY_LOBBY" => TextFormat::GRAY . "Es posible que tenga una mascota sólo en el vestíbulo.",
		"PET_WELCOME" => array(
			"Welcome back!",
			"I have missed you.",
			"I am so happy to be here.",
			"Thank you for summoning me!",
			"We meet again...",
			"Hi.  What up?",
			"Hello, how have you been?",
			"Hello, Master!",
			"Howdy!",
			"Hey!",
			"G'day mate.",
			"What will we do today?",
			"Greetings, Master!  I am at your service!",
			"It's been a while, happy to see you!",
			"Sup?!",
			"Glad to have you back, Master!",
			"It's about time I see you again!",
			"Happy to keep you company.",
			"Glad to have you back!",
			"I'm happy to be accompanying you!",
			"Happy to be of service.",
			"Happy to keep you company, Master."
		),
		"PET_BYE" => array(
			"Okay, bye.",
			"Well, I will just find someone else.",
			"/human....../human.... not working.",
			"Oink.  or moo.  or whatever I am supposed to say.",
			"Well I was going to go get a snack anyway.",
			"Chao! (i am Italian)",
			"See ya!",
			"I think I see a squirrel I can go play with.",
			"It's been fun, thanks!",
			"Nap time!",
			"Time to go get food.",
			"Goodbye."
		),
		"PET_OWNER_DEAD" => array(
			"About this arg1 that killed you... should I bite him in the leg?",
			"I do not like arg1.",
			"That arg1 thinks he's all that.  We'll get him next time.",
			"Tough break, Master.",
			"I bet arg1 does not have a pet!",
			"Well, snap, I thought you had that one.",
			"Win some, lose some.",
			"I don't know how a legend like you even died?",
			"You were just warming up the thumbs.",
			"Don't worry that was just warm up.",
			"arg1 wish he had a pet as cool as me!"
		),
		"PET_OWNER_SOON_BACK" => array(
			"Back so soon?",
			"That was quick!",
			"I knew you would not be gone long.",
			"I hardly blinked and you were back.",
			"Did you forget something?"
		),
		"PET_OWNER_LONG_BACK" => array(
			"You were gone for ages!",
			"What took you so long?",
			"Can you just leave me like that!",
			"That was ages!",
			"Finally, you are back.",
			"Please don't leave me again!",
			"I missed you!",
			"I thought you were never coming back...",
			"Some people even have to take breaks from Lifeboat... sometimes.",
			"It's been a long daaay, without you my friend.",
			"That felt like an eternity!",
			"Gadzooks, how long to you expect me to wait?",
			"Next time don't be gone so long!",
			"I was getting hungry."
		),
		"PET_OWNER_RETURN" => array(
			"Welcome back!",
			"Hello again!",
			"So good to see you again",
			"Oh what fun we are having, aren't we?!",
			"Hello Hello Hello!",
			"Well good to see you again, Master!",
			"Hail, Master, well met!",
			"Did you miss me?",
			"I missed you.",
			"Well here we are again in the lobby",
			"Should we go to the VIP lounge?  I heat they have treats.",
			"So how was the game?",
			"Did you open any chests?",
			"Weirdest thing. You tapped a sign, and disappeared!",
			"What is on the other side of the sign wall, anyway?"
		),
		"PET_LOBBY_RANDOM" => array(
			"Got any food?",
			"Should you get in a game?",
			"Splendid weather we are having.",
			"Is that a squirrel over there?",
			"Come on, I want to play a game!",
			"Don't fall asleep, it's time to play!",
			"What does the fox say?",
			"Seen any good movies lately?"
		),
		"PET_CHAT_FILTER" => array(
			"Do you worry I might start to talk like that?",
			"I did not know that word.",
			"The opinions of my owner are not necessarily my opinions.",
			"I said something like that once but my mom said I shouldn't",
			"There are a lot of other good, descriptive words out there.",
			"Well that wasn't very nice.",
			"I feel like that sometimes too but I'm told to keep it to myself.",
			"Let all the anger out."
		),
		"PET_OWNER_WINS" => array(
			"You won!  Wow!  I can't believe it you won!",
			"Congratulations, Master!",
			"Another victory!",
			"Good job!",
			"That was awesome! Can we do another one?",
			"Wow, you looked like Katniss out there!"
		),
		"HACKER_USAGE" => Translate::PREFIX_PLAYER_ACTION."Uso: /hacker <report>",
		"REPORT_SEND" => "El informe ha sido enviado",
		"STATUE_DIRECT_CONNECT" => TextFormat::RED . "Por favor conectate directamente a " . TextFormat::GREEN . "arg1",
		"STATUE_WALLS" => TextFormat::RED . "Disculpe, este modo de juego no está disponible por el momento.",
		"STATUE_COMING_SOON" => TextFormat::YELLOW . "Este modo de juego no está listo aun ingenuo.",
		"TELL_ONESELF" => Translate::PREFIX_ACTION_FAILED . "No puedes enviarte un mensaje a ti mismo.",
		"PLAYER_NOT_ONLINE" => Translate::PREFIX_ACTION_FAILED . "No hay ningun jugador con ese nombre conectado.",
		"CLASSIC_SG_VIP_KIT" => "Diamond Axe, Leather Cap, Chain Chestplate, Pants, Boots",
		"MIDAS_KIT" => "Gold Helmet, Chestplate, Leggings and Boots",
		"ARCHER_KIT" => "1 Bow, 16 Arrows",
		"TELEPORTER_KIT" => "1 Throwable Egg every minute, teleport where it lands",
		"BRAWLER_KIT" => "Give more knockback, take less.",
		"ATHLETE_KIT" => "Jump I",
		"PROSPECTOR_KIT" => "Diamond Pickaxe, Iron Helmet, Extra items in chests",
		"CREEPER_KIT" => "Throwable TNT (1x per 90s)",
		"TANK_KIT" => "Diamond Chestplate",
		"ASSASSIN_KIT" => "Iron Sword",
		"PARTICLES_OFF" => "Partículas desactivadas",
		"BOUNCER_NPC" => "Bouncer[NPC]",
		"PET_NPS" => "[NPC] Your Pet",
		"BAD_USERNAME" => TextFormat::RED . "This nickname does not comply with the Lifeboat policy. Try another one.",
		"SAME_PASS" => TextFormat::RED . "Your password is the same. Please use the different one when change it.",
	);
	
	
}
