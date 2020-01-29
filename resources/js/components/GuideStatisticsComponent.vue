<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Economics
                    </div>
                    <div class="card-body">
                        <b-row>
                            <b-col md="3">
                                <b-form-select class="mb-3" v-model="filter" @change="selectFilter">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </b-form-select>
                            </b-col>
                        </b-row>
                        <b-row v-if="filter === 'yearly'">
                            <b-col md="3">
                                <b-form-select class="mb-3" v-model="selectedYear" @change="yearChange">
                                    <option :value="null">Year:</option>
                                    <option v-for="(year, index) in years" :key="index" :value="year.toString()">{{ year }}</option>
                                </b-form-select>
                            </b-col>
                        </b-row>
                        <b-row v-else-if="filter === 'monthly'">
                            <b-col md="3">
                                <vue-monthly-picker
                                v-model="selectedMonth"
                                dateFormat="MMMM YYYY"
                                :clearOption="false"
                                @selected="monthSelected()">
                                </vue-monthly-picker>
                            </b-col>
                        </b-row>
                        <b-row v-else-if="filter === 'weekly'">
                            <b-col md="3">
                                <b-form-input type="week" v-model="selectedWeek" @change="weekSelected"></b-form-input>
                            </b-col>
                        </b-row>
                        <b-row v-else-if="filter === 'daily'">
                            <b-col md="3">
                                <b-form-input type="date" v-model="selectedDate" :formatter="dateInputFormat" @change="dateSelected"></b-form-input>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col md="12">
                                <b-table 
                                :items="items" 
                                :fields="fields"
                                striped 
                                responsive="sm" 
                                foot-clone
                                >

                                    <div slot="table-busy" class="text-center text-danger my-2">
                                        <b-spinner class="align-middle"></b-spinner>
                                        <strong>Loading...</strong>
                                    </div>

                                    <template slot="rate_total" slot-scope="data">
                                        € {{data.item.rate_total}}
                                    </template>
                                    
                                    <template slot="is_rate_total_paid" slot-scope="data">
                                        <span v-if="!data.item.grand_total">
                                            <b-badge v-if="!data.item.is_rate_total_paid" variant="danger" style="cursor: pointer;">Unpaid</b-badge>
                                            <b-badge v-else variant="success" style="cursor: pointer;">Paid</b-badge>
                                        </span>
                                    </template>

                                    <template slot="payment_total" slot-scope="data">
                                        € {{data.item.payment_total}}
                                    </template>

                                    <template slot="total" slot-scope="data">
                                        € {{data.item.rate_total + data.item.payment_total}}
                                    </template>
                                    
                                    <template slot="is_balance" slot-scope="data">
                                        <b-badge v-if="data.item.is_balance" variant="danger">To Balance</b-badge>
                                        <b-badge v-else variant="success">Balanced</b-badge>
                                    </template>

                                    <template slot="FOOT_payment_type">
                                        <span></span>
                                    </template>

                                    <template slot="FOOT_is_balance">
                                        <span></span>
                                    </template>

                                    <template slot="FOOT_guide">
                                        <span class="text-info">{{filter.charAt(0).toUpperCase() + filter.slice(1)}} Grand Total</span>
                                    </template>

                                    <template slot="FOOT_rate_total">
                                        <span class="text-info">€ {{total.rate}}</span>
                                    </template>

                                    <template slot="FOOT_payment_total">
                                        <span class="text-info">€ {{total.payment}}</span>
                                    </template>

                                    <template slot="FOOT_total">
                                        <span class="text-info">€ {{total.rate + total.payment}}</span>
                                    </template>

                                    <template slot="show_details" slot-scope="row">
                                        <b-button size="sm" @click="row.toggleDetails" class="mr-2">
                                        {{ row.detailsShowing ? 'Hide' : 'Show'}} Details
                                        </b-button>
                                    </template>

                                    <template slot="row-details" slot-scope="row">
                                        <div class="row justify-content-center">
                                            <b-card class="col-md-6">
                                                <b-row class="mb-2">
                                                    <b-col v-if="filter === 'monthly'" sm="12" class="text-sm-center"><b>Tours of the month</b></b-col>
                                                    <b-col v-else-if="filter === 'daily'" sm="12" class="text-sm-center"><b>Tours of the day</b></b-col>
                                                    <b-col v-else-if="filter === 'weekly'" sm="12" class="text-sm-center"><b>Tours of the week</b></b-col>
                                                    <b-col v-else-if="filter === 'yearly'" sm="12" class="text-sm-center"><b>Tours of the year</b></b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="12">
                                                        <table class="table table-sm">
                                                            <tbody>
                                                                <tr v-for="(value, index) in row.item.data.departures" :key="index">
                                                                    <td>{{value.date}}</td>
                                                                    <td>{{row.item.tour}}</td>
                                                                    <td>€ {{value.rate.amount}}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="12" class="text-sm-center"><b>Anticipi & Incassi</b></b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="12" class="text-sm-left">Amount - € {{row.item.payment_total}}</b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="12" class="text-sm-left">
                                                        <b>Grand Total - € {{row.item.payment_total + row.item.rate_total}}</b> 
                                                    </b-col>
                                                    <b-col sm="12" class="text-sm-right">
                                                        <b-badge v-if="row.item.is_balance" variant="danger">To Balance</b-badge>
                                                        <b-badge v-else variant="success">Balanced</b-badge>
                                                    </b-col>
                                                </b-row>
                                            </b-card>
                                        </div>
                                    </template>

                                </b-table>
                            </b-col>
                        </b-row>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    import VueMonthlyPicker from 'vue-monthly-picker'
    import moment from 'moment'
    // import LineChart from './BarChart.js'
    // import { globalAgent } from 'https'

    export default {
        components: {
            VueMonthlyPicker,
            // LineChart
        },
        props: {
            guide: Object
        },
        data() {
            return {
                selectedDate: moment().format('YYYY-MM-DD'),
                selectedWeek: moment().format('YYYY-[W]WW'),
                selectedMonth: moment(),
                selectedYear: moment().format('YYYY').toString(),
                filter: 'monthly',
                isBusy: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 'All'],
                imageProps: { width: 50, height: 50, class: 'm1' },
                fields: [
                    {key: 'payment_type', label: 'Payment Type'}, 
                    'tour', 
                    'rate_total', 
                    {key: 'is_rate_total_paid', label: 'Status'}, 
                    {key: 'payment_total', label: 'Extra Rates'}, 
                    {key: 'is_balance', label: 'Status'}, 
                    'total', 
                    'show_details'
                ],
                items: [],
                total: {
                    'payment': 0,
                    'rate' : 0
                }
            }
        },
        methods: {
            get() {
                let data
            
                if(this.filter === 'monthly') {
                    data = moment(this.selectedMonth).format('YYYY-MM')
                } else if(this.filter === 'yearly') {
                    data = moment(this.selectedYear).format('YYYY-MM');
                } else if(this.filter === 'weekly') {
                    data = this.selectedWeek
                } else if(this.filter === 'daily') {
                    data = this.selectedDate
                }

                return axios.get('/guide/statistics/filter/' + this.filter, {
                    params: {
                        date: data,
                        user_id: this.guide.id
                    }
                })
                .then(response => {      

                    this.totalRows = response.data.length

                    this.items = response.data

                    this.total.rate = 0

                    this.total.payment = 0

                    for (let a = 0; a < this.items.length; a++) {
                        const item = this.items[a];
                        
                        this.total.rate += item.rate_total

                        this.total.payment += item.payment_total
                    }
                    
                    return(response.data)
                })
                .catch(error => {
                    return [];
                })
                .finally(final => {

                });
            },
            monthSelected() {
                this.selectedYear = moment(this.selectedMonth).format('YYYY').toString()

                this.get()
            },
            weekSelected() {
                this.get()
            },
            dateSelected() {
                this.get()
            },
            selectFilter() {
                this.get()
            },
            yearChange() {                
                let year = this.selectedYear.toString()
                
                this.selectedMonth = moment(year).format('YYYY-MM').toString()

                this.get();
            },
            dateInputFormat(value, event) {
                return moment(value).format('YYYY-MM-DD')
            }
        },
        computed : {
            years () {
                const year = new Date().getFullYear()
                return Array.from({length: year - 1900}, (value, index) => 1901 + index).reverse()
            }
        },
        mounted() {
            this.selectedMonth = moment(this.date)

            this.get()
            
        }
    }
</script>