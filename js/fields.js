const puppeteer = require('puppeteer');
const cheerio = require('cheerio');
const CREDS = require('./creds');
const mysql = require('mysql');
const mysqlUtilities = require('mysql-utilities');
const loginUrl = 'https://kmr.dealer-portal.net/irj/portal';
const USERNAME_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(3) > input[type="text"]';
const PASSWORD_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(3) > td:nth-child(3) > input[type="password"]';
const BUTTON_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(5) > a';
const SELL_TAB_SELECTOR = '#tabIndex1';
const FREE_SKLAD_LEFT_SIDEBAR_SELECTOR = '#L2N1';

const FREE_SKLAD_IFRAME_SELECTOR = '#contentAreaFrame';
const FREE_SKLAD_CONTENT_IFRAME = '#isolatedWorkArea';
const FORM_MODEL_SELECTOR = '#model';
const MANUFACTURE_CODE_SELECTOR = '#fsc';
const COLOR_INSIDE_SELECTOR = '#intcolor';
const COLOR_OUTSIDE_SELECTOR = '#extcolor';

run();

async function run() {
    const connection = await connectToDb();
    await sync(connection);
}

function delay(timeout) {
    return new Promise((resolve) => {
        setTimeout(resolve, timeout);
    });
}

async function sync(connection) {
    const browser = await puppeteer.launch({
        headless: false
    });
    const page = await browser.newPage();

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

    await page.click(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);

    await page.waitFor(FREE_SKLAD_IFRAME_SELECTOR);

    let firstFrame = await page.frames().find(f => f.name() === 'contentAreaFrame');

    await firstFrame.waitFor(FREE_SKLAD_CONTENT_IFRAME);
    await firstFrame.waitFor(2000);

    let formFrame;
    for (const secondFrame of firstFrame.childFrames()) {
        const modelField = await secondFrame.$(FORM_MODEL_SELECTOR);
        if (modelField) {
            formFrame = secondFrame
        }
    }

    let innerHtml = await (await (await formFrame.$(FORM_MODEL_SELECTOR)).getProperty('innerHTML')).jsonValue();
    let $ = cheerio.load(innerHtml);

    let models = [];
    await cleanUpTable(connection, 'color_inside');
    await cleanUpTable(connection, 'color_outside');
    await cleanUpTable(connection, 'manufacture_code');
    await cleanUpTable(connection, 'model');
    $('option').each(function () {
        let value = $(this).attr('value');
        let model = {name: $(this).text(), value: value};
        models.push(model);
    });
    console.log(models);

    for (const i in models) {
        models[i].id = await saveOptionToDb(connection, 'model', models[i]);
        console.log('Processing ', models[i]);
        await formFrame.select(FORM_MODEL_SELECTOR, models[i].value);
        await formFrame.waitFor(2000);

        let innerManufactureHtml = await (await (await formFrame.$(MANUFACTURE_CODE_SELECTOR)).getProperty('innerHTML')).jsonValue();
        let $manufacture = cheerio.load(innerManufactureHtml);
        let manufactures = [];
        $manufacture('option').each(function () {
            let manufacture = {
                name: $(this).text(),
                value: $(this).attr('value'),
                model_id: models[i].id
            };
            manufactures.push(manufacture);
        });

        for (const j in manufactures) {
            await saveOptionToDb(connection, 'manufacture_code', manufactures[j]);
        }

        let innerInsideColorHtml = await (await (await formFrame.$(COLOR_INSIDE_SELECTOR)).getProperty('innerHTML')).jsonValue();
        let $insideColor = cheerio.load(innerInsideColorHtml);
        let insideColors = [];
        $insideColor('option').each(function () {
            let insideColor = {
                name: $(this).text(),
                value: $(this).attr('value'),
                model_id: models[i].id
            };
            insideColors.push(insideColor);
        });

        for (const j in insideColors) {
            await saveOptionToDb(connection, 'color_inside', insideColors[j]);
        }

        let innerOutsideColorHtml = await (await (await formFrame.$(COLOR_OUTSIDE_SELECTOR)).getProperty('innerHTML')).jsonValue();
        let $outsideColor = cheerio.load(innerOutsideColorHtml);
        let outsideColors = [];
        $outsideColor('option').each(function () {
            let outsideColor = {
                name: $(this).text(),
                value: $(this).attr('value'),
                model_id: models[i].id
            };
            outsideColors.push(outsideColor);
        });

        for (const j in outsideColors) {
            await saveOptionToDb(connection, 'color_outside', outsideColors[j]);
        }
    }

    console.log('End saving');
    browser.close();
    disconnectFromDb(connection);
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

function cleanUpTable(connection, tableName) {
    return new Promise((resolve, reject) => {
        connection.delete(
            tableName,
            {id: '>0'},
            (err, affectedRows) => {
                if (err) {
                    reject(err);
                }
                connection.query('ALTER TABLE ' + tableName + ' AUTO_INCREMENT = 1', function () {
                    resolve(affectedRows);
                });
            }
        )
    })
}

function saveOptionToDb(connection, tableName, option, callback) {
    return new Promise((resolve, reject) => {
        connection.insert(
            tableName, option,
            (err, recordId) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(recordId)
                }
            }
        );
    })
}

