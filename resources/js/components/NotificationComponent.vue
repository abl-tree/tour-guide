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
                                <b-button variant="primary" @click="modification">Update All the Last 3 Days Modifications Tour Guides</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button variant="primary">Update All the Tour Guides</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success">Download</b-button>
                                <b-button variant="primary">Autosend the Summary</b-button>
                                </b-button-group>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success">Download</b-button>
                                <b-button variant="primary">Autosend Tours Without a Guide</b-button>
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

export default {
    components: { DateRangePicker },
    data() {
        return {
            dateRange: null
        }
    },
    methods: {
        updateRange: function(values) {

            console.log(values)
            
        },
        modification: function() {
            
            axios.post('notification/modification')

        }
    }
}
</script>
