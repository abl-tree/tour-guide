<template>
    <div>
        <div id="accordion">
            <div class="card">
                <div class="card-header" id="headingOne" style="cursor: pointer;" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" @click="onToggleCollapse(0)">
                    <h5 class="mb-0">
                        <span class="badge badge-pill badge-warning" style="background-color: orange; color: white;">Morning <small>{{date}}</small></span>
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
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }} </label>
                                <b-badge v-if="schedule.remarks === 'Paid'" variant="success" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                                <b-badge v-else variant="danger" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.morning" :key="index">
                            <div v-if="user && user.schedules.length > 0">
                                
                                <b-form-group id="input-group-3">
                                    <b-form-select
                                    id="input-3"
                                    v-model="tour_title_selected_morning"
                                    :options="renderOptions()"
                                    @change="tourTitleChange(user.schedules[0].id, 'morning')"
                                    :disabled="user.schedules[0].is_locked"
                                    required
                                    ></b-form-select>
                                </b-form-group>

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

                                <!-- Add Anticipi and Incossi Amounts -->
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.anticipi_morning>Add Anticipi</b-button>

                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.anticipi" :key="index">
                                                € {{receipt.amount}}
                                                <b-button variant="link" v-b-tooltip.hover title="Receipt Image" size="sm" @click="openReceipt(receipt.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="anticipi_morning" title="Anticipi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'anticipi')" @ok="handleOk">

                                            <b-form-group
                                            :state="antAmountState"
                                            label="Amount"
                                            label-for="anticipi-amount-input"
                                            :invalid-feedback="antAmountError"
                                            >
                                                <b-input-group prepend="€">
                                                    <b-form-input id="anticipi-amount-input" :state="antAmountState" v-model="antAmount" type="number" min="0" required></b-form-input>
                                                </b-input-group>                                               
                                            </b-form-group>

                                            <!-- Plain mode -->
                                            <b-form-group
                                            :state="receiptState"
                                            label="Receipt"
                                            label-for="receipt-input"
                                            :invalid-feedback="receiptError"
                                            >
                                                <b-form-file id="receipt-input" ref="file" v-model="receipt_img" @change="fileChange" accept="image/*" class="mt-3" plain></b-form-file>
                                            </b-form-group>

                                            <b-img v-show="receipt_img" :src="preview" fluid alt="Receipt Image"></b-img>

                                        </b-modal>
                                    </div>
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.incossi_morning>Add Incossi</b-button>

                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.incossi" :key="index">
                                                € {{receipt.amount}}
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="incossi_morning" title="Incossi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'incossi')" @ok="handleOk">

                                            <form>
                                                <b-form-group
                                                :state="incAmountState"
                                                label="Amount"
                                                label-for="incossi-amount-input"
                                                :invalid-feedback="incAmountError"
                                                >
                                                    <b-input-group prepend="€">
                                                        <b-form-input id="incossi-amount-input" v-model="incAmount" type-number min="0"></b-form-input>
                                                    </b-input-group>
                                                </b-form-group>
                                            </form>

                                        </b-modal>
                                    </div>
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
                        <p v-if="loading">Loading...</p>
                        <p v-else-if="data.afternoon && data.afternoon.length === 0">No available tour guide.</p>
                        <!-- Default unchecked -->
                        <div v-else-if="data.afternoon && data.afternoon[0].schedules.length > 0 && isAdmin === true" class="custom-control custom-checkbox" v-for="(data, index) in data.afternoon" :key="index">
                            <div v-for="(schedule, key) in data.schedules" :key="key">
                                <input type="checkbox" class="custom-control-input" :id="'schedule-' + schedule.id" v-model="schedule.flag" true-value="1" false-value="0" @change="check(schedule)">
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }} </label>
                                <b-badge v-if="schedule.remarks === 'Paid'" variant="success" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                                <b-badge v-else variant="danger" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.afternoon" :key="index">
                            <div v-if="user && user.schedules.length > 0">
                                
                                <b-form-group id="input-group-3">
                                    <b-form-select
                                    id="input-3"
                                    v-model="tour_title_selected_afternoon"
                                    :options="renderOptions()"
                                    @change="tourTitleChange(user.schedules[0].id, 'afternoon')"
                                    :disabled="user.schedules[0].is_locked"
                                    required
                                    ></b-form-select>
                                </b-form-group>

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

                                <!-- Add Anticipi and Incossi Amounts -->
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.anticipi_afternoon>Add Anticipi</b-button>

                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.anticipi" :key="index">
                                                € {{receipt.amount}}
                                                <b-button variant="link" v-b-tooltip.hover title="Receipt Image" size="sm" @click="openReceipt(receipt.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="anticipi_afternoon" title="Anticipi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'anticipi')" @ok="handleOk">

                                            <form>
                                                
                                                <b-form-group
                                                :state="antAmountState"
                                                label="Amount"
                                                label-for="anticipi-amount-input"
                                                :invalid-feedback="antAmountError"
                                                >
                                                    <b-input-group prepend="€">
                                                        <b-form-input id="anticipi-amount-input" :state="antAmountState" v-model="antAmount" type="number" min="0" required></b-form-input>
                                                    </b-input-group>                                               
                                                </b-form-group>

                                                <!-- Plain mode -->
                                                <b-form-group
                                                :state="receiptState"
                                                label="Receipt"
                                                label-for="receipt-input"
                                                :invalid-feedback="receiptError"
                                                >
                                                    <b-form-file id="receipt-input" ref="file" v-model="receipt_img" @change="fileChange" accept="image/*" class="mt-3" plain></b-form-file>
                                                </b-form-group>

                                                <b-img v-show="receipt_img" :src="preview" fluid alt="Receipt Image"></b-img>
                                            </form>

                                        </b-modal>
                                    </div>
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.incossi_afternoon>Add Incossi</b-button>
                                        
                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.incossi" :key="index">
                                                € {{receipt.amount}}
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="incossi_afternoon" title="Incossi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'incossi')" @ok="handleOk">

                                            <form>
                                                <b-form-group
                                                :state="incAmountState"
                                                label="Amount"
                                                label-for="incossi-amount-input"
                                                :invalid-feedback="incAmountError"
                                                >
                                                    <b-input-group prepend="€">
                                                        <b-form-input id="incossi-amount-input" v-model="incAmount" type-number min="0"></b-form-input>
                                                    </b-input-group>
                                                </b-form-group>
                                            </form>

                                        </b-modal>
                                    </div>
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
                    <span class="badge badge-pill badge-dark">Evening <small>{{date}}</small></span>
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
                                <label class="custom-control-label" :for="'schedule-' + schedule.id">{{ schedule.full_name }} </label>
                                <b-badge v-if="schedule.remarks === 'Paid'" variant="success" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                                <b-badge v-else variant="danger" @click="toggleModal(schedule)" style="cursor:pointer;" pill>{{schedule.remarks}}</b-badge>
                            </div>
                        </div>
                        <div v-else v-for="(user, index) in data.evening" :key="index">
                            <div v-if="user && user.schedules.length > 0">
                                
                                <b-form-group id="input-group-3">
                                    <b-form-select
                                    id="input-3"
                                    v-model="tour_title_selected_evening"
                                    :options="renderOptions()"
                                    @change="tourTitleChange(user.schedules[0].id, 'evening')"
                                    :disabled="user.schedules[0].is_locked"
                                    required
                                    ></b-form-select>
                                </b-form-group>

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

                                <!-- Add Anticipi and Incossi Amounts -->
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.anticipi_evening>Add Anticipi</b-button>

                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.anticipi" :key="index">
                                                € {{receipt.amount}}
                                                <b-button variant="link" v-b-tooltip.hover title="Receipt Image" size="sm" @click="openReceipt(receipt.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="anticipi_evening" title="Anticipi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'anticipi')" @ok="handleOk">

                                            <form>
                                                
                                                <b-form-group
                                                :state="antAmountState"
                                                label="Amount"
                                                label-for="anticipi-amount-input"
                                                :invalid-feedback="antAmountError"
                                                >
                                                    <b-input-group prepend="€">
                                                        <b-form-input id="anticipi-amount-input" :state="antAmountState" v-model="antAmount" type="number" min="0" required></b-form-input>
                                                    </b-input-group>                                               
                                                </b-form-group>

                                                <!-- Plain mode -->
                                                <b-form-group
                                                :state="receiptState"
                                                label="Receipt"
                                                label-for="receipt-input"
                                                :invalid-feedback="receiptError"
                                                >
                                                    <b-form-file id="receipt-input" ref="file" v-model="receipt_img" @change="fileChange" accept="image/*" class="mt-3" plain></b-form-file>
                                                </b-form-group>

                                                <b-img v-show="receipt_img" :src="preview" fluid alt="Receipt Image"></b-img>
                                            </form>

                                        </b-modal>
                                    </div>
                                </div>
                                <div v-show="user.schedules[0].flag === 1" class="row">
                                    <div class="col-md-12">
                                        <b-button variant="link" v-b-modal.incossi_evening>Add Incossi</b-button>
                                        
                                        <b-list-group>
                                            <b-list-group-item class="d-flex justify-content-between align-items-center" v-for="(receipt, index) in user.schedules[0].categorized_payments.incossi" :key="index">
                                                € {{receipt.amount}}
                                            </b-list-group-item>
                                        </b-list-group>

                                        <b-modal id="incossi_evening" title="Incossi Amount" no-close-on-backdrop @hidden="resetModal" @shown="modalShown(user.schedules[0].id, 'incossi')" @ok="handleOk">

                                            <form>
                                                <b-form-group
                                                :state="incAmountState"
                                                label="Amount"
                                                label-for="incossi-amount-input"
                                                :invalid-feedback="incAmountError"
                                                >
                                                    <b-input-group prepend="€">
                                                        <b-form-input id="incossi-amount-input" v-model="incAmount" type-number min="0"></b-form-input>
                                                    </b-input-group>
                                                </b-form-group>
                                            </form>

                                        </b-modal>
                                    </div>
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
        
        <b-modal ref="my-modal" :title="'Payments - ' + date" ok-title="Paid" @ok="paid">
            <div class="d-block text-center">
                <p>Remarks: 
                    <b-badge v-if="payment_schedule && payment_schedule.paid_at" variant="success" pill>Paid</b-badge>
                    <b-badge v-else variant="danger" pill>Unpaid</b-badge> 
                </p>
            </div>
            <div class="d-block text-center">
                <b-table small :fields="anticipi_fields" :items="anticipi_items" show-empty>
                    <template slot="amount" slot-scope="data">
                        {{ '€' + data.item.amount }} 
                        <b-button variant="link" v-b-tooltip.hover title="Receipt Image" size="sm" @click="openReceipt(data.item.receipt_url)"><font-awesome-icon icon="file-image" style="cursor: pointer;" /></b-button>
                    </template>
                </b-table>
                <b-table small :fields="incossi_fields" :items="incossi_items" show-empty>
                    <template slot="amount" slot-scope="data">
                        {{ '€' + data.item.amount }}
                    </template>
                </b-table>
                <b-table small :fields="balance_fields" :items="balance_items" show-empty>
                    <template slot="balance" slot-scope="data">
                        {{ '€' + data.item.balance }}
                    </template>
                </b-table>
            </div>
        </b-modal>
    </div>
</template>

<script>
import { log } from 'util';
export default {
    name: 'TourGuideList',
    props: {
        date: String,
        data: Object,
        tour_titles: Array,
        loading: Boolean,
        isAdmin: Boolean,
        errors: Object,
        toggleCollapse: Number
    },
    data() {
        return {
            antAmountState: null,
            antAmount: 0,
            antAmountError: "The amount must be greater than 0",
            incAmountState: null,
            incAmount: 0,
            incAmountError: "The amount must be greater than 0",
            receiptState: null,
            receiptError: 'The receipt image is required',
            sched_id: null,
            category: null,
            receipt_img: null,
            preview: '',
            tour_title_selected_morning: null,
            tour_title_selected_afternoon: null,
            tour_title_selected_evening: null,
            anticipi_fields: [
                { key: 'amount', label: 'Anticipi' }
            ],
            anticipi_items: null,
            incossi_fields: [
                { key: 'amount', label: 'Incossi' }
            ],
            incossi_items: null,
            balance_fields: [
                { key: 'balance', label: 'Balance' }
            ],
            balance_items: null,
            payment_schedule: null
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
        tourTitleChange(id, time) {
            if(time === 'morning') {
                this.$emit('onTourTitleChange', {'schedule' : id, 'tour_title' : this.tour_title_selected_morning});
            } else if(time === 'afternoon') {
                this.$emit('onTourTitleChange', {'schedule' : id, 'tour_title' : this.tour_title_selected_afternoon});
            } else if(time === 'evening') {
                this.$emit('onTourTitleChange', {'schedule' : id, 'tour_title' : this.tour_title_selected_evening});   
            }
        },
        handleOk(bvModalEvt) {
            // Prevent modal from closing
            bvModalEvt.preventDefault()

            // Trigger submit handler
            this.handleSubmit()
        },
        checkFormValidity() {
            let valid = true
            let validReceipt = true

            if(this.category === 'anticipi') {

                valid = (this.antAmount > 0) ? true : false
                validReceipt = (this.receipt_img) ? true : false

                this.antAmountState = valid ? 'valid' : 'invalid'
                this.receiptState = validReceipt ? 'valid' : 'invalid'

            } else if(this.category === 'incossi') {

                valid = (this.incAmount > 0) ? true : false

                this.incAmountState = valid ? 'valid' : 'invalid'

            }

            return valid && validReceipt
        },
        handleSubmit() {
            // Exit when the form isn't valid
            if (!this.checkFormValidity()) {
                return
            }

            let vm = this

            let formData = new FormData()
            formData.append('schedule_id', this.sched_id)

            if(this.receipt_img && this.category === 'anticipi') {
                formData.append('file', this.receipt_img)
                formData.append('amount', this.antAmount)
            } else formData.append('amount', this.incAmount)

            axios.post('/payment/' + this.category,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
            ).then(function(){
                vm.$emit('onLoad')
            })
            .catch(err => {
                if(err.response.data.errors && err.response.data.errors.amount) {
                    this.antAmountError = err.response.data.errors.amount[0]
                    this.antAmountState = 'invalid'
                }

                if(err.response.data.errors && err.response.data.errors.amount) {
                    this.incAmountError = err.response.data.errors.amount[0]
                    this.incAmountState = 'invalid'
                }
                
                if(err.response.data.errors && err.response.data.errors.file) {
                    this.receiptError = err.response.data.errors.file[0]
                    this.receiptState = 'invalid'
                }
            })
        },
        fileChange(e) {
            let file = e.target.files[0]
            this.preview = URL.createObjectURL(file);
        },
        resetModal() {
            this.receipt_img = null
            this.antAmountState = null
            this.antAmount = 0
            this.antAmountError = "The amount must be greater than 0"
            this.incAmountState = null
            this.incAmount = 0
            this.incAmountError = "The amount must be greater than 0"
            this.receiptState = null
            this.receiptError = 'The receipt image is required'
            this.category = null
            this.sched_id = null
            this.preview = ''
        },
        modalShown(schedule, category) {
            this.sched_id = schedule
            this.category = category
        },
        openReceipt(url) {
            window.open(url, "_blank")
        },
        renderOptions() {
            let options = [{ text: 'Select Title', value: null }];

            for(let a = 0; a < this.$props.tour_titles.length; a++) {
                let obj = {text: this.tour_titles[a].title, value: this.tour_titles[a].id}

                if(this.toggleCollapse === 0 && this.tour_titles[a].time === 'am') {
                    options.push(obj)
                } else if(this.toggleCollapse === 1 && this.tour_titles[a].time === 'pm') {
                    options.push(obj)
                } else if(this.toggleCollapse === 2 && this.tour_titles[a].time === 'pm') {
                    options.push(obj)
                }
                
            }

            return options
        },
        toggleModal(schedule) {
            this.payment_schedule = schedule

            this.anticipi_items = schedule.categorized_payments.anticipi

            this.incossi_items = schedule.categorized_payments.incossi

            this.balance_items = [schedule.categorized_payments]
            
            // We pass the ID of the button that we want to return focus to
            // when the modal has hidden
            this.$refs['my-modal'].toggle('#toggle-btn')
        },
        paid(e) {
            e.preventDefault()

            let vm = this

            if(!this.payment_schedule) return
            
            axios.put('/payment/'+this.payment_schedule.id, {paid: 'true'})
            .then(response => {
                vm.$emit('onLoad')

                this.$refs['my-modal'].hide()
            })
            .catch(function (error) {
                //error
            });
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
    },
    watch: {
        data: function(data) {
            // if(this.toggleCollapse === 0) {
            this.tour_title_selected_morning = data && data.morning.length && data.morning[0].schedules.length ? data.morning[0].schedules[0].tour_title_id : null
            // } else if(this.toggleCollapse === 1) {
            this.tour_title_selected_afternoon = data && data.afternoon.length && data.afternoon[0].schedules.length ? data.afternoon[0].schedules[0].tour_title_id : null
            // } else if(this.toggleCollapse === 2) {
            this.tour_title_selected_evening = data && data.evening.length && data.evening[0].schedules.length ? data.evening[0].schedules[0].tour_title_id : null
            // }
        }
    }
}
</script>
