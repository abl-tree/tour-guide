<template>
    <div>
        <div class="card">
            <div class="card-header">

            </div>
            <div class="card-body" v-if="isWatchList">   

                <div class="row" v-if="statsDates">
                    <div class="col-md-5">
                        <div class="d-block text-left">
                            <h4><strong>Name: {{guide.full_name}}</strong></h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <b-form-group
                        :state="dateState"
                        label-for="date-input"
                        invalid-feedback="The date is required"
                        >
                            <b-input-group>
                                <date-picker v-if="!statsDates" id="date-input" v-model="date" lang="en" valueType="format" type="month" format="YYYY-MM"></date-picker>
                                <b-form-select v-else v-model="date">
                                    <option v-for="(data, index) in statsDates" :key="index" :value="data.value">{{data.text}}</option>
                                </b-form-select>
                                <b-input-group-append>
                                    <b-button variant="primary" @click="watchList()">Search</b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </b-form-group>
                    </div>

                    <div class="col-md-6">
                        <b-button size="sm" class="pull-right" v-if="!statsDates" @click="isWatchList = false">Back to Incassi/Anticipi</b-button>
                    </div>
                </div>

                <div class="row" v-if="date_formats">
                    <div class="col-md-5">
                        <div class="d-block text-left">
                            <strong>{{date_formats.month}} {{date_formats.year}}</strong>
                        </div>
                    </div>
                    <div class="col-md-7" v-if="!statsDates">
                        <div class="d-block text-left" v-if="tour_guides_items_full && !tour_guides_items_full.selected_guide">
                            <strong>Grand Total: {{tour_guides_items_full.overall_total}}</strong>
                        </div>
                        <div class="d-block text-left" v-else>
                            <strong>Name: {{tour_guides_items_full.selected_guide.full_name}}</strong>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="d-block text-center">
                        <b-table small 
                            :fields="receipts_fields" 
                            :items="receipts_items" 
                            sort-by="event_date.day"
                            sort-direction="asc"
                            show-empty 
                            :busy="populating_payment_table">

                                <div slot="table-busy" class="text-center text-danger my-2">
                                    <b-spinner class="align-middle"></b-spinner>
                                    <strong>Loading...</strong>
                                </div>

                            <template slot="payment.anticipi" slot-scope="data">
                                {{ '€ ' + data.item.payment.anticipi }} 
                            </template>

                            <template slot="payment.incassi" slot-scope="data">
                                {{ '€ ' + data.item.payment.incassi }} 
                            </template>

                            <template slot="payment.balance" slot-scope="data">
                                {{ '€ ' + data.item.payment.balance }} 
                                <b-button v-if="data.item.payment && data.item.payment.receipt_url" variant="link" v-b-tooltip.hover title="Receipt Image" size="sm" @click="openReceipt(data.item.payment.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                            </template>

                            <template slot="notes" slot-scope="data">
                                <b-button-group size="sm">
                                    <b-button :variant="data.item.payment.admin_note ? 'danger' : 'success'" @click="notes(data, 'admin')">Admin</b-button>
                                    <b-button :variant="data.item.payment.guide_note ? 'danger' : 'success'" @click="notes(data, 'guide')">Guide</b-button>
                                </b-button-group>
                            </template>

                        </b-table>
                    </div>

                    <div v-if="tour_guides_items && tour_guides_items.receipts && tour_guides_items.receipts.length">
                        
                        <div class="d-block text-right" v-if="tour_guides_items && tour_guides_items.balance">
                            <strong><h5>To Balance: € {{tour_guides_items.balance}}</h5></strong>
                        </div>

                        <div class="d-block text-right" v-if="tour_guides_items">
                            <b-badge v-if="tour_guides_items.to_balance" variant="danger" @click="balanced()" style="cursor: pointer;">To be balanced</b-badge>
                            <b-badge v-else variant="success" @click="balanced(false)" style="cursor: pointer;">Balanced</b-badge>
                        </div>

                    </div>

                </div>

            </div>
            <div class="card-body" v-else>
                <div class="row">
                    <div class="col-md-12">

                        <b-alert variant="success" :show="isSaved">Data have been saved successfully</b-alert>

                        <b-alert variant="danger" :show="isError">Data not saved</b-alert>

                        <b-form-group
                        :state="dateState"
                        label-for="date-input"
                        :invalid-feedback="dateError"
                        >
                            <b-input-group>
                                <date-picker id="date-input" v-model="date" style="width: 100%;" lang="en" valueType="format"></date-picker>
                            </b-input-group>
                        </b-form-group>

                        <b-form-group id="input-group-3">      

                            <b-form-group
                            :state="titleState"
                            label-for="date-input"
                            invalid-feedback="Please select a tour title"
                            >
                                <b-form-select
                                id="input-3"
                                v-model="tour_title_selected"
                                :options="renderOptions()"
                                required
                                ></b-form-select>
                            </b-form-group>
                        </b-form-group>

                        <b-form-group
                        :state="antAmountState"
                        label="Anticipi"
                        label-for="anticipi-amount-input"
                        :invalid-feedback="antAmountError"
                        >
                            <b-input-group prepend="€">
                                <b-form-input id="anticipi-amount-input" :state="antAmountState" v-model="antAmount" type="number" min="0" required></b-form-input>
                                <b-input-group-append>
                                    <b-button variant="primary" v-b-modal.anticipi><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </b-form-group>
                        
                        <b-form-group
                        :state="incAmountState"
                        label="Incassi"
                        label-for="incossi-amount-input"
                        :invalid-feedback="incAmountError"
                        >
                            <b-input-group prepend="€">
                                <b-form-input id="incossi-amount-input" v-model="incAmount" type="number" min="0" required></b-form-input>
                            </b-input-group>
                        </b-form-group>

                        <b-row class="justify-content-md-center">
                            <b-button-group>
                                <b-button variant="success" @click="handleSubmit"><b-spinner v-if="saving" small label="Saving"></b-spinner> Save</b-button>
                                <b-button variant="info" @click="reset">Reset</b-button>
                                <b-button variant="warning" @click="watchList()">Watch Lists</b-button>
                            </b-button-group>
                        </b-row>
                    </div>
                </div>
            </div>
        </div>

        <b-modal ref="notes-modal" :title="(isAdminNote ? 'Admin ' : 'Guide ') + 'Notes'" size="sm" @hidden="resetModal" @ok="submitNote" centered>
            <b-row v-if="selected_payment">
                <b-col>
                    <b-form-textarea v-if="isAdminNote" type="text" v-model="selected_payment.admin_note" rows="3" max-rows="6"></b-form-textarea>
                    <b-form-textarea v-else v-model="selected_payment.guide_note" type="text" rows="3" max-rows="6" readonly></b-form-textarea>
                </b-col>
            </b-row>
            
            <template v-slot:modal-footer="{ ok, cancel, hide }">
                <!-- Emulate built in modal footer ok and cancel button actions -->
                <b-button size="sm" variant="success" @click="ok()">
                    Save <b-spinner v-show="submittingNote" variant="light" small></b-spinner>
                </b-button>
                <b-button size="sm" variant="danger" @click="cancel()">
                    Cancel
                </b-button>
            </template>
        </b-modal>

        <b-modal id="anticipi" title="Receipt" no-close-on-backdrop>

            <!-- Plain mode -->
            <b-form-group
            :state="receiptState"
            label-for="receipt-input"
            :invalid-feedback="receiptError"
            >
                <b-form-file id="receipt-input" ref="file" v-model="receipt_img" @change="fileChange" accept="image/*" class="mt-3" plain></b-form-file>
            </b-form-group>

            <b-img v-show="receipt_img" :src="preview" fluid alt="Receipt Image"></b-img>

        </b-modal>
    </div>
</template>

<script>
import { log } from 'util';
import DatePicker from 'vue2-datepicker'

const CancelToken = axios.CancelToken
let cancel

export default {
    components: {
        DatePicker
    },
    name: 'TourGuideList',
    props: {
        tour_titles: Array,
        loading: Boolean,
        isAdmin: Boolean,
        payment: Boolean,
        statsDates: Array,
        errors: Object,
        guide: Object
    },
    data() {
        return {
            selected_payment: null,
            isAdminNote: false,
            submittingNote: false,
            selected_user: null,
            isWatchList: false,
            antAmountState: null,
            antAmount: null,
            antAmountError: "The amount must be greater than 0",
            incAmountState: null,
            incAmount: null,
            incAmountError: "The amount must be greater than 0",
            receiptState: null,
            receiptError: 'The receipt image is required',
            receipt_id: null,
            receipt_img: null,
            preview: '',
            tour_title_selected: null,
            titleState: null,
            receipts_fields: [
                { key: 'event_date.day', label: 'Date' },
                { key: 'payment.anticipi', label: 'Anticipi' },
                { key: 'payment.incassi', label: 'Incassi' },
                { key: 'payment.balance', label: 'Balance' },
                { key: 'notes', label: 'Notes' }
            ],
            tour_guides_fields: [
                { key: 'full_name', label: 'Tour Guides' }
            ],
            tour_guides_items: null,
            tour_guides_items_selected: null,
            tour_guides_items_full: null,
            receipts_items: null,
            balance_items: null,
            date: null,
            dateState: null,
            dateError: 'The date is required',
            date_formats: null,
            totalRows: 1,
            currentPage: 1,
            perPage: 10,
            sortBy: 'to_balance',
            sortDesc: true,
            sortDirection: 'desc',
            isSaved: null,
            isError: null,
            deleting: false,
            saving: false,
            populating_payment_table: false
        }
    },
    methods: {
        checkFormValidity() {
            let validAntAmount = true
            let validIncAmount = true
            let validReceipt = true
            let validTour = true
            let validDate = true

            if(this.antAmount) {
                validAntAmount = (this.antAmount >= 0) ? true : false

                validReceipt = (this.receipt_img) ? true : false
            } else {
                validAntAmount = true

                validReceipt = true
            }

            this.antAmountError = (validReceipt) ? this.antAmountError : 'The receipt image is required'

            this.antAmountError = (validAntAmount) ? this.antAmountError : 'The amount must be greater than 0'

            this.antAmountState = validAntAmount && validReceipt ? 'valid' : 'invalid'

            this.receiptState = validReceipt ? 'valid' : 'invalid'

            validIncAmount = (this.incAmount >= 0) ? true : false

            this.incAmountState = validIncAmount ? 'valid' : 'invalid'

            validDate = (this.date) ? true : false

            this.dateState = (validDate) ? 'valid' : 'invalid'

            validTour = (this.tour_title_selected) ? true : false

            this.titleState = (validTour) ? 'valid' : 'invalid'

            return validAntAmount && validIncAmount && validTour
        },
        handleSubmit() {
            // Exit when the form isn't valid
            if (!this.checkFormValidity()) {
                return
            }            

            this.saving = true
            this.isSaved = null
            this.isError = null

            cancel && cancel()

            let vm = this

            let formData = new FormData()
            formData.append('date', this.date)

            if(this.receipt_img && this.antAmount) formData.append('file', this.receipt_img)
            if(this.antAmount) formData.append('anticipi', this.antAmount)
            if(this.incAmount) formData.append('incassi', this.incAmount)
            if(this.tour_title_selected) formData.append('title', this.tour_title_selected)
            formData.append('guide', this.guide.id)

            axios.post('/payment',
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                cancelToken: new CancelToken(function executor(c) {
                    cancel = c
                })
            }
            ).then(function(){

                vm.isSaved = true

                vm.isError = false

            })
            .catch(err => {

                if(axios.isCancel(err)) {                    
                    return
                }

                vm.isSaved = false

                vm.isError = true

                if(!err.response) return;

                if(err.response.data.errors && err.response.data.errors.date) {
                    this.dateError = err.response.data.errors.date[0]
                    this.dateState = 'invalid'
                }
                
                if(err.response.data.errors && err.response.data.errors.title) {
                    this.titleState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.file) {
                    this.antAmountError = err.response.data.errors.file[0]
                    this.antAmountState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.anticipi) {
                    this.antAmountError = err.response.data.errors.anticipi[0]
                    this.antAmountState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.incassi) {
                    this.incAmountError = err.response.data.errors.incassi[0]
                    this.incAmountState = 'invalid'
                }
            })
            .finally(final => {
                this.saving = false
            })
        },
        fileChange(e) {
            let file = e.target.files[0]
            this.preview = URL.createObjectURL(file);
        },
        reset() {
            this.receipt_img = null
            this.antAmountState = null
            this.antAmount = 0
            this.antAmountError = "The amount must be greater than 0"
            this.incAmountState = null
            this.incAmount = 0
            this.incAmountError = "The amount must be greater than 0"
            this.receiptState = null
            this.receiptError = 'The receipt image is required'
            this.receipt_id = null
            this.preview = ''
            this.date = null
            this.dateState = null
            this.tour_title_selected = null
            this.titleState = null
            this.isSaved = null
            this.isError = null
        },
        openReceipt(url) {
            window.open(url, "_blank")
        },
        deleteReceipt(receipt) {
            let vm = this

            if(this.receipts_items[receipt.index])
            this.receipts_items[receipt.index].delete_attempt = true          

            axios.delete('/payment/' + receipt.item.id)
            .then(data => {
                this.receipts_items.splice(receipt.index, 1)
  
                this.watchList({
                    index : this.selected_guide,
                    item : {
                        id : receipt.item.user_id
                    },
                    loading: false
                })
            })
            .finally(data => {
                if(this.receipts_items[receipt.index])
                this.receipts_items[receipt.index].delete_attempt = false
            });
        },
        balanced(option = true) {

            let url = ""
            let params = {
                user: this.tour_guides_items_full.selected_guide.id, 
                date: this.date
            };
            
            if(option) {
                url = '/payment/balanced'
            } else {
                url = '/payment/unbalanced'
            }

            axios.put(url, params)
            .then(data => {
                
                this.watchList();
                
            })
            .catch(err => {

            })
            .finally(final => {

            })
        },
        renderOptions() {
            let options = [{ text: 'Select the Tour', value: null }];

            for(let a = 0; a < this.$props.tour_titles.length; a++) {
                let obj = {text: this.tour_titles[a].title, value: this.tour_titles[a].id}

                options.push(obj)
            }

            return options
        },
        watchList(user = "") {            
            this.selected_guide = user.index
            
            if(user) {
                user = user.item ? '/' + user.item.id : ''
            }

            if(!this.isWatchList) {
                this.isWatchList = true

                this.populating_payment_table = false

                return
            }
            
            this.populating_payment_table = true
            
            axios.get('/payment/show' + user, {
                params: {
                    'date': this.date,
                    'guide': this.guide.id
                }
            })
            .then(response => {
                console.log('watchlist', response);
            
                this.dateState = 'valid'
                
                this.tour_guides_items_full = response.data.data

                this.tour_guides_items = response.data.data.tour_guides

                this.totalRows = response.data.data.tour_guides.length

                this.receipts_items = this.isAdmin && response.data.data.selected_guide ? response.data.data.selected_guide.receipts : response.data.data.tour_guides.receipts

                this.date_formats = response.data.formats
                
            })
            .catch(err => {

                if(!err.response) return;

                if(err.response.data.errors && err.response.data.errors.date) {
                    this.dateState = 'invalid'
                }
                
                if(err.response.data.errors && err.response.data.errors.title) {
                    this.titleState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.file) {
                    this.antAmountError = err.response.data.errors.file[0]
                    this.antAmountState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.anticipi) {
                    this.antAmountError = err.response.data.errors.anticipi[0]
                    this.antAmountState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.incassi) {
                    this.incAmountError = err.response.data.errors.incassi[0]
                    this.incAmountState = 'invalid'
                }
            })
            .finally(final => {
                this.populating_payment_table = false
            })
        },
        notes(data, option) {
            this.isAdminNote = (option === 'admin') ? true : false

            this.selected_payment = data.item.payment

            this.selected_user = data.item.user

            this.$refs['notes-modal'].show()
        },
        submitNote(event) {
            event.preventDefault()

            let option = this.isAdminNote ? 'admin' : 'guide'

            this.submittingNote = true
            
            axios.put('/payment/notes/' + option , this.selected_payment)
            .then(data => {
  
                this.watchList({
                    index : this.selected_guide,
                    item : this.selected_user,
                    loading: false
                })

            })
            .finally(final => {
                this.submittingNote = false
            })
        }
    },
    created() {

        this.isWatchList = this.statsDates ? true : false
        
    }
}
</script>
