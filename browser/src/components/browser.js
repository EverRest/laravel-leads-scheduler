const randomUseragent = require('random-useragent')
const puppeteer = require('puppeteer-extra')
const StealthPlugin = require('puppeteer-extra-plugin-stealth')

class Browser {
    browser;
    proxy;
    constructor(proxy) {
        puppeteer.use(StealthPlugin())
        this.proxy = proxy
        const arg = ['--disable-gpu', '--disable-setuid-sandbox', '--no-sandbox', '--no-zygote']
        return (async () => {
            this.browser = await puppeteer.launch({
                executablePath: '/usr/bin/chromium-browser',
                // args: proxy ? [...arg,
                //     `--proxy-server=${proxy.protocol}://${proxy.host}:${proxy.port}`,
                // ] : [...arg],
                args: [...arg],
            });

            return this;
        })();
    }

    async close() {
        this.browser.close();
    }

    async createPage(url) {
        const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.75 Safari/537.36';

        //Randomize User agent or Set a valid one
        const userAgent = randomUseragent.getRandom();
        const UA = userAgent || USER_AGENT;
        const page = await this.browser.newPage();
        await page.setDefaultNavigationTimeout(30000);

        // if (this.proxy) {
        //     console.log('Using proxy:', this.proxy.username, this.proxy.password, this.proxy.host, this.proxy.port);
        //     await page.authenticate({
        //         username: this.proxy.username,
        //         password: this.proxy.password
        //     })
        // }

        //Randomize viewport size
        await page.setViewport({
            width: 1920 + Math.floor(Math.random() * 100),
            height: 1280 + Math.floor(Math.random() * 100),
            deviceScaleFactor: 1,
            hasTouch: false,
            isLandscape: false,
            isMobile: false,
        });

        await page.setUserAgent(UA);
        await page.setJavaScriptEnabled(true);
        await page.setDefaultNavigationTimeout(0);

        await page.evaluateOnNewDocument(() => {
            // Pass webdriver check
            Object.defineProperty(navigator, 'webdriver', {
                get: () => false,
            });
        });

        await page.evaluateOnNewDocument(() => {
            // Pass chrome check

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
                    Promise.resolve({state: Notification.permission}) :
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

        let screenshot

        try {
            console.log('Before page.goto:', url);
            await page.goto(url,  { waitUntil: 'networkidle0', timeout: 60000 }).then(async () => {
                console.log('After page.goto');
                screenshot = await page.screenshot({
                    //path: '',
                    omitBackground: true,
                    encoding: 'binary'
                });
            })
        } catch (error) {
            if (error.name === "TimeoutError") {
                console.log(error.name)
                screenshot = await page.screenshot({
                    // path: './example.png'
                    omitBackground: true,
                    encoding: 'binary'
                });
            } else {
                console.log(error)
            }
        }


        return {page, screenshot};
    }

}

module.exports = Browser
