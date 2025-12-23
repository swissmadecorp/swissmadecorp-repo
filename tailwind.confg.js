/** @type {import('tailwindcss').Config} */

module.exports = {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
      // "./node_modules/flowbite/**/*.js"
    ],
    safelist: [
      'text-ellipsis',
      
      {
        pattern: /bg-(red|gray)-(100|200|300|400|500|600|700|800|900)/,
        variants: ['lg', 'hover', 'focus', 'lg:hover','group','drop-shadow'],
      },
      {
        pattern: /mt-(20|24|32|40|48|56|64|auto)/,
      },
      {
        pattern: /drop-shadow-(sm|md|lg|xl|2x1)/,
      },
    ],
    theme: {
      extend: {
        screens: {
          'custom': '820px',
          'pagination': '800px',
          '640-787': {'min': '640px', 'max': '787px'},
          'custom-830': '830px',
          'custom-786': '786px',
          'thumbs':{'min': '320px', 'max': '640px'},
          'thumbs1x':{'min': '1024'},
        },
        opacity: {
          '67': '.67',
          '80': '.80',
        },
        dropShadow: {
          '3xl': '0 35px 35px rgba(0, 0, 0, 0.25)',
          '4xl': [
              '0 35px 35px rgba(0, 0, 0, 0.25)',
              '0 45px 65px rgba(0, 0, 0, 0.15)'
          ]
        },
        zIndex: {
          '60': 60,
        },
      },
    },
    plugins: [
      require('flowbite/plugin'),
    ],
  }
  
  