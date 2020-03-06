<template>
    <div>
        <div class="card">
            <div class="card-header">
                Tour Coordinator Lists
            </div>
            <div class="card-body">
                <b-row>
                    <b-col md="4">
                        <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
                            <b-form-select v-model="perPage" :options="pageOptions" @change="paginationChange"></b-form-select>
                        </b-form-group>
                    </b-col>

                    <b-col md="8">
                        <b-button size="sm" class="pull-right" variant="primary" href="/coordinator/create">Add Coordinator</b-button>
                    </b-col>
                </b-row>

                <b-table 
                :items="items" 
                :fields="fields"
                :busy="isBusy"
                striped 
                responsive="sm" >

                    <div slot="table-busy" class="text-center text-danger my-2">
                        <b-spinner class="align-middle"></b-spinner>
                        <strong>Loading...</strong>
                    </div>

                    <template slot="actions" slot-scope="row">
                        <b-button-group size="sm">
                            <b-button variant="dark" :href="'/coordinator/'+row.item.id+'/edit'">Edit</b-button>
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
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                isBusy: true,
                totalRows: 0,
                currentPage: 1,
                perPage: 5,
                pageOptions: [5, 10, 15, 100, 500],
                fields: ['first_name', 'last_name', 'email', 'contact', 'actions'],
                items: []
            }
        },
        methods: {
            deleteCoordinator(coordinator) {
                console.log(coordinator)
            },
            paginationChange() {
                this.get()
            },
            get() {
                this.isBusy = true

                axios.get('/get/coordinator/list', {
                    params: {
                        per_page: this.perPage,
                        page: this.currentPage
                    }
                })
                .then(response => {
                    this.items = response.data.data
                    this.totalRows = response.data.total
                    this.currentPage = response.data.current_page                    
                })
                .finally(final => {
                    this.isBusy = false
                })
            }
        },
        mounted() {
            this.get()
        }
    }
</script>