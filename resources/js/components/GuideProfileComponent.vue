<template>
    <div>
        <div class="card">
            <div class="card-body">
                <b-row>
                    <b-col md="5">
                        <b-img-lazy v-if="guide && guide.info && guide.info.picture" center v-bind="mainProps" :src="guide.info.picture" style="max-width: 100%; height: auto;" rounded alt="Tour Image"></b-img-lazy>
                        <b-img v-else center v-bind="mainProps" style="max-width: 100%; height: auto;" rounded alt="Blank Tour Image"></b-img>
                    </b-col>
                    <b-col md="7">
                        <h2>{{guide && guide.full_name ? guide.full_name : ''}}</h2>
                        <b-row class="my-1">
                            <b-col sm="4">
                                <strong>Email: </strong>
                            </b-col>
                            <b-col sm="8">
                                <b-form-input v-if="modifyEmail && isAdmin" type="text" size="sm" v-model="guide.email"></b-form-input>
                                <span v-else>{{guide && guide.email ? guide.email : ''}}</span>
                                
                                <b-badge v-if="!modifyEmail && isAdmin" variant="warning" style="cursor: pointer;" @click="modifyEmail = true">Edit Email</b-badge>
                                <b-badge v-else-if="modifyEmail && isAdmin" variant="success" style="cursor: pointer;" @click="submitEmail">Done</b-badge>
                            </b-col>
                        </b-row>
                        <b-row class="my-1">
                            <b-col sm="4">
                                <strong>Tel. No.: </strong>
                            </b-col>
                            <b-col sm="8">
                                <b-form-input v-if="modifyContact" type="text" size="sm" v-model="contact"></b-form-input>
                                <span v-else>{{contact}}</span>
                                
                                <b-badge v-if="!modifyContact" variant="warning" style="cursor: pointer;" @click="modifyContact = true">Edit</b-badge>
                                <b-badge v-else-if="modifyContact" variant="success" style="cursor: pointer;" @click="submitContact">Done</b-badge>
                            </b-col>
                        </b-row>
                        <b-row class="my-1" v-if="isAdmin">
                            <b-col sm="4">
                                <strong>Rating: </strong>
                            </b-col>
                            <b-col sm="8">
                                <star-rating :inline="true" :star-size="25" v-model="rating" @rating-selected ="setRating"></star-rating>
                                <!-- <star-rating v-else :inline="true" :star-size="25" v-model="rating" :read-only="true"></star-rating> -->
                            </b-col>
                        </b-row>
                        <b-row class="my-1">
                            <b-col sm="4">
                                <strong>Languages: </strong>
                            </b-col>
                            <b-col sm="8">
                                <div v-if="modifyLanguage">
                                    <b-form-input type="text" size="sm" v-model="languages[0]"></b-form-input>
                                    <b-form-input type="text" size="sm" v-model="languages[1]"></b-form-input>
                                    <b-form-input type="text" size="sm" v-model="languages[2]"></b-form-input>
                                    <b-form-input type="text" size="sm" v-model="languages[3]"></b-form-input>
                                    <b-form-input type="text" size="sm" v-model="languages[4]"></b-form-input>
                                </div>
                                <span v-else>
                                    <small v-for="(language, index) in languages" :key="index">
                                        {{language}}
                                        <span v-if="index < languages.length - 1">,</span>
                                    </small>
                                </span>
                                <b-badge v-if="!modifyLanguage" variant="warning" style="cursor: pointer;" @click="modifyLanguage = true">Edit</b-badge>
                                <b-badge v-else-if="modifyLanguage" variant="success" style="cursor: pointer;" @click="submitLanguage">Done</b-badge>
                            </b-col>
                        </b-row>
                        <b-row class="my-1">
                            <b-col sm='10'>
                                <b-form-file
                                size="sm"
                                v-model="profilePicture"
                                placeholder="Choose a file or drop it here..."
                                drop-placeholder="Drop file here..."
                                ></b-form-file>
                            </b-col>
                            <b-badge variant="success" style="cursor: pointer;" @click="submitProfilePicture">Upload</b-badge>
                        </b-row>
                    </b-col>
                </b-row>
                <b-row v-if="isAdmin">
                    <b-col md="12">
                        <h4>Note</h4>
                    </b-col>
                </b-row>
                <b-row v-if="isAdmin">
                    <b-col md="12">
                        <b-form @submit="descriptionSubmit($event, guide)" v-if="isAdmin">
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
        <guide-statistics-component :guide="guide"></guide-statistics-component>
        <b-row>
            <b-col>
                <b-button class="pull-right" size="sm" variant="success" href="/tourguide">Back</b-button>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    export default {
        props: {
            guide: Object,
            isAdmin: Boolean,
            myProfile: Boolean
        },
        data() {
            return {
                rating: 0,
                saving: false,
                modify: false,
                modifyContact: false,
                modifyLanguage: false,
                modifyEmail: false,
                mainProps: { blank: true, blankColor: '#777', width: 250, height: 250, class: 'm1' },
                description: '',
                contact: '',
                email: '',
                languages: [],
                profilePicture: null
            }
        },
        methods: {
            modifyBtn() {
                this.modify = true
            },
            submitContact() {

                this.saving = true

                let url = '/myprofile/contact'

                if(this.isAdmin) {
                    url = 'contact'
                }

                axios.put(url,
                {
                    contact: this.contact
                })
                .then(data => {
                    this.modifyContact = false

                    let contact = data.data.contact_number

                    this.contact = contact
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            },
            submitEmail() {

                this.saving = true

                let url = '/myprofile/email'

                if(this.isAdmin) {
                    url = 'email'
                }

                axios.put(url,
                {
                    email: this.guide.email
                })
                .then(data => {
                    this.modifyEmail = false

                    let email = data.data.email

                    this.guide.email = email
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            },
            submitLanguage() {

                this.saving = true

                let url = '/myprofile/language'

                if(this.isAdmin) {
                    url = 'language'
                }

                axios.put(url,
                {
                    languages: this.languages
                })
                .then(data => {
                    this.modifyLanguage = false

                    let languages = data.data

                    this.languages = []
                    
                    for (let a = 0; a < languages.length; a++) {
                        const language = languages[a].language;
                        
                        this.languages[a] = language
                    }
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            },
            submitProfilePicture() {

                this.saving = true

                let url = '/myprofile/picture'

                if(this.isAdmin) {
                    url = 'picture'
                }

                let formData = new FormData()          

                if(this.profilePicture) formData.append('image', this.profilePicture)

                axios.post(url,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(data => {
                    window.location.reload()
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            },
            setRating(rates) {

                this.saving = true

                axios.put('rating',
                {
                    rating: this.rating
                })
                .then(data => {
                    this.modify = false
                })
                .catch(err => {

                })
                .finally(final => {
                    this.saving = false
                })
                
            },
            descriptionSubmit(evt, guide) {
                evt.preventDefault()

                if(!this.modify || !this.description) return

                this.saving = true

                axios.put('',
                {
                    note: this.description
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
            this.rating = this.guide && this.guide.info && this.guide.info.rating ? this.guide.info.rating : 0

            if(this.guide) {
                this.description = this.guide.info && this.guide.info.note ? this.guide.info.note : ''
                this.contact = this.guide.info && this.guide.info.contact_number ? this.guide.info.contact_number : ''

                for (let a = 0; a < this.guide.languages.length; a++) {
                    const language = this.guide.languages[a].language;
                    
                    this.languages[a] = language
                }
            }
        }
    }
</script>