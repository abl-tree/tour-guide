<template>
    <div>
        <div id="accordion">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header text-center bg-primary text-white">
                            {{dateFormatted}} Agenda

                            <b-spinner v-if="loading" class="pull-right" size="sm" variant="light" label="Spinning"></b-spinner>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" v-if="data && data.tours_today && data.tours_today.length">
                    <div class="card border-primary" v-for="(tour, index) in data.tours_today" :key="index">
                        <div class="card-header text-center border-primary" id="headingOne">
                            <small v-if="tour.info && tour.info.tour_code" style="cursor: pointer;" data-toggle="collapse" :data-target="'#collapse'+index" aria-expanded="true" :aria-controls="'collapse'+index" @click="onToggleCollapse($event, index)">{{ tour.info.tour_code }}</small>
                            <br v-if="tour.info && tour.info.tour_code">
                            <small style="font-weight: bold;" data-toggle="collapse" :data-target="'#collapse'+index" aria-expanded="true" :aria-controls="'collapse'+index" @click="onToggleCollapse($event, index)">{{ tour.title }}</small>
                            <br>
                            <small>
                                <b-link @click="coordinatorModal(tour)">{{ tour.coordinators[0] ? tour.coordinators[0].coordinator.full_name : 'No coordinator'}}</b-link>
                                <font-awesome-icon icon="paper-plane" title="Send manifest" style="cursor: pointer; color: orange; font-size: 12px;" @click="sendDepartureByDate($event, date, tour)" />
                            </small>
                        </div>

                        <div :id="'collapse'+index" :class="'collapse'+(index === 0 ? ' show' : '')" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body" style="max-height: 500px; overflow: auto;">
                                <b-list-group>
                                    <b-list-group-item v-for="(departure, depIndex) in tour.departures" :key="depIndex" class="text-center">
                                        <span class="pull-right">
                                            <font-awesome-icon icon="paper-plane" title="Send manifest" style="cursor: pointer; color: orange; font-size: 12px;" @click="sendDeparture(departure)" />
                                            <font-awesome-icon icon="minus-circle" title="Remove departure" style="cursor: pointer; color: red; font-size: 12px;" @click="deleteDeparture(departure)" />
                                        </span>
                                        <small>Departure {{depIndex + 1}}</small><br>
                                        <small><b-link @click="serialModal(departure)">Show Voucher Numbers</b-link> <b-badge pill :variant="departure.complete_voucher ? 'success' : 'danger'">{{departure.serial_numbers.length}}</b-badge></small><br>
                                        <small>Tour Guide: 
                                            <span v-if="departure.schedule && departure.schedule.full_name">
                                                {{departure.schedule.full_name}} 
                                                <font-awesome-icon icon="paper-plane" title="Notify Assigned Guide" style="cursor: pointer; color: orange; font-size: 12px;" @click="notifyGuide(departure.schedule)" />
                                                <font-awesome-icon icon="user-times" title="Cancel Assignation" style="cursor: pointer; color: red; font-size: 12px;" @click="cancelGuide(departure)" />
                                            </span>
                                            <span v-else style="font-weight: bold; color: red;">No Guide Yet</span>
                                        </small><br>
                                        <small>
                                            <div v-if="!modifyTime">{{departure.departure}} <b-badge variant="warning" style="cursor: pointer;" @click="modifyTime = true">Edit</b-badge></div>
                                            <b-input-group v-else size="sm" class="mt-3">
                                                <date-picker class="form-control-sm" v-model="departure.departure" :config="options" placeholder="hh:mm"></date-picker>
                                                <b-input-group-append>
                                                <b-button variant="success" @click="submitTourRateUpdates(departure)">Save</b-button>
                                                </b-input-group-append>
                                            </b-input-group>
                                        </small>
                                        <small>
                                            <b-link @click="autoAssignment(departure)">Auto</b-link> | 
                                            <b-link data-id="id ni" @click="manualAssignmentForm($event, departure, tour.vacant)">Full Lists</b-link> |
                                            <b-link data-id="id ni" @click="manualAssignmentForm($event, departure, tour.available)">Availables</b-link>
                                        </small><br>
                                        <small>
                                            <b-link @click="noteModal(departure)">Notes</b-link>
                                            <font-awesome-icon icon="sticky-note" :color="departure.notes ? 'red' : 'green'" /> |
                                            <b-link>FH CSV</b-link>
                                            <font-awesome-icon :icon="departure.has_fareharbor ? 'check' : 'times'" :color="departure.has_fareharbor ? 'green' : 'red'" /> |
                                            <b-link>AirBnb CSV</b-link>
                                            <font-awesome-icon :icon="departure.has_airbnb ? 'check' : 'times'" :color="departure.has_airbnb ? 'green' : 'red'" />
                                        </small>
                                        <small>
                                            <div v-if="!modifyRate">€ {{departure.custom_rate}} <b-badge variant="warning" style="cursor: pointer;" @click="modifyRate = true">Edit</b-badge></div>
                                            <b-input-group v-else size="sm" prepend="Rate (€)" class="mt-3">
                                                <b-form-input v-model="departure.custom_rate"></b-form-input>
                                                <b-input-group-append>
                                                <b-button variant="success" @click="submitTourRateUpdates(departure)">Save</b-button>
                                                </b-input-group-append>
                                            </b-input-group>
                                        </small>
                                        <small>
                                            <b-input-group prepend="Adult" size="sm" v-if="tour.info.type.code === 'small'">
                                                <b-form-input type="number" min="0" v-model="departure.adult_participants"></b-form-input>
                                                <b-input-group-prepend is-text>Children</b-input-group-prepend>
                                                <b-form-input type="number" min="0" v-model="departure.child_participants"></b-form-input>
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
                                    <b-list-group-item class="text-center btn-brown" button @click="addDeparture(tour)">Add Departure</b-list-group-item>
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

        <b-modal id="upload-modal" title="Upload Voucher" centered>
            <b-form-file
            v-model="voucher_file"
            :state="Boolean(voucher_file)"
            placeholder="Choose a file or drop it here..."
            drop-placeholder="Drop file here..."
            ></b-form-file>
        </b-modal>

        <b-modal ref="assign-coordinator-modal" title="Assign Coordinator" centered @ok="coordinatorSubmit">
            <v-select v-model="selectedCoordinator" label="full_name" :options="coordinatorsLists">
                <template slot="option" slot-scope="option">
                    <div class="d-center">
                    {{ option.first_name+ ' ' + option.last_name }}
                    </div>
                </template>
                
                <template slot="selected-option" slot-scope="option">
                    <div class="d-center">
                    {{ option.first_name+ ' ' + option.last_name }}
                    </div>
                </template>

            </v-select>
            
            <!-- <template v-slot:modal-footer="{ ok, cancel}">
                <b-button size="sm" variant="danger" @click="cancel()">
                    Cancel
                </b-button>
                <b-button size="sm" variant="primary" @click="ok()">
                    OK
                    <b-spinner
                        variant="light"
                        small
                    ></b-spinner>
                </b-button>
            </template> -->
        </b-modal>

        <!-- The modal -->
        <b-modal ref="serial-numbers-modal" centered cancel-variant="danger" cancel-title="Incomplete" ok-title="Complete" @ok="completeVoucher" @cancel="incompleteVoucher" title="Voucher Numbers">

            <div v-if="selectedDeparture && selectedDeparture.serial_numbers">
                <small v-for="(serial, index) in selectedDeparture.serial_numbers" :key="index" class="row justify-content-md-center">
                    <b-col sm="6">
                        <b-input-group size="sm" :state="true">
                            <b-form-input v-model="serial.serial_number" placeholder="Serial Number" size="sm" maxlength="30" @input="serialInputChange(serial)"></b-form-input>
                        </b-input-group>
                    </b-col>
                    <b-col sm="5">
                        <b-input-group prepend="€" size="sm" :state="true">                            
                            <b-form-input v-model="serial.cost" type="number" placeholder="Serial Cost" size="sm" @input="serialInputChange(serial)"></b-form-input>
                            <b-input-group-append v-if="serial.file_link">
                                <b-button variant="info" title="Voucher File" @click="openVoucher(serial.file_link)">
                                    <font-awesome-icon icon="file" style="cursor: pointer;" />
                                </b-button>
                            </b-input-group-append>
                            <b-input-group-append v-if="selectedDeparture.complete_voucher === 0">
                                <b-button variant="danger" title="Delete Voucher" @click="deleteSerialNumber(serial)">
                                    <span>X</span>
                                </b-button>
                            </b-input-group-append>
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
                            <b-button variant="info" v-b-modal.upload-modal>
                                Upload
                            </b-button>
                        </b-input-group-append>
                        
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
            <div v-if="selectedDeparture && selectedDeparture.complete_voucher !== 0">
                Remarks: <b-badge variant="success">Complete</b-badge>
            </div>
            <b-form-invalid-feedback :state="!Boolean(serialError)">
                {{serialError}}
            </b-form-invalid-feedback>
            <b-form-invalid-feedback :state="!Boolean(completedSerialError)">
                {{completedSerialError}}
            </b-form-invalid-feedback>
        </b-modal>
        <VuePNotify></VuePNotify>
    </div>
</template>

<style src="vue-pnotify/dist/vue-pnotify.css"></style>

<script>
    // Import this component
    import datePicker from 'vue-bootstrap-datetimepicker'

const CancelToken = axios.CancelToken
let cancel

export default {
    name: 'TourDepartureList',
    components: {
        datePicker
    },
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
            options: {
                format: 'HH:mm',
                useCurrent: false,
            }, 
            selectedAvailable: null,
            selectedDeparture: null,
            newSerialNumber: null,
            newSerialNumberCost: 0,
            serialError: null,
            completedSerialError: null,
            availableGuideLists: null,
            selectedCoordinator: null,
            coordinatorsLists: [],
            addVoucherLoading: false,
            note: null,
            modifyRate: false,
            modifyTime: false,
            dateFormatted: null,
            voucher_file: null,
            selectedTour: null
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
        onToggleCollapse(event, $toggle) {
            event.preventDefault()

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
        sendDeparture(departure) {

            Swal.fire({
                title: 'Send tour manifest?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post('manifest/send', departure)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            return response.data
                        }).catch(error => {
                            let errors = error.response.data.errors

                            let tmp = Object.values(errors);

                            let errorMessage = tmp.join(' ')

                            Swal.showValidationMessage(
                            `Request failed: ${errorMessage}`
                            )
                        }).finally(() => {        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if(typeof result.value !== 'undefined') {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
            })

        },
        sendDepartureByDate(event, date, tour) {
            event.preventDefault()

            Swal.fire({
                title: 'Send tour manifest?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post('manifest/send/date', {
                        date: date,
                        tour: tour
                        })
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            return response.data
                        }).catch(error => {
                            let errors = error.response.data.errors

                            let tmp = Object.values(errors);

                            let errorMessage = tmp.join(' ')

                            Swal.showValidationMessage(
                            `Request failed: ${errorMessage}`
                            )
                        }).finally(() => {        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if(typeof result.value !== 'undefined') {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
            })

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
        coordinatorModal: function(data) {
            this.selectedTour = data
            
            this.$refs['assign-coordinator-modal'].show()
        },
        coordinatorSubmit(e) {
            e.preventDefault()
            
            cancel && cancel()

            let self = this

            let item = self.data.tours_today.filter(function(elem) {
                if(elem.id === self.selectedTour.id) return elem
            })

            return axios.post('departure/coordinator',{
                'tour': item ? item[0] : null,
                'coordinator': self.selectedCoordinator,
                'date': self.date
            }, {
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            }).then(response => {

                let data = response.data

                let coordinator = data.coordinator
                
                if(typeof item[0].coordinators[0] === 'undefined') {
                    item[0].coordinators.push(data)

                    item[0].coordinators[0].coordinator = coordinator
                }
                else {
                    item[0].coordinators[0].coordinator = coordinator

                    item[0].coordinators[0].coordinator_id = data.id
                }

                self.$notify({
                    title: 'Saved!',
                    text: 'Data have been saved.',
                    style: 'success'
                })

                return item

            }).catch(error => {

                let errors = error.response.data.errors

                let tmp = Object.values(errors);

                let errorMessage = tmp.join()

                self.$notify({
                    title: 'Error!',
                    text: errorMessage,
                    style: 'error'
                })
                
            }).finally(final => {

            })
            
        },
        coordinatorsListsMethod: function() {
            axios.get('/get/coordinator/list/search')
            .then(response => {
                this.coordinatorsLists = response.data
            })
            .catch(err => {
                this.coordinatorsLists = []
            })
            .finally(final => {
                
            })
        },
        addSerialNumber: function(input) {            
            this.serialError = null

            this.addVoucherLoading = true

            input.serial_number = this.newSerialNumber

            input.cost = this.newSerialNumberCost

            cancel && cancel()

            let formData = new FormData()

            if(this.voucher_file) formData.append('file', this.voucher_file)
            
            for (let key in input) {
                formData.append(key, input[key]);
            }
             
            axios.post('departure/serial_number/add',
            formData, {
                headers: {
                    'Content-Type' : 'multipart/form-data'
                },
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
        openVoucher(url) {
            window.open(url, "_blank")
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
            .then(response => {
                this.$notify({
                    title: 'Saved!',
                    text: 'Data have been saved.',
                    style: 'success'
                })
            })
            .catch(error => {
                let errors = error.response.data.errors

                let tmp = Object.values(errors);

                let errorMessage = tmp.join()

                this.$notify({
                    title: 'Error!',
                    text: errorMessage,
                    style: 'error'
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
            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post('notification/departure', schedule)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            return response.data
                        }).catch(error => {
                            Swal.showValidationMessage(
                            `Request failed: ${error}`
                            )
                        }).finally(() => {        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if(typeof result.value !== 'undefined') {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
            })
        },
        cancelGuide(schedule) {    
            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.put('departure/cancel/guide', schedule)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }
                
                            this.$emit('onLoad')

                            return response.data
                        }).catch(error => {
                            Swal.showValidationMessage(
                            `Request failed: ${error}`
                            )
                        }).finally(() => {        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    Swal.fire({
                    title: 'Cancellation request sent!'
                    })
            })
        },
        deleteSerialNumber(serial) {
            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    this.serialError = null

                    this.addVoucherLoading = true

                    cancel && cancel()
                    
                    return axios.delete('departure/serial_number/delete', 
                    {
                        data: serial
                    }, 
                    {
                        cancelToken: new CancelToken(function executor(c) {
                            cancel = c
                        })
                    }).then(data => {
                        this.selectedDeparture = data.data

                        this.$emit('onLoad')

                        return data.data

                    }).catch(error => {
                        Swal.showValidationMessage(
                        `Request failed: ${error}`
                        )
                    }).finally(final => {

                        this.addVoucherLoading = false

                    })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if(result.value) {
                        Swal.fire({
                            title: 'Request sent!'
                        })
                    }
                }
            )
        },
        submitTourRateUpdates(tours) {

            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.put('departure/rate/update', tours)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            // self.items[index].data = response.data

                            this.$emit('onLoad')

                            return response.data
                        }).catch(error => {
                            Swal.showValidationMessage(
                            `Request failed: ${error}`
                            )
                        }).finally(() => {        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if(result.value) {
                    Swal.fire({
                    title: 'Request sent!'
                    })

                    this.modifyRate = false
                }
            })
            
        }
    },
    watch: {
        'date': function(newVal, oldVal) {

            this.dateFormatted = moment(newVal).format('DD/MM/YYYY')
            
        }
    },
    mounted() {
        this.$root.$on('bv::modal::show', (bvEvent, modalId) => {
        })
        
        this.$root.$on('bv::modal::hidden', (bvEvent, modalId) => {
            this.departure = null
            this.selectedAvailable = null
        })

        this.coordinatorsListsMethod();
    }
}
</script>
