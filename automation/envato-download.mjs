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

function debug(step, payload = null) {
    const stamp = new Date().toISOString();
    if (payload === null || payload === undefined) {
        process.stderr.write(`[envato-debug ${stamp}] ${step}\n`);
        return;
    }

    try {
        process.stderr.write(`[envato-debug ${stamp}] ${step} ${JSON.stringify(payload)}\n`);
    } catch {
        process.stderr.write(`[envato-debug ${stamp}] ${step}\n`);
    }
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
const scanEnabled = !["false", "0", "no"].includes(String(readArg("scan", "false")).toLowerCase());
const keepOpenOnFail = !["false", "0", "no"].includes(String(readArg("keepOpenOnFail", "false")).toLowerCase());
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
debug("init", { id, url, headless, scanEnabled, keepOpenOnFail, authPath, downloadsDir });

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
        debug("nav.mainFrame", { url: frame.url() });
    }
});
page.on("console", (msg) => {
    if (msg.type() === "error") {
        debug("page.console.error", { text: msg.text() });
    }
});
page.on("pageerror", (err) => {
    debug("page.error", { message: err?.message ?? String(err) });
});
await page.goto(url, { waitUntil: "domcontentloaded", timeout: 120000 });
await page.waitForTimeout(3000);
debug("page.loaded", { currentUrl: page.url() });

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
    debug("clickXpath.wait", { xpath, timeout });
    await locator.waitFor({ state: "visible", timeout });
    debug("clickXpath.click", { xpath });
    await locator.click({ timeout });
}

/**
 * Cierra como máximo UN overlay (un solo clic). Si se repite el clic en el mismo selector
 * puede activarse otro botón debajo y estropear la ruta (navegación / otro flujo).
 * Tu XPath apuntaba al svg; el clic es en el button. Id React dinámico → [id^="_r_"] + divs.
 */
async function cerrarPopupPrevioSiExiste() {
    const selectorEnvatoX =
        '[id^="_r_"] > div:nth-child(2) > div > div > div:first-child > div:nth-child(2) > button';

    try {
        const envatoBtns = page.locator(selectorEnvatoX);
        const n = await envatoBtns.count();
        for (let i = 0; i < n; i++) {
            const loc = envatoBtns.nth(i);
            const visible = await loc.isVisible({ timeout: 600 }).catch(() => false);
            if (!visible) continue;
            await loc.click({ timeout: 5000 });
            debug("popup.cerrado", { via: "react-root-x-botón (estructura Envato)", index: i, clicks: 1 });
            await page.waitForTimeout(450);
            return;
        }
    } catch (e) {
        debug("popup.cerrar.intento-fallo", { via: "react-root-x", message: e?.message ?? String(e) });
    }

    try {
        const ariaClose = page
            .locator('[role="dialog"] button[aria-label="Cerrar"], [role="dialog"] button[aria-label="Close"]')
            .first();
        if ((await ariaClose.count()) > 0 && (await ariaClose.isVisible({ timeout: 800 }).catch(() => false))) {
            await ariaClose.click({ timeout: 5000 });
            debug("popup.cerrado", { via: "dialog aria Cerrar/Close", clicks: 1 });
            await page.waitForTimeout(450);
            return;
        }
    } catch (e) {
        debug("popup.cerrar.intento-fallo", { via: "dialog-aria", message: e?.message ?? String(e) });
    }

    try {
        await page.keyboard.press("Escape");
        await page.waitForTimeout(350);
        debug("popup.escape", { note: "sin clic en botón; solo tecla" });
    } catch {
        // ignorar
    }
}

async function waitForDownloadTrigger(maxWaitMs = 90000) {
    const start = Date.now();
    const triggerLocators = [
        page.locator('button:has-text("Descargar")').first(),
        page.locator('button:has-text("Download")').first(),
        page.locator('a:has-text("Descargar")').first(),
        page.locator('a:has-text("Download")').first(),
        page.getByRole("button", { name: /descargar/i }).first(),
        page.getByRole("button", { name: /download/i }).first(),
        page.getByRole("link", { name: /descargar/i }).first(),
        page.getByRole("link", { name: /download/i }).first(),
    ];

    while (Date.now() - start < maxWaitMs) {
        for (const locator of triggerLocators) {
            try {
                const count = await locator.count();
                if (count === 0) {
                    continue;
                }
                await locator.waitFor({ state: "visible", timeout: 2000 });
                debug("wait.trigger.found", { waitedMs: Date.now() - start });
                return true;
            } catch {
                // Continuar: algunos elementos aparecen y desaparecen durante render.
            }
        }
        await page.waitForTimeout(1000);
    }

    debug("wait.trigger.timeout", { waitedMs: Date.now() - start });
    return false;
}

async function clickLocatorAndWaitDownload(locator, label) {
    const [download] = await Promise.all([
        page.waitForEvent("download", { timeout: 30000 }),
        locator.click({ timeout: 12000 }),
    ]);
    debug("download.event", { via: label, suggestedFilename: download?.suggestedFilename?.() ?? null });
    return download;
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
    const shouldWriteFiles = !!scanEnabled;
    const stamp = shouldWriteFiles ? Date.now() : null;

    let screenshotPath = null;
    let htmlPath = null;
    let jsonPath = null;
    let txtPath = null;

    if (shouldWriteFiles) {
        screenshotPath = path.join(debugDir, `scan-${id}-${stamp}.png`);
        htmlPath = path.join(debugDir, `scan-${id}-${stamp}.html`);
        jsonPath = path.join(debugDir, `scan-${id}-${stamp}.json`);
        txtPath = path.join(debugDir, `scan-${id}-${stamp}.txt`);

        await page.screenshot({ path: screenshotPath, fullPage: true });
    }

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
            // No truncamos demasiado: para video/video-4K queremos incluirlo.
            candidates: candidates.slice(0, Math.min(limit * 5, 100)),
        };
    }, scanLimit);

    if (shouldWriteFiles) {
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
    return scan.candidates;
}

async function intentarFlujoDescargaDirecta() {
    try {
        debug("flujo.directo.inicio");
        // Envato redirige desde elements.envato.com a app.envato.com.
        await page.waitForURL("**/app.envato.com/**", { timeout: 45000 });
        debug("flujo.directo.urlOk", { currentUrl: page.url() });
        await page.waitForTimeout(800);
        await cerrarPopupPrevioSiExiste();
        await waitForDownloadTrigger(90000);

        // Scan para guardar la estructura real (botones + xpaths + redirecciones).
        const scannedCandidates = await scanButtonsAndXPaths();

        // Elegimos el XPath correcto según el texto detectado.
        // Esto permite diferenciar flujos "video" (ej: "Descargar 4K") vs "no video".
        let clickTargetXpath = xpathDownloadDirecto;
        if (Array.isArray(scannedCandidates) && scannedCandidates.length > 0) {
            const normalized = scannedCandidates
                .filter((c) => c && typeof c.text === "string" && c.xpath)
                .map((c) => ({ text: c.text.toLowerCase(), xpath: c.xpath }));

            const videoCandidate = normalized.find((c) => c.text.includes("4k"));
            if (videoCandidate?.xpath) {
                clickTargetXpath = videoCandidate.xpath;
            } else {
                const downloadCandidate = normalized.find(
                    (c) => c.text.includes("descargar") || c.text.includes("download")
                );
                if (downloadCandidate?.xpath) {
                    clickTargetXpath = downloadCandidate.xpath;
                }
            }
        }
        debug("flujo.directo.clickTarget", { clickTargetXpath });
        if (Array.isArray(scannedCandidates)) {
            debug("flujo.directo.candidates", {
                count: scannedCandidates.length,
                preview: scannedCandidates.slice(0, 5).map((c) => ({
                    text: c.text,
                    xpath: c.xpath,
                })),
            });
        } else {
            debug("flujo.directo.candidates", { note: "scan desactivado o sin resultado" });
        }

        // 1) Intento robusto por texto/rol (menos frágil que XPath absoluto).
        const textAttempts = [
            { label: 'button:has-text("Descargar 4K")', locator: page.locator('button:has-text("Descargar 4K")').first() },
            { label: 'button:has-text("Descargar 4k")', locator: page.locator('button:has-text("Descargar 4k")').first() },
            { label: 'button:has-text("Descargar")', locator: page.locator('button:has-text("Descargar")').first() },
            { label: 'role button /descargar 4k/i', locator: page.getByRole("button", { name: /descargar 4k/i }).first() },
            { label: 'role button /descargar/i', locator: page.getByRole("button", { name: /descargar/i }).first() },
        ];

        for (const attempt of textAttempts) {
            try {
                const count = await attempt.locator.count();
                if (count === 0) continue;
                await attempt.locator.scrollIntoViewIfNeeded({ timeout: 5000 });
                await attempt.locator.waitFor({ state: "visible", timeout: 10000 });
                debug("flujo.directo.tryText", { label: attempt.label });
                return await clickLocatorAndWaitDownload(attempt.locator, attempt.label);
            } catch (error) {
                debug("flujo.directo.tryText.error", {
                    label: attempt.label,
                    message: error?.message ?? String(error),
                });
            }
        }

        // 2) Fallback al XPath detectado.
        const [download] = await Promise.all([
            page.waitForEvent("download", { timeout: 30000 }),
            clickXpath(clickTargetXpath, 25000),
        ]);
        debug("flujo.directo.downloadEvent", { suggestedFilename: download?.suggestedFilename?.() ?? null });
        return download;
    } catch (error) {
        debug("flujo.directo.error", {
            message: error?.message ?? String(error),
            stack: error?.stack ?? null,
            currentUrl: page.url(),
        });
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
            debug("flujo.selector.try", { selector });
            const [download] = await Promise.all([
                page.waitForEvent("download", { timeout: 25000 }),
                loc.click({ timeout: 10000 }),
            ]);
            debug("flujo.selector.downloadEvent", { selector, suggestedFilename: download?.suggestedFilename?.() ?? null });
            return download;
        } catch (error) {
            debug("flujo.selector.error", {
                selector,
                message: error?.message ?? String(error),
            });
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
    debug("download.fail.screenshot", { screenshotPath, currentUrl: page.url() });

    if (await isCloudflareChallenge()) {
        await browser.close();
        finish({
            ok: false,
            requiresVerification: true,
            message: "Cloudflare requiere verificacion humana para continuar.",
            screenshotPath,
        });
    }

    if (!keepOpenOnFail) {
        await browser.close();
    } else {
        debug("download.fail.keepOpen", { reason: "keepOpenOnFail=true" });
    }
    finish({
        ok: false,
        message: `No se pudo iniciar la descarga. Captura: ${screenshotPath}`,
        screenshotPath,
    });
}

const suggested = download.suggestedFilename() || `envato-${id}.zip`;
const filePath = path.join(downloadsDir, `${id}-${suggested}`);
debug("download.saveAs.start", { suggested, filePath });
await download.saveAs(filePath);
debug("download.saveAs.done", { exists: fs.existsSync(filePath) });

await browser.close();

process.stdout.write(
    JSON.stringify({
        ok: true,
        filename: suggested,
        filePath,
    }),
);
