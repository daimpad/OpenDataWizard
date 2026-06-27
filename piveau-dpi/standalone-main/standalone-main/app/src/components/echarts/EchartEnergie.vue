<template>
  <div id="echartEnergie">

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
      const el = document.getElementById('echartEnergie');
      const { setOption } = useEcharts(el);
      const data = props.data

      // Specify the configuration items and data for the chart
      var colors = ['#5470c6', '#91cc75', '#fac858', '#ee6666'];

      var header_size = 20

      var text_size = 15

      var font_family = 'Inter Variable, Inter, sans-serif'

      var bar_border_Width = 1

      var svgContent = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M17.0306 12.5306L9.53055 20.0306C9.46087 20.1003 9.37815 20.1556 9.2871 20.1933C9.19606 20.231 9.09847 20.2504 8.99993 20.2504C8.90138 20.2504 8.8038 20.231 8.71276 20.1933C8.62171 20.1556 8.53899 20.1003 8.4693 20.0306C8.39962 19.9609 8.34435 19.8782 8.30663 19.7872C8.26892 19.6961 8.24951 19.5986 8.24951 19.5C8.24951 19.4015 8.26892 19.3039 8.30663 19.2128C8.34435 19.1218 8.39962 19.0391 8.4693 18.9694L15.4396 12L8.4693 5.03063C8.32857 4.8899 8.24951 4.69903 8.24951 4.50001C8.24951 4.30098 8.32857 4.11011 8.4693 3.96938C8.61003 3.82865 8.80091 3.74959 8.99993 3.74959C9.19895 3.74959 9.38982 3.82865 9.53055 3.96938L17.0306 11.4694C17.1003 11.539 17.1556 11.6218 17.1933 11.7128C17.2311 11.8038 17.2505 11.9014 17.2505 12C17.2505 12.0986 17.2311 12.1962 17.1933 12.2872C17.1556 12.3783 17.1003 12.461 17.0306 12.5306Z" fill="#0172AD"/></svg>';

      var axis_max = Math.ceil(Math.max(data['Wind'], data['Solar'], data['Biomasse'], data['Wasser']) / 10) * 10

      var axis_interval = 10

      var div_width = document.getElementById("echartEnergie").clientWidth;

      var option = {
        textStyle: { fontFamily: font_family },
        title: [
          // KPIs
          {
            text: data['Wind'].toLocaleString() + '%',
            subtext: 'Wind',
            top: header_size * 0,
            left: '3%',
            textStyle: { color: '#0172AD', fontSize: header_size },
            subtextStyle: { color: '#0172AD', fontSize: text_size }
          },

          {
            text: data['Solar'].toLocaleString() + '%',
            subtext: 'Solar',
            top: header_size * 3,
            left: '3%',
            textStyle: { color: '#A9242C', fontSize: header_size },
            subtextStyle: { color: '#A9242C', fontSize: text_size }
          },

          {
            text: data['Biomasse'].toLocaleString() + '%',
            subtext: 'Biomasse',
            top: header_size * 6,
            left: '3%',
            textStyle: { color: '#3D6E26', fontSize: header_size },
            subtextStyle: { color: '#3D6E26', fontSize: text_size }
          },

          {
            text: data['Wasser'].toLocaleString() + '%',
            subtext: 'Wasser',
            top: header_size * 9,
            left: '3%',
            textStyle: { color: '#872E8F', fontSize: header_size },
            subtextStyle: { color: '#872E8F', fontSize: text_size }
          },
        ],

        grid: {
          backgroundColor: '#FAFAFB',
          left: 100,
          right: '5%',
          top: 2,
          bottom: 18,
          show: true // Set background color for the data canvas
        },
        yAxis: {
          type: 'category',
          data: ['Wasser', 'Biomasse', 'Solar', 'Wind'],
          show: false
        },
        xAxis: {
          type: 'value',
          splitLine: {
            show: false
          },


          min: 0,
          max: axis_max,
          interval: axis_interval,
          position: 'right',
          axisLabel: {
            formatter: function (value, index) {
              if (index === 0 || index === axis_max / axis_interval) { // Display only first and last labels
                return value + '%';
              } else {
                return '';
              }
            }
          },
          axisTick: {
            show: true, // Show axis ticks
            alignWithLabel: true, // Align ticks with labels
            lineStyle: {
              width: 0.5 // Set width of the tick stroke
            },
          }
        },
        series: [
          {
            data: [
              {
                value: data['Wasser'], itemStyle: {
                  color: new echarts.graphic.LinearGradient(1, 0, 0, 0, [
                    {
                      offset: 0,
                      color: '#F3A7F9'
                    },
                    {
                      offset: 1,
                      color: '#F7D6FA'
                    }
                  ]),
                  borderColor: '#872B8F',
                  borderWidth: bar_border_Width,
                }
              },
              {
                value: data['Biomasse'], itemStyle: {
                  color: new echarts.graphic.LinearGradient(1, 0, 0, 0, [
                    {
                      offset: 0,
                      color: '#9CDB7E'
                    },
                    {
                      offset: 1,
                      color: '#D5ECCB'
                    }
                  ]),
                  borderColor: '#3A6D22',
                  borderWidth: bar_border_Width,
                }
              },
              {
                value: data['Solar'], itemStyle: {
                  color: new echarts.graphic.LinearGradient(1, 0, 0, 0, [
                    {
                      offset: 0,
                      color: '#FD9EA3'
                    },
                    {
                      offset: 1,
                      color: '#FCD3D7'
                    }
                  ]),
                  borderColor: '#AC262E',
                  borderWidth: bar_border_Width,
                }
              },
              {
                value: data['Wind'], itemStyle: {
                  color: new echarts.graphic.LinearGradient(1, 0, 0, 0, [
                    {
                      offset: 0,
                      color: '#6BC7F2'
                    },
                    {
                      offset: 1,
                      color: '#BCE4F8'
                    }
                  ]),
                  borderColor: '#117AB1',
                  borderWidth: bar_border_Width,
                  borderWidthBottom: 0
                }
              }],
            type: 'bar',
            barWidth: 20
          }
        ]
      };

      // Display the chart using the configuration items and data just specified.
      setOption(option);
    })

  }
})
</script>
