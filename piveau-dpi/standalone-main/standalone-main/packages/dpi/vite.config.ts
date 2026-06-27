import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'
import vue from '@vitejs/plugin-vue'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import { defineConfig } from 'vite'
import packageJson from './package.json' with { type: 'json' }
import dts from 'vite-plugin-dts'

const devDependencies = Object.keys(packageJson.devDependencies)
  .filter((pkgName: string) => ![
    '@antfu/eslint-config',
    'typescript',
    'vite',
    'vite-plugin-lib-inject-css',
    'vite-plugin-static-copy',
    'vue-tsc',
    'eslint',
    'jiti',
    'histoire',
    '@histoire/plugin-vue',
    'execa',
    'minimist',
    'picocolors',
    'semver',
  ].includes(pkgName),
  )

// Creating regexes of the packages to make sure subpaths of the
// packages are also treated as external
const regexesOfPackages = devDependencies
  .map((packageName: string) => new RegExp(`^${packageName}(/.*)?`))

const __dirname = dirname(fileURLToPath(import.meta.url))

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    dts({
      insertTypesEntry: true,
      copyDtsFiles: true,
    }),
    viteStaticCopy({
      targets: [
        {
          src: resolve(__dirname, 'src/styles'),
          dest: resolve(__dirname, 'dist'),
        }
      ]
    })
  ],

  resolve: {
    alias: [
      { find: 'lodash', replacement: 'lodash-es' },
    ],
  },

  build: {
    lib: {
      formats: ['es'],
      entry: {
        index: resolve(__dirname, 'src/data-provider-interface/index.ts'),
        msw: resolve(__dirname, 'src/msw/index.ts'),
      },
    },

    rollupOptions: {
      external: ['vue', ...regexesOfPackages],
      output: {
        // Put chunk files at <output>/chunks
        chunkFileNames: 'chunks/[name].[hash].js',
        // Put chunk styles at <output>/assets
        assetFileNames: 'assets/[name][extname]',
        entryFileNames: '[name].js',

        preserveModules: true,
      },
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        silenceDeprecations: ['mixed-decls', 'color-functions', 'global-builtin', 'import'],
      },
    },
  },
})
