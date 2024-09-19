/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{js,jsx,ts,tsx}",
    __dirname+"/../Pages/*.{php,html}",
    __dirname+"/../process/API/*.{php,html}",
    __dirname+"/../asset/template/Pages/*.{php,html}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
