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
        console.log(proxy, url, "Proxy and URL");
        const browser = await new Browser(proxy)
        console.log($browser, "Browser created");
        const {page, screenshot} = await browser.createPage(url)
        base64screenshot = screenshot.toString("base64")
        console.log(page, base64screenshot, "Page and Screenshot created");
        await browser.close();
        return res.send({status: 200, screenshot: base64screenshot})
    }catch (e) {
        console.log(e)
        return res.send({status: 400, screenshot: base64screenshot})
    }
})

module.exports = router
