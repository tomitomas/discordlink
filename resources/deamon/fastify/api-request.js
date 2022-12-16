function loadApiRequest(fastify) {


    fastify.route({
        method: 'POST',
        url: '/api/request',
        schema: {
            querystring: {
                action: {type: 'string'}
            },

            response: {
                200: {
                    type: 'object',
                    properties: {
                        hello: {type: 'string'}
                    }
                }
            }
        },
        // this function is executed for every request before the handler is executed
        preHandler: async (request, reply) => {
            // E.g. check authentication
        },
        handler: async (request, reply) => {

            console.log(request.body);

            return {hello: 'world'}
        }
    })
}

module.exports = {
    loadApiRequest
}