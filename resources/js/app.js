

// IMPORTANT: bootstrap AFTER jQuery
import './bootstrap';

import $ from 'jquery';
window.$ = $;
window.jQuery = $; // sometimes needed for plugins
import 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';


/*
  Add custom scripts here
*/
import.meta.glob([
  '../assets/img/**',
  '../assets/vendor/fonts/**'
]);

import select2 from 'select2/dist/js/select2.js';
select2($); // attach plugin to your global jQuery
import 'select2/dist/css/select2.min.css';
