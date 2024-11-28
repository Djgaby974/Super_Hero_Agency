import './bootstrap.js';
import './styles/app.css'; // Importation du fichier CSS principal
import Chart from 'chart.js/auto';

// Importation de Switchery CSS et JS


import Switchery from 'switchery';

// Initialisation des Switchery toggles
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.js-switch-toggle');
    elements.forEach((el) => {
        new Switchery(el, { color: '#4CAF50', secondaryColor: '#F44336' });
    });
});


/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
