import type { UserConfig } from 'vite';
import { defaultAllowedOrigins } from 'vite';
import { globSync } from 'glob';
import tsconfigPaths from 'vite-tsconfig-paths';
import autoprefixer from 'autoprefixer';
import tailwindcss from '@tailwindcss/vite';
import postcssUtopia from 'postcss-utopia';
import postcssHelpersFunctions from '@locomotivemtl/postcss-helpers-functions';
import ViteSvgSpriteWrapper from 'vite-svg-sprite-wrapper';
import dotenv from 'dotenv';

dotenv.config();

const inputFiles = globSync(
    ['src/scripts/main.ts', 'src/scripts/components/**/*.ts', 'src/styles/main.css'],
    {
        ignore: ['src/scripts/components/Example.ts', 'src/scripts/components/globals/**/*.ts']
    }
).map((file) => {
    return file;
});

const ddevConfig = process.env.DDEV_PRIMARY_URL
    ? await import('./vite.config.ddev.ts').then((config) => config.default).catch(() => ({}))
    : {};

// @ts-expect-error -- Optional local configuration file.
const localConfig = await import('./vite.config.local.ts')
    .then((config) => config.default)
    .catch(() => ({}));

/** @todo Maybe add support for deep merge? */
export default {
    build: {
        assetsDir: '',
        emptyOutDir: true,
        manifest: 'manifest.json',
        outDir: `dist`,
        rollupOptions: {
            input: inputFiles,
            output: {
                // Custom logic: don't hash `sprite.svg`, hash everything else
                assetFileNames: (assetInfo) => {
                    const fileNames = assetInfo.names ?? '';
                    for (let fileName of fileNames) {
                        if (fileName.endsWith('sprite.svg')) {
                            return 'sprite.svg'; // no hash
                        }
                    }
                    return '[name]-[hash][extname]'; // default for others
                }
            }
        }
    },
    css: {
        postcss: {
            plugins: [autoprefixer, postcssUtopia(), postcssHelpersFunctions()]
        }
    },
    plugins: [
        tailwindcss(),
        tsconfigPaths(),
        ViteSvgSpriteWrapper({
            icons: './src/assets/svgs/*.svg',
            outputDir: './dist/',
            sprite: {
                shape: {
                    transform: [
                        {
                            svgo: {
                                pluggins: [
                                    {
                                        removeAttrs: { attrs: '(fill)' }
                                    }
                                ]
                            }
                        }
                    ]
                }
            }
        }),
        {
            name: 'php',
            handleHotUpdate({ file, server }) {
                if (file.endsWith('.php')) {
                    server.ws.send({ type: 'full-reload', path: '*' });
                }
            }
        }
    ],
    server: {
        cors: [defaultAllowedOrigins],
        port: process.env.VITE_SERVER_PORT
    },
    ...ddevConfig,
    ...localConfig
} as UserConfig;
