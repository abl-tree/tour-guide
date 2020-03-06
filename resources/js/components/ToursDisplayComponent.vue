<template>
    <div>
        <div class="card">
            <div class="card-header">
                Tours Lists
            </div>
            <div class="card-body">
                <b-row>
                    <b-col md="4" class="my-1">
                        <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
                        <b-form-select v-model="perPage" :options="pageOptions"></b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col md="6" class="my-1">
                        <b-form-group label-cols-sm="3" label="Tour Type" class="mb-0">
                        <b-form-select v-model="tour_type" @change="onTourTypeChange">
                            <option :value="null">All Tours</option>
                            <option v-for="(type, index) in types" :key="index" :value="type.id">{{type.name}}</option>
                        </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col md="2" class="my-1">
                        <b-button class="pull-right" variant="primary" href="/tours/create">Add Tour</b-button>
                    </b-col>
                </b-row>

                <b-table 
                :items="items" 
                :fields="fields"
                striped 
                responsive="sm" 
                :current-page="currentPage"
                :per-page="perPage"
                :total-row="totalRows">

                    <div slot="table-busy" class="text-center text-danger my-2">
                        <b-spinner class="align-middle"></b-spinner>
                        <strong>Loading...</strong>
                    </div>

                    <template slot="title" slot-scope="row">
                        <b-link v-if="row.item && row.item.title" :href="'/tours/' + row.item.id +'/profile'" target="_blank">{{row.item && row.item.title ? row.item.title : ''}}</b-link>
                    </template>

                    <template slot="code" slot-scope="row">
                        {{row.item.info && row.item.info.tour_code ? row.item.info.tour_code : ''}}
                    </template>

                    <template slot="image" slot-scope="row">
                        <b-img v-if="row.item.info && row.item.info.image_link" v-bind="imageProps" :src="row.item.info.image_link" rounded alt="Rounded image"></b-img>
                    </template>

                    <template slot="color" slot-scope="row">
                        <b-img v-if="row.item.info && row.item.info.color" v-bind="imageProps" :blank="true" :blankColor="row.item.info.color" rounded alt="Rounded image"></b-img>
                    </template>

                    <template slot="type" slot-scope="row">
                        {{row.item.info && row.item.info.type ? row.item.info.type.name : ''}}
                    </template>

                    <template v-if="is_admin" slot="suspended_at" slot-scope="row">
                        {{row.item && row.item.suspended_at ? row.item.suspended_at : ''}}
                    </template>

                    <template slot="actions" slot-scope="row">
                        <b-button-group size="sm">
                            <b-button @click="row.toggleDetails" variant="info">
                            {{ row.detailsShowing ? 'Hide' : 'Show'}}
                            </b-button>
                            <b-button v-if="is_admin" variant="success" :href="'/manifest/'+row.item.id+'/edit'">Manifest</b-button>
                            <b-button v-if="is_admin" variant="dark" :href="'/tours/show/'+row.item.id">Edit</b-button>
                            <b-button v-if="is_admin" :variant="row.item.suspended_at ? 'success' : 'warning'" @click="suspendTour(row.item)">{{row.item.suspended_at ? 'Reactivate' : 'Suspend'}}</b-button>
                            <b-button v-if="is_admin" variant="danger" @click="deleteTour(row.item)">Delete</b-button>
                        </b-button-group>
                    </template>

                    <template slot="row-details" slot-scope="row">
                        <b-card>
                            <b-row class="mb-2">
                                <b-col sm="3" class="text-sm-right"><b>Cash:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.tour_rates && row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'cash'
                                    }).length ? row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'cash'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2">
                                <b-col sm="3" class="text-sm-right"><b>Invoice:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.tour_rates && row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'invoice'
                                    }).length ? row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'invoice'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2">
                                <b-col sm="3" class="text-sm-right"><b>Payoneer:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.tour_rates && row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'payoneer'
                                    }).length ? row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'payoneer'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2">
                                <b-col sm="3" class="text-sm-right"><b>Paypal:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.tour_rates && row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'paypal'
                                    }).length ? row.item.other_info.tour_rates.filter( array => {
                                        return array.type === 'paypal'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2" v-if="row.item.info && row.item.info.type">
                                <b-col sm="3" class="text-sm-right"><b>Adult:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.participant_rates && row.item.other_info.participant_rates.filter( array => {
                                        return array.type === 'adult'
                                    }).length ? row.item.other_info.participant_rates.filter( array => {
                                        return array.type === 'adult'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2" v-if="row.item.info && row.item.info.type">
                                <b-col sm="3" class="text-sm-right"><b>Children:</b></b-col>
                                <b-col>{{ '€ ' + 
                                    (row.item.other_info && row.item.other_info.participant_rates && row.item.other_info.participant_rates.filter( array => {
                                        return array.type === 'child'
                                    }).length ? row.item.other_info.participant_rates.filter( array => {
                                        return array.type === 'child'
                                    })[0].amount : '0.00')  }}
                                </b-col>
                            </b-row>
                            <b-row class="mb-2" v-if="row.item.info && row.item.info.type">
                                <b-col sm="3" class="text-sm-right"><b>Duration:</b></b-col>
                                <b-col>{{(row.item.other_info && row.item.other_info.duration ? row.item.other_info.duration.duration_day + 'd ' + row.item.other_info.duration.duration_time : '0d 00:00')  }}</b-col>
                            </b-row>
                            <b-row class="mb-2">
                                <b-col sm="3" class="text-sm-right"><b>Availabilities:</b></b-col>
                                <b-col>
                                    <ul>
                                        <li v-for="(data, index) in row.item.availabilities" :key="index">
                                            {{data.day}}
                                        </li>
                                    </ul>
                                </b-col>
                                <!-- <b-col>{{ row.item.availabilities }}</b-col> -->
                            </b-row>
                        </b-card>
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
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            is_admin: Boolean,
            types: Array
        },
        data() {
            return {
                tour_type: null,
                isBusy: false,
                totalRows: 1,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 'All'],
                imageProps: { width: 50, height: 50, class: 'm1' },
                fields: ['title', 'code', 'image', 'color', 'type', 'suspended_at', 'actions'],
                items: []
            }
        },
        methods: {
            get() {                
                return axios.get('/tours/show', {
                    params : {
                        type: this.tour_type
                    }
                })
                .then(response => {                
                    this.totalRows = response.data.length

                    this.items = response.data
                    
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
                let message = "Do you really want to suspend it?"

                if($tour.suspended_at) {
                    message = "Do you really want to reactivate it?"
                }

                if(!confirm(message)) return

                axios.post('/tours/' + $tour.id + '/suspend')
                .then(data => {

                })
                .catch(error => {

                })
                .finally(final => {
                    this.get()
                })
            },
            onTourTypeChange() {
                this.get()
            }
        },
        mounted() {
            if(!this.is_admin) this.fields = ['title', 'code', 'image', 'color', 'type', 'actions']

            this.get()
        }
    }
</script>