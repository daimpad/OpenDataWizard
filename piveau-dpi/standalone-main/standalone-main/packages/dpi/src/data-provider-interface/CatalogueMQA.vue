<template>
    <div class="container mb-3">
        <div>
            <div>
                <div class="debug">
                    <strong> API object:</strong> <br><br>

                   <textarea> {{ token }} </textarea>
                  <hr>
                    <!-- Disabled for PPE release (no backend on PPE yet)-->
                    {{ notficationInfo }}

                    <br> --- <br>

                    {{ mailList }}

                    <br>---<br>
                    <strong>Fequency:</strong> <br>
                    weekly: {{ weekly }}<br>
                    monthly: {{ monthly }} <br>
                    <p></p>
                    unit: {{ frequencyUnit }} <br>
                    value: {{ frequencyValue }}

                    <br>---<br>
                    treshold: {{ treshold }}

                </div>
                <h1>MQA Report Settings</h1>
                <p>Configuration for

                    <strong>{{ catalogId }}</strong>
                    <!-- <strong>Test Catalogue</strong> -->
                </p>
                <p>For this Catalogue the <b>MQA Rating Checks </b>are currently <b>
                        <span v-if="!active">deactivated</span>
                        <span v-if="active">activated</span></b>.</p>
            </div>

            <button type="button"  :class="{ 'btn btn-primary mb-5': !active, 'btn btn-secondary': active }" @click="handleActivate()">
                {{ activatedString }}
            </button>
        </div>
       
        <div v-if="active">
            <div class="mqaWrapper" >
                <h3>Recipients Mail</h3>
                <span>Add and edit mail addresses for recieving the MQA report</span>
                <table class="mt-4" ref="mailButtonWrap" v-if="mailList.length != 0">
                    <tr>
                        <th>Mail</th>
                    </tr>
                    <tr v-for="(item, index) in mailList" class="mailItems" :key="index">
                        <td>
                            <span v-if="!editMode">{{ item }}</span>
                            <input type="text" v-model="mailList[index]" class="mail-input" v-if="editMode"
                                @input="editErrors[index] = ''"
                                :class="{ 'invalidNewMail': editErrors[index] }" >
                              
                            <div>
                                <button type="button" class="btn btn-simple"
                                    @click="editMode ? saveMail(index) : editMail(index)">
                                    {{ editMode ? 'Save' : 'Edit' }}
                                </button>
                                <button type="button" class="btn btn-simple"
                                    @click="deleteMail(index)">Delete</button>
                            </div>
                        </td>
                        <span class="errormsg" v-if="editErrors[index]">{{ editErrors[index] }}</span>
                    </tr>
                </table>
                <div class="d-flex mt-3">
                    <input type="text" v-model="newMail.mail" @input="newMail.isValid = true"
                        :class="{ 'invalidNewMail': !newMail.isValid }" placeholder="Enter email address"
                        class="mail">
                    <button type="button" class="btn btn-simple mx-3" @click="addNewMail()">+ Add Mail</button>
                </div>
                <span class="errormsg" v-if="!newMail.isValid">*Invalid email format</span>
            </div>
            <div class="mqaWrapper">
                <h3>Frequency of Rating Checks</h3>
                <span>Configure the frequency of the MQA rating checks.</span>

                <div class="d-flex mt-3">
                    <div class="mr-3 my-3">
                        <button type="button" class="btn btn-simple" @click="setWeekly()"
                            :class="{ 'activeChoiceButton': weekly }">Weekly</button>
                        <div class="weekdays" :class="{ 'blur': !weekly }">
                            <span v-for="(day, index) in week" :key="index">
                                <button :disabled="!weekly" class="dayButtons"
                                    :class="{ 'activeItem': selectedDay === index }" @click="selectDay(index)">
                                    {{ day }}
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="my-3">
                        <button type="button" :class="{ 'activeChoiceButton': monthly }" class="btn btn-simple"
                            @click="setMonthly()">Monthly</button>
                        <div class="d-flex daypicker my-3" :class="{ 'blur': !monthly }">
                            <input v-model="daysInMonth" @input="selectDay(index)" :class="{ dynamicWidth: inputWidth }"
                                :disabled="!monthly">
                            <div class="caretWrap">
                                <button @click="editDate('up')" class="caretButtons ml-1"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                                        class="bi bi-caret-up-fill" viewBox="0 0 16 16">
                                        <path
                                            d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z" />
                                    </svg></button>
                                <button @click="editDate()" class="caretButtons mr-1"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor"
                                        class="bi bi-caret-down-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z" />
                                    </svg></button>
                            </div>
                            <span>day of the month</span> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="mqaWrapper">
                <h3>Notification Treshold</h3>
                <span>Set the threshold for triggering the report.</span>
                <div class="w-50 mt-3">
                    <div class="d-flex justify-content-between">
                        <span><b>0</b></span>
                        <span><b>400</b></span>
                    </div>
                    <input type="range" v-model="treshold" min="0" max="400" />
                    <p>Lower than <b>{{ treshold }}</b> Points</p>
                </div>
            </div>
            <div class="actionWrapper">
                <button type="button" class="btn btn-primary" @click="postNotificationSettings">Save</button>
                <button type="button" class="btn btn-cancel" @click="back()">Cancel</button>
            </div>
        </div>
    </div>

</template>
<script setup>

import { ref } from 'vue';
import { useRoute } from 'vue-router'
import { getCurrentInstance } from "vue";
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { mapActions } from 'vuex'
import { computed } from 'vue'
import { mapGetters } from 'vuex'

import axios from 'axios'

const store = useStore();
const router = useRouter();
const getUserDrafts = computed(() => store.getters['auth/getUserDrafts'])
const getUserData = computed(() => store.getters['auth/getUserData'])
const token = computed(() => getUserData.value.rtpToken)

// Update the mapActions usage
const authActions = mapActions('auth', {
  updateUserData: 'updateUserData'
})

// Destructure the action
const { updateUserData } = authActions

const showSnackbar = (payload) => {
  store.dispatch('snackbar/showSnackbar', payload);
};

const triggerSnackbar = () => {
            showSnackbar({
                message: 'Saved Successfully',
                variant: 'success',
            });
            };

// Map the showSnackbar action from the snackbar module

const route = useRoute()

let monthly = ref(false)
let weekly = ref(false)
let mailButtonWrap = ref(null)
let activatedString = ref('Activate')
let inputWidth = ref(false)
let active = ref(false)
let mailList = ref()
let week = ['Mo', 'Tue', 'We', 'Th', 'Fr', 'Sa', 'Su']
let daysInMonth = ref(1)
let editMode = ref(false);
let treshold = ref(0);
let frequencyUnit = ref('');
let frequencyValue = ref('');
let selectedDay = ref(null);
const catalogId = route.params.id
const app = getCurrentInstance()
const notificationBaseUrl = app.appContext.app.config.globalProperties.$env.api.notificationBaseUrl
const apiKey = app.appContext.app.config.globalProperties.$env.api.apiKey



const selectDay = (index) => {
    if (weekly.value) {
        selectedDay.value = index;
        frequencyValue.value = selectedDay; // Update frequencyValue when a day is selected
    } 
};

const setWeekly = () => {
    weekly.value = true;
    monthly.value = false;
    selectedDay.value = frequencyValue.value; // Set the selected day based on frequencyValue
};

const setMonthly = () => {
    weekly.value = false;
    monthly.value = true;
    selectedDay.value = frequencyValue.value; // Set the day of the month based on frequencyValue
};

const notficationInfo = ref({})

const isValidEmail = (email) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
};

let newMail = ref({ mail: '', isValid: true });
let editErrors = ref({});

// enabeld for PPE release (dummy data)
//  mailList.value = [{ 'mail': "mail@mail2.com" }, { 'mail': "mail@mail1.com" }]

const fetchNotificationInfo = async () => {
    const config = {
        method: 'get',
        url: `${notificationBaseUrl}/catalogue/${catalogId}/setting`,
        headers: {
                Authorization: `Bearer ${token.value}`,
             },
    }

    try {
        const response = await axios.request(config)
        notficationInfo.value = response.data

        if (notficationInfo.value.activeStatus === true) {
            active.value = true
            activatedString.value = 'Deactivate'
        } if (notficationInfo.value.activeStatus === false) {
            active.value = false
            activatedString.value = 'Activate'
        }

        mailList.value = notficationInfo.value.receiverEmailList
        frequencyUnit.value = notficationInfo.value.frequency.unit
        frequencyValue.value = notficationInfo.value.frequency.value
        treshold.value = notficationInfo.value.threshold

        if (frequencyUnit.value === 'week') {
            weekly.value = true
            monthly.value = false
            selectedDay.value = frequencyValue.value; // Set the selected day based on frequencyValue
        } else if (frequencyUnit.value === 'month') {
            weekly.value = false
            monthly.value = true
            daysInMonth.value = frequencyValue.value
        }

        console.log('Response:', response)
    } catch (error) {
        console.log('Full error:', error)
    }
}

const postNotificationSettings = async () => {

    if (weekly.value === true) {
        frequencyUnit.value = "week"
        frequencyValue.value = selectedDay.value
    } else if (monthly.value === true) {
        frequencyUnit.value = "month"
        frequencyValue.value = daysInMonth.value
    }

    const config = {
        method: 'post',
        url: `${notificationBaseUrl}/catalogue/${catalogId}/setting`,
        headers: {
                Authorization: `Bearer ${token.value}`,
                'Content-Type': 'application/json', 
             },
        withCredentials: true,
        data: JSON.stringify({
            receiverEmailList: mailList.value,
            threshold: Number(treshold.value),
            frequency: {
                unit: frequencyUnit.value,
                value: frequencyValue.value
            },
            activeStatus: true
        })
    }

    try {
        const response = await axios.request(config)
        console.log('Settings updated:', response.data)
        triggerSnackbar();
        router.push({ name: 'DataProviderInterface-UserCatalogues' });
        return response.data
       
    } catch (error) {
        triggerSnackbar({
        message: 'Error updating settings.',
        variant: 'error',
      });

        console.log('Error updating settings:', error)
        
        throw error    
    }
}

fetchNotificationInfo()

const postDeactive = async () => {

    const config = {
        method: 'post',
        url: `${notificationBaseUrl}/catalogue/${catalogId}/setting`,
        headers: {
                Authorization: `Bearer ${token.value}`,
                'Content-Type': 'application/json', 
             },
        withCredentials: true,
        data: JSON.stringify({
            activeStatus: active.value
        })
    }

    try {
        const response = await axios.request(config)
        console.log('Settings updated:', response.data)
        triggerSnackbar();
        if(!active.value) {router.push({ name: 'DataProviderInterface-UserCatalogues' });}
        return response.data
       

    } catch (error) {
        triggerSnackbar({
        message: 'Error updating settings.',
        variant: 'error',
      });

        console.log('Error updating settings:', error)
        
        throw error
        
    }
}

const editMail = () => {
    editMode.value = true
    showSnackbar({
      message: 'Email added successfully.',
      variant: 'success',
    });
}

const saveMail = (index) => {
    const email = mailList.value[index].trim();
    if (isValidEmail(email)) {
        editMode.value = false;
        delete editErrors.value[index];
    } else {
        editErrors.value[index] = '*Invalid email format';
    }
};

const deleteMail = (index) => {
        mailList.value.splice(index, 1);
}

const editDate = (count) => {
    if (count === "up" && daysInMonth.value < 28) {
        if (daysInMonth.value > 8) {
            inputWidth.value = true
        }
        daysInMonth.value++
    }
    else if (daysInMonth.value > 1) {
        if (daysInMonth.value < 11) {
            inputWidth.value = false
        }
        daysInMonth.value--
    }
}

const addNewMail = () => {
    if (newMail.value.mail.trim() === '') {
        newMail.value.isValid = false;
        return;
    }
    if (isValidEmail(newMail.value.mail)) {
        mailList.value.push(newMail.value.mail);
        newMail.value.mail = ''; // Clear input after successful addition
        newMail.value.isValid = true;
    } else {
        newMail.value.isValid = false;
    }
}
const handleActivate = () => {
    if (!active.value) {
        active.value = true
        activatedString.value = 'Deactivate'
        postDeactive()
    } else {
        active.value = false
        activatedString.value = 'Activate'
        postDeactive()
    }
}

const back = () => {
    router.push({ name: 'DataProviderInterface-UserCatalogues' });
}

</script>
<style scoped>
.debug {
    position: fixed;
    right: 20px;
    top: 20px;
    border-radius: 20px;
    width: 400px;
    z-index: 999999;
    padding: 20px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    display: none;  
}

.btn-simple {
    border-color: rgb(115, 115, 115);
}

.btn-cancel {
    border-color: transparent;
}

.mail {
    width: 270px;
    padding: 5px 5px 5px 10px;
}


.errormsg {
    color: red;
    font-size: 10px;
}

.activeChoiceButton {
    background-color: var(--primary);
    color: white;
    border-color:  var(--primary);

    &:active {
        background-color: #3E6CD5 !important;
    }

    &:focus {
        outline: none;
        background-color: #3E6CD5;
    }

    &:focus-visible {
        outline: none;
        background-color: #3E6CD5;
    }
}


.invalid {
    border-bottom: 1px solid red !important;
}

.blur {
    opacity: 0.3;
}

.invalidNewMail {

    border-radius: 2px;
    border: 2px solid rgba(255, 0, 0, 0.336);

    &:focus {
        border-radius: 2px;
        box-shadow: 0 0 0 0.1rem rgba(255, 0, 0, 0.774);
        border: 1px solid rgba(255, 0, 0, 0.336);
    }

    &:focus-visible {
        outline: 0;
        border-radius: 2px;
        box-shadow: 0 0 0 0.1rem rgba(255, 0, 0, 0.774);
        border: 1px solid rgba(255, 0, 0, 0.336);
    }
}

.editable {
    transition: all 200ms ease-in-out;
    padding-left: 0.5rem;
    border-bottom: 2px solid var(--primary) !important;
}

.invalid {
    border-bottom: 1px solid red !important;
}

.caretButtons {
    all: unset;
    cursor: pointer;
}

.caretWrap {
    display: contents;

}

.actionWrapper {
    display: flex;
    flex-direction: row-reverse;
    margin-bottom: 100px;

    button {
        margin-left: 1rem;
    }
}

input[type="range"] {
    width: 100%;
}

.mqaWrapper {
    margin: 3rem 0;
}

table {
    margin-top: 1rem;
    min-width: 50%;
}

th {
    border-bottom: 1px solid lightgray;
}

td {
    display: flex;
    justify-content: space-between;
    padding: 15px 0 0 0cap;
}

.daypicker {
    padding: 1rem;
    border: 1px solid lightgray;
    border-radius: 15px;

    input {
        width: 25px;
        border: none;
        background-color: unset;
        font-weight: 700;
    }

}

.dynamicWidth {
    width: 22px !important;
}

.activeItem {
    background-color: var(--primary);
    color: white;
}

.weekdays {
    border: 1px solid lightgray;
    border-radius: 15px;
    margin: 1rem 0;
    overflow: hidden;

    .dayButtons {
        border: none;
        display: inline-block;
        text-align: center;
        flex-direction: row;
        min-width: 60px;
        padding: 1rem;
        border-right: 1px solid lightgray;
        cursor: pointer;

        &:focus-visible {
            outline: unset;
            background-color: #3E6CD5;
            color: white;
        }

        &:hover {
            background-color: #3E6CD5;
            color: white;
        }
    }

    span:last-child button {
        border: none;
    }
}

.mqaWrapper {
    padding: 1rem;
    background-color: #f3f6fc;
    border-radius: 3px;
}

button {
    background-color: unset;
    border: 1px solid var(--primary);
    color: black;

    &:hover {
        background-color: #3E6CD5;
        color: white;
        border: 1px solid #3E6CD5;
    }
}

.btn-primary {
    background-color: var(--primary);
    color: white;

}

.btn-secondary {
    color: #0e47cb;
}

button,
span {
    transition: all 100ms ease-in-out;
}
</style>