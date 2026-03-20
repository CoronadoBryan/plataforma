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
const scanEnabled = !["false", "0", "no"].includes(String(readArg("scan", "true")).toLowerCase());
const scanLimit = Math.max(10, parseInt(String(readArg("scanLimit", "50")), 10) || 50);
const htmlLimit = Math.max(200000, parseInt(String(readArg("htmlLimit", "1200000")), 10) || 1200000);

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
// Guardar historial de navegación para depurar redirecciones.
const navUrls = [];
page.on("framenavigated", (frame) => {
    if (frame === page.mainFrame()) {
        navUrls.push(frame.url());
    }
});
await page.goto(url, { waitUntil: "domcontentloaded", timeout: 120000 });
await page.waitForTimeout(3000);

const xpathDownloadDirecto =
    "/html/body/div[1]/div/div/div/div/div/div[2]/div[2]/div[2]/div/div/div/div[2]/div[2]/div/div[2]/div/div/button";

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

function uniq(arr) {
    return Array.from(new Set(arr));
}

function truncate(str, max) {
    if (typeof str !== "string") return "";
    if (str.length <= max) return str;
    return str.slice(0, max);
}

async function scanButtonsAndXPaths() {
    if (!scanEnabled) return;

    const stamp = Date.now();
    const screenshotPath = path.join(debugDir, `scan-${id}-${stamp}.png`);
    const htmlPath = path.join(debugDir, `scan-${id}-${stamp}.html`);
    const jsonPath = path.join(debugDir, `scan-${id}-${stamp}.json`);
    const txtPath = path.join(debugDir, `scan-${id}-${stamp}.txt`);

    await page.screenshot({ path: screenshotPath, fullPage: true });

    const scan = await page.evaluate((limit) => {
        function isVisible(el) {
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            return rect.width > 0 && rect.height > 0 && style.visibility !== "hidden" && style.display !== "none";
        }

        function getXPathForElement(el) {
            if (!el || el.nodeType !== 1) return null;
            if (el.id) return `//*[@id="${el.id}"]`;

            const parts = [];
            let current = el;
            while (current && current.nodeType === 1 && current !== document.body) {
                const tag = current.tagName.toLowerCase();
                const parent = current.parentNode;
                if (!parent) break;

                const siblings = Array.from(parent.children).filter((sib) => sib.tagName === current.tagName);
                const index = siblings.indexOf(current) + 1;

                parts.unshift(`${tag}[${index}]`);
                current = parent;
            }

            return `/html/${parts.join("/")}`;
        }

        const nodes = Array.from(document.querySelectorAll("button, a"));
        const visible = nodes
            .filter((el) => isVisible(el))
            .map((el) => {
                const text = (el.innerText || el.textContent || "").replace(/\s+/g, " ").trim();
                const href = el.getAttribute("href");
                const dataTestId = el.getAttribute("data-testid");
                const className = el.getAttribute("class");
                const ariaLabel = el.getAttribute("aria-label");
                const xpath = getXPathForElement(el);
                return {
                    tag: el.tagName.toLowerCase(),
                    text,
                    href: href || null,
                    dataTestId: dataTestId || null,
                    className: className || null,
                    ariaLabel: ariaLabel || null,
                    xpath,
                };
            })
            .filter((x) => x.text || x.href || x.dataTestId || x.className);

        const candidates = visible.filter((x) => {
            const t = (x.text || "").toLowerCase();
            return t.includes("download") || t.includes("descargar") || t.includes("4k") || (x.className || "").toLowerCase().includes("download");
        });

        return {
            visibleCount: visible.length,
            candidatesCount: candidates.length,
            visible: visible.slice(0, limit),
            candidates: candidates.slice(0, Math.min(limit, 20)),
        };
    }, scanLimit);

    const nav = uniq(navUrls);
    const html = truncate(await page.content(), htmlLimit);

    fs.writeFileSync(jsonPath, JSON.stringify({ urlInput: url, navUrls: nav, scan }, null, 2), "utf8");
    fs.writeFileSync(htmlPath, html, "utf8");

    const lines = [];
    lines.push(`url_input=${url}`);
    lines.push(`navUrlsCount=${nav.length}`);
    for (const u of nav) lines.push(`nav=${u}`);
    lines.push("");
    lines.push(`visibleCount=${scan.visibleCount}`);
    lines.push(`candidatesCount=${scan.candidatesCount}`);
    lines.push("");
    lines.push("Candidates (text + xpath):");
    for (const c of scan.candidates) {
        lines.push(`- text="${c.text}" xpath="${c.xpath}"`);
    }

    fs.writeFileSync(txtPath, lines.join("\n"), "utf8");
}

async function intentarFlujoDescargaDirecta() {
    try {
        // Envato redirige desde elements.envato.com a app.envato.com.
        await page.waitForURL("**/app.envato.com/**", { timeout: 45000 });

        // Scan para guardar la estructura real (botones + xpaths + redirecciones).
        await scanButtonsAndXPaths();
        const [download] = await Promise.all([
            page.waitForEvent("download", { timeout: 30000 }),
            clickXpath(xpathDownloadDirecto, 25000),
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

let download = await intentarFlujoDescargaDirecta();

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
