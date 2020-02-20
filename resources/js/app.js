/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

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

import StarRating from 'vue-star-rating'
import VueApexCharts from 'vue-apexcharts'
import vSelect from 'vue-select'
import Vuetify from 'vuetify'
// import plugin
import { TiptapVuetifyPlugin } from 'tiptap-vuetify'
// don't forget to import CSS styles
import 'tiptap-vuetify/dist/main.css'
// Vuetify's CSS styles 
import 'vuetify/dist/vuetify.min.css'
import BootstrapVue from 'bootstrap-vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import VuePNotify from 'vue-pnotify'
// Import date picker css
import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
import Multiselect from 'vue-multiselect'

Vue.use(BootstrapVue)
Vue.use(VuePNotify)

// Vuetify Object (as described in the Vuetify 2 documentation)
const vuetify = new Vuetify()

// use Vuetify's plugin
Vue.use(Vuetify)
// use this package's plugin
Vue.use(TiptapVuetifyPlugin, {
  // the next line is important! You need to provide the Vuetify Object to this place.
  vuetify, // same as "vuetify: vuetify"
  // optional, default to 'md' (default vuetify icons before v2.0.0)
  iconsGroup: 'md'
})

Vue.use(VueApexCharts)
Vue.component('manifest-component', require('./components/ManifestComponent.vue').default);
Vue.component('tours-list-component', require('./components/ToursListComponent.vue').default);
Vue.component('tours-display-component', require('./components/ToursDisplayComponent.vue').default);
Vue.component('tours-profile-component', require('./components/ToursProfileComponent.vue').default);
Vue.component('guide-profile-component', require('./components/GuideProfileComponent.vue').default);
Vue.component('statistics-component', require('./components/StatisticsComponent.vue').default);
Vue.component('guide-statistics-component', require('./components/GuideStatisticsComponent.vue').default);
Vue.component('calendar-component', require('./components/CalendarComponent.vue').default);
Vue.component('notification-component', require('./components/NotificationComponent.vue').default);
Vue.component('tour-guide-list-component', require('./components/TourGuideListComponent.vue').default);
Vue.component('tour-guide-component', require('./components/TourGuideComponent.vue').default);
Vue.component('payment-component', require('./components/PaymentComponent.vue').default);
Vue.component('admin-payment-component', require('./components/AdminPaymentComponent.vue').default);
Vue.component('small-group-component', require('./components/SmallGroupComponent.vue').default);
Vue.component('article-component', require('./components/ArticleComponent.vue').default);
Vue.component('article-create-component', require('./components/ArticleCreateComponent.vue').default);
Vue.component('booking-component', require('./components/BookingComponent.vue').default);
Vue.component('star-rating', StarRating);
Vue.component('apexchart', VueApexCharts);
Vue.component('v-select', vSelect);
Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.component('multiselect', Multiselect)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});

$(document).ready(function() {

    function display_c(){
        var refresh=1000; // Refresh rate in milli seconds
        setTimeout(display_ct, refresh)
    }

    function display_ct() {
        var x = new Date()
        var hours = x.getHours()
        var minutes = x.getMinutes()
        var seconds = x.getSeconds()
        var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
        var day = x.getDate()
        var month = months[x.getMonth()]
        var year = x.getFullYear()

        if(hours < 10) {
            hours = '0' + hours
        }

        if(minutes < 10) {
            minutes = '0' + minutes
        }

        if(seconds < 10) {
            seconds = '0' + seconds
        }

        var time = hours + ':' + minutes + ':' + seconds
        $('.clock-widget .time, .clock-widget-mobile .time').html(time)

        var date = month + ' ' + day + ', ' + year
        $('.clock-widget .date, .clock-widget-mobile .date').html(date)

        display_c()
    }

    display_c()
})
