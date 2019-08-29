/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

import BootstrapVue from 'bootstrap-vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faTrashAlt } from '@fortawesome/free-solid-svg-icons'
import { faFileImage } from '@fortawesome/free-solid-svg-icons'
import { faEdit } from '@fortawesome/free-solid-svg-icons'
import { faClock } from '@fortawesome/free-solid-svg-icons'
import { faArrowUp } from '@fortawesome/free-solid-svg-icons'
import { faArrowDown } from '@fortawesome/free-solid-svg-icons'
import { faMinusCircle } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(faTrashAlt)
library.add(faFileImage)
library.add(faEdit)
library.add(faClock)
library.add(faArrowUp)
library.add(faArrowDown)
library.add(faMinusCircle)

Vue.use(BootstrapVue)
Vue.component('tours-list-component', require('./components/ToursListComponent.vue').default);
Vue.component('tours-display-component', require('./components/ToursDisplayComponent.vue').default);
Vue.component('tours-profile-component', require('./components/ToursProfileComponent.vue').default);
Vue.component('calendar-component', require('./components/CalendarComponent.vue').default);
Vue.component('tour-guide-list-component', require('./components/TourGuideListComponent.vue').default);
Vue.component('tour-guide-component', require('./components/TourGuideComponent.vue').default);
Vue.component('payment-component', require('./components/PaymentComponent.vue').default);
Vue.component('small-group-component', require('./components/SmallGroupComponent.vue').default);
Vue.component('font-awesome-icon', FontAwesomeIcon)

// Import required dependencies 
import 'bootstrap/dist/css/bootstrap.css';

// Import date picker css
import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});
