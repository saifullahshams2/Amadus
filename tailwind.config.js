/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        mono: ['Courier New', 'Courier', 'Consolas', 'Menlo', 'monospace'],
        gds: ['"Lucida Console"', '"Courier New"', 'Courier', 'monospace'],
      },
      colors: {
        amadeus: {
          dark: '#0a0e17',
          panel: '#111827',
          surface: '#1e293b',
          border: '#334155',
          accent: '#005ea6', // Amadeus corporate blue
          cyan: '#38bdf8',
          amber: '#f59e0b',
          green: '#22c55e',
          text: '#e2e8f0',
          muted: '#94a3b8',
        },
        crt: {
          bg: '#050a05',
          green: '#33ff33',
          greenDim: '#1b8a1b',
          amber: '#ffb000',
          amberDim: '#996600',
        }
      },
      animation: {
        'cursor-blink': 'blink 1s step-end infinite',
      },
      keyframes: {
        blink: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0' },
        }
      }
    },
  },
  plugins: [],
};
