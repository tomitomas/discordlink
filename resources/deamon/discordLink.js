/*jshint esversion: 6,node: true,-W041: false */
//Test : node discordLink.js http://192.168.1.200 NjkzNDU5ODg2NTY2Mjc3MTUw.Xn9Y2A.ldbfL6uAUwGxF-wdU7YOsNkg6ew 100 http://192.168.1.200:80/plugins/discordlink/core/api/jeeDiscordlink.php?apikey=kZxOHfEX aelfgZZWEJaDFnlkhH2wO2pi kZxOHfEXaelfgZZWEJaDFnlkhH2wO2pi Me%20pr%C3%A9pare%20%C3%A0%20faire%20r%C3%A9gner%20la%20terreur
const express = require('express');
const bodyParser = require('body-parser');
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));

const { Client, Intents, Permissions, MessageEmbed } = require('discord.js');
const client = new Client({ intents: [
        Intents.FLAGS.GUILDS,
        Intents.FLAGS.GUILD_MEMBERS,
        Intents.FLAGS.GUILD_BANS,
        Intents.FLAGS.GUILD_EMOJIS_AND_STICKERS,
        Intents.FLAGS.GUILD_INTEGRATIONS,
        Intents.FLAGS.GUILD_WEBHOOKS,
        Intents.FLAGS.GUILD_INVITES,
        Intents.FLAGS.GUILD_VOICE_STATES,
        Intents.FLAGS.GUILD_PRESENCES,
        Intents.FLAGS.GUILD_MESSAGES,
        Intents.FLAGS.GUILD_MESSAGE_REACTIONS,
        Intents.FLAGS.GUILD_MESSAGE_TYPING,
        Intents.FLAGS.DIRECT_MESSAGES,
        Intents.FLAGS.DIRECT_MESSAGE_REACTIONS,
        Intents.FLAGS.DIRECT_MESSAGE_TYPING
    ] });
//const request = require('request');

const token = process.argv[3];
const IPJeedom = process.argv[2];
const ClePlugin = process.argv[6];
const joueA = decodeURI(process.argv[7]);


/* Configuration */
const config = {
    logger: console2,
    token: token,
    listeningPort: 3466
};

let dernierStartServeur = 0;
if (!token) config.logger('DiscordLink-Config: *********************TOKEN NON DEFINI*********************');

function console2(text, level = '') {
    console.log(text)
}

/* Routing */
const app = express();
app.use(bodyParser.json())

let server = null;

/***** Stop the server *****/
app.get('/stop', (req, res) => {
    config.logger('DiscordLink: Shutting down');
    res.status(200).json({});
    server.close(() => {
        process.exit(0);
    });
});

/***** Request *****/
app.post('/api/request', async (req, res) => {
    console.log(req.body);

    let result = await requestInfo(req.body)
    console.log(result)

    res.status(200);
    res.send(result);
});

async function requestInfo(data) {
    let result;
    switch (data.action) {
        case 'getDiscordChannels':
            result = getDiscordChannels()
            break;
        case 'sendDiscordMessage':
            result = sendDiscordMessage(data)
            break;
        case 'deleteDiscordMessage':
            result = await deleteDiscordMessages(data)
            break;
    }

    return result;
}



function sendDiscordMessage(data) {
    let channel = client.channels.cache.get(data.channelID);
    let options = {
        content: data.message ?? null,
        tts: data.tts ?? false,
        files: data.files ? generateDiscordFile(data.files) : null,
        embeds: data.embeds ? generateDiscordEmbed(data.embeds) : null,
    }

    if (channel != null) channel.send(options);
    return true;
}

function generateDiscordEmbed(data) {
    let result = [];
    data.forEach(embedData => {
        const embed = new MessageEmbed()
            .setTimestamp();
        embed.setColor(embedData.color ?? "#ff0000");
        embedData.url ? embed.setURL(embedData.url) : false;
        embedData.title ? embed.setTitle(embedData.title) : false;
        embedData.footer ? embed.setFooter(embedData.footer) : false;
        embedData.thumbnail ? embed.setThumbnail(embedData.thumbnail) : false;
        embedData.description ? embed.setDescription(embedData.description) : false;

        if (embedData.fields) {
            embedData.fields.forEach(field => {
                if (field.name !== undefined && field.value !== undefined && field.inline !== undefined) {
                    embed.addField(field.name, field.value, field.inline);
                }
            })
        }
        result.push(embed);
    })
    return result;
}

function generateDiscordFile(data) {
    let result = [];

    data.forEach(file => {
        let filedef = {
            "description": file.description,
            "attachment": file.attachment,
            "name": file.name
        }
        result.push(filedef);
    })
    return result;
}

async function deleteDiscordMessages(data) {
    let finish = false;
    let date = new Date();

    let dateStart = data.start ?? (date.getTime() - 172800000)
    let dateFinish = data.finish ?? date.getTime()
    let nbMessages = data.nbMessages ?? 1000

    let channel = client.channels.cache.get(data.channelID);

    while (finish !== true) {
        const delmessage = [];

        let limit = nbMessages > 100 ? 100 : nbMessages;
        let fetch = await channel.messages.fetch({
            force: true,
            limit: limit,
        })

        for (const message of fetch) {
            if (!message[1].deletable) continue;
            if (dateStart <= message[1].createdTimestamp) continue;
            if (dateFinish >= message[1].createdTimestamp)  continue;
            if (nbMessages <= 0) continue;
            delmessage.push(message[1])

            nbMessages--;
        }

        if (delmessage.length === 0) finish = true;
        else await channel.bulkDelete(delmessage);
        if (nbMessages === 0) finish = true;
    }

    return true;
}

/*
app.get('/sendEmbed', (req, res) => {
       client.channels.cache.get(req.query.channelID).send(embed).then(async m => {
        if (countanswer !== "null") {
            let timecalcul = (req.query.timeout * 1000);
            toReturn.push({
                'querry': req.query,
                'timeout': req.query.timeout,
                'timecalcul': timecalcul
            });
            res.status(200).json(toReturn);

            if (countanswer !== "0") {
                let emojy = ["🇦", "🇧", "🇨", "🇩", "🇪", "🇫", "🇬", "🇭", "🇮", "🇯", "🇰", "🇱", "🇲", "🇳", "🇴", "🇵", "🇶", "🇷", "🇸", "🇹", "🇺", "🇻", "🇼", "🇽", "🇾", "🇿"];
                let a = 0;
                while (a < countanswer) {
                    await m.react(emojy[a]);
                    a++;
                }
                const filter = (reaction, user) => {
                    return ["🇦", "🇧", "🇨", "🇩", "🇪", "🇫", "🇬", "🇭", "🇮", "🇯", "🇰", "🇱", "🇲", "🇳", "🇴", "🇵", "🇶", "🇷", "🇸", "🇹", "🇺", "🇻", "🇼", "🇽", "🇾", "🇿"].includes(reaction.emoji.name) && user.id !== m.author.id;
                };
                m.awaitReactions(filter, {max: 1, time: timecalcul, errors: ['time']})
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

                        url = JSON.parse(url);

                        httpPost("ASK", {
                            idchannel: m.channel.id,
                            reponse: reponse,
                            demande: url
                        });
                    })
                    .catch(() => {
                        m.delete();
                    });
            } else {
                let filter = m => m.author.bot === false
                m.channel.awaitMessages(filter, {
                    max: 1,
                    time: timecalcul,
                    errors: ['time']
                })
                .then(message => {
                    let msg = message.first();
                    reponse = msg.content;
                    msg.react("✅");

                    httpPost("ASK", {
                        idchannel: m.channel.id,
                        reponse: reponse,
                        demande: url
                    });
                })
                .catch(collected => {
                    m.delete();
                });
            }
        }
    }).catch(console.error);
    if (countanswer === "null") {
        toReturn.push({
            'querry': req.query
        });
        res.status(200).json(toReturn);
    }
}); */

function startServer() {
    dernierStartServeur = Date.now();
    config.logger('DiscordLink:    ******************** Lancement BOT ***********************', 'INFO');


    app.listen(3466, () => {
        console.log(`Example app listening on port ${3466}`)
    })
}

function httpPost(nom, jsonaenvoyer) {
    let url = IPJeedom + "/plugins/discordlink/core/php/jeediscordlink.php?apikey=" + ClePlugin + "&nom=" + nom;

    config.logger && config.logger('URL envoyée: ' + url, "DEBUG");

    console.log("jsonaenvoyer : "+ jsonaenvoyer)
    config.logger && config.logger('DATA envoyé:' + jsonaenvoyer, 'DEBUG');

    fetch(url, {method: 'post', body: JSON.stringify(jsonaenvoyer)})
        .then(res => {
            if (!res.ok) {
                console.log("Erreur lors du contact de votre JeeDom")
            }
        })
}

startServer();

client.on("ready", async () => {
    await client.user.setActivity(joueA);
});

client.on('messageCreate', (receivedMessage) => {
    if (receivedMessage.content === "!clearmessagechannel") {
        deletemessagechannel(receivedMessage).then(
            value => value.delete()
        );
        return;
    }
    if (receivedMessage.author === client.user) return;
    if (receivedMessage.author.bot) return;

    httpPost("messagerecu", {
        idchannel: receivedMessage.channel.id,
        message: receivedMessage.content,
        iduser: receivedMessage.author.id
    });
});
