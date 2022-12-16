const { Client} = require('discord.js');
const {GatewayIntentBits} = require("discord-api-types/v10");

async function loadDiscordClient() {
    global.discord = new Client({ intents: [
            GatewayIntentBits.Guilds,
            GatewayIntentBits.GuildMembers,
            GatewayIntentBits.GuildIntegrations,
            GatewayIntentBits.GuildInvites,
            GatewayIntentBits.GuildMessages,
            GatewayIntentBits.GuildMessageReactions,
            GatewayIntentBits.DirectMessages,
            GatewayIntentBits.DirectMessageReactions,
            GatewayIntentBits.DirectMessageTyping
        ]});

    discord.on('ready', () => {
        const {setActivity} = require("./index");
        console.log(`Logged in as ${discord.user.tag}!`);

        //TODO: Make this configurable
        setActivity("Hello World");
    });

    await discord.login("NjkzNDU5ODg2NTY2Mjc3MTUw.GXpJLz.WUMnC02mkYl_m9xjz8LV8WyabeR6cBZ151zceg");
}

module.exports = {
    loadDiscordClient
}