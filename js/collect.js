const puppeteer = require('puppeteer');
const cheerio = require('cheerio');
const env = process.env.APP_ROLE || 'local';
const CREDS = (env === 'production') ? require('./creds_production') : require('./creds_local');
const mysql = require('mysql');
const fs = require('fs');
const mysqlUtilities = require('mysql-utilities');
const {Cluster} = require('puppeteer-cluster');
const loginUrl = 'https://kmr.dealer-portal.net/irj/portal';
const USERNAME_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(3) > input[type="text"]';
const PASSWORD_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(3) > td:nth-child(3) > input[type="password"]';
const LOGIN_BUTTON_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(5) > a';
const SELL_TAB_SELECTOR = '#tabIndex1';
const FREE_SKLAD_LEFT_SIDEBAR_SELECTOR = '#L2N1';
const FIRST_PAGE_NEXT_SELECTOR = "#board1 > tbody > tr > td > table > tbody > tr > td.ar > a";
const NON_FIRST_PAGE_NEXT_SELECTOR = '#board1 > tbody > tr > td > table > tbody > tr > td.ar > a:nth-child(2)';
const SCREENSHOT_PATH = '../php/web/screenshots';

const FREE_SKLAD_IFRAME_SELECTOR = '#contentAreaFrame';
const FREE_SKLAD_CONTENT_IFRAME = '#isolatedWorkArea';
const FORM_MODEL_SELECTOR = '#model';
const FORM_MANUFACTURE_CODE_SELECTOR = '#fsc';
const FORM_COLOR_INSIDE_SELECTOR = '#intcolor';
const FORM_COLOR_OUTSIDE_SELECTOR = '#extcolor';
const FORM_ONLY_AVAILABLE_SELECTOR = '#board1 > tbody > tr:nth-child(6) > td:nth-child(4) > input[type="checkbox"]';
const FORM_REQUEST_BUTTON_SELECTOR = '#subContents > table > tbody > tr > td:nth-child(2) > form > div.buttons > a';
const ORDER_TABLE = '#resultTable';
const FORM_CHANGE_ORDER_BUTTON = '#subContents > table > tbody > tr > td:nth-child(2) > form > div.buttons > a.negative';
const ORDER_BUTTON = '#commonparam > table > tbody > tr:nth-child(4) > td > div > a:nth-child(2)';
const ORDER_FREE_SKLAD = '#L2N2';
const ORDER_FREE_SKLAD_BUTTON = '#subContents > div.buttons > a';
const PAGING_SELECTOR = '#sel_paging';

const MAX_CONCURRENCY = 1;

run();

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function run() {
    await delay(5000);
    const connection = await connectToDb();
    await robot(connection);
    disconnectFromDb(connection);
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

function currentDate() {
    let date = new Date();
    return '[' + date.getFullYear().toString() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate())
        + ' ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes()) + ':' + pad2(date.getSeconds()) + ']';
}

function log(...messages) {
    if (!CREDS.enableLogging) {
        return;
    }
    for (let i = 0; i < messages.length; i++) {
        console.log(currentDate() + " " + messages[i]);
    }
}

function makeUniqueScreenshotName() {
    return +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
}

async function connectToDb() {
    const connection = mysql.createConnection({
        host: CREDS.mysqlHost,
        user: CREDS.mysqlUsername,
        password: CREDS.mysqlPassword,
        database: CREDS.mysqlDatabase
    });

    connection.connect();

    // Mix-in for Data Access Methods and SQL Autogenerating Methods
    mysqlUtilities.upgrade(connection);

    // Mix-in for Introspection Methods
    mysqlUtilities.introspection(connection);

    return connection;
}

async function disconnectFromDb(connection) {
    connection.end();
}

let firstFrame;

async function loginAndSwitchToFreeSklad(page, login, password) {
    let formFrame;

    await page.setViewport({width: 1280, height: 800});

    log("Start logging in");
    await page.goto(loginUrl);
    await page.click(USERNAME_SELECTOR);
    await page.keyboard.type(login);
    await page.click(PASSWORD_SELECTOR);
    await page.keyboard.type(password);
    await page.click(LOGIN_BUTTON_SELECTOR);
    log("Logged in");

    log("Switching to free sklad search page");
    await page.waitFor(SELL_TAB_SELECTOR);
    await page.click(SELL_TAB_SELECTOR);
    await page.waitFor(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);

    await page.click(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);
    await page.waitFor(FREE_SKLAD_IFRAME_SELECTOR);

    firstFrame = await page.frames().find(f => f.name() === 'contentAreaFrame');
    await firstFrame.waitFor(FREE_SKLAD_CONTENT_IFRAME);
    await firstFrame.waitFor(2000);

    for (const secondFrame of firstFrame.childFrames()) {
        const modelField = await secondFrame.$(FORM_MODEL_SELECTOR);
        if (modelField) {
            formFrame = secondFrame
        }
    }
    log("Switched to free sklad search page");
    return formFrame;
}

let searchResultExists;

async function sendSearchRequestForAllCars(page, formFrame) {
    page.on('dialog', async dialog => {
        searchResultExists = false;
        await dialog.dismiss();
    });

    searchResultExists = true;
    log("Setup search params");
    const checkbox = await formFrame.$(FORM_ONLY_AVAILABLE_SELECTOR);
    const isChecked = await (await checkbox.getProperty('checked')).jsonValue();
    if (!isChecked) {
        await formFrame.click(FORM_ONLY_AVAILABLE_SELECTOR);
        log("Setup only available checkbox to true");
    }
    log("Send search request");
    await formFrame.click(FORM_REQUEST_BUTTON_SELECTOR);
    await formFrame.waitFor(5000);
    log("Sent search request");

    return searchResultExists;
}

async function saveScreenshot(currentScreenshotPath, page, name) {
    let fullpath = currentScreenshotPath + "/" + makeUniqueScreenshotName();
    await page.screenshot({path: fullpath, fullPage: true});
    return {
        name: name,
        filepath: fullpath
    };
}

const runner = async ({page, data: task}) => {
    let screenshots = [];
    let formFrame = await loginAndSwitchToFreeSklad(page, CREDS.username, CREDS.password);
    //screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Вход в систему осуществлен'));
    let searchResultExistis = sendSearchRequestForAllCars(page, formFrame);
    //screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Послали поисковый запрос'));
};

async function robot(connection) {
    let currentScreenshotPath = __dirname + "/" + SCREENSHOT_PATH;
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }
    currentScreenshotPath = currentScreenshotPath + "/" + formattedDate(new Date());
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }

    const cluster = await Cluster.launch({
        concurrency: Cluster.CONCURRENCY_CONTEXT,
        maxConcurrency: MAX_CONCURRENCY,
        puppeteerOptions: {
            headless: !CREDS.chromeVisible
        },
        timeout: 20 * 60000 //20 minutes
    });

    // Event handler to be called in case of problems
    cluster.on('taskerror', (err, data) => {
        console.log(`Error crawling `, data, `: ${err.message}`);
    });

    await cluster.queue({currentScreenshotPath: currentScreenshotPath}, runner);

    await cluster.idle();
    await cluster.close();
}

