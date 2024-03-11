const { Router } = require('express')
const Browser = require('../components/browser')
const router = Router()

router.get('/browser', (req, res, next) => {
    console.log(req.query)
    res.send({message: "success"})
})

router.post('/browser', async (req, res, next) => {
    const {proxy, url} = req.body
    let base64screenshot = "";
console.log('Proxy:' + proxy)
console.log('Proxy:' + url)
    try {
        const browser = await new Browser(proxy)
        const {page, screenshot} = await browser.createPage(url)
        base64screenshot = screenshot.toString("base64")
        await browser.close();

        return res.send({status: 200, screenshot: base64screenshot})
    }catch (e) {
        console.log(e)
        return res.send({status: 400, screenshot: base64screenshot})
    }
})

module.exports = router
