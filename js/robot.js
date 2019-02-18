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

const MAX_CONCURRENCY = CREDS.maxConcurrency;

let bannedCompaniesIds = [];

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

async function getTasksFromDb(connection) {
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
                            remain: results[i].amount,
                            more_auto: (results[i].more_auto === 1),
                            searchDialogHandled: false,
                            company_id: results[i].company_id,
                            user_id: results[i].user_id,
                            goal: results[i].goal
                        };
                        tasks.push(task);
                    }
                    resolve(tasks);
                }
            }
        );
    })
}

async function checkIfCompanyBanned(connection, company_id) {
    return new Promise((resolve, reject) => {
        connection.select(
            'company', '*',
            {'id': company_id},
            (err, results) => {
                if (err) {
                    reject(err);
                } else {
                    for (const i in results) {
                        if (results[i].banned_at > 0) {
                            resolve(true);
                        } else {
                            resolve(false);
                        }
                    }
                }
            }
        );
    })
}

async function markCompanyAsBanned(connection, company_id) {
    bannedCompaniesIds.push(company_id);
    return new Promise((resolve, reject) => {
        connection.update(
            'company',
            {'banned_at': Math.floor(new Date() / 1000)},
            {id: company_id},
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

async function getColorPreferences(connection, company_id) {
    return new Promise((resolve, reject) => {
        connection.select(
            'color_preferences', '*',
            {company_id: company_id},
            {},
            (err, results) => {
                if (err) {
                    reject(err);
                } else {
                    let preferences = [];
                    for (const i in results) {
                        preferences[results[i].model_value] = results[i].colors.split(',');
                    }
                    resolve(preferences);
                }
            }
        );
    })
}

async function getCredentials(connection, company_id) {
    return new Promise((resolve, reject) => {
        connection.select(
            'company', '*',
            {id: company_id},
            {},
            (err, results) => {
                if (err) {
                    reject(err);
                } else {
                    let result = {};
                    for (const i in results) {
                        result.login = results[i].kia_login;
                        result.password = results[i].kia_password;
                    }
                    resolve(result);
                }
            }
        );
    })
}

async function saveTaskRunToDb(connection, taskInfo) {
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
                description: taskInfo.description,
                amount_ordered: taskInfo.amount_ordered,
                ordered_manufacture_codes: taskInfo.ordered_manufacture_codes,
                company_id: taskInfo.company_id,
                user_id: taskInfo.user_id
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

async function saveScreenshotToDb(connection, screenshot) {
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

function isSimpleTask(task, colorPreferences) {
    let colorOutside = task.color_outside;
    let currentColorPreferences = (typeof colorPreferences[task.model] !== 'undefined') ? colorPreferences[task.model] : false;
    if (colorOutside.length) {
        return true;
    }
    if (currentColorPreferences !== false) {
        return currentColorPreferences.length === 0;
    }

    return true;
}

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
    try {
        await page.waitFor(SELL_TAB_SELECTOR, {timeout: 5000});
    } catch (e) {
        return false;
    }
    log("Logged in");
    log("Switching to free sklad search page");
    await page.click(SELL_TAB_SELECTOR);
    await page.waitFor(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);

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
    log("Switched to free sklad search page");
    return formFrame;
}

function logInfoAboutSearch(task, isFirstStage) {
    let description;
    if (isFirstStage) {
        description = currentDate() + " начали выполнять сложную задачу(этап 1, идет анализ наличия цветов):<br>";
    } else {
        description = currentDate() + " выполняем сложную задачу(этап 2, заказываем авто):<br>";
    }
    description += "<ul>";
    description += "<li><b>Модель</b>: " + task.model_name + "</li>";
    description += "<li><b>Код производителя</b>: " + task.manufacture_code_name + "</li>";
    description += "<li><b>Цвет салона</b>: " + task.color_inside_name + "</li>";
    description += "<li><b>Цвет кузова</b>: " + task.color_outside + "</li>";
    description += "<li><b>Требуемое количество</b>: " + task.amount + "</li>";
    description += "</ul>";
    if (isFirstStage) {
        description += "Собираем информацию о доступных цветах кузова<br>";
    }
    description += currentDate() + " послали поисковый запрос<br>";
    return description;
}

async function sendSearchRequest(page, formFrame, task, additionalDescription) {
    if (!task.searchDialogHandled) {
        page.on('dialog', async dialog => {
            task.searchResultExists = false;
            await dialog.dismiss();
        });
        task.searchDialogHandled = true;
    }

    task.searchResultExists = true;
    log("Setup search params");
    let result = await formFrame.select(FORM_MODEL_SELECTOR, task.model);
    log("Task model: " + task.model);
    await formFrame.waitFor(2000);
    if ((task.goal == 0) && (result.length === 0)) {
        log("Could not setup task.model: " + task.model);
        additionalDescription = `В фильтре не найдена модель ${task.model}`;
        return false;
    }
    const manufactureCode = task.more_auto ? '' : task.manufacture_code;
    const manufactureCodeDescription = task.more_auto ? task.manufacture_code + ' and other' : task.manufacture_code;
    result = await formFrame.select(FORM_MANUFACTURE_CODE_SELECTOR, manufactureCode);
    log("Manufacture code: " + manufactureCodeDescription);
    if ((task.goal == 0) && (result.length === 0)) {
        log("Could not setup task.manufacture_code: " + task.manufacture_code);
        additionalDescription = `В фильтре не найден код модели ${task.manufacture_code}`;
        return false;
    }
    result = await formFrame.select(FORM_COLOR_INSIDE_SELECTOR, task.color_inside);
    log("Color inside: " + task.color_inside);
    if ((task.goal == 0) && (result.length === 0)) {
        log("Could not setup task.color_inside: " + task.color_inside);
        additionalDescription = `В фильтре не найден цвет салона ${task.color_inside}`;
        return false;
    }
    result = await formFrame.select(FORM_COLOR_OUTSIDE_SELECTOR, task.color_outside);
    const colorOutsideDescription = task.color_outside.length ? task.color_outside : 'all';
    log("Color outside: " + colorOutsideDescription);
    if ((task.goal == 0) && (result.length === 0)) {
        log("Could not setup task.color_outside: " + task.color_outside);
        additionalDescription = `В фильтре не найден цвет кузова ${task.color_outside}`;
        return false;
    }
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

    return task.searchResultExists;
}

function makeUniqueScreenshotName() {
    return +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
}

async function saveScreenshot(currentScreenshotPath, page, name) {
    let fullpath = currentScreenshotPath + "/" + makeUniqueScreenshotName();
    await page.screenshot({path: fullpath, fullPage: true});
    return {
        name: name,
        filepath: fullpath
    };
}

async function orderTask(page, formFrame, task, description, manufactureCodes, screenshots) {
    let orderMore = true;
    let currentPage = 1;

    do {
        let additionalInfo = '';
        let searchResultExists = await sendSearchRequest(page, formFrame, task, additionalInfo);
        screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Результат поиска нужного авто. ' + additionalInfo));
        if (!searchResultExists) {
            log("No search result");
            break;
        }

        log("Search result exists");
        description += currentDate() + " Страница " + currentPage + "<br>";
        let orderTable = await formFrame.$(ORDER_TABLE);
        let outerHtmlOrderTable = await orderTable.getProperty('outerHTML');
        let ordersTableHtml = await outerHtmlOrderTable.jsonValue();
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

        let chosen = 0;
        if (manufactureCodes === false) {
            chosen = 0;
            orderMore = true;
        } else {
            orderMore = false; //мы будем заказывать по 1 авто каждого цвета, ротируя цвета
            chosen = false;
            for (let i in orders) {
                if (!manufactureCodes.includes(orders[i].manufacture_code)) {
                    chosen = i;
                    break;
                }
            }
        }
        log("Current chosen row: " + chosen);

        if (chosen !== false) {
            log("Ordering using " + chosen + " row radio selector, manufacture_code: " + orders[chosen].manufacture_code);
            await formFrame.click(orders[chosen].radioSelector);

            screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Результат выбора нужного авто'));

            task.reachedMaximum = false;
            await formFrame.click(FORM_CHANGE_ORDER_BUTTON);
            if (task.searchResultExists === false) {
                description += currentDate() + " Достигнут максимум заказа авто<br>";
                log("Reached maximum cars");
                orderMore = false;
                task.reachedMaximum = true;
            } else {
                log("Ordered using " + chosen + " row radio selector, manufacture_code: " + orders[chosen].manufacture_code);

                screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, 'Окно заказа нужной комплектации авто'));

                await formFrame.click(ORDER_BUTTON);
                await formFrame.waitFor(5000);

                if (manufactureCodes !== false) {
                    manufactureCodes.push(orders[chosen].manufacture_code);
                }
                description += currentDate() + " Требуемые авто с кодом производителя " + orders[chosen].manufacture_code + " найдены и заказаны<br>";

                task.remain -= 1;
                task.orderedManufactureCode = orders[chosen].manufacture_code;
                log("remainingAmount: " + task.remain);
            }
        } else {
            task.orderedManufactureCode = false;
            log("Could not find suitable auto at page " + currentPage);
            description += currentDate() + " Требуемые авто на странице " + currentPage + " не найдены<br>";
            let condition = false;
            if (currentPage === 1) {
                condition = await formFrame.$(FIRST_PAGE_NEXT_SELECTOR) !== null;
            } else {
                condition = await formFrame.$(NON_FIRST_PAGE_NEXT_SELECTOR) !== null;
            }
            if (condition) {
                log("Next page exists, switch to it");
                description += currentDate() + " Есть следующая страница, идем на нее<br>";
                orderMore = true;
                if (currentPage === 1) {
                    await formFrame.click(FIRST_PAGE_NEXT_SELECTOR);
                } else {
                    await formFrame.click(NON_FIRST_PAGE_NEXT_SELECTOR);
                }
                await formFrame.waitFor(2000);
                currentPage++;
            } else {
                log("No next page exists, stop search");
                description += currentDate() + " Следующей страницы нет, прекращаем поиски<br>";
                orderMore = false;
            }
        }

        if (task.remain <= 0) {
            log('We have ordered enough, stop');
            description += currentDate() + " Заказали достаточно, останавливаемся<br>";
            orderMore = false;
        }
    } while (orderMore);

    return task;
}

async function switchToFreeSkladAndSaveScreenshot(page, formFrame, screenshots, task) {
    await page.click(ORDER_FREE_SKLAD);
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

    await formFrame.click(ORDER_FREE_SKLAD_BUTTON);
    await formFrame.waitFor(5000);
    const name = 'Результат заказа авто';
    screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, name));
    return formFrame;
}

/**
 * Задача - поиск автомобилей с жестко заданным цветом кузова, либо с моделью у которой нет предпочтений в наборе цветов
 * @param page
 * @param task
 * @returns {Promise<void>}
 */
const processSimpleTask = async ({page, data: task}) => {
    log(`Running task ${task.id}`);
    if (bannedCompaniesIds.includes(task.company_id)) {
        log(`Stop executing ${task.id} because company ${task.company_id} marked as banned`);
    }

    if (!task.searchDialogHandled) {
        page.on('dialog', async dialog => {
            task.searchResultExists = false;
            await dialog.dismiss();
        });
        task.searchDialogHandled = true;
    }

    await page.setViewport({width: 1280, height: 800});

    log("Start logging in");
    await page.goto(loginUrl);
    await page.click(USERNAME_SELECTOR);
    await page.keyboard.type(task.credentials.login);
    await page.click(PASSWORD_SELECTOR);
    await page.keyboard.type(task.credentials.password);
    await page.click(LOGIN_BUTTON_SELECTOR);
    try {
        await page.waitFor(SELL_TAB_SELECTOR, {timeout: 5000});
    } catch (e) {
        log(`Could not log in, mark company ${task.company_id} as banned`);
        await markCompanyAsBanned(task.connection, task.company_id);
        return;
    }
    log("Logged in");
    log("Switching to free sklad search page");
    await page.click(SELL_TAB_SELECTOR);
    await page.waitFor(FREE_SKLAD_LEFT_SIDEBAR_SELECTOR);

    let formFrame, description;
    let screenshots = [];

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
    log("Switched to free sklad search page");

    description = currentDate() + " начали выполнять задачу:<br>";
    description += "<ul>";
    description += "<li><b>Модель</b>: " + task.model_name + "</li>";
    description += "<li><b>Код производителя</b>: " + task.manufacture_code_name + "</li>";
    description += "<li><b>Цвет салона</b>: " + task.color_inside_name + "</li>";
    description += "<li><b>Цвет кузова</b>: " + task.color_outside_name + "</li>";
    description += "<li><b>Требуемое количество</b>: " + task.amount + "</li>";
    description += "</ul>";
    let flag = true;
    let remainingAmount = task.amount;
    let totalOrdered = 0;
    let ind = 1;
    /**
     * 0 - ищем строго по данным из фильтра для параметра "Код производителя"
     * 1 - ищем с параметром "Код производителя"="Все" (этот параметр жестко задан при создании задачи)
     * @type {number}
     */
    let stage;
    if (task.manufacture_code.length > 0) {
        stage = 0;
    } else {
        stage = 1;
    }
    let orderedManufactureCodes = [];
    let additionalDescription = '';
    while (flag) {
        let result = [];
        task.searchResultExists = true;
        log("Setup search params");
        result = await formFrame.select(FORM_MODEL_SELECTOR, task.model);
        log("Task model: " + task.model);
        if ((task.goal == 0) && (result.length === 0)) {
            log("Could not setup task.model: " + task.model);
            additionalDescription = `В фильтре не найдена модель ${task.model}`;
            break;
        }
        await formFrame.waitFor(2000);
        const manufactureCode = (stage < 1) ? task.manufacture_code : '';
        result = await formFrame.select(FORM_MANUFACTURE_CODE_SELECTOR, manufactureCode);
        log("Manufacture code: " + manufactureCode);
        if ((task.goal == 0) && (result.length === 0)) {
            log("Could not setup task.manufacture_code: " + manufactureCode);
            additionalDescription = `В фильтре не найден код производителя ${manufactureCode}`;
            break;
        }
        result = await formFrame.select(FORM_COLOR_INSIDE_SELECTOR, task.color_inside);
        log("Color inside: " + task.color_inside);
        if ((task.goal == 0) && (result.length === 0)) {
            log("Could not setup task.color_inside: " + task.color_inside);
            additionalDescription = `В фильтре не найден цвет салона ${task.color_inside}`;
            break;
        }
        result = await formFrame.select(FORM_COLOR_OUTSIDE_SELECTOR, task.color_outside);
        log("Color outside: " + task.color_outside);
        if ((task.goal == 0) && (result.length === 0)) {
            log("Could not setup task.color_outside: " + task.color_outside);
            additionalDescription = `В фильтре не найден цвет кузова ${task.color_outside}`;
            break;
        }
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

        let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
        let fullpath = task.currentScreenshotPath + "/" + filename;
        await page.screenshot({path: fullpath, fullPage: true});
        screenshots.push({name: 'Скриншот #' + (ind++) + '. Результат поискового запроса', filepath: fullpath});
        description += currentDate() + " послали поисковый запрос<br>";

        if (task.searchResultExists) {
            log("Search result exists");
            let pageCount = 1;
            let badManufactureCode = false;
            let chosen;
            let orders;
            const currentOrderAmount = 1;
            do {
                log("Look through page " + pageCount);
                description += currentDate() + " Страница " + pageCount + "<br>";
                let orderTable = await formFrame.$(ORDER_TABLE);
                let outerHtmlOrderTable = await orderTable.getProperty('outerHTML');
                let ordersTableHtml = await outerHtmlOrderTable.jsonValue();
                let $ordersTable = cheerio.load(ordersTableHtml);
                orders = [];
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
                log("Rows at searct result table: " + orders.length);

                if (stage === 0) {
                    chosen = 0;
                } else {
                    chosen = false;
                    for (let i in orders) {
                        if (!orderedManufactureCodes.includes(orders[i].manufacture_code)) {
                            chosen = i;
                            break;
                        }
                    }
                }
                log("Current chosen row: " + chosen);

                if (chosen !== false) {
                    log("Ordering using " + chosen + " row radio selector, manufacture_code: " + orders[chosen].manufacture_code);
                    await formFrame.click(orders[chosen].radioSelector);
                    task.reachedMaximum = false;
                    await formFrame.click(FORM_CHANGE_ORDER_BUTTON);
                    if (task.searchResultExists === false) {
                        description += currentDate() + " Достигнут максимум заказа авто<br>";
                        log("Reached maximum cars");
                        badManufactureCode = false;
                        flag = false;
                        task.reachedMaximum = true;
                    } else {
                        log("Ordered using " + chosen + " row radio selector, manufacture_code: " + orders[chosen].manufacture_code);

                        orderedManufactureCodes.push(orders[chosen].manufacture_code);
                        badManufactureCode = false;
                        description += currentDate() + " Требуемые авто с кодом производителя " + orders[chosen].manufacture_code + " найдены<br>";

                        remainingAmount -= currentOrderAmount;
                        totalOrdered += currentOrderAmount;
                        log("remainingAmount: " + remainingAmount);
                        log("totalOrdered: " + totalOrdered)
                    }
                } else {
                    log("Could not find suitable auto at page " + pageCount);
                    description += currentDate() + " Требуемые авто на странице " + pageCount + " не найдены<br>";
                    let condition = false;
                    if (pageCount === 1) {
                        condition = await formFrame.$(FIRST_PAGE_NEXT_SELECTOR) !== null;
                    } else {
                        condition = await formFrame.$(NON_FIRST_PAGE_NEXT_SELECTOR) !== null;
                    }
                    if (condition) {
                        log("Next page exists, switch to it");
                        description += currentDate() + " Есть следующая страница, идем на нее<br>";
                        badManufactureCode = true;
                        if (pageCount === 1) {
                            await formFrame.click(FIRST_PAGE_NEXT_SELECTOR);
                        } else {
                            await formFrame.click(NON_FIRST_PAGE_NEXT_SELECTOR);
                        }
                        await formFrame.waitFor(2000);
                        pageCount++;
                    } else {
                        log("No next page exists, stop search");
                        description += currentDate() + " Следующей страницы нет, прекращаем поиски<br>";
                        badManufactureCode = false;
                    }
                }
            } while (badManufactureCode);

            if (task.reachedMaximum) {
                flag = false;
            } else if (chosen !== false) {
                description += currentDate() + " заказали " + currentOrderAmount + " авто с параметрами:<br>";
                description += "<ul>";
                description += "<li><b>Модель</b>: " + orders[chosen].model + "</li>";
                description += "<li><b>Код производителя</b>: " + orders[chosen].manufacture_code + "</li>";
                description += "<li><b>Описание</b>: " + orders[chosen].description + "</li>";
                description += "<li><b>Цвет салона</b>: " + orders[chosen].color_inside + "</li>";
                description += "<li><b>Цвет кузова</b>: " + orders[chosen].color_outside + "</li>";
                description += "<li><b>Год выпуска</b>: " + orders[chosen].year + "</li>";
                description += "<li><b>Код склада</b>: " + orders[chosen].storage_code + "</li>";
                description += "</ul>";

                let filename = +new Date() + '_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15) + '.png';
                let fullpath = task.currentScreenshotPath + "/" + filename;
                await page.screenshot({path: fullpath, fullPage: true});
                screenshots.push({
                    name: 'Скриншот #' + (ind++) + '. Результат выбора нужного набора авто',
                    filepath: fullpath
                });

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
                if (task.more_auto && stage < 1) {
                    stage++;
                    flag = true;
                } else {
                    flag = false;
                }
            }
        } else {
            log("Search result does not exists");
            description += currentDate() + " требуемые авто не найдены<br>";
            if (task.more_auto && stage < 1) {
                log("More auto mode enabled, go to next stage");
                stage++;
                flag = true;
            } else {
                log("Search done");
                flag = false;
            }
        }
    }

    if (totalOrdered > 0) {
        log("So we have ordered: " + totalOrdered);
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
        let fullpath = task.currentScreenshotPath + "/" + filename;
        await page.screenshot({path: fullpath, fullPage: true});
        screenshots.push({name: 'Скриншот #' + (ind++) + '. Результат заказа авто', filepath: fullpath});
    } else {
        log("So we have ordered nothing");
    }
    if (additionalDescription.length > 0) {
        description += currentDate() + " " + additionalDescription + "<br>";
    }

    task.task_id = task.id;
    task.description = description;
    task.amount_ordered = totalOrdered;
    task.ordered_manufacture_codes = orderedManufactureCodes.join(",");
    let taskRunId = await saveTaskRunToDb(task.connection, task);

    for (let iScr in screenshots) {
        screenshots[iScr].task_run_id = taskRunId;
        await saveScreenshotToDb(task.connection, screenshots[iScr]);
    }
};

function cleanCodes(specificManufactureCodeQueue) {
    for (let colorCode in specificManufactureCodeQueue) {
        if (specificManufactureCodeQueue.hasOwnProperty(colorCode)) {
            if (specificManufactureCodeQueue[colorCode] === 0) {
                delete specificManufactureCodeQueue[colorCode];
            }
        }
    }
}

/**
 * Задача - поиск автомобилей с разными цветами кузова, отобранными по приоритетам
 * @param page
 * @param task
 * @returns {Promise<void>}
 */
const processComplexTask = async ({page, data: task}) => {
    log(`Running task ${task.id}`);
    if (bannedCompaniesIds.includes(task.company_id)) {
        log(`Stop executing ${task.id} because company ${task.company_id} marked as banned`);
    }

    let screenshots = [];

    let formFrame = await loginAndSwitchToFreeSklad(page, task.credentials.login, task.credentials.password);
    if (formFrame === false) {
        log(`Could not log in, mark company ${task.company_id} as banned`);
        await markCompanyAsBanned(task.connection, task.company_id);
        return;
    }
    let description = logInfoAboutSearch(task, true);

    task.color_outside = '';
    let additionalDescription = '';
    let searchResultExists = await sendSearchRequest(page, formFrame, task, additionalDescription);
    let currentPage = 1;
    let pageCount = 0;
    let paginationSelector = await formFrame.$(PAGING_SELECTOR);
    let paginationSelectorOuterHtml = await paginationSelector.getProperty('outerHTML');
    let paginationSelectorHtml = await paginationSelectorOuterHtml.jsonValue();
    let $paginationSelector = cheerio.load(paginationSelectorHtml);
    $paginationSelector('option').each(function (el) {
        let currentPageOption = parseInt(this.attribs['value']);
        if (currentPageOption > pageCount) {
            pageCount = currentPageOption;
        }
    });

    const name = `Результат поискового запроса, страница ${currentPage} из ${pageCount}`;
    screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, name));

    let specificManufactureCodeQueue = [];
    let commonManufactureCodeQueue = [];

    if (searchResultExists) {
        log("Search result exists");
        let orders;
        let nextPageExists;
        for (let i in task.colorPreferences) {
            specificManufactureCodeQueue[task.colorPreferences[i]] = 0;
            commonManufactureCodeQueue[task.colorPreferences[i]] = 0;
        }
        do {
            let currentPageSpecificColors = [];
            let currentPageCommonColors = [];
            for (let i in task.colorPreferences) {
                currentPageSpecificColors[task.colorPreferences[i]] = 0;
                currentPageCommonColors[task.colorPreferences[i]] = 0;
            }
            log("Look through page " + currentPage);
            description += currentDate() + " Страница " + currentPage + "<br>";
            let orderTable = await formFrame.$(ORDER_TABLE);
            let outerHtmlOrderTable = await orderTable.getProperty('outerHTML');
            let ordersTableHtml = await outerHtmlOrderTable.jsonValue();
            let $ordersTable = cheerio.load(ordersTableHtml);
            orders = [];
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
            for (let i in orders) {
                if (orders[i].manufacture_code === task.manufacture_code) {
                    if (typeof specificManufactureCodeQueue[orders[i].color_outside] !== 'undefined') {
                        specificManufactureCodeQueue[orders[i].color_outside]++;
                    }
                    if (typeof currentPageSpecificColors[orders[i].color_outside] !== 'undefined') {
                        currentPageSpecificColors[orders[i].color_outside]++;
                    }
                } else {
                    if (typeof commonManufactureCodeQueue[orders[i].color_outside] !== 'undefined') {
                        commonManufactureCodeQueue[orders[i].color_outside]++;
                    }
                    if (typeof currentPageCommonColors[orders[i].color_outside] !== 'undefined') {
                        currentPageCommonColors[orders[i].color_outside]++;
                    }
                }
            }
            for (let colorCode in currentPageSpecificColors) {
                if (!currentPageSpecificColors[colorCode]) {
                    continue;
                }
                if (currentPageSpecificColors.hasOwnProperty(colorCode)) {
                    description += currentDate() + " на странице " + currentPage + " для кода модели "
                        + task.manufacture_code + " нашли цвет " + colorCode + " в количестве "
                        + currentPageSpecificColors[colorCode] + " штук<br>";
                }
            }
            for (let colorCode in currentPageCommonColors) {
                if (!currentPageCommonColors[colorCode]) {
                    continue;
                }
                if (currentPageCommonColors.hasOwnProperty(colorCode)) {
                    description += currentDate() + " на странице " + currentPage + " для произвольного кода модели"
                        + " нашли цвет " + colorCode + " в количестве "
                        + currentPageCommonColors[colorCode] + " штук<br>";
                }
            }
            log("Rows at search result table: " + orders.length);
            let condition = false;
            if (currentPage === 1) {
                condition = await formFrame.$(FIRST_PAGE_NEXT_SELECTOR) !== null;
            } else {
                condition = await formFrame.$(NON_FIRST_PAGE_NEXT_SELECTOR) !== null;
            }
            if (condition) {
                log("Next page exists, switch to it");
                description += currentDate() + " Есть следующая страница, идем на нее<br>";
                nextPageExists = true;
                if (currentPage === 1) {
                    await formFrame.click(FIRST_PAGE_NEXT_SELECTOR);
                } else {
                    await formFrame.click(NON_FIRST_PAGE_NEXT_SELECTOR);
                }
                await formFrame.waitFor(2000);
                currentPage++;
                const name = `Результат поискового запроса, страница ${currentPage} из ${pageCount}`;
                screenshots.push(await saveScreenshot(task.currentScreenshotPath, page, name));
            } else {
                log("No next page exists, stop search");
                description += currentDate() + " Следующей страницы нет, прекращаем поиски<br>";
                nextPageExists = false;
            }
        } while (nextPageExists);
    } else {
        log("Search result does not exists");
        if (additionalDescription.length > 0) {
            description += currentDate() + " " + additionalDescription + "<br>";
        }
        description += currentDate() + " требуемые авто не найдены<br>";
    }

    cleanCodes(specificManufactureCodeQueue);
    cleanCodes(commonManufactureCodeQueue);

    let totalOrdered = 0;
    for (let colorCode in specificManufactureCodeQueue) {
        if (specificManufactureCodeQueue.hasOwnProperty(colorCode)) {
            log("Order task with manufacture_code = " + task.manufacture_code + " and color = " + colorCode);
            description += currentDate() + " Сперва заказываем согласно коду производителя из задачи: " + task.manufacture_code + " и приоритетный цвет: " + colorCode + "<br>";
            task.color_outside = colorCode;
            let remainBefore = task.remain;
            description += logInfoAboutSearch(task, false);
            task = await orderTask(page, formFrame, task, description, false, screenshots);
            if (task.reachedMaximum) {
                break;
            }
            let remainAfter = task.remain;
            let ordered = remainBefore - remainAfter;
            totalOrdered += ordered;
            description += currentDate() + " на данном этапе заказано: " + ordered + " штук, всего заказано: " + totalOrdered + ", осталось: " + task.remain + "<br>";
            if (task.remain <= 0) {
                break;
            }
        }
    }

    let orderedManufactureCodesByTaskRun = [];

    let orderedForCycle;
    do {
        orderedForCycle = 0;
        for (let colorCode in commonManufactureCodeQueue) {
            if (commonManufactureCodeQueue.hasOwnProperty(colorCode)) {
                let manufactureCodes = task.alreadyOrderedManufactureCodes;
                log("Order task with any manufacture_code and color " + colorCode);
                description += currentDate() + " Теперь заказываем произвольный код производителя и приоритетный цвет: " + colorCode + "<br>";
                task.manufacture_code = "";
                task.color_outside = colorCode;
                let remainBefore = task.remain;
                description += logInfoAboutSearch(task, false);
                task = await orderTask(page, formFrame, task, description, manufactureCodes, screenshots);
                if (task.reachedMaximum) {
                    break;
                }
                let remainAfter = task.remain;
                let ordered = remainBefore - remainAfter;
                totalOrdered += ordered;
                orderedForCycle += ordered;
                if (task.orderedManufactureCode !== false) {
                    orderedManufactureCodesByTaskRun.push(task.orderedManufactureCode);
                }
                description += currentDate() + " на данном этапе заказано: " + ordered + " штук, всего заказано: " + totalOrdered + ", осталось: " + task.remain + "<br>";
                if (task.remain <= 0) {
                    break;
                }
            }
        }
        if (task.reachedMaximum) {
            break;
        }
    } while (orderedForCycle > 0);

    if (totalOrdered > 0) {
        log("So we have ordered: " + totalOrdered);
        formFrame = await switchToFreeSkladAndSaveScreenshot(page, formFrame, screenshots, task);
    } else {
        log("So we have ordered nothing");
    }

    task.task_id = task.id;
    task.description = description;
    task.amount_ordered = totalOrdered;
    task.ordered_manufacture_codes = orderedManufactureCodesByTaskRun.join(",");
    let taskRunId = await saveTaskRunToDb(task.connection, task);

    for (let iScr in screenshots) {
        screenshots[iScr].task_run_id = taskRunId;
        await saveScreenshotToDb(task.connection, screenshots[iScr]);
    }
};

async function getOrderedCars(connection, task_id) {
    return new Promise((resolve, reject) => {
        connection.select(
            'task_run', '*',
            {task_id: task_id},
            {},
            (err, results) => {
                if (err) {
                    reject(err);
                } else {
                    let ordered = 0;
                    let alreadyOrderedManufactureCodes = [];
                    for (const i in results) {
                        ordered += results[i].amount_ordered;
                        let alreadyOrdered;
                        if (results[i].ordered_manufacture_codes !== null) {
                            alreadyOrdered = results[i].ordered_manufacture_codes.split(',');
                        } else {
                            alreadyOrdered = [];
                        }
                        alreadyOrderedManufactureCodes.push(...alreadyOrdered);
                    }
                    resolve({
                        amount: ordered,
                        alreadyOrderedManufactureCodes: alreadyOrderedManufactureCodes
                    });
                }
            }
        );
    })
}

async function filterTasks(connection, allTasks) {
    let result = [];
    let bannedCompanies = [];
    let nonBannedCompanies = [];
    for (let i in allTasks) {
        if (bannedCompanies.includes(allTasks[i].company_id)) {
            log(`Skip task ${allTasks[i].id} because company ${allTasks[i].company_id} marked as banned`);
            continue;
        } else if (!nonBannedCompanies.includes(allTasks[i].company_id)) {
            log(`For task ${allTasks[i].id} check if company ${allTasks[i].company_id} is banned`);
            const isBanned = await checkIfCompanyBanned(connection, allTasks[i].company_id);
            if (isBanned) {
                log(`Skip task ${allTasks[i].id} because company ${allTasks[i].company_id} marked as banned`);
                bannedCompanies.push(allTasks[i].company_id);
                continue;
            } else {
                log(`Processing task ${allTasks[i].id} because company ${allTasks[i].company_id} marked as non banned`);
                nonBannedCompanies.push(allTasks[i].company_id)
            }
        } else {
            log(`Processing task ${allTasks[i].id} because company ${allTasks[i].company_id} marked as non banned`);
        }

        let orderedCars = await getOrderedCars(connection, allTasks[i].id);
        if (allTasks[i].amount > orderedCars.amount) {
            let newTask = allTasks[i];
            newTask.amount = allTasks[i].amount - orderedCars.amount;
            newTask.remain = newTask.amount;
            newTask.alreadyOrderedManufactureCodes = orderedCars.alreadyOrderedManufactureCodes;

            result.push(newTask);
        }
    }

    return result;
}

async function robot(connection) {
    let allTasks = await getTasksFromDb(connection);
    let tasks = await filterTasks(connection, allTasks);

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

    for (const i in tasks) {
        let colorPreferences = await getColorPreferences(connection, tasks[i].company_id);
        tasks[i].currentScreenshotPath = currentScreenshotPath;
        tasks[i].connection = connection;
        tasks[i].credentials = await getCredentials(connection, tasks[i].company_id);
        if (isSimpleTask(tasks[i], colorPreferences)) {
            await cluster.queue(tasks[i], processSimpleTask);
        } else {
            tasks[i].colorPreferences = colorPreferences[tasks[i].model];
            await cluster.queue(tasks[i], processComplexTask);
        }
    }

    await cluster.idle();
    await cluster.close();
}

