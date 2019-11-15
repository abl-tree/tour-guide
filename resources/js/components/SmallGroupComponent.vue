<style lang='scss'>

@import '~@fullcalendar/core/main.css';
@import '~@fullcalendar/daygrid/main.css';

.fc-highlight {
    background: green
}

</style>

<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ tour_category ? 'Private Tour Calendar' : 'Small Group Calendar'}}
                    <b-spinner v-if="loading" class="pull-right" variant="primary" label="Spinning"></b-spinner>
                </div>

                <div class="card-body">
                    <b-row class="my-1" v-if="isAdmin">
                        <b-col sm="3">
                            <label for="guide-filter">Category:</label>
                        </b-col>
                        <b-col sm="9">
                            <b-form-select
                            id="guide-filter"
                            v-model="tour_category"
                            @change="load"
                            required
                            >
                                <option :value="false"> Small Group</option>
                                <option :value="true"> Private Tour</option>
                            </b-form-select>
                        </b-col>
                    </b-row>

                    <b-row class="my-1" v-if="isAdmin">
                        <b-col sm="3">
                            <label for="date-picker">Select Date:</label>
                        </b-col>
                        <b-col sm="9">
                            <b-form-input id="date-picker" v-model="selectedDate" type="month" @change="selectedDateChange"></b-form-input>
                        </b-col>
                    </b-row>

                    <b-row>
                        <b-col>
                                    
                            <FullCalendar 
                            defaultView="dayGridMonth"
                            ref="fullCalendar"
                            eventOrder="id"
                            eventTextColor="White"
                            @dateClick="handleDateClick"
                            @eventClick="handleEventClick"
                            @eventRender="eventRender"
                            :customButtons="customButtons"
                            :plugins="calendarPlugins"
                            :events="events"
                            :selectable="true"
                            :header="header"
                            :eventLimit="eventLimit"
                            :views="views" />

                        </b-col>
                    </b-row>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <TourDepartureList 
                :date="date" 
                :data="tour_departures" 
                :tour_titles="tour_titles"
                :loading="loading" 
                :isAdmin="isAdmin" 
                :errors="errors" 
                :toggleCollapse="toggleCollapse"
                @onToggleCollapse="onToggleCollapseChange"
                @tourGuideClicked="onChangeTourGuide" 
                @departureDelete="onDeleteDeparture"
                @departureAdd="onAddDeparture"
                @autoAssignment="onAutoAssign"
                @manualAssignment="onManualAssign"
                @availabilityClicked="onChangeAvailability"
                @addGuide="onAddGuide"
                @onTourTitleChange="onTourTitleChange"
                @onLoad="load"/>
        </div>
    </div>
    
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import TourDepartureList from './TourDepartureListComponent'
import { log } from 'util'
import moment from 'moment'

export default {
    components: {
        TourDepartureList,
        FullCalendar // make the <FullCalendar> tag available
    },
    props: {
        private: Boolean
    },
    data() {
        var self = this

        return {
            tour_category: false,
            calendarPlugins: [ dayGridPlugin, interactionPlugin ],
            events: [],
            full_data: null,
            tour_guide: null,
            tour_departures: {},
            tour_titles: [],
            date: "",
            selectedDate: moment().format('YYYY-MM'),
            loading: true,
            isAdmin: false,
            errors: {
                morning: "",
                afternoon: "",
                evening: "",
            },
            toggleCollapse: 0,
            eventLimit: true, // for all non-TimeGrid views
            views: {
                dayGridMonth: {
                    eventLimit: 4 // adjust to 6 only for timeGridWeek/timeGridDay
                }
            },
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

                        let params = {url: (self.tour_category ? "/privategroup/show/" : "/smallgroup/show/") + self.date, data: dates}

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

                        let params = {url: (self.tour_category ? "/privategroup/show/" : "/smallgroup/show/") + self.date, data: dates}

                        self.get(params)         
                    }
                },
                currentDay: {
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

                        let params = {url: (self.tour_category ? "/privategroup/show/" : "/smallgroup/show/") + self.date, data: dates}

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

                        let routeData = window.location.origin + '/departure/export' + (self.tour_guide ? '?user=' + self.tour_guide + '&' : '?') + 'start=' + parseDate(calendarApi.view.currentStart) + '&end=' + parseDate(calendarApi.view.currentEnd, true);

                        window.open(routeData, '_blank');
                    }
                }
            }
        }
    },
    methods: {
        eventRender: function(info) {

            let element = info.el;            
            
            // $(element).tooltip({
            //     title: info.event.extendedProps.name,
            //     placement: 'top',
            //     trigger: 'hover',
            //     container: 'body',
            //     html: true
            // })         

            $(element).find('.fc-title').css({'white-space': 'normal'})

        },
        renderOptions() {
            let options = [{ text: 'All', value: null }];
            
            let guides = this.full_data ? this.full_data.lists : null; 
            
            if(guides)
            for(let a = 0; a < guides.length; a++) {
                let obj = {text: guides[a].full_name, value: guides[a].id}

                options.push(obj)
            }

            return options
        },
        guideFilterChange() {
            this.load();
        },
        handleDateClick(arg) {
    // change the day's background color just for fun
    $(this).css('background-color', 'red');

            this.date = arg.dateStr

            let calendarApi = this.$refs.fullCalendar.getApi()

            var dates = {
                'start': calendarApi.view.activeStart,
                'end': calendarApi.view.activeEnd
            }
            
            var params = {url:"/smallgroup/show/" + this.date, data: dates}
            
            if(this.tour_category) {
                params = {url:"/privategroup/show/" + this.date, data: dates}
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
            
            var params = {url:"/smallgroup/show/" + this.date, data: dates}
            
            if(this.tour_category) {
                params = {url:"/privategroup/show/" + this.date, data: dates}
            }

            this.get(params);
        },
        onDeleteDeparture(args) {

            axios.delete('departure/'+args.id)
            .then(data => {
                
                this.load()

            })
            .catch(err => {

            })

        },
        onAddDeparture(args) {
            
            axios.post('departure', args)
            .then(data => {
                
                this.load()

            })
            .catch(err => {

            })

        },
        onAutoAssign(args) {
            
            axios.put('departure/auto', args)
            .then(data => {
                
                this.load()

            })
            .catch(err => {

                let error = err.response.data.error
                alert(error);

            })

        },
        onManualAssign(departure, guide) {

            console.log(departure, guide)
            
            axios.put('departure/manual', {
                id: departure.id,
                guide: guide
            })
            .then(data => {
                
                this.load()

            })
            .catch(err => {

                let error = err.response.data.error
                alert(error)

            })
            
        },
        onChangeTourGuide (args) {
            // var params = {data: args.data, url:"/schedule/" + args.data.id};
            
            // this.update(params);
        },
        onChangeAvailability (args) {
            // var params;

            // if(args.data.schedules.length) {
            //     params = {data: args.data, url:"/schedule/" + args.data.schedules[0].id};

            //     this.delete(params);
            // } else {
            //     params = {data: args.data, url:"/schedule"};

            //     this.store(params);
            // }
        },
        onAddGuide(args) {
            let params = {data: args.data, url:"/schedule"};

            this.store(params);
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

            let params = (this.tour_guide) ? {
                    'start': (data && data.start) ? data.start : '',
                    'end': (data && data.end) ? data.end : '',
                    'user': this.tour_guide
                } : {
                    'start': (data && data.start) ? data.start : '',
                    'end': (data && data.end) ? data.end : ''
                };

            axios.get(args.url, {
                params: params
            })
            .then(response => {
                this.full_data = response.data
                this.events = response.data.schedules
                this.tour_departures = response.data.selected_date                
                this.date = response.data.date
                this.isAdmin = response.data.isAdmin
                this.header = {
                    // right: 'download today prev,next'
                    right: 'download currentDay prev,next'
                }
                this.tour_titles = response.data.tour_titles

                let select_date = moment(response.data.date).format('YYYY-MM')
                
                this.selectedDate = select_date
                
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

                $('.fc-day[data-date="' + moment(this.date).format('YYYY-MM-DD') + '"]').addClass("fc-highlight")
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
            load = true
            
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

            let params = {url:"/smallgroup/show/" + this.date, data: dates}        
            
            if(this.tour_category) {
                params = {url:"/privategroup/show/" + this.date, data: dates}
            }

            this.get(params, load)
        },
        selectedDateChange() {
            this.date = this.selectedDate + '-01'
            let calendarApi = this.$refs.fullCalendar.getApi()

            calendarApi.gotoDate(this.date)

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

            this.date = [year, month, day].join('-')

            let params = {url: (this.private ? "/privategroup/show/" : "/smallgroup/show/") + this.date, data: dates}

            this.get(params)
        }
    },
    mounted() {
        this.tour_category = this.private

        this.load()
    }
}
</script>
