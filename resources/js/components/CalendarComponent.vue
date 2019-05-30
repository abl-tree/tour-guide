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
                    :plugins="calendarPlugins"
                    :weekends="true"
                    :events="events" />
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Tour Guide</div>

                <div class="card-body">
                    <TourGuideList title="Title" :data="events" />
                </div>
            </div>
        </div>
    </div>
    
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import TourGuideList from './TourGuideListComponent'

export default {
    components: {
        TourGuideList,
        FullCalendar // make the <FullCalendar> tag available
    },
    data() {
        return {
            calendarPlugins: [ dayGridPlugin, interactionPlugin ],
            events: []
        }
    },
    methods: {
        handleDateClick(arg) {
            console.log(arg)
        }
    },
    created() {

        // this.events = [
        //                 { title: 'event 1', date: '2019-05-01' , color: 'red'},
        //                 { title: 'event 1', date: '2019-05-02' },
        //                 { title: 'event 1', date: '2019-04-30' },
        //                 { title: 'event 1', date: '2019-05-01' },
        //                 { title: 'event 1', date: '2019-05-01' },
        //                 { title: 'event 1', date: '2019-05-01' },
        //                 { title: 'event 2', date: '2019-05-01' }
        //             ];
        axios.get('/schedule/show')
            .then(res => {
                this.events = res.data;
            }).catch(err => {
            console.log(err)
        });
    }
}
</script>
