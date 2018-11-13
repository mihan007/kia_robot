const puppeteer = require('puppeteer');
const cheerio = require('cheerio');
const env = process.env.APP_ROLE || 'local';
const CREDS = (env === 'production') ? require('./creds_production') : require('./creds_local');
const mysql = require('mysql');
const fs = require('fs');
const mysqlUtilities = require('mysql-utilities');
const { Cluster } = require('puppeteer-cluster');
const loginUrl = 'https://kmr.dealer-portal.net/irj/portal';
const USERNAME_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(3) > input[type="text"]';
const PASSWORD_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(3) > td:nth-child(3) > input[type="password"]';
const BUTTON_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(5) > a';
const SELL_TAB_SELECTOR = '#tabIndex1';
const FREE_SKLAD_LEFT_SIDEBAR_SELECTOR = '#L2N1';
const SCREENSHOT_PATH = '../php/web/screenshots_small';

const FREE_SKLAD_IFRAME_SELECTOR = '#contentAreaFrame';
const FREE_SKLAD_CONTENT_IFRAME = '#isolatedWorkArea';
const FORM_MODEL_SELECTOR = '#model';
const FORM_MANUFACTURE_CODE_SELECTOR = '#fsc';
const FORM_COLOR_INSIDE_SELECTOR = '#intcolor';
const FORM_COLOR_OUTSIDE_SELECTOR = '#extcolor';
const FORM_ONLY_AVAILABLE_SELECTOR = '#board1 > tbody > tr:nth-child(6) > td:nth-child(4) > input[type="checkbox"]';
const FORM_REQUEST_BUTTON_SELECTOR = '#subContents > table > tbody > tr > td:nth-child(2) > form > div.buttons > a';
const ORDER_TABLE = '#board2';
const FORM_CHANGE_ORDER_BUTTON = '#subContents > table > tbody > tr > td:nth-child(2) > form > div.buttons > a.negative';
const FORM_AMOUNT_FIELD = '#commonparam > table > tbody > tr:nth-child(2) > td > input[type="text"]';
const ORDER_BUTTON = '#commonparam > table > tbody > tr:nth-child(4) > td > div > a:nth-child(2)';
const ORDER_FREE_SKLAD = '#L2N2';
const ORDER_FREE_SKLAD_BUTTON = '#subContents > div.buttons > a';

const MAX_CONCURRENCY = 1;

run();

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function run() {
    await robot();
}

function formattedDate(date) {
    var mm = date.getMonth() + 1; // getMonth() is zero-based
    var dd = date.getDate();

    return [date.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('');
}

function pad2(n) {
    return n < 10 ? '0' + n : n
}

async function robot(connection) {

    let requestExist = true;

    let currentScreenshotPath = SCREENSHOT_PATH;
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }
    currentScreenshotPath = currentScreenshotPath + "/" + formattedDate(new Date());
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }
    
    const cluster = await Cluster.launch({
        concurrency: Cluster.CONCURRENCY_CONTEXT,
        maxConcurrency: MAX_CONCURRENCY
    });

    const processTask = async ({page, data: task}) => {
        page.on('dialog', async dialog => {
            requestExist = false;
            await dialog.dismiss();
        });

        await page.setViewport({width: 1280, height: 800});

        await page.goto(loginUrl);

        await page.click(USERNAME_SELECTOR);
        await page.keyboard.type(CREDS.username);

        await page.click(PASSWORD_SELECTOR);
        await page.keyboard.type(CREDS.password);

        await page.click(BUTTON_SELECTOR);

        await page.waitFor(SELL_TAB_SELECTOR);

        await page.click(SELL_TAB_SELECTOR);

        await page.waitFor(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);

        let formFrame;

        await page.click(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);
        await page.waitFor(FREE_SKLAD_IFRAME_SELECTOR);
        let firstFrame = await page.frames().find(f => f.name() === 'contentAreaFrame');
        await firstFrame.waitFor(FREE_SKLAD_CONTENT_IFRAME);
        await firstFrame.waitFor(2000);

        for (const secondFrame of firstFrame.childFrames()) {
            const modelField = await secondFrame.$(FORM_MODEL_SELECTOR);
            if (modelField) {
                formFrame = secondFrame
            }
        }

        const checkbox = await formFrame.$(FORM_ONLY_AVAILABLE_SELECTOR);
        const isChecked = await (await checkbox.getProperty('checked')).jsonValue();
        if (!isChecked) {
            await formFrame.click(FORM_ONLY_AVAILABLE_SELECTOR);
        }
        await formFrame.click(FORM_REQUEST_BUTTON_SELECTOR);
        await formFrame.waitFor(5000);

        let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
        let fullpath = currentScreenshotPath + "/" + filename;
        await page.screenshot({path: fullpath, fullPage: true});

        await delay(10000);
    };

    for (let i=1; i<20; i++) {
        await cluster.queue({}, processTask);
    }

    await cluster.idle();
    await cluster.close();
}
