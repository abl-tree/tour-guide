<template>
    <div>
        <div id="accordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Morning <small>{{date}}</small>
                    </button>
                </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.morning && data.morning.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.morning && data.morning[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.morning" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)">
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }}</label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.morning" :key="index">
                            <div v-if="user && user.schedules.length > 0">
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
                <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Afternoon <small>{{date}}</small>
                    </button>
                </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                    <div class="card-body"> 
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.afternoon && data.afternoon.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.afternoon && data.afternoon[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.afternoon" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)">
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }}</label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.afternoon" :key="index">
                            <div v-if="user && user.schedules.length > 0">
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
                <div class="card-header" id="headingThree">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        Evening <small>{{date}}</small>
                    </button>
                </h5>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                    <div class="card-body"> 
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.evening && data.evening.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.evening && data.evening[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.evening" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)">
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }}</label>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.evening" :key="index">
                            <div v-if="user && user.schedules.length > 0">
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
export default {
    name: 'TourGuideList',
    props: {
        date: String,
        data: Object,
        loading: Boolean,
        isAdmin: Boolean,
        errors: Object
    },
    methods: {
        check(args) {
            this.$emit('tourGuideClicked', {'data' : args});
        }, 
        available(args, flag, shift) {
            args.shift = shift;

            this.$emit('availabilityClicked', {'data' : args, 'flag' : flag});
        }
    }
}
</script>
