/*
 * This file will be included onto the page via the importmap() Twig function,
 */
import './bootstrap.js';
import singleJavascript from './lib/singleJavascript.js';
import './styles/app.css';

const alias = new singleJavascript(1, 'sebastian');
console.log(alias.describe());