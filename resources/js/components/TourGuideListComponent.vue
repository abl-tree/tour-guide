<template>
    <div>
        <div id="accordion">
            <div class="card">
                <div class="card-header" id="headingOne" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" @click="onToggleCollapse(0)">
                    <h5 class="mb-0">
                        <span class="badge badge-pill badge-info" style="background-color: #6cb2eb; color: white;">Morning <small>{{date}}</small></span>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <div v-if="isAdmin" class="row">
                            <div class="col-md-12">
                                <v-select v-model="add_guide_morning" label="text" :reduce="text => text.value" :options="renderOptions('morning')">
                                </v-select>
                                <b-button class="pull-right" size="sm" variant="info" @click="addGuide('Morning')">Add</b-button>
                            </div>
                        </div>
                        <div v-if="isAdmin" class="dropdown-divider"></div>
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.morning && data.morning.available.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.morning && data.morning.available[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.morning.available" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <!-- <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)"> -->
                                <label>
                                    <font-awesome-icon v-if="schedule.departure" icon="user-lock" style="color: rgb(44, 148, 233);" /> 
                                    <font-awesome-icon v-else icon="user-lock"  style="opacity: 0.20" /> 
                                    {{ schedule.full_name }} 
                                </label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.morning.available" :key="index">
                            <div v-if="user && user.schedules && user.schedules.length > 0">

                                <div v-show="user.schedules[0].flag === 0" class="alert alert-danger" role="alert">
                                    Schedule has been submitted. 
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="alert alert-success" role="alert">
                                    Schedule has been confirmed. You can't change it, anymore.
                                </div>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="morning-available" v-model="user.schedules_count" :value="1" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Morning')">
                                    <label class="custom-control-label" for="morning-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="morning-unavailable" v-model="user.schedules_count" :value="0" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Morning')">
                                    <label class="custom-control-label" for="morning-unavailable">Unavailable</label>
                                </div>
                            </div>
                            <div v-else>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="morning-available" v-model="user.schedules_count" :value="1" @change="available(user, 'new', 'Morning')">
                                    <label class="custom-control-label" for="morning-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="morning-unavailable" v-model="user.schedules_count" :value="0" @change="available(user, 'new', 'Morning')">
                                    <label class="custom-control-label" for="morning-unavailable">Unavailable</label>
                                </div>
                            </div>

                            <span v-show="errors.morning && errors.morning.length > 0" class="invalid-feedback" style="display: unset" role="alert">
                                <strong>{{ errors.morning }}</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" style="cursor: pointer;" id="headingTwo">
                    <h5 class="mb-0 collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" @click="onToggleCollapse(1)">
                        <span class="badge badge-pill badge-danger">Afternoon <small>{{date}}</small></span>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body"> 
                        <div class="row" v-if="isAdmin">
                            <div class="col-md-12">
                                <v-select v-model="add_guide_afternoon" label="text" :reduce="text => text.value"  :options="renderOptions('afternoon')">
                                </v-select>
                                <b-button class="pull-right" size="sm" variant="info" @click="addGuide('Afternoon')">Add</b-button>
                            </div>
                        </div>
                        <div class="dropdown-divider" v-if="isAdmin"></div>
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.afternoon.available && data.afternoon.available.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.afternoon.available && data.afternoon.available[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.afternoon.available" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <!-- <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)"> -->
                                <label>
                                    <font-awesome-icon v-if="schedule.departure" icon="user-lock" style="color: rgb(44, 148, 233);" /> 
                                    <font-awesome-icon v-else icon="user-lock"  style="opacity: 0.20" /> 
                                    {{ schedule.full_name }} 
                                </label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.afternoon.available" :key="index">
                            <div v-if="user && user.schedules && user.schedules.length > 0">
                                <div v-show="user.schedules[0].flag === 0" class="alert alert-danger" role="alert">
                                    Schedule has been submitted. 
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="alert alert-success" role="alert">
                                    Schedule has been confirmed. You can't change it, anymore.
                                </div>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="afternoon-available" v-model="user.schedules_count" :value="1" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Afternoon')">
                                    <label class="custom-control-label" for="afternoon-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="afternoon-unavailable" v-model="user.schedules_count" :value="0" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Afternoon')">
                                    <label class="custom-control-label" for="afternoon-unavailable">Unavailable</label>
                                </div>
                            </div>
                            <div v-else>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="afternoon-available" v-model="user.schedules_count" :value="1" @change="available(user, 'new', 'Afternoon')">
                                    <label class="custom-control-label" for="afternoon-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="afternoon-unavailable" v-model="user.schedules_count" :value="0" @change="available(user, 'new', 'Afternoon')" checked>
                                    <label class="custom-control-label" for="afternoon-unavailable">Unavailable</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" style="cursor: pointer;" id="headingThree">
                <h5 class="mb-0 collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree" @click="onToggleCollapse(2)">
                    <span class="badge badge-pill badge-success">Evening <small>{{date}}</small></span>
                </h5>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="card-body"> 
                        <div v-if="isAdmin" class="row">
                            <div class="col-md-12">
                                <v-select v-model="add_guide_evening" label="text" :reduce="text => text.value"  :options="renderOptions('evening')">
                                </v-select>
                                <b-button class="pull-right" size="sm" variant="info" @click="addGuide('Evening')">Add</b-button>
                            </div>
                        </div>
                        <div v-if="isAdmin" class="dropdown-divider"></div>
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.evening.available && data.evening.available.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.evening.available && data.evening.available[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.evening.available" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <!-- <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)"> -->
                                <label>
                                    <font-awesome-icon v-if="schedule.departure" icon="user-lock" style="color: rgb(44, 148, 233);" /> 
                                    <font-awesome-icon v-else icon="user-lock"  style="opacity: 0.20" /> 
                                    {{ schedule.full_name }} 
                                </label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.evening.available" :key="index">
                            <div v-if="user && user.schedules && user.schedules.length > 0">

                                <div v-show="user.schedules[0].flag === 0" class="alert alert-danger" role="alert">
                                    Schedule has been submitted. 
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="alert alert-success" role="alert">
                                    Schedule has been confirmed. You can't change it, anymore.
                                </div>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="evening-available" v-model="user.schedules_count" :value="1" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Evening')">
                                    <label class="custom-control-label" for="evening-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="evening-unavailable" v-model="user.schedules_count" :value="0" :disabled="user.schedules[0].is_locked" @change="available(user, 'existing', 'Evening')">
                                    <label class="custom-control-label" for="evening-unavailable">Unavailable</label>
                                </div>
                            </div>
                            <div v-else>
                                <!-- Default unchecked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="evening-available" v-model="user.schedules_count" :value="1" @change="available(user, 'new', 'Evening')">
                                    <label class="custom-control-label" for="evening-available">Available</label>
                                </div>

                                <!-- Default checked -->
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="evening-unavailable" v-model="user.schedules_count" :value="0" @change="available(user, 'new', 'Evening')">
                                    <label class="custom-control-label" for="evening-unavailable">Unavailable</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// import { log } from 'util';
export default {
    name: 'TourGuideList',
    props: {
        date: String,
        data: Object,
        loading: Boolean,
        isAdmin: Boolean,
        errors: Object,
        toggleCollapse: Number
    },
    data() {
        return {
            add_guide_morning: null,
            add_guide_afternoon: null,
            add_guide_evening: null
        }
    },
    methods: {
        check(args) {
            this.$emit('tourGuideClicked', {'data' : args});
        }, 
        available(args, flag, shift) {
            args.shift = shift;

            this.$emit('availabilityClicked', {'data' : args, 'flag' : flag});
        },
        onToggleCollapse($toggle) {
            this.$emit('onToggleCollapse', $toggle);
        },
        renderOptions(option) {
            let options = []
            let data = []

            if(option === 'morning' && this.$props.data && this.$props.data.morning && this.$props.data.morning.unavailable) {
                data = this.data.morning.unavailable

                console.log('morning unavailable', data)
                
            } else if(option === 'afternoon' && this.$props.data && this.$props.data.afternoon && this.$props.data.afternoon.unavailable) {
                data = this.data.afternoon.unavailable
            } else if(option === 'evening' && this.$props.data && this.$props.data.evening && this.$props.data.evening.unavailable) {
                data = this.data.evening.unavailable
            }

            for(let a = 0; a < data.length; a++) {
                let obj = {text: data[a].full_name, value: data[a]}

                options.push(obj)
            }

            return options
        },
        addGuide(option) {
            if(this.add_guide_morning && option === 'Morning') {
                this.add_guide_morning.schedule_at = this.date
                this.add_guide_morning.shift = option

                this.$emit('addGuide', {'data' : this.add_guide_morning})
            } else if(this.add_guide_afternoon && option === 'Afternoon') {
                this.add_guide_afternoon.schedule_at = this.date
                this.add_guide_afternoon.shift = option

                this.$emit('addGuide', {'data' : this.add_guide_afternoon, 'flag' : option})
            } else if(this.add_guide_evening && option === 'Evening') {
                this.add_guide_evening.schedule_at = this.date
                this.add_guide_evening.shift = option

                this.$emit('addGuide', {'data' : this.add_guide_evening, 'flag' : option})
            }
        }
    },
    updated() {
        if(this.toggleCollapse === 0) {
            $('#collapseOne').collapse('show');
        } else if(this.toggleCollapse === 1) {
            $('#collapseTwo').collapse('show');
        } else if(this.toggleCollapse === 2) {
            $('#collapseThree').collapse('show');
        }
    }
}
</script>
