<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-row>
                    <b-col md="5">
                        <b-img-lazy v-if="tour && tour.info && tour.info.image_link" center v-bind="mainProps" :src="tour.info.image_link" style="max-width: 100%; height: auto;" rounded alt="Tour Image"></b-img-lazy>
                        <b-img v-else center v-bind="mainProps" style="max-width: 100%; height: auto;" rounded alt="Blank Tour Image"></b-img>
                    </b-col>
                    <b-col md="7">
                        <h2>{{tour && tour.title ? tour.title : ''}}</h2>
                        <p><strong>Code: </strong>{{tour && tour.info && tour.info.tour_code ? tour.info.tour_code : ''}}</p>
                        <p><strong>Type: </strong>{{tour && tour.info && tour.info.type ? tour.info.type.name : ''}}</p>
                        <p><strong>Duration: </strong>{{tour && tour.info && tour.info.duration ? tour.info.duration : '00:00'}}</p>
                        <p><strong>Price per adult: </strong>€{{tour && tour.info && tour.info.adult_price ? tour.info.adult_price : '0.00'}}</p>
                        <p><strong>Price per child: </strong>€{{tour && tour.info && tour.info.children_price ? tour.info.children_price : '0.00'}}</p>
                        <p><strong>Cash Rate: </strong>€{{tour && tour.info && tour.info.cash ? tour.info.cash : '0.00'}}</p>
                        <p><strong>Invoice Rate: </strong>€{{tour && tour.info && tour.info.invoice ? tour.info.invoice : '0.00'}}</p>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="12">
                        <h4>Description</h4>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="12">
                        <b-form @submit="descriptionSubmit($event, tour)" v-if="isAdmin">
                            <b-form-group v-if="modify">
                                <b-form-textarea
                                v-if="isAdmin"
                                id="description"
                                v-model="description"
                                rows="3"
                                max-rows="6"
                                ></b-form-textarea>
                            </b-form-group>
                            <p v-else style="text-indent: 2.0em;">
                                {{description}}
                            </p>

                            <b-button v-show="!modify" variant="warning" @click="modifyBtn">Modify</b-button>
                            <b-button v-show="modify" type="submit" variant="success">
                                <b-spinner
                                small
                                v-show="saving"
                                variant="light"
                                ></b-spinner>Save
                            </b-button>
                        </b-form>
                        <p v-else style="text-indent: 2.0em;">
                            {{description}}
                        </p>
                    </b-col>
                </b-row>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            tour: Object,
            isAdmin: Boolean
        },
        data() {
            return {
                saving: false,
                modify: false,
                mainProps: { blank: true, blankColor: '#777', width: 250, height: 250, class: 'm1' },
                description: ''
            }
        },
        methods: {
            modifyBtn() {
                this.modify = true
            },
            descriptionSubmit(evt, tour) {
                evt.preventDefault()

                if(!this.modify || !this.description) return

                this.saving = true

                axios.put('',
                {
                    description: this.description
                })
                .then(data => {
                    this.modify = false
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            }
        },
        created() {            
            if(this.tour) {
                this.description = this.tour.info && this.tour.info.description ? this.tour.info.description : ''
            }
        }
    }
</script>