import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
const plugin = require('tailwindcss/plugin');
const scrollbar = require('tailwind-scrollbar');
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans], // Replace 'Figtree' with 'Poppins'
            },
        },
    },

    plugins: [
        forms, // Tailwind Forms Plugin for better form styling
        scrollbar({ nocompatible: true }), // Tailwind Scrollbar Plugin
    ],
};
