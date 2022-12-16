const {loadApiRequest} = require("./api-request");
const fastify = require('fastify')({ logger: true })

async function initializeFastify() {
    loadApiRequest(fastify);


        try {
            await fastify.listen({ port: 3466 })
        } catch (err) {
            fastify.log.error(err)
            process.exit(1)
        }
}

module.exports = {
    initializeFastify
}