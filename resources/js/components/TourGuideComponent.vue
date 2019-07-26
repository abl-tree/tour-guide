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

      <b-col md="6" class="my-1">
        <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
          <b-form-select v-model="perPage" :options="pageOptions"></b-form-select>
        </b-form-group>
      </b-col>
    </b-row>

    <!-- Main table element -->
    <b-table
      show-empty
      stacked="md"
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

      <template slot="accepted_at" slot-scope="row">
        {{ row.value ? 'Active' : 'Pending' }}
      </template>

      <template slot="isActive" slot-scope="row">
        {{ row.value ? 'Active' : 'Pending' }}
      </template>

      <template slot="actions" slot-scope="row">
        <b-button size="sm" :variant="row.item.accepted_at ? 'warning' : 'success'" @click="status_update(row.item, row.index, $event.target)" class="mr-1">
          {{ row.item.accepted_at ? 'Cancel' : 'Confirm' }}
        </b-button>
        <b-button size="sm" variant="danger" @click="user_delete(row.item, row.index, $event.target)" class="mr-1">
          Delete
        </b-button>
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

<script>
  export default {
    data() {
      return {
        items: [],
        fields: [
          { key: 'full_name', label: 'Name', sortable: true, sortDirection: 'asc' }, 
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
        filter: null
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
        get(args) {
            axios.get(args.url)
            .then(response => {
                this.items = response.data

                this.totalRows = this.items.length
            })
            .catch(error => {
            })
            .finally(final => {
            });
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
        }

    }
  }
</script>