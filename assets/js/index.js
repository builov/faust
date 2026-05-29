import 'bootstrap';
import '../css/style.scss';
import { App } from './App.js';

document.addEventListener('DOMContentLoaded', () => {
    const app = new App();
    app.init();
});