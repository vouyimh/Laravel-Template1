import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// IMPORTANT: bootstrap AFTER jQuery
import './bootstrap';

import.meta.glob([
  '../assets/img/**',
  '../assets/vendor/fonts/**'
]);

import select2 from 'select2/dist/js/select2.js';
select2($); // attach plugin to your global jQuery
import 'select2/dist/css/select2.min.css';
