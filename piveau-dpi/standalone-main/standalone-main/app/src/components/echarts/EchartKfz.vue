<template>
  <div id="echartKfz">
  </div>
</template>

<script>
import { defineComponent, onMounted } from 'vue'
import * as echarts from 'echarts'
import { useEcharts } from '@/composables/useEcharts';

export default defineComponent({
  props: ['data'],
  setup(props) {

    onMounted(() => {
      // Initialize the echarts instance based on the prepared dom
      const el = document.getElementById('echartKfz');
      const { setOption } = useEcharts(el);
      const data = props.data

      var colors = ['#A9242C', '#3D6E26', '#0172AD', '#872E8F', '#59A436', '#C44BCE'];

      var header_size = 20

      var text_size = 15

      var font_family = 'Inter Variable, Inter, sans-serif'

      var result = {};

      // Extract entries from the "Anteil" object and sort them by their values
      var sortedEntries = Object.entries(data['Anteil']).sort((a, b) => b[1] - a[1]);

      // Assign the indices based on the sorted order
      sortedEntries.forEach((entry, index) => {
        result[entry[0]] = index;
      });

      var svgContent = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M17.0306 12.5306L9.53055 20.0306C9.46087 20.1003 9.37815 20.1556 9.2871 20.1933C9.19606 20.231 9.09847 20.2504 8.99993 20.2504C8.90138 20.2504 8.8038 20.231 8.71276 20.1933C8.62171 20.1556 8.53899 20.1003 8.4693 20.0306C8.39962 19.9609 8.34435 19.8782 8.30663 19.7872C8.26892 19.6961 8.24951 19.5986 8.24951 19.5C8.24951 19.4015 8.26892 19.3039 8.30663 19.2128C8.34435 19.1218 8.39962 19.0391 8.4693 18.9694L15.4396 12L8.4693 5.03063C8.32857 4.8899 8.24951 4.69903 8.24951 4.50001C8.24951 4.30098 8.32857 4.11011 8.4693 3.96938C8.61003 3.82865 8.80091 3.74959 8.99993 3.74959C9.19895 3.74959 9.38982 3.82865 9.53055 3.96938L17.0306 11.4694C17.1003 11.539 17.1556 11.6218 17.1933 11.7128C17.2311 11.8038 17.2505 11.9014 17.2505 12C17.2505 12.0986 17.2311 12.1962 17.1933 12.2872C17.1556 12.3783 17.1003 12.461 17.0306 12.5306Z" fill="#0172AD"/></svg>';

      var option = {
        textStyle: { fontFamily: font_family },
        title: [
          //KPIs
          {
            text: data['Anteil']['Elektro'].toLocaleString() + '%',
            subtext: 'Elektro',
            top: header_size * 3 * result['Elektro'],
            left: '3%',
            textStyle: { color: colors[2], fontSize: header_size },
            subtextStyle: { color: colors[2], fontSize: text_size }
          },

          {
            text: data['Anteil']['Benzin'].toLocaleString() + '%',
            subtext: 'Benzin',
            top: header_size * 3 * result['Benzin'],
            left: '3%',
            textStyle: { color: colors[0], fontSize: header_size },
            subtextStyle: { color: colors[0], fontSize: text_size },
          },

          {
            text: data['Anteil']['Diesel'].toLocaleString() + '%',
            subtext: 'Diesel',
            top: header_size * 3 * result['Diesel'],
            left: '3%',
            textStyle: { color: colors[1], fontSize: header_size },
            subtextStyle: { color: colors[1], fontSize: text_size }
          },

          {
            text: data['Anteil']['Hybrid'].toLocaleString() + '%',
            subtext: 'Hybrid',
            top: header_size * 3 * result['Hybrid'],
            left: '3%',
            textStyle: { color: colors[3], fontSize: header_size },
            subtextStyle: { color: colors[3], fontSize: text_size }
          },
        ],

        tooltip: {
          trigger: 'axis',
          backgroundColor: '#FAFAFB',
          valueFormatter: (value) => value.toLocaleString(),
        },
        grid: {
          backgroundColor: '#FAFAFB',
          show: true,
          left: 80,
          right: '5%',
          top: 10,
          bottom: 20,

          containLabel: true
        },
        xAxis: {
          boundaryGap: false,
          data: data['Jahr'],
          axisTick: {
            show: false
          },
          axisLabel: {
            formatter: function (value, index) {
              if (index === 0 || index === data['Jahr'].length - 1) { // Display only first and last labels
                return value;
              } else {
                return '';
              }
            }
          },
        },
        yAxis: {
          type: 'value',
          splitLine: {
            show: false
          },
          axisLabel: {
            formatter: function (value, index) {
              if (index === 0 || index === 7) { // Display only first and last labels
                return value.toLocaleString();
              } else {
                return '';
              }
            }
          }
        },
        series: [
          {
            name: 'Benzin',
            type: 'line',
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data[data.length - 1], 425588.0], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label except for the last symbol
              },
              itemStyle: {
                color: colors[0] // Customize symbol color
              }
            },
            itemStyle: { color: colors[0] },
            lineStyle: { type: [20, 5], width: 2 },
            data: data['Benzin']
          },
          {
            name: 'Diesel',
            type: 'line',
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data[data.length - 1], 200865.0], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label except for the last symbol
              },
              itemStyle: {
                color: colors[4] // Customize symbol color
              }
            },
            itemStyle: { color: colors[4] },
            lineStyle: { type: [5, 5], width: 2 },
            data: data['Diesel']
          },
          {
            name: 'Elektro',
            type: 'line',
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data[data.length - 1], 32329.0], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label except for the last symbol
              },
              itemStyle: {
                color: colors[2] // Customize symbol color
              }
            },
            itemStyle: { color: colors[2] },
            lineStyle: { width: 2 },
            data: data['Elektro']
          },
          {
            name: 'Hybrid',
            type: 'line',
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data[data.length - 1], 103555.0], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label except for the last symbol
              },
              itemStyle: {
                color: colors[5] // Customize symbol color
              }
            },
            itemStyle: { color: colors[5] },
            lineStyle: { type: [12, 5, 3, 5], width: 2 },
            data: data['Hybrid']
          }
        ]
      };

      // Display the chart using the configuration items and data just specified.
      setOption(option);
    })

  }
})
</script>
