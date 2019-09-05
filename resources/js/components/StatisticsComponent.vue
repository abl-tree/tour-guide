<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Economics and Statistics
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
                                                                <tr v-for="(value, index) in row.item.data" :key="index">
                                                                    <td>{{value.departure.date}}</td>
                                                                    <td>{{value.departure.tour.title}}</td>
                                                                    <td>€ {{value.rate}}</td>
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
        <br>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Charts for the Performance Monitoring
                    </div>
                    <div class="card-body">                        
                        <b-row>
                            <b-col md="3">
                                <b-form-select class="mb-3" v-model="filterCompanyOption" @change="optionChange">
                                    <option :value="null">All</option>
                                    <option value="category">By Category</option>
                                    <option value="tour">By Tour</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="5" v-if="filterCompanyOption === null">
                                <b-form-select class="mb-3" v-model="allTourCategories">
                                    <option :value="null">Small and Private Categories</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="5" v-else-if="filterCompanyOption === 'category'">
                                <b-form-select class="mb-3" v-model="selectedTourCategory" @change="tourCategoryChange">
                                    <option :value="null">Please select category</option>
                                    <option value="small">Small Category</option>
                                    <option value="private">Private Category</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="5" v-else-if="filterCompanyOption === 'tour'">
                                <b-form-select class="mb-3" v-model="selectedTour" @change="tourChange">
                                    <option :value="null">Please select tour</option>
                                    <option v-for="(tour, index) in tours" :key="index" :value="tour.id">{{tour.title}}</option>
                                </b-form-select>
                            </b-col>
                        </b-row>
                        
                        <b-row>
                            <b-col md="3">
                                <b-form-select class="mb-3" v-model="filterCompany" @change="selectFilterCompany">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="3" v-if="filterCompany === 'yearly'">
                                <b-form-select class="mb-3" v-model="selectedYearCompany" @change="yearCompanyChange">
                                    <option :value="null">Year:</option>
                                    <option v-for="(year, index) in years" :key="index" :value="year.toString()">{{ year }}</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="3" v-else-if="filterCompany === 'monthly'">
                                <vue-monthly-picker
                                v-model="selectedMonthCompany"
                                dateFormat="MMMM YYYY"
                                :clearOption="false"
                                @selected="monthSelectedCompany()">
                                </vue-monthly-picker>
                            </b-col>
                            <b-col md="3" v-else-if="filterCompany === 'weekly'">
                                <b-form-input type="week" v-model="selectedWeekCompany" @change="weekSelectedCompany"></b-form-input>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col md="6">
                                <div class="small">
                                    <line-chart :chart-data="datacollection" :options="{
                                        responsive: true,
                                        maintainAspectRatio: false
                                    }"></line-chart>
                                </div>
                            </b-col>
                            <b-col md="6">
                                <div class="small">
                                    <apexchart type="pie" width="380" :options="chartOptions" :series="series"></apexchart>
                                </div>
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
    import LineChart from './BarChart.js'
    import { globalAgent } from 'https'

    export default {
        name: 'Statistics',
        components: {
            VueMonthlyPicker,
            LineChart
        },
        props: {
            is_admin: Boolean,
            date: String,
            tours: {
                type: Array,
                default: null
            }
        },
        data() {
            return {
                selectedDate: moment().format('YYYY-MM-DD'),
                selectedWeek: moment().format('YYYY-[W]WW'),
                selectedMonth: moment(),
                selectedYear: moment().format('YYYY').toString(),
                selectedWeekCompany: moment().format('YYYY-[W]WW'),
                selectedMonthCompany: moment(),
                selectedYearCompany: moment().format('YYYY').toString(),
                filter: 'monthly',
                filterCompany: 'monthly',
                filterCompanyOption: null,
                allTourCategories: null,
                selectedTourCategory: null,
                selectedTour: null,
                isBusy: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 'All'],
                imageProps: { width: 50, height: 50, class: 'm1' },
                fields: [{key: 'payment_type', label: 'Payment Type'}, 'guide', 'rate_total', {key: 'payment_total', label: 'Anticipi and Incassi'}, 'total', {key: 'is_balance', label: 'Remarks'}, 'show_details'],
                items: [],
                total: {
                    'payment': 0,
                    'rate' : 0
                },
                datacollection: null,
                series: [],
                chartOptions: {
                    labels: [],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            title: {
                                text: "Percentage of tours per guide"
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                }
            }
        },
        methods: {
            fillData (data) {
                let labels = []
                let datasets = []
                let globaLabel = data.label

                for (let a = 0; a < data.guides.length; a++) {
                    const label = data.guides[a].full_name;
                    const count = data.guides[a].schedules_count;
                    
                    datasets[a] = count
                    labels[a] = label
                }

                this.series = datasets
                this.chartOptions = {
                    labels : labels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    title: {
                        text: "% of tours per guide"
                    },
                }

                labels = []
                datasets = []

                data = data.data

                for (let a = 0; a < data.length; a++) {
                    const datum = data[a];

                    labels[a] = datum.label
                    datasets[a] = datum.data
                }
                
                this.datacollection = {
                    labels: labels,
                    datasets: [
                        {
                            label: globaLabel,
                            backgroundColor: '#f87979',
                            data: datasets
                        }
                    ]
                }
            },
            getCompany() {
                let data
                let params = {}
                
                if(this.filterCompany === 'monthly') {
                    if(this.filterCompanyOption === 'category' && this.selectedTourCategory) {
                        params = {
                            date: moment(this.selectedMonthCompany).format('YYYY-MM'),
                            category: this.selectedTourCategory
                        }
                    } else if(this.filterCompanyOption === 'tour' && this.selectedTour) {
                        params = {
                            date: moment(this.selectedMonthCompany).format('YYYY-MM'),
                            tour_id: this.selectedTour
                        }
                    } else {
                        params = {
                            date: moment(this.selectedMonthCompany).format('YYYY-MM')
                        }
                    }
                } else if(this.filterCompany === 'yearly') {
                    if(this.filterCompanyOption === 'category' && this.selectedTourCategory) {
                        params = {
                            date: moment(this.selectedYearCompany).format('YYYY-MM'),
                            category: this.selectedTourCategory
                        }
                    } else if(this.filterCompanyOption === 'tour' && this.selectedTour) {
                        params = {
                            date: moment(this.selectedYearCompany).format('YYYY-MM'),
                            tour_id: this.selectedTour
                        }
                    } else {
                        params = {
                            date: moment(this.selectedYearCompany).format('YYYY-MM')
                        }
                    }
                } else if(this.filterCompany === 'weekly') {
                    if(this.filterCompanyOption === 'category' && this.selectedTourCategory) {
                        params = {
                            date: this.selectedWeekCompany,
                            category: this.selectedTourCategory
                        }
                    } else if(this.filterCompanyOption === 'tour' && this.selectedTour) {
                        params = {
                            date: this.selectedWeekCompany,
                            tour_id: this.selectedTour
                        }
                    } else {
                        params = {
                            date: this.selectedWeekCompany
                        }
                    }
                }

                return axios.get('/charts/filter/' + this.filterCompany, {
                    params: params
                })
                .then(response => {
                    this.fillData(response.data)
                    
                    return(response.data)
                })
                .catch(error => {
                    return [];
                })
                .finally(final => {

                });
            },
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

                return axios.get('/statistics/filter/' + this.filter, {
                    params: {
                        date: data
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
            deleteTour(tour) {
                if(!confirm("Do you really want to delete it?")) return

                axios.delete('/tours/'+tour.id)
                .then(data => {

                })
                .catch(error => {

                })
                .finally(final => {
                    this.get()
                })
            },
            suspendTour($tour) {
                if(!confirm("Do you really want to suspend it?")) return

                axios.post('/tours/' + $tour.id + '/suspend')
                .then(data => {

                })
                .catch(error => {

                })
                .finally(final => {
                    this.get()
                })
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
            },
            selectFilterCompany() {                
                this.getCompany()
            },
            yearCompanyChange() {                
                let year = this.selectedYearCompany.toString()
                
                this.selectedMonthCompany = moment(year).format('YYYY-MM').toString()

                this.getCompany();
            },
            monthSelectedCompany() {
                this.selectedYearCompany = moment(this.selectedMonthCompany).format('YYYY').toString()

                this.getCompany()
            },
            weekSelectedCompany() {
                this.getCompany()
            },
            tourCategoryChange() {
                this.getCompany()
            },
            tourChange() {
                this.getCompany()
            },
            optionChange() {
                if(!this.filterCompanyOption) {
                    this.getCompany()
                }
            }
        },
        computed : {
            years () {
                const year = new Date().getFullYear()
                return Array.from({length: year - 1900}, (value, index) => 1901 + index).reverse()
            }
        },
        mounted() {
            if(!this.is_admin) this.fields = ['title', 'code', 'image', 'color', 'type', 'actions']

            this.selectedMonth = moment(this.date)

            this.selectedMonthCompany = moment(this.date)

            this.get()

            this.getCompany()
            
        }
    }
</script>