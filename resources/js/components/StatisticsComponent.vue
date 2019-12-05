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
                                <b-form-input
                                v-model="selectedMonth" 
                                type="month" 
                                @change="monthSelected">
                                </b-form-input>
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
                                :busy="isBusyPaymentItem"
                                :items="items" 
                                :fields="fields"
                                striped 
                                responsive="sm" 
                                show-empty
                                >

                                    <div slot="table-busy" class="text-center text-danger my-2">
                                        <b-spinner class="align-middle"></b-spinner>
                                        <strong>Loading...</strong>
                                    </div>

                                    <!-- <template slot="payment_type" slot-scope="data">
                                        <b-form-select v-model="data.item.payment_type_id" size="sm" class="mt-3" @change="paymentTypeChange(data.item)">
                                            <option v-for="(type, index) in payment_types" :key="index" :value="type.id">{{type.name}}</option>
                                        </b-form-select>
                                    </template> -->

                                    <template slot="rate_total" slot-scope="data">
                                        <span :style="data.item.grand_total ? 'font-weight: bold' : ''">
                                            € {{data.item.rate_total}}
                                        </span>
                                    </template>

                                    <template slot="payment_total" slot-scope="data">
                                        <span :style="data.item.grand_total ? 'font-weight: bold' : ''">
                                        € {{data.item.payment_total}}
                                        </span>
                                    </template>

                                    <template slot="total" slot-scope="data">
                                        <span :style="data.item.grand_total ? 'font-weight: bold' : ''">
                                        € {{data.item.rate_total + data.item.payment_total}}
                                        </span>
                                    </template>
                                    
                                    <template slot="is_rate_total_paid" slot-scope="data">
                                        <span v-if="!data.item.grand_total">
                                            <b-badge v-if="!data.item.is_rate_total_paid" variant="danger" style="cursor: pointer;">Unpaid</b-badge>
                                            <b-badge v-else variant="success" style="cursor: pointer;">Paid</b-badge>
                                        </span>
                                    </template>
                                    
                                    <template slot="is_balance" slot-scope="data">
                                        <span v-if="!data.item.grand_total">
                                            <b-badge v-if="data.item.is_balance" variant="danger" @click="toBalance(data.item)" style="cursor: pointer;">To Balance</b-badge>
                                            <b-badge v-else variant="success" @click="toBalance(data.item)" style="cursor: pointer;">Balanced</b-badge>
                                        </span>
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
                                        <b-button size="sm" @click="row.toggleDetails" class="mr-2" v-if="!row.item.grand_total">
                                        {{ row.detailsShowing ? 'Hide' : 'Show'}} Details
                                        </b-button>
                                    </template>

                                    <template slot="row-details" slot-scope="row">
                                        <div class="row justify-content-center" v-if="!row.item.grand_total">
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
                                                                        <td>
                                                                            <b-form-checkbox
                                                                            :id="'checkbox-' + value.departure.id"
                                                                            v-model="value.departure.is_paid"
                                                                            :name="'checkbox-' + value.departure.id"
                                                                            :value="true"
                                                                            :unchecked-value="false"
                                                                            @change="togglePayment(row)"
                                                                            >
                                                                            </b-form-checkbox>
                                                                        </td>
                                                                        <td>{{value.departure.date}}</td>
                                                                        <td>{{value.departure.tour.title}}</td>
                                                                        <td>€ {{value.rate}}</td>
                                                                        <td>
                                                                            <b-badge :variant="value.departure.paid_at ? 'success' : 'danger'" style="cursor: pointer;" @click="paidBtn(value.departure, row.index, index)">{{value.departure.paid_at ? 'Paid' : 'Unpaid'}}</b-badge>
                                                                        </td>
                                                                    </tr>
                                                            </tbody>
                                                        </table>
                                                    </b-col>

                                                    <b-row class="mb-2">
                                                        <b-col sm="12" class="text-sm-left">
                                                            <b-form-checkbox
                                                            v-model="row.item.data.allSelected"
                                                            @change="toggleAllPayment(row)"
                                                            >
                                                            {{ row.item.data.allSelected ? 'Un-select All' : 'Select All' }}
                                                            </b-form-checkbox>
                                                        </b-col>
                                                    </b-row>

                                                    <b-row class="mb-2">
                                                        <b-col sm="12" class="text-sm-right">
                                                            <b-button-group size="sm">
                                                                <b-button variant="success" @click="submitTourUpdates('paid', row)">Paid</b-button>
                                                                <b-button variant="danger" @click="submitTourUpdates('unpaid', row)">Unpaid</b-button>
                                                            </b-button-group>
                                                        </b-col>
                                                    </b-row>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="12" class="text-sm-center"><b>Anticipi & Incassi</b></b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    {{/*row.item.payment_data.length ? 'balance' : 'unbalance'*/}}
                                                    <b-col sm="12" class="text-sm-left">Amount - € {{row.item.payment_total}}</b-col>
                                                </b-row>

                                                <b-row class="mb-2">
                                                    <b-col sm="6" class="text-sm-left">
                                                        <b>Grand Total - € {{row.item.payment_total + row.item.rate_total}}</b> 
                                                    </b-col>
                                                    <b-col sm="6" class="text-sm-right">
                                                        <b-badge v-if="row.item.is_balance" variant="danger" style="cursor: pointer;">To Balance</b-badge>
                                                        <b-badge v-else variant="success" style="cursor: pointer;">Balanced</b-badge>
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
                            <b-col md="5" v-if="filterCompany === 'yearly'">
                                <b-form-select class="mb-3" v-model="selectedYearCompany" @change="yearCompanyChange">
                                    <option :value="null">Year:</option>
                                    <option v-for="(year, index) in years" :key="index" :value="year.toString()">{{ year }}</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="5" v-else-if="filterCompany === 'monthly'">
                                
                                <b-form-input
                                v-model="selectedMonthCompany" 
                                type="month" 
                                @change="monthSelectedCompany">
                                </b-form-input>
                                
                            </b-col>
                            <b-col md="5" v-else-if="filterCompany === 'weekly'">
                                <b-form-input type="week" v-model="selectedWeekCompany" @change="weekSelectedCompany"></b-form-input>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col md="6">
                                <b-form-select class="mb-3" v-model="selectedGuide" @change="guideChange">
                                    <option :value="null">Please select guide</option>
                                    <option v-for="(guide, index) in guides" :key="index" :value="guide.id">{{guide.full_name}}</option>
                                </b-form-select>
                            </b-col>
                            <b-col md="2">
                                <b-button variant="success" style="width: 100%;" @click="getCompany(true)">Download</b-button>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col md="6">
                                <div class="small">
                                    <apexchart type=bar height=350 :options="datacollection.chartOptions" :series="datacollection.series" />
                                </div>
                            </b-col>
                            <b-col md="6" style="overflow: auto;">
                                <apexchart type="pie" :options="chartOptions" :series="series"></apexchart>
                            </b-col>
                            
                            <b-col md="12" class="text-right">
                                <h5>{{filterCompany[0].toUpperCase() + filterCompany.slice(1)}} Grand Total: {{tourGrandTotal}}</h5>
                            </b-col>
                        </b-row>

                        <b-row>
                            <b-col md="12">
                                <apexchart type=line height=350 :options="trends.chartOptions" :series="trends.series" />
                            </b-col>
                            <b-col md="12" class="text-right">
                                <h5>{{filterCompany[0].toUpperCase() + filterCompany.slice(1)}} Grand Total: € {{trendGrandTotal}}</h5>
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
    import { log } from 'util';

    const CancelToken = axios.CancelToken
    let cancel
    let cancel1
    let cancel2

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
            },
            guides: {
                type: Array,
                default: null
            },
            payment_types: {
                type: Array
            },
        },
        data() {
            return {
                activeTab: 'accordion-0',
                selected_monthly_payment: {},
                isBusyPaymentItem: true,
                selectedDate: moment().format('YYYY-MM-DD'),
                selectedWeek: moment().format('YYYY-[W]WW'),
                selectedMonth: moment().format('YYYY-MM'),
                selectedYear: moment().format('YYYY').toString(),
                selectedWeekCompany: moment().format('YYYY-[W]WW'),
                selectedMonthCompany: moment().format('YYYY-MM'),
                selectedYearCompany: moment().format('YYYY').toString(),
                filter: 'monthly',
                filterCompany: 'monthly',
                filterCompanyOption: null,
                allTourCategories: null,
                selectedTourCategory: null,
                selectedTour: null,
                selectedGuide: null,
                isBusy: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 'All'],
                imageProps: { width: 50, height: 50, class: 'm1' },
                fields: [
                    {key: 'payment_type', label: 'Payment Type'}, 
                    {key: 'guide', label: 'Guide'}, 
                    {key: 'rate_total', label: 'Rate Total'}, 
                    {key: 'is_rate_total_paid', label: 'Status'}, 
                    {key: 'payment_total', label: 'Extra-rates'}, 
                    {key: 'is_balance', label: 'Status'}, 
                    {key: 'total', label: 'Total'}, 
                    {key: 'show_details', label: 'Show Details'}
                ],
                items: [],
                total: {
                    payment: 0,
                    rate : 0
                },
                trends: {
                    series: [],
                    chartOptions: {}
                },
                trendGrandTotal: 0,
                tourGrandTotal: 0,
                datacollection: {
                    series: [],
                    chartOptions: {
                        plotOptions: {
                            bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'flat'	
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },

                        xaxis: {
                            categories: [],
                            tooltip: {
                                enabled: true
                            }
                        },
                        yaxis: {
                            title: {
                            text: 'No. of Tours'
                            }
                        },
                        fill: {
                            opacity: 1

                        },
                        tooltip: {
                            y: {
                            formatter: function (val) {
                                // return "$ " + val + " thousands"
                            }
                            }
                        }
                    }
                },
                series: [],
                chartOptions: {
                    labels: [],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                type: 'pie',
                                size: '100%'
                            },
                            title: {
                                text: "Percentage of tours per guide"
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                },
                selected_payment: [],
                test: null
            }
        },
        methods: {
            toBalance: function(data) {
                
                let payments = data.monthly_payment

                let params = ''

                if(payments.length > 1) {
                    params += 'start=' + payments[0].date

                    params += '&end=' + payments[payments.length - 1].date
                } else {
                    params += 'start=' + payments[0].date
                }
                
                window.open('/admin/payment/' + data.user.id + '/watchlist' +'?' + params)
                
            },
            fillData (data) {
                let labels = []
                let datasets = []
                let globaLabel = data.label
                this.tourGrandTotal = data.total

                for (let a = 0; a < data.guides.length; a++) {
                    const label = data.guides[a].full_name;
                    const count = data.guides[a].schedules_count;
                    
                    datasets.push(count)
                    labels.push(label)
                }

                this.series = datasets

                this.chartOptions = {
                    labels : labels,
                    responsive: [{
                        breakpoint: 400,
                        options: {
                            chart: {
                                width: 400
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
                let small_group = []
                let private_tour = []

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
                    ],
                    series: [{
                        name: globaLabel,
                        data: datasets
                    }],
                    chartOptions: {
                        plotOptions: {
                            bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'flat'	
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },

                        xaxis: {
                            categories: labels,
                            tooltip: {
                                enabled: true
                            }
                        },
                        yaxis: {
                            title: {
                            text: 'No. of Tours'
                            }
                        },
                        fill: {
                            opacity: 1

                        },
                        tooltip: {
                            y: {
                            formatter: function (val) {
                                return val + " tours"
                            }
                            }
                        }
                    }
                }
            },
            getCompany(download = false) {
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

                params.guide = this.selectedGuide

                let url = '/charts/filter/'

                if(download) {
                    url = '/statistics/download/' + this.filterCompany
                    
                    if(params.date) {
                        url += '?date=' + params.date;
                    }
                    
                    if(params.tour_id) {
                        url += '&tour_id=' + params.tour_id
                    }
                    
                    if(params.guide) {
                        url += '&guide=' + params.guide
                    }
                    
                    if(params.category) {
                        url += '&category=' + params.category
                    }

                    window.open(url)

                    return
                }

                this.getTourTrends()
                
                cancel && cancel()

                return axios.get(url + this.filterCompany, {
                    params: params,
                    cancelToken: new CancelToken(function executor(c) {
                        cancel = c
                    })
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
            getTourTrends() {
                let data
                let params = {}
                let self = this
                
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

                params.guide = this.selectedGuide

                let url = 'statistics/tour_trends/'

                cancel2 && cancel2()

                return axios.get(url + this.filterCompany, {
                    params: params,
                    cancelToken: new CancelToken(function executor(c) {
                        cancel2 = c
                    })
                })
                .then(response => {
                    let data = response.data['data']
                    let earnings = []
                    let costs = []
                    let labels = []
                    let grand_total = response.data['grand_total']

                    self.trendGrandTotal = grand_total

                    for (let a = 0; a < data.length; a++) {
                        const value = data[a]
                        
                        costs.push(value.cost)
                        earnings.push(value.earning)
                        labels.push(value.label)
                    }
                    
                    this.trends.series = [{
                        name: "Earnings",
                        data: earnings
                    }, {
                        name: "Costs",
                        data: costs
                    }]
                    
                    this.trends.chartOptions = {
                        chart: {
                            height: 350,
                            zoom: {
                                enabled: false
                            }
                        },
                        colors: ['#00b234', '#d80000'],
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'straight'
                        },
                        title: {
                            text: 'Tour Trends',
                            align: 'left'
                        },
                        grid: {
                            row: {
                                colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                opacity: 0.5
                            },
                        },
                        xaxis: {
                            categories: labels,
                            tooltip: {
                                enabled: true
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Amount (€)'
                            }
                        }
                    }
                    
                    return(response.data)
                })
                .catch(error => {
                    return [];
                })
                .finally(final => {

                });
            },
            get(load = true) {

                let data

                this.isBusyPaymentItem = load
            
                if(this.filter === 'monthly') {
                    data = moment(this.selectedMonth).format('YYYY-MM')
                } else if(this.filter === 'yearly') {
                    data = moment(this.selectedYear).format('YYYY-MM');
                } else if(this.filter === 'weekly') {
                    data = this.selectedWeek
                } else if(this.filter === 'daily') {
                    data = this.selectedDate
                }  
                
                cancel1 && cancel1()               

                return axios.get('/statistics/filter/' + this.filter, {
                    params: {
                        date: data
                    },
                    cancelToken: new CancelToken(function executor(c) {
                        cancel1 = c
                    })
                })
                .then(response => {      

                    let rate = 0

                    let payment = 0

                    this.totalRows = response.data.length

                    for (let a = 0; a < response.data.length; a++) {
                        let item = response.data[a];
                        
                        rate += item.rate_total

                        payment += item.payment_total
                    }

                    let temp = {
                        payment_type: null,
                        guide: 'Monthly Grand Total',
                        rate_total: rate,
                        payment_total: payment,
                        total: null,
                        grand_total: 1,
                        _rowVariant: 'danger'
                    }

                    response.data.push(temp)

                    this.items = []

                    this.items = response.data
                    
                    this.total.rate = rate

                    this.total.payment = payment                    
                    
                    this.isBusyPaymentItem = false
                    
                    return(response.data)
                })
                .catch(thrown => {
                    if (axios.isCancel(thrown)) {
                        console.log('Get stats Request cancelled', thrown.message);
                    } else {
                        this.isBusyPaymentItem = false
                    }
                    // return [];
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
            guideChange() {
                this.getCompany()
            },
            optionChange() {
                if(!this.filterCompanyOption) {
                    this.getCompany()
                }
            },
            paymentTypeChange(data) {
                axios.post('/departure/payment_method', data)
                .then(data => {

                })
                .catch(error => {

                })
                .finally(final => {

                })
            },
            paidBtn(departure, item_index, departure_index) {

                axios.put('departure/paid', departure)
                .then(response => {

                    this.items[item_index].data[departure_index].departure.paid_at = response.data.paid_at

                })

            },
            submitTourUpdates(option, tours) {
                const self = this
                let has_selected_tour = false
                let index = tours.index
                tours = tours && tours.item ? tours.item.data : null

                for (let a = 0; a < tours.length; a++) {
                    const element = tours[a];
                    let departure = element.departure
                    
                    if(departure.is_paid) {
                        has_selected_tour = true

                        break
                    }
                }

                if(!has_selected_tour) {
                    Swal.fire(
                        'Error!',
                        'Please select at least one tour departure.',
                        'error'
                    );

                    return
                }

                Swal.fire({
                    title: 'Are you sure to change the status?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {

                        return axios.put('departure/payment/bulk/' + option, tours)
                            .then(response => {
                                if (!response.statusText == "OK") {
                                    throw new Error(response.statusText)
                                }

                                self.items[index].data = response.data

                                self.get(true)

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
                    }
                })
            },
            toggleAllPayment(data) {

                if(data) {
                    let index = data.index
                    let departures = this.items[index].data                    

                    for (let a = 0; a < departures.length; a++) {
                        const element = departures[a];
                        
                        this.items[index].data[a].departure.is_paid = this.items[index].data.allSelected ? false : true
                    }
                    
                }

            },
            togglePayment(data) {
                    let index = data.index
                    let departures = this.items[index].data                

                    for (let a = 0; a < departures.length; a++) {
                        const element = departures[a];
                        
                        if(!element.is_paid || element.is_paid === false) {
                            this.items[index].data.allSelected = false
                            return
                        }
                    }

                    this.items[index].data.allSelected = true
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

            // this.selectedMonth = moment(this.date).format('YYYY-MM')

            // this.selectedMonthCompany = moment(this.date).format('YYYY-MM')

            this.get(true)

            this.getCompany()
            
        }
    }
</script>