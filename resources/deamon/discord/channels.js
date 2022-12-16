const {sleep} = require("../utils");
const {ChannelType} = require("discord-api-types/v10");

async function getChannels() {
    let channels = [];

    if (discord.channels.cache.size === 0) {
        console.log("No channels found, waiting 1 seconds and trying again");
        await sleep(1000)
        return await getChannels();
    } else {
        discord.channels.cache.forEach(value => {
            console.log(value)
            switch (value.type) {
                case ChannelType.GuildText:
                case ChannelType.GuildAnnouncement:
                    channels.push({
                        'id': value.id,
                        'type': value.type,
                        'name': value.name,
                        'guildID': value.guild.id,
                    });
                    break;
                default:
                    console.log("Unknown channel type: " + value.type);
            }
        })
    }

    return channels;
}

module.exports = {
    getChannels
}