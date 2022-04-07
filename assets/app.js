/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
require('bootstrap');
// const feather = require('feather-icons');
import * as bootstrapJS from 'bootstrap';
// const feather = require('feather-icons');
import * as feather from 'feather-icons';
window.addEventListener('load', () => {
    feather.replace({width: 16, height: 16});
})
// start the Stimulus application
import './bootstrap';
