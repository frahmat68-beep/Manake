import { cpSync, existsSync, mkdirSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';

const sourceDir = resolve(process.cwd(), 'public/build');
const distDir = resolve(process.cwd(), 'dist');

if (!existsSync(sourceDir)) {
    console.warn('[vercel-sync-dist] Source directory not found:', sourceDir);
    process.exit(0);
}

const rootBuildDir = resolve(process.cwd(), 'build');

rmSync(distDir, { recursive: true, force: true });
mkdirSync(distDir, { recursive: true });
cpSync(sourceDir, distDir, { recursive: true });

rmSync(rootBuildDir, { recursive: true, force: true });
mkdirSync(rootBuildDir, { recursive: true });
cpSync(sourceDir, rootBuildDir, { recursive: true });

console.log('[vercel-sync-dist] Synced', sourceDir, '->', distDir, 'and', rootBuildDir);
