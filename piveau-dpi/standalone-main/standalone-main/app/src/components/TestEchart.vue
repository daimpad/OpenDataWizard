<template>
  <div id="echart" >

  </div>
</template>

<script>
import { defineComponent } from 'vue'
import * as echarts from 'echarts'

export default defineComponent({
  mounted() {
          // Initialize the echarts instance based on the prepared dom
      var myChart = echarts.init(document.getElementById('echart'));

      // Specify the configuration items and data for the chart
      var colors = ['#0172AD', '#872E8F', '#009FE3', '#C44BCE'];

      var header_size = 20

      var text_size = 15

      var font_family = 'Inter Variable'

      var data = {'Jahr': [2013, 2014, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023], 'Gesamt': [128045, 129370, 131405, 132140, 133974, 135538, 137408, 136831, 137314, 138537, 141515], 'Entwicklung': [1506, 1325, 2035, 735, 1834, 1564, 1870, -577, 483, 1223, 2978], 'link': 'https://open.bydata.de/datasets/12411-000-d?locale=de'}
      //{'Jahr':['2016','2017','2018','2019','2020','2021','2022'],
                  //'Gesamt':[133639, 135244, 136981, 137392, 136952, 138016, 141029],
                  //'Entwicklung':[1030, 1321, 1386, 97, -611, 905, 2977],
                  //'Gesamt':[133639, 135244, 136981, 137392, 136952, 138016, 141029],
                  //'Entwicklung':[4, 6, 12, 1, -2920, 8, 12],
                  //'link':'https://open.bydata.de/datasets/12411-000-d?locale=de'}

      function plus_minus(dataValue) {
          if (dataValue >= 0) {
              return '+' + dataValue;
          } else {
              return dataValue;
          }
      }

      var axis_max_trend = Math.ceil(Math.max(...data['Entwicklung'])/5000)*10000

      var axis_min_trend = Math.floor(Math.min(...data['Entwicklung'])/2000)*2000 //-2000

      var axis_max_gesamt = Math.ceil(Math.max(...data['Gesamt'])/50000)*50000

      if (axis_min_trend < 0) {
          var axis_min_gesamt = axis_min_trend * axis_max_gesamt / axis_max_trend;
          var axis_interval = Math.abs(axis_min_gesamt);
          var zero_label = 1;
          var max_label = Math.floor(axis_max_gesamt/axis_interval) + 1
      } else {
          var axis_min_gesamt = 0;
          var axis_interval = axis_max_gesamt/10
          var zero_label = 0;
          var max_label = 10
      }

      var option = {
          textStyle: {fontFamily: font_family},
        title: [{
            text: 'Bevölkerungsentwicklung',
            textStyle: {fontSize: header_size}
          },
          {
            text: data['Gesamt'].slice(-1).toLocaleString(),//'141.029',
            subtext: 'Gesamt',
            top: 90,
            left: '3%',
            textStyle:{color: colors[0], fontSize: header_size},
            subtextStyle:{color: colors[0], fontSize: text_size}
          },
          {
            text: plus_minus(data['Entwicklung'].slice(-1).toLocaleString()),//'+3.239',
            subtext: 'letztes Jahr',
            top: 160,
            left: '3%',
            textStyle:{color: colors[1], fontSize: header_size},
            subtextStyle:{color: colors[1], fontSize: text_size}
          },

          //Link
          {
              text: 'zum Datensatz >',
              bottom: '5%',
              right: '12%',
              textStyle: {color: '#0172AD', fontSize: text_size},
              link: data['link']
            }
            ],
        grid: {
            show: true,
            backgroundColor: '#FAFAFB',
            height: 224,
            width: '80%',//243,
            right: '0%',
            top: '15%',
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
                  if (index === 0 || index === 10) { // Display only first and last labels
                      return value;
                  } else {
                      return '';
                  }
              }
            },
            data: data['Jahr']//, '2023']
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
            interval: axis_interval
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
            itemStyle: {color: colors[2]},
            smooth: true,
            showSymbol: false,
            markPoint: {
              animationDelay: 500,
              data: [{ coord: [data['Jahr'].slice(-1).toString(), data['Gesamt'].slice(-1)], name: 'Last Symbol' }],
              symbol: 'circle', // Set symbol shape
              symbolSize: 6, // Set symbol size
              label: {
                  show: false // Hide label for the last symbol
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
            //data: [1030, 2351, 3737, 3834, 3223, 4128, 7105, 7973]
            data: data['Gesamt']
            },
            {
            name: 'Entwicklung',
            type: 'line',
            itemStyle: {color: colors[3]},
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
      myChart.setOption(option);
  }
})
</script>
