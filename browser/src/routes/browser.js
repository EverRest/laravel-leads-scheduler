const { Router } = require('express')
const Browser = require('../components/browser')
const router = Router()
const fs = require('fs').promises; // Using promises version for async/await
const path = require('path');

router.get('/browser', (req, res, next) => {
    res.send({message: 'success'})
})

router.post('/browser', async (req, res, next) => {
    const {proxy, url} = req.body
    try {
        console.log( 'body: ' + req.body, proxy, url)
        const { screenshot, filename } = await getScreenshot(proxy, url);
        const base64screenshot = await getScreenshot(proxy, url);
        const savePath = path.join(__dirname, 'files', filename);
        await fs.writeFile(savePath, screenshot);
        return res.send({status: 200, screenshot: base64screenshot, message: 'success'})
    }catch (e) {
        return res.send({status: 400, message: e.message, screenshot: ''})
    }
})

async function getScreenshot(proxy, url) {
    const browser = await new Browser(proxy)
    const {screenshot} = await browser.createPage(url)
    const base64screenshot = screenshot.toString('base64')
    await browser.close();
    return base64screenshot;
}

module.exports = router
