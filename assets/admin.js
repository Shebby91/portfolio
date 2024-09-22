/*
 * This file will be included onto the page via the importmap() Twig function,
 */
import './bootstrap.js'; 
import './styles/admin.css';
import singleJavascript from './lib/singleJavascript.js';

const alias = new singleJavascript(1, 'sebastian');
console.log(alias.describe());