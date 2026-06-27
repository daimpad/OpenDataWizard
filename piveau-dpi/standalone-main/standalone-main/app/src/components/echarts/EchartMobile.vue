<template>
  <div id="echartMobile">

  </div>
</template>

<script>
import { defineComponent, onMounted } from 'vue'
import * as echarts from 'echarts'
import { useEcharts } from '../../composables/useEcharts';

export default defineComponent({
  props: ['data'],
  setup(props) {

    onMounted(() => {
      // Initialize the echarts instance based on the prepared dom
      const el = document.getElementById('echartMobile');
      const { setOption } = useEcharts(el);
      const data = props.data

      // Specify the configuration items and data for the chart
      var header_size = 20

      var text_size = 15

      var font_family = 'Inter Variable, Inter, sans-serif'

      var fill_colors = ['#67C5F0', '#9ADB7C', '#FF9FA4'];

      var colors = ['#0172AD', '#3D6E26', '#A9242C']

      var svgContent = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M17.0306 12.5306L9.53055 20.0306C9.46087 20.1003 9.37815 20.1556 9.2871 20.1933C9.19606 20.231 9.09847 20.2504 8.99993 20.2504C8.90138 20.2504 8.8038 20.231 8.71276 20.1933C8.62171 20.1556 8.53899 20.1003 8.4693 20.0306C8.39962 19.9609 8.34435 19.8782 8.30663 19.7872C8.26892 19.6961 8.24951 19.5986 8.24951 19.5C8.24951 19.4015 8.26892 19.3039 8.30663 19.2128C8.34435 19.1218 8.39962 19.0391 8.4693 18.9694L15.4396 12L8.4693 5.03063C8.32857 4.8899 8.24951 4.69903 8.24951 4.50001C8.24951 4.30098 8.32857 4.11011 8.4693 3.96938C8.61003 3.82865 8.80091 3.74959 8.99993 3.74959C9.19895 3.74959 9.38982 3.82865 9.53055 3.96938L17.0306 11.4694C17.1003 11.539 17.1556 11.6218 17.1933 11.7128C17.2311 11.8038 17.2505 11.9014 17.2505 12C17.2505 12.0986 17.2311 12.1962 17.1933 12.2872C17.1556 12.3783 17.1003 12.461 17.0306 12.5306Z" fill="#0172AD"/></svg>';

      const gaugeData = [
        {
          value: data['5G'],
          name: '5G',
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
              {
                offset: 0,
                color: 'rgba(103, 197, 240, 1)'
              },
              {
                offset: 1,
                color: 'rgba(103, 197, 240, 0.4)'
              }
            ]),
            borderColor: colors[0]
          },
        },
        {
          value: data['4G'],
          name: '4G',
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
              {
                offset: 0,
                color: 'rgba(154, 219, 124, 1)'
              },
              {
                offset: 1,
                color: 'rgba(154, 219, 124, 0.4)'
              }
            ]),
            borderColor: colors[1]
          },
        },
        {
          value: data['2G'],
          name: '2G',
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 1, 0, [
              {
                offset: 0,
                color: 'rgba(255, 159, 164, 1)'
              },
              {
                offset: 1,
                color: 'rgba(255, 159, 164, 0.4)'
              }
            ]),
            borderColor: colors[2]
          },
        }
      ];
      const option = {
        textStyle: { fontFamily: font_family },
        title: [
          //KPIs
          {
            text: data['5G'].toLocaleString() + '%',
            subtext: '5G',
            top: header_size * 0,
            left: '3%',
            textStyle: { color: colors[0], fontSize: header_size },
            subtextStyle: { color: colors[0], fontSize: text_size }
          },
          {
            text: data['4G'].toLocaleString() + '%',
            subtext: '4G',
            top: header_size * 3,
            left: '3%',
            textStyle: { color: colors[1], fontSize: header_size },
            subtextStyle: { color: colors[1], fontSize: text_size }
          },
          {
            text: data['2G'].toLocaleString() + '%',
            subtext: '2G',
            top: header_size * 6,
            left: '3%',
            textStyle: { color: colors[2], fontSize: header_size },
            subtextStyle: { color: colors[2], fontSize: text_size }
          },

        ],
        tooltip: {
          trigger: 'item',
          backgroundColor: '#FAFAFB',
          valueFormatter: (value) => value.toLocaleString(),
        },
        series: [
          {
            type: 'gauge', // https://echarts.apache.org/en/option.html#series-gauge.type
            startAngle: 90,
            endAngle: -270,
            center: ['64%', '50%'],
            radius: '113', //120,
            pointer: {
              show: false
            },
            progress: {
              show: true,
              overlap: false,
              roundCap: false,
              clip: false,
              itemStyle: {
                borderWidth: 1
              }
            },
            axisLine: {
              show: false,
              lineStyle: {
                width: 40
              }
            },
            splitLine: {
              show: false
            },
            axisTick: {
              show: false
            },
            axisLabel: {
              show: false
            },
            data: gaugeData,
            title: {
              show: false,
            },
            detail: {
              show: true,
              color: '#FAFAFB',
              backgroundColor: 'FAFAFB',
              top: 0,
              width: '200%',//244,
              height: '200%',
              borderWidth: 1, // Add border width to ensure the edges are visible

              offsetCenter: [0, 0]
            },
            backgroundColor: '#FAFAFB',
          },
        ]
      };

      // Display the chart using the configuration items and data just specified.
      setOption(option);
    })
  }
});
</script>
