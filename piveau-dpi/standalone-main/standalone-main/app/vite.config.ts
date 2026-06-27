import { lstatSync } from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import vue from '@vitejs/plugin-vue'
import Icons from 'unplugin-icons/vite'
import { defineConfig } from 'vite'

function isSymlink(pkg: string) {
  const packagePath = path.resolve('..', '..', 'node_modules', pkg)
  try {
    return lstatSync(packagePath).isSymbolicLink()
  }
  catch {
    return false
  }
}

const buildConfig = {
  BASE_PATH: '/',
  SERVICE_URL: undefined,
}

export default defineConfig(({ command }) => ({
  base: buildConfig.BASE_PATH,

  define: {
    // Shim process.env from webpack
    'process.env': {
      buildconf: buildConfig,
    },
  },

  plugins: [
    vue({
      template: {
        compilerOptions: {
          whitespace: 'preserve',
        },
      },
    }),
    Icons({
      compiler: 'vue3',
      scale: 1.5,
    }),
    // Virtual module plugin for empty CSS in development
    {
      name: 'virtual-empty-css',
      resolveId(id: string) {
        if (id === 'virtual:empty-css') {
          return id
        }
      },
      load(id: string) {
        if (id === 'virtual:empty-css') {
          return '/* Empty CSS for development mode */'
        }
      },
    },
  ],

  resolve: {
    alias: [
      {
        find: 'vue',
        replacement: fileURLToPath(new URL('./node_modules/vue', import.meta.url)),
      },
      {
        find: '@',
        replacement: path.resolve(__dirname, 'src'),
      },
      {
        find: '@modules-scss',
        replacement: isSymlink('@piveau/piveau-hub-ui-modules')
          ? path.resolve(__dirname, '..', '..', 'node_modules', '@piveau/piveau-hub-ui-modules', 'dist', 'scss')
          : path.resolve(__dirname, 'node_modules', '@piveau/piveau-hub-ui-modules', 'dist', 'scss'),
      },
      // {
      //   find: /^~(.*)$/,
      //   replacement: '$1',
      // },
      {
        find: 'lodash',
        replacement: 'lodash-es',
      },
      {
        find: '#dpi-css',
        replacement: command === 'build'
          ? '@piveau/dpi/css'
          : 'virtual:empty-css',
      },
      ...command === 'build'
        ? []
        : [
            {
              find: '@piveau/dpi',
              replacement: path.resolve(__dirname, '..', 'packages', 'dpi', 'src/data-provider-interface', 'index.ts'),
            },
          ],
    ],
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    preserveSymlinks: false,
  },

  server: {
    port: 8080,
    proxy: {
      '/api/sparql': {
        target: 'https://open.bydata.de/api/sparql',
        changeOrigin: true,
        rewrite: path => path.replace(/^\/api\/sparql/, ''),
      },
    },
  },

  build: {
    target: 'esnext',
    sourcemap: false,
    // Disable css code splitting because it causes
    // flashing-of-unstyled-content (FOUC) issues on production build
    cssCodeSplit: false,
    rollupOptions: {
      output: {
        // Must align with the filename in runtimeconfig.sh
        entryFileNames: 'app.[hash].js',
      },
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        quietDeps: true,
      },
    },
  },
}))
