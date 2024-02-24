const { Router } = require('express')
const Browser = require('../components/index')
const router = Router()

router.get('/browser', (req, res, next) => {
    res.send({message: "success"})
})

router.post('/browser', async (req, res, next) => {
    const {proxy, url} = req.body
    let base64screenshot = "";
    try {
        const browser = await new Browser(proxy)
        const {screenshot} = await browser.createPage(url)
        base64screenshot = screenshot.toString("base64")
        await browser.close();
        return res.send({status: 200, screenshot: base64screenshot, message: "success"})
    }catch (e) {
        return res.send({status: 400, message: e.message, screenshot: base64screenshot})
    }
})

module.exports = router
