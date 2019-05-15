const puppeteer = require('puppeteer');
const cheerio = require('cheerio');
require('dotenv').config()
const CREDS = {
    username: 'KRU7802400',
    password: 'Optima@0319',
    mysqlHost: process.env.MYSQL_HOST,
    mysqlUsername: process.env.MYSQL_USER,
    mysqlPassword: process.env.MYSQL_PASSWORD,
    mysqlDatabase: process.env.MYSQL_DATABASE,

    chromeVisible: process.env.CHROME_VISIBLE === 'true',
    enableLogging: process.env.ENABLE_LOGGING === 'true',

    maxConcurrency: parseInt(process.env.MAX_CONCURRENCY),
    monitor: process.env.JOB_MONITORING === 'true',

    screenshotPath: process.env.SCREENSHOT_PATH,
    currentWorkerName: process.env.CURRENT_WORKER_NAME,

    delayForSearch: process.env.DELAY
}
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

let firstFrame = null;
let searchResultExists = false;
let screenshots = [];
let connection;

/** Helpers **/

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function pad2(n) {
    return n < 10 ? '0' + n : n
}

function formattedDate(date) {
    var mm = date.getMonth() + 1; // getMonth() is zero-based
    var dd = date.getDate();

    return [date.getFullYear(),
        (mm > 9 ? '' : '0') + mm,
        (dd > 9 ? '' : '0') + dd
    ].join('');
}

function currentDate() {
    let date = new Date();
    return '[' + date.getFullYear().toString() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate())
        + ' ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes()) + ':' + pad2(date.getSeconds()) + ']';
}

function mysqlCurrentDate() {
    let date = new Date();
    return date.getFullYear().toString() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate())
        + ' ' + pad2(date.getHours()) + ':' + pad2(date.getMinutes()) + ':' + pad2(date.getSeconds());
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

async function saveScreenshot (page, name) {
    let currentScreenshotPath = CREDS.screenshotPath
    let fullpath = currentScreenshotPath + '/' + makeUniqueScreenshotName()
    await page.screenshot({ path: fullpath, fullPage: true })
    log(`Saved screenshot ${fullpath}`, task)

    return {
        name: name,
        filepath: fullpath
    }
}

/** Db **/

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

async function disconnectFromDb() {
    connection.end();
}

async function saveStorageItemsToDb(items, storageSessionId, currentPage) {
    for (const i in items) {
        saveStorageItemToDb(items[i], storageSessionId, currentPage);
    }
}

async function saveStorageItemToDb(item, storage_session_id, currentPage) {
    return new Promise((resolve, reject) => {
        connection.insert(
            'storage', {
                storage_session_id: storage_session_id,
                page: currentPage,
                model: item.model,
                manufacture_code: item.manufacture_code,
                description: item.description,
                color_outside: item.color_outside,
                color_inside: item.color_inside,
                year: item.year.length>0 ? item.year : 0,
                storage_code: item.storage_code,
                available: item.available,
                reserved: item.reserved,
                created_at: mysqlCurrentDate()
            },
            (err, recordId) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(recordId);
                }
            }
        );
    });
}

async function saveStartOfSessionStorage() {
    return new Promise((resolve, reject) => {
        connection.insert(
            'storage_session', {
                'started_at' : mysqlCurrentDate()
            },
            (err, recordId) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(recordId);
                }
            }
        );
    });
}

async function saveFinishOfSessionStorage(connection, storageSessionId) {
    return new Promise((resolve, reject) => {
        connection.update(
            'storage_session',
            {
                'finished_at': mysqlCurrentDate()
            },
            {id: storageSessionId},
            (err, affectedRows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(affectedRows);
                }
            }
        );
    })
}

/** Robot **/

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

async function saveCurrentStorageToDb(page, formFrame, storageSessionId) {
    page.on('dialog', async dialog => {
        searchResultExists = false;
        await dialog.dismiss();
    });

    searchResultExists = true;
    log("Setup search params");
    let currentPage = 1;
    let nextPageExists = false;
    let condition;

    log(`Send search request, page ${currentPage}`);
    await formFrame.click(FORM_REQUEST_BUTTON_SELECTOR);
    await formFrame.waitFor(10000);
    log(`Sent search request, page ${currentPage}`);

    do {
        if (!searchResultExists) {
            log(`No search result, page ${currentPage}`);
            break;
        }

        let orderTable = await (await (await formFrame.$(ORDER_TABLE)).getProperty('outerHTML')).jsonValue();

        log(`Analyze page ${currentPage} start`);
        analyzeSearchResult(orderTable, storageSessionId, currentPage);
        log(`Analyze page ${currentPage} end`);

        if (currentPage === 1) {
            condition = await formFrame.$(FIRST_PAGE_NEXT_SELECTOR) !== null;
        } else {
            condition = await formFrame.$(NON_FIRST_PAGE_NEXT_SELECTOR) !== null;
        }
        if (condition) {
            nextPageExists = true;
            if (currentPage === 1) {
                await formFrame.click(FIRST_PAGE_NEXT_SELECTOR);
            } else {
                await formFrame.click(NON_FIRST_PAGE_NEXT_SELECTOR);
            }
            await formFrame.waitFor(5000);
            currentPage++;
            log(`Next page exists, switch to it, new page ${currentPage}`);
        } else {
            log("No next page exists, stop search");
            nextPageExists = false;
        }
    } while (nextPageExists);
}

function analyzeSearchResult(searchResult, storageSessionId, currentPage) {
    let $ordersTable = cheerio.load(searchResult);
    let storageItems = [];
    $ordersTable('tr').each(function (i) {
        let order = {};
        $ordersTable(this).find('td').each(function (j) {
            switch (j) {
                case 0:
                    break;
                case 1:
                    order.model = $ordersTable(this).text();
                    break;
                case 2:
                    order.manufacture_code = $ordersTable(this).text();
                    break;
                case 3:
                    order.description = $ordersTable(this).text();
                    break;
                case 4:
                    order.color_outside = $ordersTable(this).text();
                    break;
                case 5:
                    order.color_inside = $ordersTable(this).text();
                    break;
                case 6:
                    order.year = $ordersTable(this).text();
                    break;
                case 7:
                    order.storage_code = $ordersTable(this).text();
                    break;
                case 8:
                    order.available = $ordersTable(this).text();
                    break;
                case 9:
                    order.reserved = parseInt($ordersTable(this).text());
                    break;
            }
        });
        if (order.hasOwnProperty('available')) {
            storageItems.push(order);
        }
    });
    const foundSize = storageItems.length;
    log(`Found ${foundSize} items`);
    saveStorageItemsToDb(storageItems, storageSessionId, currentPage);
}

/** Main flow **/

const runner = async ({page, data: task}) => {
    let formFrame = await loginAndSwitchToFreeSklad(page, CREDS.username, CREDS.password);
    screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Вход в систему осуществлен'));
    await saveCurrentStorageToDb(page, formFrame, task.storageSessionId);
    screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Послали поисковый запрос'));
};

async function robot(storageSessionId) {
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
        timeout: 50 * 60000 //20 minutes
    });

    // Event handler to be called in case of problems
    cluster.on('taskerror', (err, data) => {
        console.log(`Error crawling `, data, `: ${err.message}`);
    });

    await cluster.queue({currentScreenshotPath: currentScreenshotPath, storageSessionId: storageSessionId}, runner);

    await cluster.idle();
    await cluster.close();

    await saveFinishOfSessionStorage(connection, storageSessionId);
}

async function run() {
    connection = await connectToDb();
    const storageSessionId = await saveStartOfSessionStorage();
    await robot(storageSessionId);
    await disconnectFromDb();
}

run();