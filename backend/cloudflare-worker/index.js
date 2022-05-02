import { Router } from 'itty-router'

// Create a new router
const router = Router()

/*
Our index route, a simple hello world.
*/
router.get("/tucao", async () => {
    let rawTucaoIds = await TUCAO.get("tucao_ids", { type: "json" })
    if (rawTucaoIds == null) {
        return new Response(null, { status: 404 })
    }

    let idObjects = {
        "tucao_ids": rawTucaoIds
    }
    const returnData = JSON.stringify(idObjects)
    return new Response(returnData, {
        headers: {
            "Content-Type": "application/json"
        },
        status: 200
    })
})

/*
This route demonstrates path parameters, allowing you to extract fragments from the request
URL.

Try visit /example/hello and see the response.
*/
router.get("/tucao/:id", async ({ params }) => {

    let tucao = await TUCAO.get(params.id, { type: "json" })
    if (tucao == null) {
        return new Response(null, { status: 404 })
    }

    const returnData = JSON.stringify(tucao)
    return new Response(returnData, {
        headers: {
            "Content-Type": "application/json"
        },
        status: 200
    })
})

router.delete("/tucao/:id", async request => {

    //check authorization
    let sentToken = request.headers.get("Authorization")
    if (sentToken == null) {
        return new Response(null, { status: 403 })
    }
    sentToken = sentToken.substring(6)
    let storageToken = await TUCAO.get("token")
    if (sentToken != storageToken) {
        return new Response(null, { status: 403 })
    }

    //check if exists
    let tucao = await TUCAO.get(request.params.id, { type: "json" })
    if (tucao == null) {
        return new Response(null, { status: 404 })
    }

    //processing
    let ids = await TUCAO.get("tucao_ids", { type: "json" })
    if (ids == null) {
        ids = Array()
    }
    ids = ids.filter(function (item) {
        return item != request.params.id
    });
    await TUCAO.put("tucao_ids", JSON.stringify(ids))

    await TUCAO.delete(request.params.id)
    return new Response(null, { status: 200 })
})

router.post("/tucao", async request => {

    //check authorization
    let sentToken = request.headers.get("Authorization")
    if (sentToken == null) {
        return new Response(null, { status: 403 })
    }
    sentToken = sentToken.substring(6)
    let storageToken = await TUCAO.get("token")
    if (sentToken != storageToken) {
        return new Response(null, { status: 403 })
    }
    //check request content type
    if (request.headers.get("Content-Type") != "application/json") {
        console.log("not content type")
        return new Response(null, { status: 400 })

    }
    let json = await request.json()
    let content = json.content
    console.log(json)
    if (content == null) {
        console.log("no content")
        return new Response(null, { status: 400 })

    }

    //processing
    let lastIdText = await TUCAO.get("tucao_lastid")
    let nextId = 0
    if (lastIdText != null) {
        nextId = parseInt(lastIdText)
    }
    nextId++;
    await TUCAO.put("tucao_lastid", nextId.toString())

    let ids = await TUCAO.get("tucao_ids", { type: "json" })
    if (ids == null) {
        ids = Array()
    }
    ids.push(nextId)
    await TUCAO.put("tucao_ids", JSON.stringify(ids))

    let storageItem = {
        "id": nextId,
        "content": content,
        "createdAt": new Date().getTime()
    }

    await TUCAO.put(nextId.toString(), JSON.stringify(storageItem))
    return new Response(null, { status: 200 })

})

/*
This is the last route we define, it will match anything that hasn't hit a route we've defined
above, therefore it's useful as a 404 (and avoids us hitting worker exceptions, so make sure to include it!).

Visit any page that doesn't exist (e.g. /foobar) to see it in action.
*/
router.all("*", () => new Response("404, not found!", { status: 404 }))

/*
This snippet ties our worker to the router we deifned above, all incoming requests
are passed to the router where your routes are called and the response is sent.
*/
addEventListener('fetch', (e) => {
    e.respondWith(router.handle(e.request))
})
