<template>
    <div>
        <div class="card">
            <div class="card-header">

            </div>
            <div class="card-body" v-if="isWatchList">   

                <b-form-group
                :state="dateState"
                label-for="date-input"
                invalid-feedback="The date is required"
                >
                    <b-input-group>
                        <date-picker id="date-input" v-model="date" lang="en" valueType="format" type="month" format="YYYY-MM"></date-picker>
                        <b-input-group-append>
                            <b-button variant="primary" @click="watchList()">Search</b-button>
                        </b-input-group-append>
                    </b-input-group>
                </b-form-group>

                <div class="d-block text-left" v-if="date_formats">
                    <strong><h4>{{date_formats.month}} {{date_formats.year}}</h4></strong>
                </div>

                <div class="row" v-if="isAdmin">
                    <div class="text-center col-md-5">
                        <b-table small 
                        :fields="tour_guides_fields" 
                        :items="tour_guides_items_full && tour_guides_items_full.tour_guides ? tour_guides_items_full.tour_guides : null" 
                        :current-page="currentPage"
                        :per-page="perPage"
                        :sort-by="sortBy"
                        :sort-desc="sortDesc"
                        :sort-direction="sortDirection"
                        show-empty>

                            <template slot="full_name" slot-scope="data">
                                <b-button variant="link" @click="watchList(data)" >{{ data.item.full_name }}</b-button>
                                <span>
                                    <b-badge v-if="data.item.to_balance" variant="danger" style="cursor:pointer;" pill>To Balance</b-badge>
                                </span>
                            </template>

                        </b-table>

                        <b-row>
                            <b-col md="6" class="my-1">
                                <b-pagination
                                v-model="currentPage"
                                :total-rows="totalRows"
                                :per-page="perPage"
                                class="my-0"
                                ></b-pagination>
                            </b-col>
                        </b-row>
                    </div>
                    
                    <div class="text-center col-md-7">
                        <div class="d-block text-center">
                            <b-table small 
                                    :fields="receipts_fields" 
                                    :items="tour_guides_items_full && tour_guides_items_full.selected_guide ? tour_guides_items_full.selected_guide.receipts : null" 
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
                                    <b-button-group>
                                        <b-button v-if="data.item.payment && data.item.payment.receipt_url" variant="link" v-b-tooltip.hover title="Receipt Image" style="padding: 3px;" @click="openReceipt(data.item.payment.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer; color: green;" /></b-button>
                                        <b-button v-show="false" variant="link" v-b-tooltip.hover title="Modify" style="padding: 3px;"><font-awesome-icon icon="edit" style="cursor: pointer;" /></b-button>
                                        <b-button variant="link" v-b-tooltip.hover title="Delete" style="padding: 3px;" @click="deleteReceipt(data)"><b-spinner v-if="data.item.delete_attempt" small label="Deleting" variant="danger" type="grow"></b-spinner><font-awesome-icon v-else icon="trash-alt" style="cursor: pointer; color: red;" /></b-button>
                                    </b-button-group>
                                </template>

                            </b-table>
                        </div>

                        <div v-if="tour_guides_items_full && tour_guides_items_full.selected_guide && tour_guides_items_full.selected_guide.receipts.length">

                            <div class="d-block text-right" v-if="tour_guides_items_full && tour_guides_items_full.selected_guide">
                                <strong><h5>To Balance: € {{tour_guides_items_full.selected_guide.balance}}</h5></strong>
                            </div>

                            <div class="d-block text-right" v-if="tour_guides_items_full && tour_guides_items_full.selected_guide">
                                <strong><h5>Remarks: {{tour_guides_items_full.selected_guide.to_balance ? 'Unbalanced' : 'Balanced'}}</h5></strong>
                            </div>

                            <div class="d-block text-right" v-if="tour_guides_items_full && tour_guides_items_full.selected_guide">
                                <b-button-group>
                                    <b-button variant="success" @click="balanced()">Balanced</b-button>
                                    <b-button variant="danger" @click="balanced(false)">Unbalanced</b-button>
                                </b-button-group>
                                
                            </div>

                        </div>
                    </div>
                </div>

                <div v-else>
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

                        </b-table>
                    </div>

                    <div v-if="tour_guides_items && tour_guides_items.receipts.length">
                        
                        <div class="d-block text-right" v-if="tour_guides_items && tour_guides_items.balance">
                            <strong><h5>To Balance: € {{tour_guides_items.balance}}</h5></strong>
                        </div>

                        <div class="d-block text-right" v-if="tour_guides_items">
                            <b-badge v-if="tour_guides_items.to_balance" variant="danger">To be balanced</b-badge>
                            <b-badge v-else variant="success">Balanced</b-badge>
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
                        invalid-feedback="The date is required"
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
                                <b-button variant="info" @click="reset">Add</b-button>
                                <b-button variant="warning" @click="watchList()">Watch Lists</b-button>
                            </b-button-group>
                        </b-row>
                    </div>
                </div>
            </div>
        </div>

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
        errors: Object
    },
    data() {
        return {
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
                { key: 'payment.balance', label: 'Balance' }
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
            if(!this.isAdmin) return

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
                
                axios.get('/payment/show/' + params.user, {
                    params: {
                        'date': this.date
                    }
                })
                .then(response => {
                    this.dateState = 'valid'
                    
                    this.tour_guides_items_full = response.data.data

                    this.tour_guides_items = response.data.data.tour_guides

                    this.totalRows = response.data.data.tour_guides.length

                    this.receipts_items = this.isAdmin && response.data.data.selected_guide ? response.data.data.selected_guide.receipts : null

                    this.date_formats = response.data.formats
                    
                })
                
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
                user = user.item ? user.item.id : ''
            }

            if(!this.isWatchList) {
                this.isWatchList = true

                this.populating_payment_table = false

                return
            }
            
            this.populating_payment_table = true
            
            axios.get('/payment/show/' + user, {
                params: {
                    'date': this.date
                }
            })
            .then(response => {
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
        }
    },
    created() {

        this.isWatchList = this.isAdmin;
        
    }
}
</script>
