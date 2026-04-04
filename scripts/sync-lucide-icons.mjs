/**
 * Sync only needed Lucide icons from node_modules to resources/svg/lucide/
 * Usage: node scripts/sync-lucide-icons.mjs
 */
import { copyFileSync, mkdirSync, existsSync, readdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const source = join(root, 'node_modules', 'lucide-static', 'icons');
const dest = join(root, 'resources', 'svg', 'lucide');

// Only the icons we actually use
const icons = [
    // Admin sidebar — Operaciones
    'layout-dashboard',
    'building-2',
    'users',
    'circle-play',
    'square-check',
    // Admin sidebar — Historico
    'diamond',
    'home',
    // Admin sidebar — Equipo
    'star',
    'building',
    'link',
    'user-cog',
    'shield-alert',
    // Admin sidebar — Marketing
    'bar-chart-3',
    'mail',
    'megaphone',
    'target',
    'settings',
    'zap',
    'trophy',
    // Admin sidebar — Finanzas
    'arrow-left-right',
    // Admin sidebar — CMS
    'briefcase',
    'users-round',
    'pen-line',
    'list',
    'flag',
    'camera',
    'menu',
    'clipboard-list',
    'panel-bottom',
    // Admin sidebar — Legal
    'scale',
    'file-text',
    // Admin sidebar — Config
    'cloud',
    'puzzle',
    'image',
    // Admin sidebar — Ayuda
    'circle-help',
    // Admin sidebar — misc
    'log-out',
    'bell',
    'check',
    'triangle-alert',
    'align-justify',
    // Public site — navigation
    'x',
    'arrow-right',
    'arrow-left',
    'chevron-right',
    'chevron-down',
    // Public site — contact
    'phone',
    'map-pin',
    'clock',
    // Public site — features
    'shield-check',
    'shield',
    'key',
    'sparkles',
    'eye',
    'lock',
    'trending-up',
    'handshake',
    'search',
    'filter',
    'heart',
    'share-2',
    'bed-double',
    'bath',
    'maximize',
    'car',
    'calendar',
    'tag',
    'info',
    'loader-2',
    'map',
    'grid-3x3',
    'list-filter',
    'chevron-left',
    'chevrons-left',
    'chevrons-right',
    'plus',
    'eye-off',
    'external-link',
];

mkdirSync(dest, { recursive: true });

if (!existsSync(source)) {
    console.error('❌ lucide-static not found. Run: npm install --save-dev lucide-static');
    process.exit(1);
}

let copied = 0;
let missing = [];

for (const name of icons) {
    const src = join(source, `${name}.svg`);
    if (existsSync(src)) {
        copyFileSync(src, join(dest, `${name}.svg`));
        copied++;
    } else {
        missing.push(name);
    }
}

console.log(`✅ Copied ${copied}/${icons.length} icons to resources/svg/lucide/`);
if (missing.length) {
    console.warn(`⚠️  Missing: ${missing.join(', ')}`);
    // Try to find similar names
    const available = readdirSync(source).map(f => f.replace('.svg', ''));
    for (const m of missing) {
        const similar = available.filter(a => a.includes(m) || m.includes(a)).slice(0, 3);
        if (similar.length) console.log(`   "${m}" → maybe: ${similar.join(', ')}`);
    }
}
