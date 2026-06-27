<template>
  <div class="d-flex flex-column bg-transparent container-fluid justify-content-between content">
    <h1 class="small-headline dataset-details-title">My Profile</h1>
    <div class="panel-body inf-content">
      <div class="row">
        <div class="col-md-12">
          <!-- <strong class="table-header">User Information</strong><br> -->
          <div class="table-responsive">
            <table class="table table-user-information">
              <tbody>
                <tr>
                  <td class="label-column">
                    <strong>
                      <span class="glyphicon glyphicon-user text-primary"></span>
                      User ID:
                    </strong>
                  </td>
                  <td class="text-primary value-column">
                    {{ getUserName }}
                  </td>
                </tr>
                <tr>
                  <td class="label-column">
                    <strong>
                      <span class="glyphicon glyphicon-user text-primary"></span>
                      Roles:
                    </strong>
                  </td>
                  <td class="text-primary value-column">
                    <p v-for="i in getUserData['roles'].filter(role => !role.startsWith('default-roles') && !['offline_access', 'uma_authorization'].includes(role))" :key="i">
                      {{ i }}
                    </p>
                  </td>
                </tr>
                <tr v-if="userProfileDashboardUrl">
                  <td class="label-column">
                    <strong>
                      <span class="glyphicon glyphicon-user text-primary"></span>
                      User Profile:
                    </strong>
                  </td>
                  <td class="text-primary value-column">
                    <a :href="userProfileDashboardUrl" target="_blank" class="btn btn-link">Edit Profile</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { getCurrentInstance } from "vue";

// let instance = getCurrentInstance().appContext.app.config.globalProperties.$env



export default {
  name: 'DataProviderInterface-UserProfile',
  // props: [],
  // data() {
  //   return {
  //     values: {},
  //   };
  // },

  data() {
    return {
      // Need to assign the URL for userProfileDashboardUrl in the config files. 
      userProfileDashboardUrl: getCurrentInstance().appContext.app.config.globalProperties.$env.content.dataProviderInterface.userProfileDashboardUrl || '',
    };
  },
  computed: {
    ...mapGetters('auth', [
      'getUserName',
      'getUserData'
    ]),
  },
  // methods: {},
  // created() { },
};
</script>

<style scoped>
.inf-content {
  /* border: 1px solid #DDDDDD;
  -webkit-border-radius: 10px;
  -moz-border-radius: 10px;
  border-radius: 10px;
  box-shadow: 7px 7px 7px rgba(0, 0, 0, 0.3); */
  padding: 10px;
}

/* .table-header {
  padding: 0.75rem;
} */

.table td {
  padding: 15px;
  vertical-align: middle;
  border-bottom: 1px solid #DDDDDD; /* Ensures the horizontal lines span full width */
}

.table th {
  text-align: left;
}

h1.small-headline {
  margin-bottom: 20px;
}

p {
  margin: 0; /* Ensure proper line breaks */
}
/* Added space at the bottom of the page */
.content {
  padding-bottom: 60px;
}

.table {
  width: 100%;
  border-collapse: collapse; /* Ensures the borders are merged properly */
}

.label-column {
  width: 10%; /* Adjust as needed */
  text-align: left;
  padding-right: 10px;
  min-width: 200px;
}

.value-column {
  width: 100%; /* Adjust as needed */
}

.value-column .btn {
  padding: 0;
  /* text-decoration: none;
  color: #5bc0de; */
}

.table tr:last-child td {
  border-bottom: none;
}

/* .dataset-details-title {
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
  font-family: inherit;
  font-weight: 500;
  line-height: 1.2;
  color: inherit;
} */

.dataset-details-title {
  display: flex;
  align-items: center; /* Aligns the text and line in the center */
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
  font-family: inherit;
  font-weight: 500;
  line-height: 1.2;
  color: inherit;
}

.dataset-details-title::after {
  content: ""; /* Creates the line after the text */
  flex-grow: 1; /* Makes the line span the remaining width */
  height: 1px; /* Thickness of the line */
  background-color: #ccc; /* Line color */
  margin-left: 10px; /* Space between the text and the line */
}

</style>
