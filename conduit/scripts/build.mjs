/**
 * Copy the site sources into dist/, which is what Wrangler uploads.
 * Run from anywhere: node conduit/scripts/build.mjs
 * or, inside this project: node scripts/build.mjs
 */
import { copyFileSync, mkdirSync, readdirSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const site = join(root, 'site');
const dist = join(root, 'dist');
const files = readdirSync(site).filter((name) => !name.startsWith('.'));

rmSync(dist, { recursive: true, force: true });
mkdirSync(dist, { recursive: true });
for (const name of files) {
  copyFileSync(join(site, name), join(dist, name));
}
console.log('dist/: ' + files.join(', '));
