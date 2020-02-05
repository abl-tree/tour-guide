<template>
  <b-container fluid>
    <!-- User Interface controls -->
    <b-row>
      <b-col md="6" class="my-1">
        <b-form-group label-cols-sm="3" label="Filter" class="mb-0">
          <b-input-group>
            <b-form-input v-model="filter" placeholder="Type to Search"></b-form-input>
            <b-input-group-append>
              <b-button :disabled="!filter" @click="filter = ''">Clear</b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
      </b-col>

      <b-col md="6" class="my-1">
        <b-form-group label-cols-sm="3" label="Language Filter" class="mb-0">
          <multiselect v-model="languageFilter" :options="languagesOptions" :multiple="true" track-by="id" label="english" placeholder="Search language" @input="languageFilterMethod"></multiselect>
        </b-form-group>
      </b-col>

      <b-col md="4" class="my-1">
        <b-form-group label-cols-sm="3" label="Sort" class="mb-0">
          <b-input-group>
            <b-form-select v-model="sortBy" :options="sortOptions">
              <option slot="first" :value="null">-- none --</option>
            </b-form-select>
            <b-form-select v-model="sortDesc" :disabled="!sortBy" slot="append">
              <option :value="false">Asc</option> <option :value="true">Desc</option>
            </b-form-select>
          </b-input-group>
        </b-form-group>
      </b-col>

      <b-col md="4" class="my-1">
        <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
          <b-form-select v-model="perPage">
            <option v-for="(data, index) in pageOptions" :value="(data === 'All') ? totalRows : data" :key="index">{{data}}</option>
          </b-form-select>
        </b-form-group>
      </b-col>

      <b-col md="4" class="my-1">
        <b-button variant="primary" class="pull-right" href="/tourguide/register">Add Guide</b-button>
      </b-col>
    </b-row>

    <!-- Main table element -->
    <b-table
      show-empty
      responsive
      :items="items"
      :fields="fields"
      :current-page="currentPage"
      :per-page="perPage"
      :filter="filter"
      :sort-by.sync="sortBy"
      :sort-desc.sync="sortDesc"
      :sort-direction="sortDirection"
      @filtered="onFiltered"
    >

      <template slot="full_name" slot-scope="row">
          <b-link v-if="row.item && row.item.full_name" :href="'/tourguide/' + row.item.id +'/profile'" target="_blank">{{row.item && row.item.full_name ? row.item.full_name : ''}}</b-link>
      </template>

      <template slot="info.payment.id" slot-scope="row">
        <b-form-select v-model="row.item.info.payment" class="mb-3" @change="onChangePaymentType(row.item)">
          <option v-for="(payment, index) in payments" :key="index" :value="payment">{{payment.name}}</option>
        </b-form-select>
      </template>

      <template slot="accepted_at" slot-scope="row">
        {{ row.value ? 'Active' : 'Pending' }}
      </template>

      <template slot="isActive" slot-scope="row">
        {{ row.value ? 'Active' : 'Pending' }}
      </template>

      <template slot="actions" slot-scope="row">
        <b-button-group size="sm">
          <b-button variant="primary" :href="'admin/payment/' + row.item.id">
            Anticipi/Incassi
          </b-button>
          <b-button :variant="row.item.accepted_at ? 'warning' : 'success'" @click="status_update(row.item, row.index, $event.target)">
            {{ row.item.accepted_at ? 'Cancel' : 'Confirm' }}
          </b-button>
          <b-button variant="danger" @click="user_delete(row.item, row.index, $event.target)">
            Delete
          </b-button>
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
        ></b-pagination>
      </b-col>
    </b-row>
  </b-container>
</template>

<!-- Add Multiselect CSS. Can be added as a static asset or inside a component. -->
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<script>

  function array_column(array, columnName) {
      return array.map(function(value,index) {
          return value[columnName];
      })
  }

  export default {
    props : {
      payments: Array
    },
    data() {
      return {
        items: [],
        fields: [
          { key: 'full_name', label: 'Name', sortable: true, sortDirection: 'asc' }, 
          { key: 'username', label: 'Username', sortable: true, sortDirection: 'asc' }, 
          { key: 'email', label: 'Email', sortable: true, sortDirection: 'asc' }, 
          { key: 'info.payment.id', label: 'Payment Type' }, 
          { key: 'accepted_at', label: 'Date Accepted', sortable: true, sortDirection: 'asc' },
          { key: 'actions', label: 'Actions' }
        ],
        totalRows: 1,
        currentPage: 1,
        perPage: 10,
        pageOptions: [5, 10, 15, 'All'],
        sortBy: 'accepted_at',
        sortDesc: true,
        sortDirection: 'desc',
        filter: null,
        selectedPayment: [],
        languageFilter: [],
        languagesOptions: []
      }
    },
    computed: {
      sortOptions() {
        // Create an options list from our fields
        return this.fields
          .filter(f => f.sortable)
          .map(f => {
            return { text: f.label, value: f.key }
          })
      }
    },
    mounted() {
      // Set the initial number of items
      this.totalRows = this.items.length

      this.load()
    },
    methods: {
        onChangePaymentType(user) {
          console.log('change payment', user.info);

          axios.put('/tourguide/'+user.id+'/payment', user.info);
        },
        status_update(item, index, button) {
            let params = {data: item, url:"/tourguide/" + item.id};
            
            this.update(params, index);
        },
        user_delete(item, index, button) {
            let params = {data: item, url:"/tourguide/" + item.id}
            
            if (confirm("Are you sure to delete the user permanently?")) {
              this.delete(params, index);
            }
        },
        onFiltered(filteredItems) {
             // Trigger pagination to update the number of buttons/pages due to filtering
            this.totalRows = filteredItems.length
            this.currentPage = 1
        },
        async getLanguages() {
            return await axios.get('/languages')
                .then(response => {
                    return response.data
                })
        },
        languagesOptionsMethod() {

            let options = [{value: null, text: 'Please select at least 5 languages'}]

            this.getLanguages().then(data => {
              
              this.languagesOptions = data
                
            })

        },
        languageFilterMethod() {

            var params = {
              url:"/tourguide/show",
              data: array_column(this.languageFilter, 'id')
            }
            
            this.get(params)
          
        },
        get(args) {
            if(args.data) {
              axios.get(args.url, {
                params: {
                  languages: args.data
                }
              })
              .then(response => {
                  this.items = response.data

                  this.totalRows = this.items.length
              })
              .catch(error => {
              })
              .finally(final => {
              });
            } else {
              axios.get(args.url)
              .then(response => {
                  this.items = response.data

                  this.totalRows = this.items.length
              })
              .catch(error => {
              })
              .finally(final => {
              });
            }
        },
        update(args, index) {
            axios.put(args.url, args.data)
            .then(response => {
              index = this.items.indexOf(args.data) // find the post index

              this.items[index].accepted_at = response.data.accepted_at
            })
            .catch(function (error) {
                //error
            })
            .finally(final => {
                // this.load(false)
                this.loading = false
            });
        },
        delete(args, index) {
            axios.delete(args.url)
            .then(response => {
              index = this.items.indexOf(args.data) // find the post index

              this.items.splice(index, 1)

              this.totalRows = this.items.length
            })
            .catch(function (error) {
                //error
            })
            .finally(final => {
                // this.load(false)
                this.loading = false
            });
        },
        load() {
            var params = {url:"/tourguide/show"}      
            
            this.get(params)

            this.languagesOptionsMethod()
        }
    }

  }
</script>