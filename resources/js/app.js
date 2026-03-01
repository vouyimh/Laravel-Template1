

// IMPORTANT: bootstrap AFTER jQuery
import './bootstrap';

// import $ from 'jquery';
// window.$ = $;
// window.jQuery = $; // sometimes needed for plugins
// import 'datatables.net-dt';
// import 'datatables.net-dt/css/dataTables.dataTables.css';


// /*
//   Add custom scripts here
// */
// import.meta.glob([
//   '../assets/img/**',
//   '../assets/vendor/fonts/**'
// ]);

// import select2 from 'select2/dist/js/select2.js';
// select2($); // attach plugin to your global jQuery
// import 'select2/dist/css/select2.min.css';


// import { createApp } from "vue";
// import router from "./router";
// import App from "./App.vue";

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

import { createApp } from 'vue'
import Room from './pages/Room.vue'

const chatEl = document.getElementById('chat-app')

if (chatEl) {
   const roomId = chatEl.dataset.roomId

   const app = createApp(Room, { roomId })

   app.provide('$rooms', window.__app__.rooms)
   app.provide('$user', window.__app__.user)
   app.provide('$emojis', window.__app__.emojis)
   app.provide('$appName', window.__app__.appName)
   app.provide('$confettiWords', window.__app__.confettiWords)
   app.provide('$showToast', showToast)

   app.mount(chatEl)
}

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

// app.mount("#app");