<template>
    <div class="">
      <div class="distribution-format-badge text-center mt-1"
           :type="getDistributionFormat(distribution)"
           :data-toggle="distributionFormatTruncated(distribution) ? 'tooltip' : false"
           :data-placement="distributionFormatTruncated(distribution) ? 'top' : false"
           :title="distributionFormatTruncated(distribution) ? getDistributionFormat(distribution) : false">
        <span>{{ truncate(getDistributionFormat(distribution), 10, true) }}</span>
      </div>
    </div>
  </template>
  
  <script>
  import { truncate } from '@piveau/piveau-hub-ui-modules';
  export default {
    name: "DistributionFormat",
    props: [
      'distribution',
      'getDistributionFormat',
      'distributionFormatTruncated',
    ],
    methods: {
      truncate
    }
  }
  </script>
   
   <style>
    .distribution-format-badge {
        color: #131313;
        background-color: #D4EDFC;
        margin-right: 0.5rem;
        margin-left: 0.5rem;
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;   
    }
    </style>
  