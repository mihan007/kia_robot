const puppeteer = require('puppeteer');
const cheerio = require('cheerio');
const CREDS = require('./creds');
const mysql = require('mysql');
const fs = require('fs');
const mysqlUtilities = require('mysql-utilities');
const loginUrl = 'https://kmr.dealer-portal.net/irj/portal';
const USERNAME_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(3) > input[type="text"]';
const PASSWORD_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(3) > td:nth-child(3) > input[type="password"]';
const BUTTON_SELECTOR = '#logonForm > center > table > tbody > tr > td > table:nth-child(1) > tbody > tr:nth-child(2) > td:nth-child(2) > table > tbody > tr:nth-child(1) > td:nth-child(5) > a';
const SELL_TAB_SELECTOR = '#tabIndex1';
const FREE_SKLAD_LEFT_SIDEBAR_SELECTOR = '#L2N1';
const SCREENSHOT_PATH = '../php/web/screenshots';

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

run();

async function run() {
    const connection = await connectToDb();
    await robot(connection);
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


async function robot(connection) {

    let tasks = await getTasksFromDb(connection);
    console.log(tasks);
    return;

    let requestExist = true;

    let currentScreenshotPath = SCREENSHOT_PATH;
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }
    currentScreenshotPath = currentScreenshotPath + "/" + formattedDate(new Date());
    if (!fs.existsSync(currentScreenshotPath)) {
        fs.mkdirSync(currentScreenshotPath);
    }

    const browser = await puppeteer.launch({
        headless: true
    });
    const page = await browser.newPage();

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

    let formFrame, description, screenshots;

    for (const i in tasks) {
        screenshots = [];
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

        description = currentDate() + " начали выполнять задачу:<br>";
        description += "<ul>";
        description += "<li><b>Модель</b>: " + tasks[i].model_name + "</li>";
        description += "<li><b>Код производителя</b>: " + tasks[i].manufacture_code_name + "</li>";
        description += "<li><b>Цвет салона</b>: " + tasks[i].color_inside_name + "</li>";
        description += "<li><b>Цвет кузова</b>: " + tasks[i].color_outside_name + "</li>";
        description += "</ul>";
        let flag = true;
        let remainingAmount = tasks[i].amount;
        let totalOrdered = 0;
        let ind = 1;
        while (flag) {
            requestExist = true;

            await formFrame.select(FORM_MODEL_SELECTOR, tasks[i].model);
            await formFrame.waitFor(2000);
            await formFrame.select(FORM_MANUFACTURE_CODE_SELECTOR, tasks[i].manufacture_code);
            await formFrame.select(FORM_COLOR_INSIDE_SELECTOR, tasks[i].color_inside);
            await formFrame.select(FORM_COLOR_OUTSIDE_SELECTOR, tasks[i].color_outside);
            await formFrame.click(FORM_ONLY_AVAILABLE_SELECTOR);
            await formFrame.click(FORM_REQUEST_BUTTON_SELECTOR);
            await formFrame.waitFor(5000);

            let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
            let fullpath = currentScreenshotPath + "/" + filename;
            await page.screenshot({path: fullpath, fullPage: true});
            screenshots.push({name: 'Скриншот #' + (ind++) +'. Результат поискового запроса', filepath: fullpath});
            description += currentDate() + " послали поисковый запрос<br>";

            if (requestExist) {
                description += currentDate() + " требуемые авто найдены<br>";
                let ordersTableHtml = await (await (await formFrame.$(ORDER_TABLE)).getProperty('outerHTML')).jsonValue();
                let $ordersTable = cheerio.load(ordersTableHtml);
                let orders = [];
                let isDisabled = false;
                $ordersTable('tr').each(function (i) {
                    let order = {};
                    $ordersTable(this).find('td').each(function (j) {
                        if (j === 0) {
                            isDisabled = $ordersTable(this).find('input').is(':disabled');
                        }
                        if (!isDisabled) {
                            switch (j) {
                                case 0:
                                    //#board2 > tbody > tr:nth-child(7) > td:nth-child(1) > input[type="radio"]
                                    order.radioSelector = '#board2 > tbody > tr:nth-child(' + (i + 1) + ') > td:nth-child(1) > input[type="radio"]';
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
                                    order.available = parseInt($ordersTable(this).text());
                                    break;
                                case 9:
                                    order.reserved = parseInt($ordersTable(this).text());
                                    break;
                            }
                        }
                    });
                    if (order.hasOwnProperty('available')) {
                        orders.push(order);
                    }
                });

                let currentOrderAmount = 0;

                let currentRowAmount = orders[0].available;
                if (remainingAmount <= currentRowAmount) {
                    currentOrderAmount = remainingAmount;
                } else {
                    currentOrderAmount = currentRowAmount;
                }
                remainingAmount -= currentOrderAmount;
                totalOrdered += currentOrderAmount;

                await formFrame.click(orders[0].radioSelector);
                await formFrame.click(FORM_CHANGE_ORDER_BUTTON);
                const formAmount = await formFrame.$(FORM_AMOUNT_FIELD);
                await formAmount.click();
                await formAmount.focus();
                await formAmount.click({clickCount: 3});
                await formAmount.press('Backspace');
                await formAmount.type(currentOrderAmount.toString());

                description += currentDate() + " заказали " + currentOrderAmount + " авто с параметрами:<br>";
                description += "<ul>";
                description += "<li><b>Модель</b>: " + orders[0].model + "</li>";
                description += "<li><b>Код производителя</b>: " + orders[0].manufacture_code + "</li>";
                description += "<li><b>Описание</b>: " + orders[0].description + "</li>";
                description += "<li><b>Цвет салона</b>: " + orders[0].color_inside + "</li>";
                description += "<li><b>Цвет кузова</b>: " + orders[0].color_outside + "</li>";
                description += "<li><b>Год выпуска</b>: " + orders[0].year + "</li>";
                description += "<li><b>Код склада</b>: " + orders[0].storage_code + "</li>";
                description += "</ul>";

                let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
                let fullpath = currentScreenshotPath + "/" + filename;
                await page.screenshot({path: fullpath, fullPage: true});
                screenshots.push({name: 'Скриншот #' + (ind++)  + '. Результат выбора первого нужного набора авто', filepath: fullpath});

                await formFrame.click(ORDER_BUTTON);
                await formFrame.waitFor(5000);

                description += currentDate() + " всего авто с нужными параметрами заказано " + totalOrdered + " штук<br>";
                description += currentDate() + " осталось заказать " + remainingAmount + " штук<br>";

                if (remainingAmount <= 0) {
                    flag = false;
                    break;
                }
            } else {
                description += currentDate() + " требуемые авто не найдены<br>";
                flag = false;
            }
        }

        if (totalOrdered > 0) {
            await page.click(ORDER_FREE_SKLAD);
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

            await formFrame.click(ORDER_FREE_SKLAD_BUTTON);
            await formFrame.waitFor(5000);
            let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
            let fullpath = currentScreenshotPath + "/" + filename;
            await page.screenshot({path: fullpath, fullPage: true});
            screenshots.push({name: 'Скриншот #' + (ind++) +'. Результат заказа авто', filepath: fullpath});
        }

        tasks[i].task_id = tasks[i].id;
        tasks[i].description = description;
        let taskRunId = await saveTaskRunToDb(connection, tasks[i]);
        for (let iScr in screenshots) {
            screenshots[iScr].task_run_id = taskRunId;
            await saveScreenshotToDb(connection, screenshots[iScr]);
        }
    }

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

function getTasksFromDb(connection) {
    return new Promise((resolve, reject) => {
        connection.select(
            'task', '*',
            {deleted_at: 0},
            {},
            (err, results) => {
                if (err) {
                    reject(err);
                } else {
                    let tasks = [];
                    for (const i in results) {
                        let task = {
                            id: results[i].id,
                            model: results[i].model_value,
                            model_name: results[i].model_name,
                            manufacture_code: results[i].manufacture_code_value,
                            manufacture_code_name: results[i].manufacture_code_name,
                            color_inside: results[i].color_inside_value,
                            color_inside_name: results[i].color_inside_name,
                            color_outside: results[i].color_outside_value,
                            color_outside_name: results[i].color_outside_name,
                            amount: results[i].amount,
                        };
                        tasks.push(task);
                    }
                    resolve(tasks);
                }
            }
        );
    })
}

function saveTaskRunToDb(connection, taskInfo) {
    return new Promise((resolve, reject) => {
        connection.insert(
            'task_run', {
                task_id: taskInfo.task_id,
                model_name: taskInfo.model_name,
                model_value: taskInfo.model,
                manufacture_code_name: taskInfo.manufacture_code_name,
                manufacture_code_value: taskInfo.manufacture_code,
                color_inside_name: taskInfo.color_inside_name,
                color_inside_value: taskInfo.color_inside,
                color_outside_value: taskInfo.color_outside,
                color_outside_name: taskInfo.color_outside_name,
                amount: taskInfo.amount,
                description: taskInfo.description
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

function saveScreenshotToDb(connection, screenshot) {
    return new Promise((resolve, reject) => {
        connection.insert(
            'task_run_screenshot', {
                task_run_id: screenshot.task_run_id,
                name: screenshot.name,
                filepath: screenshot.filepath,
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

