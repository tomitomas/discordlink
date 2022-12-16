const {generateInvite} = require("./discord/Invite");
const {loadDiscordClient, getChannels} = require("./discord");


require('./fastify/utils').initializeFastify().then(r => console.log("Fastify initialized"));


const start = async () => {
    await loadDiscordClient();

    console.log(await generateInvite());
    console.log(await getChannels());
}
start()