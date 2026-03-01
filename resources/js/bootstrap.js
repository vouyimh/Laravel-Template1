import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import './echo';

import 'bootstrap';
import $ from 'jquery'
window.$ = $