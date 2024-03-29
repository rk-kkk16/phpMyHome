/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import * as filters from './filters';
Object.keys(filters).forEach(key => {
    Vue.filter(key, filters[key]);
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */


import KaimonoList from './components/KaimonoList';
import DashKaimonoList from './components/dash/KaimonoList';
import ImagepostEval from './components/ImagepostEval';
import ImagepostComment from './components/ImagepostComment';
import DashImagepost from './components/dash/Imagepost';
import ScrapComment from './components/ScrapComment';
import ScrapGoodPoint from './components/ScrapGoodPoint';
import DashGoodpost from './components/dash/GoodPost';
import BugReportList from './components/BugReportList';

const app = new Vue({
    el: '#app',
    components: {
        KaimonoList,
        ImagepostEval,
        ImagepostComment,
        DashKaimonoList,
        DashImagepost,
        ScrapComment,
        ScrapGoodPoint,
        DashGoodpost,
        BugReportList,
    }
});

// toastr added
require('./toast');

