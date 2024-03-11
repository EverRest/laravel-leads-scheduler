const randomUseragent = require('random-useragent')
const puppeteer = require('puppeteer-extra')
const StealthPlugin = require('puppeteer-extra-plugin-stealth')

class Browser {
    browser;
    proxy;
    constructor (proxy) {
        puppeteer.use(StealthPlugin())
        this.proxy = proxy
        const arg = ['--disable-gpu', '--disable-setuid-sandbox', '--no-sandbox', '--no-zygote' ]
        return (async () => {
            this.browser = await puppeteer.launch({
                headless: true,
                executablePath: '/usr/bin/chromium-browser',
                args: proxy ? [...arg,
                    `--proxy-server=${proxy.protocol}://${proxy.host}:${proxy.port}`,
                ] : [...arg],
            });
            return this;
        })();
    }

    async close () {
        this.browser.close();
    }
    async createPage (url) {
        console.log(url);
        const page = await this.setupPage();
        await this.setPageProxy(page);
        await this.setPageViewPort(page);
        await this.setUserAgent(page);
        await this.setPageSettings(page);
        let screenshot = await this.captureScreenshot(page, url);
        console.log(screenshot);
        return {page, screenshot};
    }

    async setupPage() {
        return await this.browser.newPage();
    }

    async setPageProxy(page) {
        if(this.proxy){
            await page.authenticate({
                username: this.proxy.username,
                password: this.proxy.password
            });
            console.log(this.proxy)
        }
    }

    async setPageViewPort(page) {
        await page.setViewport({
            width: 1920 + Math.floor(Math.random() * 100),
            height: 1280 + Math.floor(Math.random() * 100),
            deviceScaleFactor: 1,
            hasTouch: false,
            isLandscape: false,
            isMobile: false,
        });
    }

    async setUserAgent(page) {
        const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.75 Safari/537.36';
        const userAgent = randomUseragent.getRandom();
        const UA = userAgent || USER_AGENT;
        await page.setUserAgent(UA);
        await page.setJavaScriptEnabled(true);
        await page.setDefaultNavigationTimeout(0);
    }

    async setPageSettings(page) {
        await page.evaluateOnNewDocument(() => {
            Object.defineProperty(navigator, 'webdriver', {
                get: () => false,
            });
        });

        await page.evaluateOnNewDocument(() => {
            // @ts-ignore
            window.chrome = {
                runtime: {},
                // etc.
            };
        });

        await page.evaluateOnNewDocument(() => {
            //Pass notifications check
            const originalQuery = window.navigator.permissions.query;
            // @ts-ignore
            return window.navigator.permissions.query = (parameters) => (
                parameters.name === 'notifications' ?
                    Promise.resolve({ state: Notification.permission }) :
                    originalQuery(parameters)
            );
        });

        await page.evaluateOnNewDocument(() => {
            // Overwrite the `plugins` property to use a custom getter.
            Object.defineProperty(navigator, 'plugins', {
                // This just needs to have `length > 0` for the current test,
                // but we could mock the plugins too if necessary.
                get: () => [1, 2, 3, 4, 5],
            });
        });

        await page.evaluateOnNewDocument(() => {
            // Overwrite the `languages` property to use a custom getter.
            Object.defineProperty(navigator, 'languages', {
                get: () => ['en-US', 'en'],
            });
        });
    }

    async captureScreenshotWithRetry(page, url, maxRetries = 3) {
        let screenshot;
        let error;

        for (let retry = 0; retry < maxRetries; retry++) {
            try {
                console.log(`Navigating to URL (Attempt ${retry + 1}):`, url);
                await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });
                screenshot = await page.screenshot({
                    omitBackground: true,
                    encoding: 'binary'
                });
                break;
            } catch (caughtError) {
                error = caughtError;
                console.error(`Error during attempt ${retry + 1}:`, error.message);
            }
        }

        if (!screenshot) {
            console.error(`Failed to capture screenshot after ${maxRetries} attempts. Last error:`, error);
        }

        return screenshot;
    }

    async captureScreenshot(page, url) {
        let screenshot;
        try {
            console.log('Navigating to URL:', url);
            await page.goto(url, {waitUntil: 'networkidle0', timeout: 0}).then(async () => {
                screenshot = await page.screenshot({
                    omitBackground: true,
                    encoding: 'binary'
                });
            })
        } catch (error) {
            if (error.name === "TimeoutError") {
                console.log (error)
                screenshot = await page.screenshot({
                    omitBackground: true,
                    encoding: 'binary'
                });
            } else {
                console.log (error)
            }
        }
        return screenshot;
    }
}

module.exports = Browser
