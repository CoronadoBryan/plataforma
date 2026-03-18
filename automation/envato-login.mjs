import fs from "node:fs";
import path from "node:path";
import readline from "node:readline/promises";
import { stdin as input, stdout as output } from "node:process";
import { chromium } from "playwright";

const authPath = path.resolve("automation/.auth/envato.json");
fs.mkdirSync(path.dirname(authPath), { recursive: true });

const browser = await chromium.launch({ headless: false });
const context = await browser.newContext();
const page = await context.newPage();

await page.goto("https://elements.envato.com/sign-in", {
    waitUntil: "domcontentloaded",
    timeout: 120000,
});

const rl = readline.createInterface({ input, output });
await rl.question(
    "Inicia sesion en Envato Elements en el navegador abierto y luego presiona Enter aqui...",
);
rl.close();

await context.storageState({ path: authPath });
await browser.close();

process.stdout.write(`Sesion guardada en ${authPath}\n`);
