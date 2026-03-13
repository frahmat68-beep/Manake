import { cpSync, existsSync, lstatSync, mkdirSync, readdirSync, rmSync, unlinkSync } from 'node:fs';
import { resolve } from 'node:path';

const sourceDir = resolve(process.cwd(), 'storage/app/public');
const targetDir = resolve(process.cwd(), 'public/storage');

if (!existsSync(sourceDir)) {
    console.warn('[vercel-sync-storage] Source directory not found:', sourceDir);
    process.exit(0);
}

if (existsSync(targetDir)) {
    const stat = lstatSync(targetDir);
    if (stat.isSymbolicLink() || stat.isDirectory()) {
        if (stat.isSymbolicLink()) {
            unlinkSync(targetDir);
        } else {
            rmSync(targetDir, { recursive: true, force: true });
        }
    }
}

mkdirSync(targetDir, { recursive: true });

for (const entry of readdirSync(sourceDir, { withFileTypes: true })) {
    const from = resolve(sourceDir, entry.name);
    const to = resolve(targetDir, entry.name);
    cpSync(from, to, { recursive: true, force: true });
}

console.log('[vercel-sync-storage] Synced', sourceDir, '->', targetDir);
