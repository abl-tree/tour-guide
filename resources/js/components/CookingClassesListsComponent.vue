<template>
    <div>
        <div class="card">
            <div class="card-header">
                Lists
            </div>
            <div class="card-body">
                <b-row>
                    <b-col md="4">
                        <b-form-select class="mb-3" v-model="filter" @change="selectFilter">
                            <option value="null">No Filter</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </b-form-select>
                    </b-col>
                    <b-col md="4"></b-col>
                    <b-col md="4">
                        <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
                            <b-form-select v-model="perPage" :options="pageOptions" @change="paginationChange"></b-form-select>
                        </b-form-group>
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

                <b-table 
                :items="items" 
                :fields="fields"
                :busy="isBusy"
                striped 
                responsive >

                    <div slot="table-busy" class="text-center text-danger my-2">
                        <b-spinner class="align-middle"></b-spinner>
                        <strong>Loading...</strong>
                    </div>

                    <template slot="cost_per_chef" slot-scope="row">
                        €{{row.item.cost_per_chef}}
                    </template>

                    <template slot="cost_per_assistant" slot-scope="row">
                        €{{row.item.cost_per_assistant}}
                    </template>

                    <template slot="fuel_cost" slot-scope="row">
                        €{{row.item.fuel_cost}}
                    </template>

                    <template slot="ingredient_cost" slot-scope="row">
                        €{{row.item.ingredient_cost}}
                    </template>

                    <template slot="other_cost" slot-scope="row">
                        €{{row.item.other_cost}}
                    </template>

                    <template slot="cost_per_participant" slot-scope="row">
                        €{{row.item.cost_per_participant}}
                    </template>

                    <template slot="earnings" slot-scope="row">
                        €{{row.item.earnings}}
                    </template>

                    <template slot="costs" slot-scope="row">
                        €{{row.item.costs}}
                    </template>

                    <template slot="balance" slot-scope="row">
                        €{{row.item.balance}}
                    </template>

                    <template slot="actions" slot-scope="row">
                        <b-button-group size="sm" v-if="row.item.actions != false">
                            <!-- <b-button variant="dark" :href="'/coordinator/'+row.item.id+'/edit'">Edit</b-button> -->
                            <b-button variant="danger" @click="deleteCoordinator(row.item)">Delete</b-button>
                        </b-button-group>
                    </template>
                </b-table>
                
                <b-row>
                    <b-col md="6" class="my-1">
                        <b-pagination
                        v-model="currentPage"
                        :total-rows="totalRows"
                        :per-page="perPage"
                        class="my-0"
                        @input="paginationChange"
                        ></b-pagination>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col md="12">
                        <apexchart type="bar" height=350 :options="chartOptions" :series="chartSeries" />
                    </b-col>
                </b-row>
            </div>
        </div>
    </div>
</template>

<script>
    import VueMonthlyPicker from 'vue-monthly-picker'
    import moment from 'moment'

    export default {
        name: 'CookingClassesList',
        data() {
            return {
                selectedDate: moment().format('YYYY-MM-DD'),
                selectedWeek: moment().format('YYYY-[W]WW'),
                selectedMonth: moment().format('YYYY-MM'),
                selectedYear: moment().format('YYYY').toString(),
                filter: 'daily',
                isBusy: true,
                totalRows: 0,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 100, 500],
                fields: [
                    'date', 
                    'no_of_chef', 
                    'cost_per_chef', 
                    'no_of_assistant', 
                    'cost_per_assistant', 
                    'fuel_cost', 
                    'ingredient_cost', 
                    'other_cost', 
                    'no_of_participant', 
                    'cost_per_participant', 
                    'earnings',
                    'costs',
                    'balance',
                    'actions'
                ],
                items: [],
                chartSeries: [{
                    name: 'Earnings',
                    data: []
                },{
                    name: 'Costs',
                    data: []
                }],
                chartOptions: {
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
                            text: 'Cooking Classes Economics',
                            align: 'left'
                        },
                        grid: {
                            row: {
                                colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                                opacity: 0.5
                            },
                        },
                        xaxis: {
                            type: 'category',
                            categories: [],
                            tooltip: {
                                enabled: true
                            },
                            labels: {
                                show: true,
                                hideOverlappingLabels: false,
                                rotate: -45,
                                rotateAlways: true,
                                minHeight: 80
                            }
                        },
                        yaxis: {
                            labels: {
                                show: true,
                                formatter: (value) => { return '€' + value.toFixed(2) }
                            },
                            title: {
                                text: 'Amount'
                            }
                        }
                    }
            }
        },
        methods: {
            dateInputFormat(value, event) {
                return moment(value).format('YYYY-MM-DD')
            },
            yearChange() {                
                let year = this.selectedYear.toString()
                
                this.selectedMonth = moment(year).format('YYYY-MM').toString()

                this.get();
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
            deleteCoordinator(coordinator) {
                let self = this

                Swal.fire({
                    title: 'Are you sure to do this operation?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                    
                    return axios.delete('cooking_classes/' + coordinator.id)
                    .then(function(response) {
                        if (!response.statusText == "OK") {
                            throw new Error(response.statusText)
                        }

                        return response.data
                    }).catch(error => {
                        let errors = error.response.data

                        let errorMsg = errors ? errors.joing(' ') : null
                        
                        Swal.showValidationMessage(
                        `Request failed: ${errorMsg ? errorMsg : error}`
                        )
                    })
                            
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if(typeof result.value !== 'undefined') {
                        self.get()

                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
                })

            },
            paginationChange() {
                this.get()
            },
            get() {
                this.isBusy = true

                let data;
            
                if(this.filter === 'monthly') {
                    data = moment(this.selectedMonth).format('YYYY-MM')
                } else if(this.filter === 'yearly') {
                    data = moment(this.selectedYear).format('YYYY-MM');
                } else if(this.filter === 'weekly') {
                    data = this.selectedWeek
                } else if(this.filter === 'daily') {
                    data = this.selectedDate
                }

                this.table(data)

                this.lineChart(data)
            },
            table(data) {
                axios.get('/get/cooking_classes/list', {
                    params: {
                        per_page: this.perPage,
                        page: this.currentPage,
                        date: data ? data : null,
                        filter: this.filter
                    }
                })
                .then(response => {
                    let result = response.data.result

                    let total = response.data.total
                    

                    this.items = result.data
                    this.items.push(total)
                    this.totalRows = result.total
                    this.currentPage = result.current_page
                })
                .finally(final => {
                    this.isBusy = false
                })
            },
            lineChart(data) {
                axios.get('/get/cooking_classes/statistics', {
                    params: {
                        date: data ? data : null,
                        filter: this.filter
                    }
                })
                .then(response => {
                    let results = response.data.result

                    let categories = []
                    let data1 = []
                    let data2 = []

                    for (let a = 0; a < results.length; a++) {
                        const result = results[a];
                        
                        categories.push(result.title)
                        data1.push(result.earnings)
                        data2.push(result.costs)

                        this.chartOptions = {
                            xaxis: {
                                categories: categories
                            }
                        }

                        this.chartSeries = [{
                            data: data1
                        }, {
                            data: data2
                        }]
                    }

                    console.log(this.chartOptions);
                    
                    
                })
                .finally(final => {
                    this.isBusy = false
                })
            }
        },
        computed : {
            years () {
                const year = new Date().getFullYear()
                return Array.from({length: year - 1900}, (value, index) => 1901 + index).reverse()
            }
        },
        mounted() {
            this.get()
        }
    }
</script>