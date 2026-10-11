/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f5f7',
          100: '#ddebed',
          200: '#bed8de',
          300: '#92beca',
          400: '#619db0',
          500: '#428196',
          600: '#33687b',
          700: '#2b5464',
          800: '#254552',
          900: '#1c3540',
          950: '#0f1f26',
        }
      }
    },
  },
  plugins: [],
}

