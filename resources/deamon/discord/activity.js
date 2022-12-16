const {Activity} = require("discord.js");


function setActivity(value) {
    discord.user.setActivity(value, { type: Activity.Watching });
}


module.exports = {
    setActivity
}