const {PermissionFlagsBits, OAuth2Scopes} = require("discord.js");

function generateInvite() {
    return discord.generateInvite({
        scopes: [OAuth2Scopes.Bot],
        permissions: PermissionFlagsBits.admo
    });
}

module.exports = {
    generateInvite
}