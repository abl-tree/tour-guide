<template>
  <b-container fluid>
    <!-- User Interface controls -->
    <b-row>

      <b-col md="5" class="my-1">
        <b-form-group
          label="Filter"
          label-cols-sm="3"
          label-align-sm="right"
          label-size="sm"
          label-for="filterInput"
          class="mb-0"
        >
          <b-input-group size="sm">
            <b-form-input
              v-model="filter"
              type="search"
              id="filterInput"
              placeholder="Type to Search"
            ></b-form-input>
            <b-input-group-append>
              <b-button :disabled="!filter" @click="filter = ''">Clear</b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
      </b-col>

      <b-col md="5" class="my-1">
        <b-form-group
          label="Per page"
          label-cols-sm="6"
          label-cols-md="4"
          label-cols-lg="3"
          label-align-sm="right"
          label-size="sm"
          label-for="perPageSelect"
          class="mb-0"
        >
          <b-form-select
            v-model="perPage"
            id="perPageSelect"
            size="sm"
            :options="pageOptions"
          ></b-form-select>
        </b-form-group>
      </b-col>

      <b-col md="2" class="my-1" v-if="isAdmin">
          <b-button size="sm" variant="success" href="/articles/create">Add</b-button>
      </b-col>
    </b-row>

    <b-row>
        <b-col md="12">
            <!-- Main table element -->
            <b-table
            id="article-table"
            show-empty
            small
            responsive="sm"
            stacked="md"
            :items="items"
            :fields="fields"
            :current-page="currentPage"
            :per-page="perPage"
            :filter="filter"
            :filterIncludedFields="filterOn"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            :sort-direction="sortDirection"
            @filtered="onFiltered"
            >

                <template slot="name" slot-scope="row">
                    {{ row.value.first }} {{ row.value.last }}
                </template>

                <template slot="actions" slot-scope="row">
                    <b-button-group size="sm">
                        <b-button variant="primary" :href="'articles/' + row.item.id">Show</b-button>
                        <b-button v-if="isAdmin" variant="warning" :href="'articles/' + row.item.id + '/edit'">Edit</b-button>
                        <b-button v-if="isAdmin" variant="danger" @click="deleteArticle(row.item.id)">Delete</b-button>
                    </b-button-group>
                </template>

            </b-table>
        </b-col>
    </b-row>

    <b-row>
      <b-col sm="7" md="6" class="my-1">
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          aria-controls="article-table"
          size="sm"
          class="my-0"
          first-text="First"
          prev-text="Prev"
          next-text="Next"
          last-text="Last"
        ></b-pagination>
      </b-col>
    </b-row>

    <!-- Info modal -->
    <b-modal :id="infoModal.id" :title="infoModal.title" ok-only @hide="resetInfoModal">
      <pre>{{ infoModal.content }}</pre>
    </b-modal>
  </b-container>
</template>

<script>
  export default {
    props: {
      isAdmin: Boolean
    },
    data() {
      return {
        items: [],
        fields: [
          { key: 'title', label: 'Title', sortable: true, class: 'text-center' },
          { key: 'subtitle', label: 'Subtitle', sortable: true, class: 'text-center' },
          {
            key: 'preview',
            label: 'Content',
            formatter: (value, key, item) => {
              return value
            },
            sortable: true,
            sortByFormatted: true,
            filterByFormatted: true
          },
          { key: 'actions', label: 'Actions' }
        ],
        totalRows: 1,
        currentPage: 1,
        perPage: 5,
        pageOptions: [5, 10, 15],
        sortBy: '',
        sortDesc: false,
        sortDirection: 'asc',
        filter: null,
        filterOn: [],
        infoModal: {
          id: 'info-modal',
          title: '',
          content: ''
        }
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
        this.fetch()
    },
    methods: {
      info(item, index, button) {
        this.infoModal.title = `Row index: ${index}`
        this.infoModal.content = JSON.stringify(item, null, 2)
        this.$root.$emit('bv::show::modal', this.infoModal.id, button)
      },
      resetInfoModal() {
        this.infoModal.title = ''
        this.infoModal.content = ''
      },
      onFiltered(filteredItems) {
        // Trigger pagination to update the number of buttons/pages due to filtering
        this.totalRows = filteredItems.length
        this.currentPage = 1
      },
      deleteArticle($id) {
        axios.delete('articles/' + $id)
        .then(data => {
          this.fetch()
        })
      },
      fetch() {
        return axios.get('/articles/all')
        .then(data => {
          this.items = data.data
          
          // Set the initial number of items
          this.totalRows = this.items.length
        })
      }
    }
  }
</script>