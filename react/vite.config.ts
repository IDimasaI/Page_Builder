import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
type AssetInfo= {name: any};

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'build',
    rollupOptions: {
      treeshake: true,
      output: { 
        hashCharacters:"hex",
        chunkFileNames: 'static/js/[hash].chunk.js',
        entryFileNames: 'static/js/[name].[hash].js',
        assetFileNames: (assetInfo: AssetInfo) => {
          const info = assetInfo.name.split('.');
          const extType = info[info.length - 1];
          if (/\.(png|jpe?g|gif|svg|webp|webm|mp3)$/.test(assetInfo.name)) {
            return `static/media/[name].[hash].${extType}`;
          }
          if (/\.(css)$/.test(assetInfo.name)) {
            return `static/css/[name].[hash].${extType}`;
          }
          if (/\.(woff|woff2|eot|ttf|otf)$/.test(assetInfo.name)) {
            return `static/fonts/[name].[hash].${extType}`;
          }
          return `static/[name].[hash].${extType}`;
        }
      },
    },
  },
})
