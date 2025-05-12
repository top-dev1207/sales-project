const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: [
        module.exports = {
            content: [
                './resources/**/*.blade.php',
                './resources/js/**/*.tsx',
                './resources/js/**/*.ts', // 혹시 .ts 파일도 사용하는 경우
                './resources/js/**/*.jsx',
                './resources/js/**/*.js'
              ],
          }
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#4361ee',
                'primary-dark': '#3a56d4',
                good: '#4ade80',
                warning: '#facc15',
                critical: '#f87171',
                neutral: '#94a3b8',
            }
        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
