/*
 * This file will be included onto the page via the importmap() Twig function,
 */
import './bootstrap.js';
import './lib/singleJavascript.js';
import singleJavascript from './lib/singleJavascript.js';
import './styles/app.css';

const alias = new singleJavascript(1, 'Sebastian');
console.log(alias.describe());