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
                                        <small><b-link @click="serialModal(departure)">Show Voucher Numbers</b-link> <b-badge pill :variant="departure.complete_voucher ? 'success' : 'danger'">{{departure.serial_numbers.length}}</b-badge></small><br>
                                        <small>Tour Guide: 
                                            <span v-if="departure.schedule && departure.schedule.full_name">
                                                {{departure.schedule.full_name}} 
                                                <font-awesome-icon icon="paper-plane" style="cursor: pointer; color: orange; font-size: 12px;" @click="notifyGuide(departure.schedule)" />
                                            </span>
                                            <span v-else style="font-weight: bold; color: red;">No Guide Yet</span>
                                        </small><br>
                                        <small>Starting Time: {{departure.departure}}</small><br>
                                        <small>
                                            <b-link @click="autoAssignment(departure)">Auto</b-link> | 
                                            <b-link data-id="id ni" @click="manualAssignmentForm($event, departure, tour.vacant)">Full Lists</b-link> |
                                            <b-link data-id="id ni" @click="manualAssignmentForm($event, departure, tour.available)">Availables</b-link>
                                        </small><br>
                                        <small>
                                            <b-link @click="noteModal(departure)">Notes</b-link>
                                            <font-awesome-icon icon="sticky-note" :color="departure.notes ? 'red' : 'green'" />
                                        </small>
                                        <small>
                                            <b-input-group prepend="Adult" size="sm" v-if="tour.info.type.code === 'small'">
                                                <b-form-input type="number" min="0" max="13" v-model="departure.adult_participants"></b-form-input>
                                                <b-input-group-prepend is-text>Children</b-input-group-prepend>
                                                <b-form-input type="number" min="0" max="10" v-model="departure.child_participants"></b-form-input>
                                                <b-input-group-append>
                                                    <b-button size="sm" text="Button" variant="success" @click="participantSubmit(departure)">Save</b-button>
                                                </b-input-group-append>
                                            </b-input-group>
                                            <b-input-group prepend="Earning" size="sm" v-if="tour.info.type.code === 'private'">
                                                <b-form-input type="number" min="0" v-model="departure.earning"></b-form-input>
                                                <b-input-group-append>
                                                    <b-button size="sm" text="Button" variant="success" @click="earningSubmit(departure)">Save</b-button>
                                                </b-input-group-append>
                                            </b-input-group>
                                        </small>
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
        
        <b-modal id="manual-assignment" centered title="Tour Guide Lists" @ok="manualAssignment">
            <v-select v-model="selectedAvailable" label="full_name" :reduce="full_name => full_name.id" :options="availableGuideLists" class="mb-3">
                <!-- <option :value="null">Please select an option</option>
                <option v-for="(available, index) in data.availables" :key="index" :value="available.id">{{available.full_name}}</option> -->
            </v-select>
        </b-modal>

        <!-- Notes Modal -->
        <b-modal centered ref="notes-modal" title="Notes" @ok="submitNote">
            <b-form-textarea
            id="textarea"
            v-model="note"
            placeholder="Enter something..."
            rows="3"
            max-rows="6"
            ></b-form-textarea>
        </b-modal>

        <!-- The modal -->
        <b-modal ref="serial-numbers-modal" centered cancel-variant="danger" cancel-title="Incomplete" ok-title="Complete" @ok="completeVoucher" @cancel="incompleteVoucher" title="Voucher Numbers">

            <div v-if="selectedDeparture && selectedDeparture.serial_numbers">
                <small v-for="(serial, index) in selectedDeparture.serial_numbers" :key="index" class="row justify-content-md-center">
                    <b-col sm="7">
                        <b-input-group size="sm" :state="true">
                            <b-form-input v-model="serial.serial_number" placeholder="Serial Number" size="sm" maxlength="30" @input="serialInputChange(serial)"></b-form-input>
                        </b-input-group>
                    </b-col>
                    <b-col sm="5">
                        <b-input-group prepend="€" size="sm" :state="true">                            
                            <b-form-input v-model="serial.cost" type="number" placeholder="Serial Cost" size="sm" @input="serialInputChange(serial)"></b-form-input>
                        </b-input-group>
                    </b-col>
                </small>
            </div>
            <br>
            <b-row v-if="selectedDeparture && selectedDeparture.complete_voucher === 0">
                <b-col sm="6">

                    <b-input-group size="sm">
                        <b-form-input type="text" placeholder="Voucher Code" v-model="newSerialNumber" :state="!Boolean(serialError || completedSerialError)"></b-form-input>
                    </b-input-group>

                </b-col>
                <b-col sm="6">

                    <b-input-group size="sm" prepend="€">
                        <b-form-input type="text" placeholder="Voucher Cost" v-model="newSerialNumberCost" :state="!Boolean(serialError || completedSerialError)"></b-form-input>

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

                </b-col>
            </b-row>
            <div v-else>
                Remarks: <b-badge variant="success">Complete</b-badge>
            </div>
            <b-form-invalid-feedback :state="!Boolean(serialError)">
                {{serialError}}
            </b-form-invalid-feedback>
            <b-form-invalid-feedback :state="!Boolean(completedSerialError)">
                {{completedSerialError}}
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
            newSerialNumberCost: 0,
            serialError: null,
            completedSerialError: null,
            availableGuideLists: null,
            addVoucherLoading: false,
            note: null
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

            input.cost = this.newSerialNumberCost

            cancel && cancel()
             
            axios.post('departure/serial_number/add',
            input, {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            }).then(data => {
                this.selectedDeparture = data.data

                this.newSerialNumber = null

                this.newSerialNumberCost = 0
                
                this.$emit('onLoad')
            }).catch(err => {
                let errors = err.response &&  err.response.data ? err.response.data.errors : null
                
                if(errors && errors['serial_number']) this.serialError = errors['serial_number'][0]

                if(errors && errors['completed']) this.completedSerialError = errors['completed'][0]
            }).finally(final => {

                this.addVoucherLoading = false

            })
        },
        completeVoucher(event) {
            event.preventDefault()
            
            axios.put('voucher/complete', this.selectedDeparture)
            .then(data => {
                this.selectedDeparture.complete_voucher = data.data.complete_voucher

                this.$emit('onLoad')
            }).catch(err => {
                let errors = err.response &&  err.response.data ? err.response.data.errors : null
                
                if(errors && errors['serial_number']) this.serialError = errors['serial_number'][0]
            }).finally(final => {

            })

        },
        incompleteVoucher(event) {
            event.preventDefault()
            
            axios.put('voucher/incomplete', this.selectedDeparture)
            .then(data => {
                this.selectedDeparture.complete_voucher = data.data.complete_voucher

                this.$emit('onLoad')
            }).catch(err => {
                let errors = err.response &&  err.response.data ? err.response.data.errors : null
                
                if(errors && errors['serial_number']) this.serialError = errors['serial_number'][0]
            }).finally(final => {

            })
        },
        noteModal: function(data) {
            this.selectedDeparture = data

            this.note = this.selectedDeparture.notes
            
            this.$refs['notes-modal'].show()
        },
        submitNote: function(event) {
            event.preventDefault()

            this.selectedDeparture.notes = this.note

            axios.post('departure/note', this.selectedDeparture)
                .then(data => {
                    
                    this.$emit('onLoad')

                })
                .catch(err => {
                    alert('Error! Try Again!')
                })
                .finally(final => {

                })
            
        },
        participantSubmit(departure) {
            cancel && cancel()

            axios.put('departure/participant', departure,
            {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            })
        },
        earningSubmit(departure) {
            cancel && cancel()

            axios.put('departure/earning', departure,
            {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            })
        },
        notifyGuide(schedule) {
            console.log('notify', schedule)

            axios.post('notification/departure', schedule)
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
