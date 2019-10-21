<template>
    <div>
        <div id="accordion">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-center bg-primary text-white">
                            {{date}} Agenda

                            <b-spinner v-if="loading" class="pull-right" size="sm" variant="light" label="Spinning"></b-spinner>
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
                                    <b-list-group-item v-for="(departure, depIndex) in tour.departures" :key="depIndex" class="text-center">
                                        <font-awesome-icon class="pull-right" icon="minus-circle" style="cursor: pointer; color: red; font-size: 12px;" @click="deleteDeparture(departure)" />
                                        <small>Departure {{depIndex + 1}}</small><br>
                                        <small><b-link @click="serialModal(departure)">Show Voucher Numbers</b-link> <b-badge pill variant="danger">{{departure.serial_numbers.length}}</b-badge></small><br>
                                        <small>Tour Guide: 
                                            <span v-if="departure.schedule && departure.schedule.full_name">{{departure.schedule.full_name}}</span>
                                            <span v-else style="font-weight: bold; color: red;">No Guide Yet</span>
                                        </small><br>
                                        <small>Starting Time: {{departure.departure}}</small><br>
                                        <small><b-link @click="autoAssignment(departure)">Auto</b-link> | <b-link data-id="id ni" @click="manualAssignmentForm($event, departure, tour.available)">Manual</b-link></small>
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
            <v-select v-model="selectedAvailable" label="full_name" :reduce="full_name => full_name.id" :options="availableGuideLists" class="mb-3">
                <!-- <option :value="null">Please select an option</option>
                <option v-for="(available, index) in data.availables" :key="index" :value="available.id">{{available.full_name}}</option> -->
            </v-select>
        </b-modal>

        <!-- The modal -->
        <b-modal ref="serial-numbers-modal" ok-title="Complete" title="Voucher Numbers">
            <div v-if="selectedDeparture && selectedDeparture.serial_numbers">
                <small v-for="(serial, index) in selectedDeparture.serial_numbers" :key="index" class="row justify-content-md-center">
                    <b-col sm="12">
                        <b-input-group size="sm" :state="true">
                            <b-form-input v-model="serial.serial_number" placeholder="Serial Number" size="sm" maxlength="30" @input="serialInputChange(serial)"></b-form-input>

                            <!-- <b-input-group-append>
                                <b-button variant="success" @click="updateSerialNumber(serial)">Update</b-button>
                                <b-button variant="danger" @click="deleteSerialNumber(serial)">Delete</b-button>
                            </b-input-group-append> -->
                        </b-input-group>
                    </b-col>
                </small>
            </div>
            <b-input-group size="sm">
                <b-form-input type="text" v-model="newSerialNumber" :state="!Boolean(serialError)"></b-form-input>

                <b-input-group-append>
                    <b-button variant="success" @click="addSerialNumber(selectedDeparture)">
                        <b-spinner
                            v-if="addVoucherLoading"
                            small
                            variant="light"
                        ></b-spinner>
                        <span v-else>Add</span>
                    </b-button>
                </b-input-group-append>
            </b-input-group>
            <b-form-invalid-feedback :state="!Boolean(serialError)">
                {{serialError}}
            </b-form-invalid-feedback>
        </b-modal>
    </div>
</template>

<script>
import { log } from 'util';

const CancelToken = axios.CancelToken
let cancel

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
            selectedDeparture: null,
            newSerialNumber: null,
            serialError: null,
            availableGuideLists: null,
            addVoucherLoading: false
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
        manualAssignmentForm(event, departure, available) {
            this.availableGuideLists = available
            
            this.departure = departure

            this.$root.$emit('bv::show::modal', 'manual-assignment', event)
        },
        manualAssignment() {

            this.$emit('manualAssignment', this.departure, this.selectedAvailable)

        },
        serialInputChange(input) {
            cancel && cancel()
             
            axios.put('departure/serial_number',
            input,
            {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            })
        },
        serialModal: function(data) {
            this.selectedDeparture = data
            
            this.$refs['serial-numbers-modal'].show()
        },
        addSerialNumber: function(input) {
            this.serialError = null

            this.addVoucherLoading = true

            input.serial_number = this.newSerialNumber

            cancel && cancel()
             
            axios.post('departure/serial_number/add',
            input, {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            }).then(data => {
                this.selectedDeparture = data.data

                this.newSerialNumber = null
                
                this.$emit('onLoad')
            }).catch(err => {
                let errors = err.response &&  err.response.data ? err.response.data.errors : null
                
                if(errors && errors['serial_number']) this.serialError = errors['serial_number'][0]
            }).finally(final => {

                this.addVoucherLoading = false

            })
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
