
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.html.twig", "./assets/**/*.js", "./assets/**/*.vue"],
  theme: {
    extend: {
    },
  },
    safelist: [
        {
            pattern: /bg-+/,
        }
    ],
  plugins: [],
}

