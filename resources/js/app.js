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
  // '../assets/json/**',
  '../assets/vendor/fonts/**'
]);
