<template>
    <div>
        <div class="card">
            <div v-if="tour" class="card-header">
                Tour Update
            </div>
            <div v-else class="card-header">
                Tours Registration
            </div>
            <div class="card-body">
                <b-alert
                :show="dismissCountDown"
                dismissible
                fade
                variant="success"
                @dismiss-count-down="countDownChanged"
                >
                    {{this.tour ? 'Tour updated successfully' : 'Tour added successfully'}}
                </b-alert>

                <b-form @submit="onSubmit" @reset="onReset">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-tour-name">Tour Name:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input :state="form.errors && form.errors.name ? false : null" id="input-tour-name" size="sm" placeholder="Enter tour name" v-model="form.tour_name"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.name)">
                                {{form.errors && form.errors.name ? form.errors.name[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2">
                            <label for="input-tour-code">Tour Code:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input :state="form.errors && form.errors.code ? false : null" id="input-tour-code" size="sm" placeholder="Enter tour name" v-model="form.tour_code"></b-form-input>
                            <b-form-invalid-feedback :state="form.errors && form.errors.code ? false : null">
                                {{form.errors && form.errors.code ? form.errors.code[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>
                    
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-tour-type">Tour Type:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-group>
                                <b-form-radio-group
                                    v-model="form.tour_type"
                                    :options="renderOptions()"
                                    :state="form.errors && form.errors.type"
                                ></b-form-radio-group>
                                <b-form-invalid-feedback :state="form.errors && form.errors.type">
                                    {{form.errors && form.errors.type ? form.errors.type[0] : ''}}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </b-col>
                        
                        <b-col sm="2">
                            <label for="input-tour-departure">Tour Departure:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-group>
                                <b-form-radio-group
                                    v-model="form.tour_departure"
                                    :options="departure_options"
                                    :state="form.errors && form.errors.departure"
                                ></b-form-radio-group>
                                <b-form-invalid-feedback :state="form.errors && form.errors.departure">
                                    {{form.errors && form.errors.departure ? form.errors.departure[0] : ''}}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </b-col>
                    </b-row>

                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-tour-picture">Tour Picture:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-file
                            :state="form.errors && form.errors.file ? false : null"
                            accept="image/*"
                            v-model="form.file"
                            placeholder="Choose a file or drop it here..."
                            drop-placeholder="Drop file here..."
                            ></b-form-file>
                            <b-form-invalid-feedback :state="form.errors && form.errors.file">
                                {{form.errors && form.errors.file ? form.errors.file[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2" v-show="form.tour_type === 'small'">
                            <label for="input-tour-color">Tour Color:</label>
                        </b-col>
                        <b-col sm="2" v-show="form.tour_type === 'small'">
                            <b-form-input type="color" id="input-tour-color" size="sm" placeholder="Enter tour name" v-model="form.tour_color" :state="form.errors && form.errors.color ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.color)">
                                {{form.errors && form.errors.color ? form.errors.color[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>

                    <b-row class="my-1">
                        <b-col sm="12">
                            <b-form-group label="Availabilities">
                                <b-form-checkbox-group
                                    v-model="form.tour_availabilities"
                                    :options="tour_availabilities_options"
                                    name="flavour-1a"
                                    :state="form.errors && form.errors.availabilities ? false : null"
                                ></b-form-checkbox-group>
                                <b-form-invalid-feedback :state="form.errors && form.errors.availabilities ? false : null">
                                    {{form.errors && form.errors.availabilities ? form.errors.availabilities[0] : ''}}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    
                    <hr style="margin-top: 1rem; margin-bottom: 1rem; border: 0; border-top: 1px solid rgba(0, 0, 0, 0.1);"/>
                    
                    <b-row class="my-1">
                        <b-col sm="12">
                            <h5>Tour Rates</h5>
                        </b-col>
                    </b-row>
                    
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-cash">Cash Option:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-cash" size="sm" placeholder="Enter amount" v-model="form.tour_cash" :state="form.errors && form.errors.cash ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.cash)">
                                {{form.errors && form.errors.cash ? form.errors.cash[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2">
                            <label for="input-invoice">Invoice Option:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-invoice" size="sm" placeholder="Enter amount" v-model="form.tour_invoice" :state="form.errors && form.errors.invoice ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.invoice)">
                                {{form.errors && form.errors.invoice ? form.errors.invoice[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>
                    
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-payoneer">Payoneer Option:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-payoneer" size="sm" placeholder="Enter amount" v-model="form.tour_payoneer" :state="form.errors && form.errors.payoneer ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.payoneer)">
                                {{form.errors && form.errors.payoneer ? form.errors.payoneer[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2">
                            <label for="input-paypal">Paypal Option:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-paypal" size="sm" placeholder="Enter amount" v-model="form.tour_paypal" :state="form.errors && form.errors.paypal ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.paypal)">
                                {{form.errors && form.errors.paypal ? form.errors.paypal[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>
                    
                    <b-row class="my-1" v-show="form.tour_type === 'small'">
                        <b-col sm="2">
                            <label for="input-adults-price">Adults Price:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-adults-price" size="sm" placeholder="Enter amount" v-model="form.tour_adult" :state="form.errors && form.errors.adult ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.adult)">
                                {{form.errors && form.errors.adult ? form.errors.adult[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2">
                            <label for="input-children-price">Children Price:</label>
                        </b-col>
                        <b-col sm="4">
                            <b-form-input type="number" min="0" id="input-children-price" size="sm" placeholder="Enter amount" v-model="form.tour_children" :state="form.errors && form.errors.children ? false : null"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.adult)">
                                {{form.errors && form.errors.adult ? form.errors.adult[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>
                    
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-duration">Tour Duration:</label>
                        </b-col>
                        <b-col sm="1">
                            <b-form-input type="number" size="sm" placeholder="d" min="0" v-model="form.tour_duration_day"></b-form-input>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.duration_day)">
                                {{form.errors && form.errors.duration_day ? form.errors.duration_day[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                        <b-col sm="2">
                            <date-picker class="form-control-sm" v-model="form.tour_duration" :config="options" placeholder="hh:mm" :state="form.errors && form.errors.duration ? false : null"></date-picker>
                            <b-form-invalid-feedback :state="Boolean(form.errors && form.errors.duration)">
                                {{form.errors && form.errors.duration ? form.errors.duration[0] : ''}}
                            </b-form-invalid-feedback>
                        </b-col>
                    </b-row>
                    
                    <b-button type="submit" class="text-center justify-content-between" variant="primary">
                        <b-spinner
                            v-show="saving"
                            small
                            variant="light"
                            type="grow"
                        ></b-spinner> {{tour ? 'Update' : 'Submit'}}
                    </b-button>
                    <b-button type="reset" variant="danger" v-show="!tour">Reset</b-button>
                    <b-button variant="warning" href="/tours">Show All Tours</b-button>
                </b-form>
            </div>
        </div>
    </div>
</template>

<script>
    // Import this component
    import datePicker from 'vue-bootstrap-datetimepicker'

    const CancelToken = axios.CancelToken
    let cancel
    let self = this

    export default {
        components: {
            datePicker
        },
        props: {
            tour_types: Array,
            tour: Object
        },
        data() {
            return {
                options: {
                    format: 'HH:mm',
                    useCurrent: false,
                }, 
                dismissCountDown: 0,
                saving: false,
                tour_availabilities_options: [
                    { text: 'Sunday', value: 'sunday' },
                    { text: 'Monday', value: 'monday' },
                    { text: 'Tuesday', value: 'tuesday' },
                    { text: 'Wednesday', value: 'wednesday' },
                    { text: 'Thursday', value: 'thursday' },
                    { text: 'Friday', value: 'friday' },
                    { text: 'Saturday', value: 'saturday' }
                ],
                departure_options: [
                    { text: 'AM', value: 'am' },
                    { text: 'PM', value: 'pm' },
                    { text: 'Evening', value: 'evening' },
                ],
                form: {
                    tour_type: 'small',
                    tour_departure: 'am',
                    tour_availabilities: [],
                    file: '',
                    tour_color: '#000000',
                    tour_name: '',
                    tour_code: '',
                    tour_cash: 0,
                    tour_invoice: 0,
                    tour_payoneer: 0,
                    tour_paypal: 0,
                    tour_adult: 0,
                    tour_children: 0,
                    tour_duration_day: 0, 
                    tour_duration: '00:00',
                    errors: null
                }
            }
        },
        methods: {
            renderOptions() {
                let options = [];

                for(let a = 0; a < this.$props.tour_types.length; a++) {
                    let obj = {text: this.tour_types[a].name, value: this.tour_types[a].code}

                    options.push(obj)
                }

                return options
            },
            validForm() {
                if(Object.values(this.form).indexOf(null) > -1 || this.form.tour_availabilities.length < 1) {
                    return false
                } else return true
            },
            onSubmit(evt) {
                evt.preventDefault()

                // if(!this.validForm()) {
                //     return
                // }

                cancel && cancel()

                let vm = this

                let formData = new FormData()

                formData.append('name', this.form.tour_name)
                formData.append('code', this.form.tour_code ? this.form.tour_code : '')
                formData.append('type', this.form.tour_type)
                formData.append('departure', this.form.tour_departure)
                formData.append('file', this.form.file)

                if(this.form.tour_type === 'small') {
                    formData.append('color', this.form.tour_color)
                    formData.append('adult', this.form.tour_adult)
                    formData.append('children', this.form.tour_children)
                }

                for(let a in this.form.tour_availabilities) {
                    formData.append('availabilities[]', this.form.tour_availabilities[a])
                }

                formData.append('cash', this.form.tour_cash)
                formData.append('invoice', this.form.tour_invoice)
                formData.append('payoneer', this.form.tour_payoneer ? this.form.tour_payoneer : '')
                formData.append('paypal', this.form.tour_paypal ? this.form.tour_paypal : '')
                formData.append('duration_day', this.form.tour_duration_day)
                formData.append('duration', this.form.tour_duration)

                if(this.tour) {
                    this.updateTour(formData, this.tour.id)

                    return
                }

                this.addTour(formData)
            },
            addTour(formData) {
                this.saving = true

                axios.post('/tours',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        cancelToken: new CancelToken(function executor(c) {
                            cancel = c
                        })
                    }
                )
                .then((data) => {
                    this.showAlert(2)

                    this.reset()
                })
                .catch(err => {
                    let errors = err.response.data.errors

                    this.form.errors = errors
                })
                .finally(final => {
                    this.saving = false
                })
            },
            updateTour(formData, id) {                
                this.saving = true

                return axios.post('/tours/' + id,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        cancelToken: new CancelToken(function executor(c) {
                            cancel = c
                        })
                    }
                )
                .then((data) => {
                    this.showAlert(2)
                })
                .catch(err => {
                    let errors = err.response.data.errors

                    this.form.errors = errors
                })
                .finally(final => {
                    this.saving = false
                })
            },
            onReset(evt) {                
                evt.preventDefault()

                this.reset()
            },
            reset() {
                this.form.file = ''
                this.form.tour_availabilities = []
                this.form.tour_color = '#000000'
                this.form.tour_name = ''
                this.form.tour_code = ''
                this.form.tour_cash = 0
                this.form.tour_invoice = 0
                this.form.tour_payoneer = 0
                this.form.tour_paypal = 0
                this.form.tour_adult = 0
                this.form.tour_children = 0
                this.form.tour_duration_day = 0
                this.form.tour_duration = '00:00'
                this.form.errors = null
            },
            countDownChanged(dismissCountDown) {
                this.dismissCountDown = dismissCountDown
            },
            showAlert(time) {
                this.dismissCountDown = time
            }
        },
        created() {
            if(this.tour) {
                let availabilities = []

                if(this.tour.availabilities)
                for (let a = 0; a < this.tour.availabilities.length; a++) {
                    const day = this.tour.availabilities[a].day;
                    
                    availabilities.push(day)
                }

                this.form.tour_availabilities = availabilities
                this.form.tour_color = this.tour.info && this.tour.info.color ? this.tour.info.color : '#000000'
                this.form.tour_name = this.tour.title
                this.form.tour_code = this.tour.info ? this.tour.info.tour_code : null
                this.form.tour_type = this.tour.info && this.tour.info.type ? this.tour.info.type.code : 'small'
                this.form.tour_departure = this.tour ? this.tour.time : 'am'
                this.form.tour_cash = this.tour.info && this.tour.info.cash ? this.tour.info.cash : 0
                this.form.tour_invoice = this.tour.info && this.tour.info.invoice ? this.tour.info.invoice : 0
                this.form.tour_payoneer = this.tour.info && this.tour.info.payoneer ? this.tour.info.payoneer : 0
                this.form.tour_paypal = this.tour.info && this.tour.info.paypal ? this.tour.info.paypal : 0
                this.form.tour_adult = this.tour.info && this.tour.info.adult_price ? this.tour.info.adult_price : 0
                this.form.tour_children = this.tour.info && this.tour.info.children_price ? this.tour.info.children_price : 0
                this.form.tour_duration_day = this.tour.info && this.tour.info.duration_day ? this.tour.info.duration_day : 0
                this.form.tour_duration = this.tour.info && this.tour.info.duration_time ? this.tour.info.duration_time : '00:00'
            }
            
        }
    }
</script>