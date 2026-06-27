<template>
  <div id="echartBevoelkerung">

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
      const el = document.getElementById('echartBevoelkerung');
      const { setOption } = useEcharts(el);
      const data = props.data

      // Specify the configuration items and data for the chart
      var colors = ['#0172AD', '#872E8F', '#009FE3', '#C44BCE'];

      var header_size = 20

      var text_size = 15

      var font_family = 'Inter Variable, Inter, sans-serif'

      var datenstand = "18/07/2024"

      var svgContent = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
        '<path d="M17.0306 12.5306L9.53055 20.0306C9.46087 20.1003 9.37815 20.1556 9.2871 20.1933C9.19606 20.231 9.09847 20.2504 8.99993 20.2504C8.90138 20.2504 8.8038 20.231 8.71276 20.1933C8.62171 20.1556 8.53899 20.1003 8.4693 20.0306C8.39962 19.9609 8.34435 19.8782 8.30663 19.7872C8.26892 19.6961 8.24951 19.5986 8.24951 19.5C8.24951 19.4015 8.26892 19.3039 8.30663 19.2128C8.34435 19.1218 8.39962 19.0391 8.4693 18.9694L15.4396 12L8.4693 5.03063C8.32857 4.8899 8.24951 4.69903 8.24951 4.50001C8.24951 4.30098 8.32857 4.11011 8.4693 3.96938C8.61003 3.82865 8.80091 3.74959 8.99993 3.74959C9.19895 3.74959 9.38982 3.82865 9.53055 3.96938L17.0306 11.4694C17.1003 11.539 17.1556 11.6218 17.1933 11.7128C17.2311 11.8038 17.2505 11.9014 17.2505 12C17.2505 12.0986 17.2311 12.1962 17.1933 12.2872C17.1556 12.3783 17.1003 12.461 17.0306 12.5306Z" fill="#0172AD"/></svg>';


      function plus_minus(dataValue) {
        if (dataValue >= 0) {
          return '+' + dataValue;
        } else {
          return dataValue;
        }
      }

      const gesamtMax = Math.max(...data['Gesamt']);
      const entwicklungMax = Math.max(...data['Entwicklung']);
      const entwicklungMin = Math.min(...data['Entwicklung']);
      const year_diff = Math.max(...data['Jahr']) - Math.min(...data['Jahr']);

      if (gesamtMax < 5000) {
        var axis_max_trend = Math.ceil(entwicklungMax / 100) * 1000;
        var axis_max_gesamt = Math.ceil(gesamtMax / 1000) * 1000;
        var axis_min_trend = Math.floor(entwicklungMin / 100) * 100
      }
      else if (gesamtMax < 20000) {
        var axis_max_trend = Math.ceil(entwicklungMax / 500) * 5000;
        var axis_max_gesamt = Math.ceil(gesamtMax / 5000) * 5000;
        var axis_min_trend = Math.floor(entwicklungMin / 1000) * 1000
      } else {
        var axis_max_trend = Math.ceil(entwicklungMax / 5000) * 50000;
        var axis_max_gesamt = Math.ceil(gesamtMax / 50000) * 50000;
        var axis_min_trend = Math.floor(entwicklungMin / 5000) * 5000
      }

      if (entwicklungMin < 0) {
        var axis_min_gesamt = axis_min_trend * axis_max_gesamt / axis_max_trend;
        var axis_interval = Math.abs(axis_min_gesamt);
        var zero_label = 1;
        var max_label = Math.round(axis_max_gesamt / axis_interval) + 1;
      } else {
        var axis_min_trend = 0;
        var axis_min_gesamt = 0;
        var axis_interval = axis_max_gesamt / 10;
        var zero_label = 0;
        var max_label = 10
      }

      var option = {
        textStyle: { fontFamily: font_family },
        title: [
          {
            text: data['Gesamt'].slice(-1).toLocaleString(),
            subtext: 'Gesamt',
            top: 30,
            left: '3%',
            textStyle: { color: colors[0], fontSize: header_size },
            subtextStyle: { color: colors[0], fontSize: text_size }
          },
          {
            text: plus_minus(data['Entwicklung'].slice(-1).toLocaleString()),
            subtext: 'letztes Jahr',
            top: 90,
            left: '3%',
            textStyle: { color: colors[1], fontSize: header_size },
            subtextStyle: { color: colors[1], fontSize: text_size }
          },

        ],
        grid: {
          show: true,
          backgroundColor: '#FAFAFB',
          left: '20%',
          width: '80%',
          top: 6,
          bottom: 0,
          containLabel: true
        },
        tooltip: {
          trigger: 'axis',
          valueFormatter: (value) => value.toLocaleString(),
          backgroundColor: '#FAFAFB',
        },
        xAxis: [
          {
            type: 'category',
            boundaryGap: false,
            axisLabel: {
              formatter: function (value, index) {
                if (index === 0 || index === year_diff) { // Display only first and last labels
                  return value;
                } else {
                  return '';
                }
              }
            },
            data: data['Jahr']
          }
        ],
        yAxis: [
          {
            type: 'value',
            splitLine: {
              show: false
            },
            axisLabel: {
              formatter: function (value, index) {
                if (index === zero_label || index === max_label) { // Display only first and last labels
                  return value.toLocaleString();
                } else {
                  return '';
                }
              }
            },
            min: axis_min_gesamt,
            max: axis_max_gesamt,
            interval: axis_interval,
            axisTick: {
              inside: true
            },
          },
          {
            type: 'value',
            show: false,
            position: 'right',
            min: axis_min_trend,
            max: axis_max_trend,
            splitLine: {
              show: false
            }
          }
        ],
        series: [
          {
            name: 'Gesamt',
            type: 'line',
            itemStyle: { color: colors[2] },
            smooth: true,
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data['Jahr'].slice(-1).toString(), data['Gesamt'].slice(-1)], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label except for the last symbol
              },
              itemStyle: {
                color: colors[2] // Customize symbol color
              }
            },
            label: {
              show: true,
              position: 'top'
            },
            lineStyle: {
              color: colors[2],
              width: 1
            },
            areaStyle: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                {
                  offset: 0,
                  color: 'rgba(103, 197, 240, 1)'
                },
                {
                  offset: 1,
                  color: 'rgba(103, 197, 240, 0)'
                }
              ])
            },
            emphasis: {
              focus: 'series'
            },
            data: data['Gesamt']
          },
          {
            name: 'Entwicklung',
            type: 'line',
            itemStyle: { color: colors[3] },
            smooth: true,
            yAxisIndex: 1,
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data['Jahr'].slice(-1).toString(), data['Entwicklung'].slice(-1)], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                show: false // Hide label for the last symbol
              },
              itemStyle: {
                color: colors[3] // Customize symbol color
              }
            },
            label: {
              show: true,
              position: 'top'
            },
            lineStyle: {
              color: colors[3],
              width: 1
            },
            areaStyle: {
              color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                {
                  offset: 0,
                  color: 'rgba(234, 124, 243, 1)'
                },
                {
                  offset: 1,
                  color: 'rgba(234, 124, 243, 0)'
                }
              ])
            },
            emphasis: {
              focus: 'series'
            },
            data: data['Entwicklung']//, 868]
          }
        ]
      };

      // Display the chart using the configuration items and data just specified.
      setOption(option);
    })
  }
})
</script>
