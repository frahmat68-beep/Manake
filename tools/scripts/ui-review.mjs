import { chromium, devices } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';

const baseUrl = process.env.UI_REVIEW_URL || 'http://127.0.0.1:3000';
const outputDir = process.env.UI_REVIEW_DIR || path.resolve('test-results/ui-review');
const paths = [
    { name: 'home', url: '/' },
    { name: 'catalog', url: '/catalog' },
    { name: 'availability', url: '/availability-board' },
];
const viewports = [
    { name: 'desktop', viewport: { width: 1440, height: 900 } },
    { name: 'laptop', viewport: { width: 1280, height: 800 } },
    { name: 'tablet', viewport: { width: 768, height: 1024 } },
    { name: 'mobile', viewport: { width: 390, height: 844 }, isMobile: true },
];

await fs.mkdir(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true });

try {
    for (const pageDef of paths) {
        for (const size of viewports) {
            const page = await browser.newPage({
                viewport: size.viewport,
                isMobile: size.isMobile || false,
                deviceScaleFactor: 1,
                hasTouch: size.isMobile || false,
            });

            await page.goto(`${baseUrl}${pageDef.url}`, { waitUntil: 'networkidle' });
            await page.screenshot({
                path: path.join(outputDir, `${pageDef.name}-${size.name}.png`),
                fullPage: true,
            });
            await page.close();
        }
    }
} finally {
    await browser.close();
}
