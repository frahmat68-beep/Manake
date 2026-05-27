import { execSync, spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

const PAGES = [
    { name: 'home', path: '/' },
    { name: 'catalog', path: '/catalog' },
    { name: 'availability-board', path: '/availability-board' },
    { name: 'about', path: '/about' },
    { name: 'rental-rules', path: '/rental-rules' },
    { name: 'contact', path: '/contact' }
];

const PORT = 3000;
const BASE_URL = `http://127.0.0.1:${PORT}`;
const REPORT_DIR = 'storage/logs/lighthouse';

// Ensure report directory exists
if (!fs.existsSync(REPORT_DIR)) {
    fs.mkdirSync(REPORT_DIR, { recursive: true });
}

console.log('==> Starting PHP Artisan Serve in the background...');
const server = spawn('php', ['artisan', 'serve', `--port=${PORT}`], {
    detached: true,
    stdio: 'ignore'
});

// Prevent script from exiting before server starts
function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function isServerReady() {
    for (let i = 0; i < 15; i++) {
        try {
            const res = await fetch(BASE_URL, { method: 'HEAD' });
            if (res.ok || res.status === 200 || res.status === 302 || res.status === 404) {
                return true;
            }
        } catch (e) {
            // ignore fetch errors
        }
        await wait(1000);
    }
    return false;
}

async function run() {
    const ready = await isServerReady();
    if (!ready) {
        console.error('Server is not reachable on ' + BASE_URL);
        server.kill();
        process.exit(1);
    }
    console.log('==> Server is ready! Beginning Lighthouse audits for all pages...\n');

    const resultsTable = [];

    for (const page of PAGES) {
        const url = `${BASE_URL}${page.path}`;
        const reportBase = path.join(REPORT_DIR, `lighthouse-${page.name}`);
        console.log(`[${page.name.toUpperCase()}] Auditing ${url}...`);

        try {
            execSync(
                `npx lighthouse "${url}" ` +
                `--only-categories=performance,accessibility,best-practices,seo ` +
                `--output=html,json ` +
                `--output-path="${reportBase}" ` +
                `--chrome-flags="--headless=new --no-sandbox"`,
                { stdio: 'ignore' }
            );

            // Read the generated JSON results
            const jsonPath = `${reportBase}.report.json`;
            const rawJson = fs.readFileSync(jsonPath, 'utf8');
            const data = JSON.parse(rawJson);

            const perf = Math.round(data.categories.performance.score * 100);
            const a11y = Math.round(data.categories.accessibility.score * 100);
            const bp = Math.round(data.categories['best-practices'].score * 100);
            const seo = Math.round(data.categories.seo.score * 100);

            resultsTable.push({
                Page: page.name,
                Path: page.path,
                Performance: perf,
                Accessibility: a11y,
                'Best Practices': bp,
                SEO: seo
            });

            console.log(`[${page.name.toUpperCase()}] Done! Perf: ${perf} | A11y: ${a11y} | Best Practices: ${bp} | SEO: ${seo}\n`);
        } catch (error) {
            console.error(`[${page.name.toUpperCase()}] Failed to audit:`, error.message);
        }
    }

    // Shut down serve
    console.log('==> Audits finished. Shutting down PHP server...');
    server.kill();

    // Print summary as Markdown
    console.log('\n======================================================');
    console.log('               LIGHTHOUSE AUDIT SUMMARY               ');
    console.log('======================================================\n');
    console.log('| Page | Path | Performance | Accessibility | Best Practices | SEO |');
    console.log('| :--- | :--- | :---: | :---: | :---: | :---: |');
    for (const row of resultsTable) {
        console.log(`| **${row.Page}** | \`${row.Path}\` | ${row.Performance} | ${row.Accessibility} | ${row['Best Practices']} | ${row.SEO} |`);
    }
    console.log('\nAll reports saved in storage/logs/lighthouse/');
}

run();
