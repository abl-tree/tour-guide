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
                    @dateClick="handleDateClick"
                    @eventClick="handleDateClick"
                    :plugins="calendarPlugins"
                    :weekends="true"
                    eventTextColor="White"
                    :events="events"
                    :selectable="true" />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <TourGuideList :date="date" :data="tour_guides" :loading="loading" :isAdmin="isAdmin" :errors="errors" @tourGuideClicked="onChangeTourGuide" @availabilityClicked="onChangeAvailability"/>
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
    data() {
        return {
            calendarPlugins: [ dayGridPlugin, interactionPlugin ],
            events: [],
            tour_guides: {},
            date: "",
            loading: true,
            isAdmin: false,
            errors: {
                morning: "",
                afternoon: "",
                evening: "",
            }
        }
    },
    methods: {
        handleDateClick(arg) {
            this.date = arg.dateStr
            
            var params = {url:"/schedule/show/" + this.date};

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
        get(args, load = true) {
            this.loading = load

            axios.get(args.url)
            .then(response => {
                this.events = [
                    { title: response.data.schedules.pending ? response.data.schedules.pending + " pending" : "No pending", date: response.data.date },
                    { title: response.data.schedules.scheduled ? response.data.schedules.scheduled + " scheduled" : "No scheduled", date: response.data.date, color: 'green' }
                ]
                this.tour_guides = response.data.tour_guides
                this.date = response.data.date
                this.isAdmin = response.data.isAdmin
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
                this.load(false)
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
                this.loading = false
                this.load()
            });
        },
        load(load) {
            var params = {url:"/schedule/show/" + this.date}
            
            this.get(params, load)
        }
    },
    created() {
        this.load()
    }
}
</script>
