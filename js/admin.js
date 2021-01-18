/* Require bootstrap and Vue. Make sure to allocate to a variable to get the
 * require to function properly
 * 
 */
require('bootstrap');
require('@fortawesome/fontawesome-free/js/all.min.js');
window.$=require('jquery');

import Vue from 'vue';
//import { i18n } from "./locale.js";
//import { apolloProvider } from './apollo.js';
//import { router } from './routes.js';
//import { store } from './store.js';

//
// Vue Initialization
//
//Vue.filter('uppercase', function(value) {
//   return value.toUpperCase();
//});
//Vue.config.productionTip = false;

import Admin from './components/Admin.vue';
Vue.component('app', Admin);

//const localdata = {
//    menus: {
//        primary: [{
//            name: 'Login',
//            link: '/login'
//        }],
//        footer: []
//    }
//};

$(document).ready(function($) {
    console.log("creating Vue App");
    document.vm = new Vue({
        components: {
            app: Admin
        },
//        apolloProvider,
//        i18n,
//        router,
//        store,
        el: '#wpbroker-root',
//        data: { 
//            app: Object.assign(document.data, localdata)
//        },
//        methods: {
//            log: (msg) => {
//                console.log(msg);
//            },
//            errlog: (msg, exc) => {
//                console.log(msg,exc);
//            },
//        },
        render: h => h('app'),
    });
});

