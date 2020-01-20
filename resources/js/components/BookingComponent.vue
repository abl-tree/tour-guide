<template>
  <div>
      <div class="card">
          <div class="card-header">
            Booking
          </div>
          <div class="card-body">
            <b-row>
              <b-col>
                <b-form-group
                :state="!Boolean(date_error)"
                label-for="date-input"
                :invalid-feedback="date_error"
                >
                  <b-form-input id="date-input" :state="!Boolean(date_error)" v-model="date" type="date" :min="minDate" :no-wheel="true" @change="dateChange"></b-form-input>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <b-form-group
                :state="Boolean(tour_title_selected)"
                label-for="title-option"
                invalid-feedback="Please select a tour title"
                >
                    <b-form-select
                    id="title-option"
                    :state="Boolean(tour_title_selected)"
                    v-model="tour_title_selected"
                    :options="renderOptions()"
                    required
                    ></b-form-select>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <b-form-select v-model="csvType">
                  <option value="airbnb">Airbnb</option>
                  <option value="fareharbor">Fareharbor</option>
                </b-form-select>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <!-- Plain mode -->
                <b-form-file v-model="file" accept=".csv" class="mt-3" plain ref="file" @change="handleFileUpload()"></b-form-file>
              </b-col>
            </b-row>
            <b-row>
              <b-col>
                <b-button size="sm" @click="importButton">
                  Import
                </b-button>
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

        this.tour_title_selected_state = true

        this.progress = 0

        if(!this.tour_title_selected) {
          this.tour_title_selected_state = false

          Swal.fire(
            'Something went wrong!',
            'Please check the form',
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

              formData.append('date', this.date)

              if(this.file) formData.append('file', this.file)

              formData.append('tour', this.tour_title_selected)
              
              return axios.post('booking/import/' + this.csvType,
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
                let errorMsg = error.response.data.errors.file.join(', ')
                
                Swal.showValidationMessage(
                `Request failed: ${errorMsg}`
                )
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