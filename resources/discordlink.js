/*jshint esversion: 6,node: true,-W041: false */
const express = require('express');
const fs = require('fs');
const Discord = require("discord.js");

const client = new Discord.Client();

//var XMLHttpRequest = require("xmlhttprequest").XMLHttpRequest  ;
const request = require('request');

const token = process.argv[3];
//const IPJeedom = process.argv[2];
const logLevel = process.argv[4];


/* Configuration */
const config = {
	logger: console2,
	token: token,
	listeningPort: 3466
};

var dernierStartServeur=0;

// Par sécurité pour détecter un éventuel souci :
if (!token) config.logger('DiscordLink-Config: *********************TOKEN NON DEFINI*********************');

// Speed up calls to hasOwnProperty - Pour le test function isEmpty(obj)
var hasOwnProperty = Object.prototype.hasOwnProperty;
function isEmpty(obj) {

    // null and undefined are "empty"
    if (obj == null) return true;

    // Assume if it has a length property with a non-zero value
    // that that property is correct.
    if (obj.length > 0)    return false;
    if (obj.length === 0)  return true;

    // If it isn't an object at this point
    // it is empty, but it can't be anything *but* empty
    // Is it empty?  Depends on your application.
    if (typeof obj !== "object") return true;

    // Otherwise, does it have any properties of its own?
    // Note that this doesn't handle
    // toString and valueOf enumeration bugs in IE < 9
    for (var key in obj) {
        if (hasOwnProperty.call(obj, key)) return false;
    }

    return true;
}


	// arguments[0]	c'est le texte
	// arguments[1]	c'est le niveau de log ou un array
	
	//niveaudeLog=5 c'est tout
	//niveaudeLog=2 c'est reduit
	

function console2(text, level='') {
	var today = new Date();

	// 100=DEBUG
	// 200=INFO
	// 300=WARNING
	// 400=ERROR
	//1000=AUCUN
	
	try {
    	var niveauLevel;
		switch (level) {
		case "ERROR":	
				niveauLevel=400;
				break;
		case "WARNING":	
				niveauLevel=300;
				break;		
		case "INFO":	
				niveauLevel=200;
				break;		
		case "DEBUG":	
				niveauLevel=100;
				break;	
		default:
				niveauLevel=400; //pour trouver ce qui n'a pas été affecté à un niveau
				break;
		
		}
	} catch (e) {
		console.log(arguments[0]);
	}
	

}


/* Routing */
const app = express();
let server = null;
/* Objet contenant les commandes pour appeler via chaine */
var CommandAlexa = {};


function LancementCommande(commande, req) 
{
	config.logger('DiscordLink:    Lancement /'+commande, "INFO");
}


/***** Stop the server *****/
app.get('/stop', (req, res) => {
	config.logger('DiscordLink: Shuting down');
	res.status(200).json({});
	server.close(() => {
		process.exit(0);
	});
});


/***** Restart server *****/
app.get('/restart', (req, res) => {
	config.logger('DiscordLink: Restart');
	res.status(200).json({});
		config.logger('DiscordLink: ******************************************************************');
		config.logger('DiscordLink: *****************************Relance forcée du Serveur*************');
		config.logger('DiscordLink: ******************************************************************');
	startServer();
	
});

app.get('/restart', (req, res) => {
	config.logger('DiscordLink: Restart');
	res.status(200).json({});
		config.logger('DiscordLink: ******************************************************************');
		config.logger('DiscordLink: *****************************Relance forcée du Serveur*************');
		config.logger('DiscordLink: ******************************************************************');
	startServer();
	
});


app.get('/getinvite', (req, res) => {
    
    res.type('json');
    var toReturn = [];

	config.logger('DiscordLink: GetInvite');
    client.generateInvite(["ADMINISTRATOR"]).then(link => {
        toReturn.push({
            'invite': link
        });
        res.status(200).json(toReturn);
    });
});

app.get('/getguild', (req, res) => {
    res.type('json');
    var toReturn = [];

	config.logger('DiscordLink: GetGuild');
    var guildall = client.guilds.array();
    for (var a in guildall) {
        var guild = guildall[a];
        toReturn.push({
            /*'a': guildall,*/
            'id': guild.id,
            'name': guild.name
        });
    }
    res.status(200).json(toReturn);
});

app.get('/getchannel', (req, res) => {
    res.type('json');
    var toReturn = [];

	config.logger('DiscordLink: GetChannel');
    var chnannelsall = client.channels.array();
    for (var b in chnannelsall) {
        var channel = chnannelsall[b];
        if (channel.type == "text") {
            toReturn.push({
                'id': channel.id,
                'name': channel.name,
                'guildID': channel.guild.id
            });
        }
    }
    res.status(200).json(toReturn);
});

app.get('/sendMsg', (req, res) => {
    res.type('json');
    var toReturn = [];

    config.logger('DiscordLink: sendMsg');
    
    client.channels.get(req.query.channelID).send(req.query.message);

    toReturn.push({
        'id': req.query
    });
    res.status(200).json(toReturn);	
});

app.get('/sendMsgTTS', (req, res) => {
    res.type('json');
    var toReturn = [];

    config.logger('DiscordLink: sendMsgTTS');
    
    client.channels.get(req.query.channelID).send(req.query.message, {
        tts: true
       });

    toReturn.push({
        'id': req.query
    });
    res.status(200).json(toReturn);	
});

app.get('/sendEmbed', (req, res) => {
    res.type('json');
    var toReturn = [];

	config.logger('DiscordLink: sendEmbed');
	
	var color = req.query.color;
	var title = req.query.title;
	var url = req.query.url;
	var description = req.query.description;
	var field = req.query.field;
	var footer = req.query.footer;

	if (color =="null")color = "#ff0000";

	const Embed = new Discord.RichEmbed()
	.setColor(color)
	.setTimestamp();
	if(title != "null")Embed.setTitle(title);
	if(url != "null")Embed.setURL(url);
	if(description != "null")Embed.setDescription(description);
	if(field != "null") {
		var fields = field.split("||");	
		a = 0;
		console.log(fields.length);
		while (a < fields.length) {
			var fields3 = fields[a];
			config.logger(fields3);
			var field2 = fields3.split("|");
			var fieldsbool = false;
			if(field2[2].includes("true")){
				fieldsbool = true;
			}
			Embed.addField(field2[0], field2[1], fieldsbool);
			a++;
		}
	}
	if(footer != "null")Embed.setFooter(footer);
	   
    client.channels.get(req.query.channelID).send(Embed);

    toReturn.push({
		'id': req.query
    });
    res.status(200).json(toReturn);	
});
/* Main */

startServer();

function startServer() {
    dernierStartServeur=Date.now();

    config.logger('DiscordLink:    ******************** Lancement BOT ***********************','INFO');
    
    client.login(config.token);

    server = app.listen(config.listeningPort, () => {
        config.logger('DiscordLink:    **************************************************************','INFO');
        config.logger('DiscordLink:    ************** Server OK listening on port ' + server.address().port + ' **************','INFO');
        config.logger('DiscordLink:    **************************************************************','INFO');

    });
}

client.on("ready", async () => {
    config.logger(`Bot has started, with ${client.users.size} users, in ${client.channels.size} channels of ${client.guilds.size} guilds.`,'INFO');

    client.user.setActivity(`Discute avec votre jeedom`);
});

function traiteErreur(err, commandesEnErreur=null, queryEnErreur=null) {
			
if (err)
{

		
		if (Array.isArray(commandesEnErreur)) {
		config.logger("DiscordLink: "+err+" Commands: "+JSON.stringify(commandesEnErreur)+" Query: "+JSON.stringify(queryEnErreur), 'ERROR');
			if (!(queryEnErreur.replay)) { // si c'est pas défini c'est que c'est le premier essai, donc on rejoue
					var listeCommandesEnErreur=[];
					listeCommandesEnErreur.push(commandesEnErreur);
					httpPost('commandesEnErreur', {
										queryEnErreur: queryEnErreur,
										listeCommandesEnErreur: commandesEnErreur
									});
				config.logger("DiscordLink: "+commandesEnErreur.length+" commandes en erreur: "+JSON.stringify(commandesEnErreur)+" query: "+JSON.stringify(queryEnErreur), 'WARNING');
			}
		}
		else if (typeof (commandesEnErreur) == "string") {
		config.logger("DiscordLink: "+err+" Command: "+commandesEnErreur+" Query: "+JSON.stringify(queryEnErreur), 'ERROR');
			if (!(queryEnErreur.replay)) { // si c'est pas défini c'est que c'est le premier essai, donc on rejoue
					httpPost('commandesEnErreur', {
										queryEnErreur: queryEnErreur,
										listeCommandesEnErreur: commandesEnErreur
									});
				config.logger("DiscordLink: commande en erreur: "+commandesEnErreur+" query: "+JSON.stringify(queryEnErreur), 'WARNING');
			}
		}			
}		

		
}