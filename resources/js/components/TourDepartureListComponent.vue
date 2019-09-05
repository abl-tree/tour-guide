<template>
    <div>
        <div id="accordion">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-center bg-primary text-white">
                            {{date}} Agenda
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" v-if="data && data.tours_today && data.tours_today.length">
                    <div class="card border-primary" v-for="(tour, index) in data.tours_today" :key="index">
                        <div class="card-header text-center border-primary" id="headingOne" style="cursor: pointer;" data-toggle="collapse" :data-target="'#collapse'+index" aria-expanded="true" :aria-controls="'collapse'+index" @click="onToggleCollapse(index)">
                            <small v-if="tour.info && tour.info.tour_code">{{ tour.info.tour_code }}</small>
                            <br v-if="tour.info && tour.info.tour_code">
                            <small style="font-weight: bold;">{{ tour.title }}</small>
                        </div>

                        <div :id="'collapse'+index" :class="'collapse'+(index === 0 ? ' show' : '')" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <b-list-group>
                                    <b-list-group-item v-for="(departure, index) in tour.departures" :key="index" class="text-center">
                                        <font-awesome-icon class="pull-right" icon="minus-circle" style="cursor: pointer; color: red; font-size: 12px;" @click="deleteDeparture(departure)" />
                                        <small>Departure {{index + 1}}</small><br>
                                        <small>Tour Guide: 
                                            <span v-if="departure.schedule && departure.schedule.full_name">{{departure.schedule.full_name}}</span>
                                            <span v-else style="font-weight: bold; color: red;">No Guide Yet</span>
                                        </small><br>
                                        <small>Starting Time: {{departure.departure}}</small><br>
                                        <small><b-link @click="autoAssignment(departure)">Auto</b-link> | <b-link data-id="id ni" @click="manualAssignmentForm($event, departure)">Manual</b-link></small>
                                    </b-list-group-item>
                                    <b-list-group-item variant="success" class="text-center" button @click="addDeparture(tour)">Add Departure</b-list-group-item>
                                </b-list-group>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" v-else>
                    <div class="card">
                        <div class="card-body">
                            No Tour Available
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <b-modal id="manual-assignment" centered title="Manual Guide Assignment" @ok="manualAssignment">
            <b-form-select v-model="selectedAvailable" class="mb-3">
                <option :value="null">Please select an option</option>
                <option v-for="(available, index) in data.availables" :key="index" :value="available.user.id">{{available.full_name}}</option>
            </b-form-select>
        </b-modal>
    </div>
</template>

<script>
import { log } from 'util';
export default {
    name: 'TourDepartureList',
    props: {
        date: String,
        data: Object,
        loading: Boolean,
        isAdmin: Boolean,
        errors: Object,
        toggleCollapse: Number
    },
    data() {
        return {
            selectedAvailable: null,
            selectedDeparture: null
        }
    },
    methods: {
        check(args) {
            this.$emit('tourGuideClicked', {'data' : args});
        }, 
        available(args, flag, shift) {
            args.shift = shift;

            this.$emit('availabilityClicked', {'data' : args, 'flag' : flag});
        },
        onToggleCollapse($toggle) {
            this.$emit('onToggleCollapse', $toggle);
        },
        addDeparture(tour) {

            tour['date'] = this.date

            this.$emit('departureAdd', tour)

        },
        deleteDeparture(departure) {
            
            if(!confirm("Do you really want to delete it?")) return

            this.$emit('departureDelete', departure);

        },
        autoAssignment(departure) {

            this.$emit('autoAssignment', departure)

        },
        manualAssignmentForm(event, departure) {
            this.departure = departure

            this.$root.$emit('bv::show::modal', 'manual-assignment', event)
        },
        manualAssignment() {

            this.$emit('manualAssignment', this.departure, this.selectedAvailable)

        }
    },
    updated() {
        if(this.toggleCollapse === 0) {
            $('#collapseOne').collapse('show');
        } else if(this.toggleCollapse === 1) {
            $('#collapseTwo').collapse('show');
        } else if(this.toggleCollapse === 2) {
            $('#collapseThree').collapse('show');
        }
    },
    mounted() {
        this.$root.$on('bv::modal::show', (bvEvent, modalId) => {
        })
        
        this.$root.$on('bv::modal::hidden', (bvEvent, modalId) => {
            this.departure = null
            this.selectedAvailable = null
        })
    }
}
</script>
