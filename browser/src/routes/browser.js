const { Router } = require('express')
const Browser = require('../components/browser')
const router = Router()

router.get('/browser', (req, res, next) => {
    res.send({message: 'success'})
})

router.post('/browser', async (req, res, next) => {
    const {proxy, url} = req.body
    try {
        const base64screenshot = await getScreenshot(proxy, url);
        return res.send({status: 200, screenshot: base64screenshot, message: 'success'})
    }catch (e) {
        return res.send({status: 400, message: e.message, screenshot: ''})
    }
})

async function getScreenshot(proxy, url) {
    const browser = new Browser(proxy)
    await browser.init();
    const {screenshot} = await browser.createPage(url)
    const base64screenshot = screenshot.toString('base64')
    await browser.close();
    return base64screenshot;
}

module.exports = router
