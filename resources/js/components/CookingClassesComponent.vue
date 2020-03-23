<template>
  <div>
      <div class="card">
          <div class="card-header">
            Import
          </div>
          <div class="card-body">
            <b-row>
              <b-col>
                <!-- Plain mode -->
                <b-form-file v-model="file" accept=".csv" class="mt-3" plain ref="file" @change="handleFileUpload()"></b-form-file>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <b-button-group size="sm">
                  <b-button variant="success" @click="importButton">
                    Import
                  </b-button>
                  <b-button variant="info" href="/templates/Cooking Classes Template.csv">
                    Download Template
                  </b-button>
                </b-button-group>
              </b-col>
            </b-row>
          </div>
      </div>
      <b-row>
        <b-col md="12">
          <CookingClassesList ref="cooking"></CookingClassesList>
        </b-col>
      </b-row>
  </div>
</template>

<script>
  import CookingClassesList from "./CookingClassesListsComponent";

  export default {
    components: {
      CookingClassesList
    },
    data() {
      return {
        csvType: 'airbnb',
        date: null,
        date_error: null,
        file: null,
        minDate: '',
        progress: 0,
        tour_titles: '',
        tour_title_selected: null,
        tour_title_selected_state: false,
      }
    },
    methods: {
      handleFileUpload() {
        // this.file = this.$refs.file.files[0]
      },
      importButton() {

        this.progress = 0

        if(!this.file) {

          Swal.fire(
            'Something went wrong!',
            'Please select a file',
            'error'
          )
          return
        }

        Swal.fire({
            title: 'Are you sure to do this operation?',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            showLoaderOnConfirm: true,
            preConfirm: () => {

              let formData = new FormData()

              if(this.file) formData.append('file', this.file)
              
              return axios.post('/cooking_classes/import',
                formData,
                {
                  headers: {
                    'Content-Type' : 'multipart/form-data'
                  },
                  progress: function (progressEvent) {
                    // Do whatever you want with the native progress event
                    this.progress = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ) )

                    console.log('progress', this.progress)
                    
                  }
                }
              ).then(function(response) {
                if (!response.statusText == "OK") {
                    throw new Error(response.statusText)
                }

                return response.data
              }).catch(error => {
                let errors = error.response.data
                
                let errorMsg = ''

                for (let a = 0; a < errors.length; a++) {
                  const error = errors[a];
                  
                  errorMsg += 'Row #' + error.row + ': ' + (error.error ? error.error.join(' ') : '') + '<br> '
                }
                
                Swal.showValidationMessage(
                `${errorMsg ? errorMsg : error}`
                )
              })
                    
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if(result.value) {

              this.$refs.cooking.get()

              Swal.fire({
              title: 'Request sent!'
              })
            }
        })
      },
      renderOptions() {
        let options = [{ text: 'Select the Tour', value: null }];

        for(let a = 0; a < this.tour_titles.length; a++) {
            let obj = {text: this.tour_titles[a].title, value: this.tour_titles[a].id}

            options.push(obj)
        }

        return options
      },
      dateChange() {
        this.tour_title_selected = null
        this.tour_title_selected_state = false

        axios.get('tours/available/'+this.date, {
          params: {
            date: this.date
          }
        }).then(response => {

          this.date_error = null

          this.tour_titles = response.data;
          
        }).catch(error => {

          this.date_error = error.response.data.errors && error.response.data.errors.date ? error.response.data.errors.date.join(', ') : null;
          
        }).finally(final => {

        });
      }
    },
    created() {
      this.minDate, this.date = moment().add(1, 'day').format('YYYY-MM-DD')

      this.dateChange()
    }
  }
</script>