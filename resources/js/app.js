import './bootstrap';
import { createApp } from 'vue';
import { Quasar, Notify, Dialog, Loading } from 'quasar';
import '@quasar/extras/material-icons/material-icons.css';
import '@quasar/extras/mdi-v7/mdi-v7.css';
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css';
import '@quasar/extras/roboto-font/roboto-font.css';
// import 'quasar/src/css/index.sass';
import 'quasar/dist/quasar.rtl.css';
import './css/app.scss'; // Custom app styles

import App from './App.vue';
import router from './router';

import langAr from 'quasar/lang/ar';

const app = createApp(App);

app.use(Quasar, {
    plugins: {
        Notify,
        Dialog,
        Loading
    }, // import Quasar plugins and add here
    lang: langAr, // Arabic support
    config: {
        // Quasar config
        animations: 'all' // Enable all animations
    }
});

app.use(router);

app.mount('#app');
