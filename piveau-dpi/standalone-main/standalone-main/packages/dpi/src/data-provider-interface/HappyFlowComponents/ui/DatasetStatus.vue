<template>
    <!-- Static -->
    <!-- <div class="dataset-status-wrapper">
     <div class="dataset-status-container">
         <div class="status-timestamp">
             <StateTag label="Veröffentlicht" state="published" size="page" />
             <span class="timestamp">Bearbeitet am: {{ timestamp }}</span>
         </div>
         <MoreButton @click="handleMoreClick" />
     </div>
     <div class="dataset-status-container">
         <div class="status-timestamp">
             <StateTag label="Entwurf" state="draft" size="page" />
             <span class="timestamp">Bearbeitet am: {{ timestamp }}</span>
         </div>
         <MoreButton @click="handleMoreClick" />
     </div>
     </div> -->
 
     <!-- Dynamic -->
     <div class="dpiV3_dataset-status-wrapper">
         <div 
             class="dpiV3_dataset-status-container" 
             v-for="(status, index) in statuses" 
             :key="index"
         >
             <div class="dpiV3_status-timestamp">
                 <StateTag :label="status.label" :state="status.state" size="page" />
                 <span class="dpiV3_timestamp">Bearbeitet am: {{ timestamp }}</span>
             </div>
             <MoreButton @click="handleMoreClick" />
         </div>
     </div>
 </template>
 
 <script>
 import StateTag from "./StateTag.vue";
 import MoreButton from "./MoreButton.vue";
 
 export default {
 name: "DatasetStatus",
 props: {
     timestamp: {
     type: String,
     required: true,
     },
     // Dynamic Labels
     statuses: {
         type: Array,
         required: true, // Require the statuses prop
     },
 },
 components: {
     StateTag,
     MoreButton,
 },
 methods: {
     handleMoreClick() {
     console.log("Button clicked");
     },
 },
 };
 </script>
 
 <style scoped>
 .dpiV3_dataset-status-wrapper {
   display: flex;
   flex-direction: column; /* Stack children vertically */
   gap: var(--Spacing-3, 8px); /* Add spacing between the components */
 }
 
 .dpiV3_dataset-status-container {
   display: flex;
   width: 616px;
   height: 48px;
   justify-content: space-between;
   align-items: center;
   background: var(--neutral-0, #FFF);
 }
 
.dpiV3_dataset-status-container > :last-child {
    margin-right: 0; 
    flex-shrink: 0; 
} 

 .dpiV3_status-timestamp {
   display: flex;
   align-items: center;
   gap: var(--Spacing-3, 16px);
 }
 
 .dpiV3_timestamp {
  font-size: var(--copy-small-regular-font-size);
  font-weight: var(--copy-small-regular-font-weight);
  line-height: var(--copy-small-regular-line-height);
  color: var(--neutral90, #25333D);
 }
</style>