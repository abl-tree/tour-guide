<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Notifcations</div>

                <div class="card-body">
                    <b-container class="bv-example-row">
                        <b-row>
                            <b-col md="12">
                                <v-md-date-range-picker
                                @change="updateRange">
                                </v-md-date-range-picker>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button variant="primary" @click="modification(false)">Update All the Last 3 Days Modifications Tour Guides</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button variant="primary" @click="modification(true)">Update All the Tour Guides</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success" @click="summary('download', true)">Download</b-button>
                                <b-button variant="primary" @click="summary('send', true)">Autosend the Summary</b-button>
                                </b-button-group>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success" @click="summary('download', false)">Download</b-button>
                                <b-button variant="primary" @click="summary('send', false)">Autosend Tours Without a Guide</b-button>
                                </b-button-group>
                            </b-col>
                        </b-row>
                    </b-container>
                </div>
            </div>
        </div>
    </div>
    
</template>

<script>
import { log } from 'util';
import DateRangePicker from "v-md-date-range-picker";
import moment from 'moment'

function encodeQueryData(data) {
   const ret = [];
   for (let d in data)
     ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
   return ret.join('&');
}

export default {
    components: { DateRangePicker },
    data() {
        return {
            dateRange: [
                moment(), moment()
            ]
        }
    },
    methods: {
        updateRange: function(values) {

            this.dateRange = values
            
        },
        modification: function(period = false) {

            if(period) {
            
                axios.post('notification/modification', {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD')
                })

                return;
            }
            
            axios.post('notification/modification')

        },
        summary: function($option, guide = false) {
            let params = {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD'),
                    guide: guide
                }
            
            if($option === 'download') {
                const querystring = encodeQueryData(params)

                console.log('query', querystring)

                window.open('notification/summary/'+$option+'?'+querystring)
                
                // axios.get('notification/summary/'+$option, {
                //     params: params
                // })
            } else {
                axios.post('notification/summary/'+$option, params)
            }

        }
    },
    created() {
        console.log(this.dateRange);
        
    }
}
</script>
