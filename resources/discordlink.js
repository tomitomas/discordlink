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
const urlreponse = process.argv[5];


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
				'guildID': channel.guild.id,
				'guildName' : channel.guild.name
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

app.get('/sendFile', (req, res) => {
    res.type('json');
    var toReturn = [];

    config.logger('DiscordLink: sendMsg');
    
    client.channels.get(req.query.channelID).send({
		files: [{
		  attachment: req.query.patch,
		  name: req.query.name
		}]
	});
	
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
	var reponse = "null";

	if (color =="null")color = "#ff0000";

	const Embed = new Discord.RichEmbed()
	.setColor(color)
	.setTimestamp();
	if(title != "null")Embed.setTitle(title);
	if(url != "null")Embed.setURL(url);
	if(description != "null")Embed.setDescription(description);
	if(footer != "null")Embed.setFooter(footer);
	   
    client.channels.get(req.query.channelID).send(Embed).then(async m => {
		if(field != "null") {
			var emojy = ["🇦","🇧","🇨","🇩","🇪","🇫","🇬","🇭","🇮","🇯","🇰","🇱","🇲","🇳","🇴","🇵","🇶","🇷","🇸","🇹","🇺","🇻","🇼","🇽","🇾","🇿"];
			a = 0;
			while (a < field) {
				await m.react(emojy[a]);
				a++;
			}
			const filter = (reaction, user) => {
				return ["🇦","🇧","🇨","🇩","🇪","🇫","🇬","🇭","🇮","🇯","🇰","🇱","🇲","🇳","🇴","🇵","🇶","🇷","🇸","🇹","🇺","🇻","🇼","🇽","🇾","🇿"].includes(reaction.emoji.name) && user.id !== m.author.id;
			};
			m.awaitReactions(filter, { max: 1, time: 300000, errors: ['time'] })
				.then(collected => {
					const reaction = collected.first();
					if (reaction.emoji.name === '🇦') reponse = 0;	
					else if (reaction.emoji.name === '🇧') reponse = 1;	
					else if (reaction.emoji.name === '🇨') reponse = 2;	
					else if (reaction.emoji.name === '🇩') reponse = 3;	
					else if (reaction.emoji.name === '🇪') reponse = 4;	
					else if (reaction.emoji.name === '🇫') reponse = 5;	
					else if (reaction.emoji.name === '🇬') reponse = 6;	
					else if (reaction.emoji.name === '🇭') reponse = 7;	
					else if (reaction.emoji.name === '🇮') reponse = 8;	
					else if (reaction.emoji.name === '🇯') reponse = 9;	
					else if (reaction.emoji.name === '🇰') reponse = 10;	
					else if (reaction.emoji.name === '🇱') reponse = 11;	
					else if (reaction.emoji.name === '🇲') reponse = 12;	
					else if (reaction.emoji.name === '🇳') reponse = 13;	
					else if (reaction.emoji.name === '🇴') reponse = 14;	
					else if (reaction.emoji.name === '🇵') reponse = 15;	
					else if (reaction.emoji.name === '🇶') reponse = 16;	
					else if (reaction.emoji.name === '🇷') reponse = 17;	
					else if (reaction.emoji.name === '🇸') reponse = 18;	
					else if (reaction.emoji.name === '🇹') reponse = 19;	
					else if (reaction.emoji.name === '🇺') reponse = 20;	
					else if (reaction.emoji.name === '🇻') reponse = 21;	
					else if (reaction.emoji.name === '🇼') reponse = 22;	
					else if (reaction.emoji.name === '🇽') reponse = 23;	
					else if (reaction.emoji.name === '🇾') reponse = 24;	
					else if (reaction.emoji.name === '🇿') reponse = 25;	

					toReturn.push({
						'reponse': reponse
					});
					res.status(200).json(toReturn);	
				})
			.catch(collected => {
				m.reply('Une erreur est survenue. Ou le temps maximum de reponse a été depasser.');
				toReturn.push({
					'querry': req.query
				});
				res.status(200).json(toReturn);	
			});
		}
	}).catch(console.error);
	if(field == "null") {
		toReturn.push({
			'querry': req.query
		});
		res.status(200).json(toReturn);	
	}
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
    client.user.setActivity(`Travailler main dans la main avec votre Jeedom`);
});