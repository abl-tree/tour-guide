<style lang='scss'>

@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';

</style>

<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Calendar</div>

                <div class="card-body">
                    <FullCalendar 
                    defaultView="dayGridMonth"
                    ref="fullCalendar"
                    @dateClick="handleDateClick"
                    :customButtons="customButtons"
                    :plugins="calendarPlugins"
                    :selectable="true"
                    :header="header"
                    v-if="payment" />

                    <FullCalendar 
                    defaultView="dayGridMonth"
                    ref="fullCalendar"
                    eventOrder="id"
                    eventTextColor="White"
                    @dateClick="handleDateClick"
                    @eventClick="handleEventClick"
                    :customButtons="customButtons"
                    :plugins="calendarPlugins"
                    :events="events"
                    :selectable="true"
                    :header="header"
                    v-else />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <TourGuideList 
                :date="date" 
                :data="tour_guides" 
                :tour_titles="tour_titles"
                :loading="loading" 
                :isAdmin="isAdmin" 
                :errors="errors" 
                :payment="payment"
                @onLoad="load"
                v-if="payment"/>

            <TourGuideList 
                :date="date" 
                :data="tour_guides" 
                :tour_titles="tour_titles"
                :loading="loading" 
                :isAdmin="isAdmin" 
                :errors="errors" 
                :toggleCollapse="toggleCollapse"
                @onToggleCollapse="onToggleCollapseChange"
                @tourGuideClicked="onChangeTourGuide" 
                @availabilityClicked="onChangeAvailability"
                @onTourTitleChange="onTourTitleChange"
                @onLoad="load"
                v-else/>
        </div>
    </div>
    
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import TourGuideList from './TourGuideListComponent'
import { log } from 'util';

export default {
    components: {
        TourGuideList,
        FullCalendar // make the <FullCalendar> tag available
    },
    props: {
        payment: Boolean
    },
    data() {
        var self = this

        return {
            calendarPlugins: [ dayGridPlugin, interactionPlugin ],
            events: [],
            tour_guides: {},
            tour_titles: [],
            date: "",
            selectedDate: "",
            loading: true,
            isAdmin: false,
            errors: {
                morning: "",
                afternoon: "",
                evening: "",
            },
            toggleCollapse: 0,
            header: {
                right: ''
            },
            customButtons: {
                next: {
                    text: 'Next',
                    click: function() {
                        let calendarApi = self.$refs.fullCalendar.getApi()

                        calendarApi.next()

                        let dates = {
                            'start': calendarApi.view.activeStart,
                            'end': calendarApi.view.activeEnd
                        }                        

                        let d = new Date(calendarApi.view.currentStart),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        self.date = [year, month, day].join('-')

                        let params = {url:"/schedule/show/" + self.date, data: dates}    

                        self.get(params)     
                    }
                },
                prev: {
                    text: 'Previous',
                    click: function() {
                        let calendarApi = self.$refs.fullCalendar.getApi()               

                        calendarApi.prev()                        

                        let dates = {
                            'start': calendarApi.view.activeStart,
                            'end': calendarApi.view.activeEnd
                        }

                        let d = new Date(calendarApi.view.currentStart),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        self.date = [year, month, day].join('-')

                        let params = {url:"/schedule/show/" + self.date, data: dates}        

                        self.get(params)         
                    }
                },
                today: {
                    text: 'Today',
                    click: function() {
                        let calendarApi = self.$refs.fullCalendar.getApi()     

                        calendarApi.today()

                        let dates = {
                            'start': calendarApi.view.activeStart,
                            'end': calendarApi.view.activeEnd
                        }

                        let d = new Date(calendarApi.getDate()),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();

                        if (month.length < 2) month = '0' + month;
                        if (day.length < 2) day = '0' + day;

                        self.date = [year, month, day].join('-')

                        let params = {url:"/schedule/show/" + self.date, data: dates}        

                        self.get(params)         
                    }
                },
                download: {
                    text: 'Download',
                    click: function() {
                        let calendarApi = self.$refs.fullCalendar.getApi()  

                        function parseDate(date, yesterday = false) {
                            let d = new Date(date)

                            if(yesterday)
                            d.setDate(d.getDate() - 1)

                            let month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-')
                        }

                        let routeData = window.location.origin + '/schedule/export' + '?start=' + parseDate(calendarApi.view.currentStart) + '&end=' + parseDate(calendarApi.view.currentEnd, true);

                        window.open(routeData, '_blank');
                    }
                }
            }
        }
    },
    methods: {
        handleDateClick(arg) {
            this.date = arg.dateStr

            let calendarApi = this.$refs.fullCalendar.getApi()

            var dates = {
                'start': calendarApi.view.activeStart,
                'end': calendarApi.view.activeEnd
            }
            
            var params = {url:"/schedule/show/" + this.date, data: dates}
            
            if(this.payment) {
                params = {url:"/payment/show/" + this.date, data: dates}
            }

            this.get(params);
        },
        handleEventClick(arg) {
            var d = new Date(arg.event.start),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            this.date = [year, month, day].join('-')

            this.toggleCollapse = parseInt(arg.event.id)

            let calendarApi = this.$refs.fullCalendar.getApi()

            var dates = {
                'start': calendarApi.view.activeStart,
                'end': calendarApi.view.activeEnd
            }
            
            var params = {url:"/schedule/show/" + this.date, data: dates}

            this.get(params);
        },
        onChangeTourGuide (args) {
            var params = {data: args.data, url:"/schedule/" + args.data.id};
            
            this.update(params);
        },
        onChangeAvailability (args) {
            var params;

            if(args.data.schedules.length) {
                params = {data: args.data, url:"/schedule/" + args.data.schedules[0].id};

                this.delete(params);
            } else {
                params = {data: args.data, url:"/schedule"};

                this.store(params);
            }
        },
        onToggleCollapseChange (toggle) {
            this.toggleCollapse = toggle
        },
        onTourTitleChange (args) {            
            var params = {data: args, url:"/schedule/" + args.schedule};
            
            this.update(params);
        },
        get(args, load = true) {
            var data = args.data
            this.loading = load

            axios.get(args.url, {
                params: {
                    'start': (data && data.start) ? data.start : '',
                    'end': (data && data.end) ? data.end : ''
                }
            })
            .then(response => {
                if(this.payment) {                    
                    this.tour_guides = response.data.data
                    this.date = response.data.date
                    this.isAdmin = response.data.isAdmin

                    return
                }

                this.events = response.data.schedules
                this.tour_guides = response.data.tour_guides
                this.date = response.data.date
                this.isAdmin = response.data.isAdmin
                this.header = {
                    right: this.isAdmin ? 'download today prev,next' : 'today prev,next'
                }
                this.tour_titles = response.data.tour_titles
            })
            .catch(function (error) {
                if(args.data.shift === 'Morning') {
                    this.errors.morning = error.response.data.error;
                } else if(args.data.shift === 'Afternoon') {
                    this.errors.afternoon = error.response.data.error;
                } else if(args.data.shift === 'Evening') {
                    this.errors.evening = error.response.data.error;
                }
            })
            .finally(final => {
                this.loading = false
            });
        },
        update(args) {
            axios.put(args.url, args.data)
            .then(response => {
            })
            .catch(function (error) {
                //error
            })
            .finally(final => {
                this.load(false);
                this.loading = false
            });
        },
        store(args) {
            axios.post(args.url, args.data)
            .then(response => {
                this.load()
            })
            .catch(function (error) {
                //error
            })
            .finally(final => {
                this.loading = false
            });
        },
        delete(args) {
            axios.delete(args.url)
            .then(response => {
                //success
            })
            .catch(error => {
                if(args.data.shift === 'Morning') {
                    this.errors.morning = error.response.data.error;
                } else if(args.data.shift === 'Afternoon') {
                    this.errors.afternoon = error.response.data.error;
                } else if(args.data.shift === 'Evening') {
                    this.errors.evening = error.response.data.error;
                }
            })
            .finally(final => {
                this.load(false)
                this.loading = false
            });
        },
        load(load) {
            console.log('load', load);
            
            // let calendarApi = this.$refs.fullCalendar.getApi()
            // var dates = {
            //     'start': calendarApi.view.activeStart,
            //     'end': calendarApi.view.activeEnd
            // }

            // var params = {url:"/schedule/show", data: dates}      
            
            // this.get(params, load)

            let calendarApi = this.$refs.fullCalendar.getApi()

            let dates = {
                'start': calendarApi.view.activeStart,
                'end': calendarApi.view.activeEnd
            }

            let d = new Date(calendarApi.getDate()),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            this.date = this.date ? this.date : [year, month, day].join('-')

            let params = {url:"/schedule/show/" + this.date, data: dates}        
            
            if(this.payment) {
                params = {url:"/payment/show/" + this.date, data: dates}
            }

            this.get(params, load)
        }
    },
    mounted() {  
        this.load()

        console.log(this.payment)
    }
}
</script>
