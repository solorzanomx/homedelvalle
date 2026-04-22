import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/**
 * Convert CSS media query range syntax to min/max-width for Chrome < 113 compat.
 * @media (width>=40rem) → @media (min-width: 40rem)
 * @media (width<=80rem) → @media (max-width: 80rem)
 */
function mediaQueryRangeCompat() {
    const convert = (css) => css
        .replace(/@media \(width>=([\d.]+)(r?em|px)\)/g, '@media (min-width: $1$2)')
        .replace(/@media \(width<=([\d.]+)(r?em|px)\)/g, '@media (max-width: $1$2)')
        .replace(/@media \(width>([\d.]+)(r?em|px)\)/g,  '@media (min-width: calc($1$2 + 0.02px))')
        .replace(/@media \(width<([\d.]+)(r?em|px)\)/g,  '@media (max-width: calc($1$2 - 0.02px))');

    return {
        name: 'media-query-range-compat',
        // Dev server: transform each CSS module
        transform(code, id) {
            if (id.endsWith('.css')) return { code: convert(code), map: null };
        },
        // Production build: process final CSS assets
        generateBundle(_, bundle) {
            for (const file of Object.values(bundle)) {
                if (file.type === 'asset' && file.fileName.endsWith('.css')) {
                    file.source = convert(file.source);
                }
            }
        },
    };
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        mediaQueryRangeCompat(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
