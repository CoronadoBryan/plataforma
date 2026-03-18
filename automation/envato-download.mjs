import fs from "node:fs";
import path from "node:path";
import { chromium } from "playwright";

function readArg(key, fallback = null) {
    const prefix = `--${key}=`;
    const arg = process.argv.find((a) => a.startsWith(prefix));
    return arg ? arg.slice(prefix.length) : fallback;
}

function fail(message) {
    process.stderr.write(`${message}\n`);
    process.exit(1);
}

function finish(result) {
    process.stdout.write(JSON.stringify(result));
    process.exit(0);
}

const url = readArg("url");
const id = readArg("id", "0");
const downloadsDir = readArg("downloads", path.resolve("storage/app/downloads"));
const authPath = readArg("auth", path.resolve("automation/.auth/envato.json"));
const headlessArg = readArg("headless", "true");
const headless = !["false", "0", "no"].includes(String(headlessArg).toLowerCase());

if (!url) {
    fail("Falta --url.");
}

if (!fs.existsSync(authPath)) {
    fail("No existe sesion guardada. Ejecuta: npm run envato:login");
}

fs.mkdirSync(downloadsDir, { recursive: true });

const debugDir = path.resolve("storage/app/envato-debug");
fs.mkdirSync(debugDir, { recursive: true });

const browser = await chromium.launch({ headless });
const context = await browser.newContext({
    storageState: authPath,
    acceptDownloads: true,
});

const page = await context.newPage();
await page.goto(url, { waitUntil: "domcontentloaded", timeout: 120000 });
await page.waitForTimeout(3000);

const xpathDownloadVerde =
    '//*[@id="content"]/div[1]/div[2]/div[1]/div[1]/div/div/div/div/div/div/div/div[2]/div/div/div[1]/div[2]/div/div/button[1]';
const xpathTerminos = "/html/body/div[8]/div/div/div/div[2]/div/div[3]/div/div[1]/div/div/div/label/div/div/div";
const xpathDownloadFinal = "/html/body/div[8]/div/div/div/footer/div[1]/div/div/div/button";

async function isCloudflareChallenge() {
    const title = (await page.title()).toLowerCase();
    const html = (await page.content()).toLowerCase();

    return (
        title.includes("just a moment") ||
        title.includes("attention required") ||
        html.includes("performing security verification") ||
        html.includes("verify you are human") ||
        html.includes("cloudflare")
    );
}

async function clickXpath(xpath, timeout = 20000) {
    const locator = page.locator(`xpath=${xpath}`).first();
    await locator.waitFor({ state: "visible", timeout });
    await locator.click({ timeout });
}

async function intentarFlujoXpaths() {
    try {
        await clickXpath(xpathDownloadVerde);
        await page.waitForTimeout(1200);
        await clickXpath(xpathTerminos);
        await page.waitForTimeout(1200);
        const [download] = await Promise.all([
            page.waitForEvent("download", { timeout: 30000 }),
            clickXpath(xpathDownloadFinal, 25000),
        ]);
        return download;
    } catch {
        return null;
    }
}

async function intentarFlujoSelectors() {
    const selectors = [
        'button:has-text("Download")',
        'a:has-text("Download")',
        '[data-testid*="download"]',
        '[class*="download"]',
    ];

    for (const selector of selectors) {
        const loc = page.locator(selector).first();

        if ((await loc.count()) === 0) {
            continue;
        }

        try {
            const [download] = await Promise.all([
                page.waitForEvent("download", { timeout: 25000 }),
                loc.click({ timeout: 10000 }),
            ]);
            return download;
        } catch {
            // Try next selector
        }
    }

    return null;
}

let download = await intentarFlujoXpaths();

if (!download) {
    download = await intentarFlujoSelectors();
}

if (!download) {
    const screenshotPath = path.join(debugDir, `descarga-${id}.png`);
    await page.screenshot({ path: screenshotPath, fullPage: true });

    if (await isCloudflareChallenge()) {
        await browser.close();
        finish({
            ok: false,
            requiresVerification: true,
            message: "Cloudflare requiere verificacion humana para continuar.",
            screenshotPath,
        });
    }

    await browser.close();
    finish({
        ok: false,
        message: `No se pudo iniciar la descarga. Captura: ${screenshotPath}`,
        screenshotPath,
    });
}

const suggested = download.suggestedFilename() || `envato-${id}.zip`;
const filePath = path.join(downloadsDir, `${id}-${suggested}`);
await download.saveAs(filePath);

await browser.close();

process.stdout.write(
    JSON.stringify({
        ok: true,
        filename: suggested,
        filePath,
    }),
);
