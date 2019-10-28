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
                                <b-button variant="primary" @click="cancelAvailability">Cancel All Guide Availability</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button variant="primary" @click="modification(false)">Update All the Last 3 Days Modifications Tour Guides <b-spinner v-show="threeDayModificationStatus" type="grow" small label="Sending..."></b-spinner></b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button variant="primary" @click="modification(true)">Update All the Tour Guides <b-spinner v-show="updateAllGuideStatus" type="grow" small label="Sending..."></b-spinner> </b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                    <b-button variant="success" @click="noVoucherCodesTourDownload">Download</b-button>
                                    <b-button variant="primary" @click="noVoucherCodesTour">Autosend Tours Without Voucher Codes <b-spinner v-show="noCodeTourStatus" type="grow" small label="Sending..."></b-spinner> </b-button>
                                </b-button-group>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success" @click="summary('download', true)">Download</b-button>
                                <b-button variant="primary" @click="summary('send', true)">Autosend the Summary <b-spinner v-show="summaryStatus" type="grow" small label="Sending..."></b-spinner> </b-button>
                                </b-button-group>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row class="justify-content-md-center">
                            <b-col md="12 text-center">
                                <b-button-group>
                                <b-button variant="success" @click="summary('download', false)">Download</b-button>
                                <b-button variant="primary" @click="summary('send', false)">Autosend Tours Without a Guide <b-spinner v-show="noGuideTourStatus" type="grow" small label="Sending..."></b-spinner> </b-button>
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
            ],
            threeDayModificationStatus: false,
            updateAllGuideStatus: false,
            noCodeTourStatus: false,
            summaryStatus: false,
            noGuideTourStatus: false
        }
    },
    methods: {
        updateRange: function(values) {

            this.dateRange = values
            
        },
        modification: function(period = false) {

            if(period) {
                
                Swal.fire({
                    title: 'Are you sure to do this operation?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {

                        this.updateAllGuideStatus = true

                        return axios.post('notification/modification', {
                                start: this.dateRange[0].format('YYYY-MM-DD'),
                                end: this.dateRange[1].format('YYYY-MM-DD')
                            })
                            .then(response => {
                                if (!response.statusText == "OK") {
                                    throw new Error(response.statusText)
                                }

                                return response.data
                            }).catch(error => {
                                Swal.showValidationMessage(
                                `Request failed: ${error}`
                                )
                            }).finally(() => {        
                                this.updateAllGuideStatus = false
                            })
                            
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    if (result.value) {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
                })

                return;
            }
                
                Swal.fire({
                    title: 'Are you sure to do this operation?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {

                        this.threeDayModificationStatus = true

                        return axios.post('notification/modification')
                            .then(response => {
                                if (!response.statusText == "OK") {
                                    throw new Error(response.statusText)
                                }

                                return response.data
                            }).catch(error => {
                                Swal.showValidationMessage(
                                `Request failed: ${error}`
                                )
                            }).finally(() => {        
                                this.threeDayModificationStatus = false
                            })
                            
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    if (result.value) {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
                })

        },
        summary: function($option, guide = false) {
            let params = {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD'),
                    guide: guide
                }
            
            if($option === 'download') {
                const querystring = encodeQueryData(params)

                window.open('notification/summary/'+$option+'?'+querystring)
                
                // axios.get('notification/summary/'+$option, {
                //     params: params
                // })
            } else {
                let self = this
                
                Swal.fire({
                    title: 'Are you sure to do this operation?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {

                        if(guide) {
                            self.summaryStatus = true
                        } else {
                            self.noGuideTourStatus = true
                        }

                        return axios.post('notification/summary/'+$option, params)
                            .then(response => {
                                if (!response.statusText == "OK") {
                                    throw new Error(response.statusText)
                                }

                                return response.data
                            }).catch(error => {
                                Swal.showValidationMessage(
                                `Request failed: ${error}`
                                )
                            }).finally(() => {                        
                                if(guide) {                            
                                    self.summaryStatus = false
                                } else {
                                    self.noGuideTourStatus = false
                                }
                            })
                            
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    if (result.value) {
                        Swal.fire({
                        title: 'Request sent!'
                        })
                    }
                })
            }

        },
        noVoucherCodesTour: function() {

            let params = {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD')
                }
            
            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    this.noCodeTourStatus = true

                    return axios.post('notification/no_serial_tours', params)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            return response.data
                        }).catch(error => {
                            Swal.showValidationMessage(
                            `Request failed: ${error}`
                            )
                        }).finally(() => {                        
                            this.noCodeTourStatus = false
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                if (result.value) {
                    Swal.fire({
                    title: 'Request sent!'
                    })
                }
            })

        },
        noVoucherCodesTourDownload: function() {

            let params = {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD')
                }

            const querystring = encodeQueryData(params)

            window.open('notification/no_serial_tours/download?'+querystring)

        },
        cancelAvailability: function() {

            let params = {
                    start: this.dateRange[0].format('YYYY-MM-DD'),
                    end: this.dateRange[1].format('YYYY-MM-DD')
                }
            
            Swal.fire({
                title: 'Are you sure to do this operation?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post('schedule/bulk/cancel', params)
                        .then(response => {
                            if (!response.statusText == "OK") {
                                throw new Error(response.statusText)
                            }

                            return response.data
                        }).catch(error => {
                            Swal.showValidationMessage(
                            `Request failed: ${error}`
                            )
                        }).finally(() => {                        
                            
                        })
                        
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                if (result.value) {
                    Swal.fire({
                    title: 'Unconfirmed availabilities have been cancelled!'
                    })
                }
            })

        }
    },
    created() {
        
    }
}
</script>
