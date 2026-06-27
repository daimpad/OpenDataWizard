<template>
  <div ref="chartContainer" style="width: 100%; height: 400px;"></div>
</template>

<script>
import { defineComponent } from 'vue';
import * as echarts from 'echarts';

// Import bar charts, all suffixed with Chart
import { BarChart } from 'echarts/charts';

// Import the tooltip, title, rectangular coordinate system, dataset and transform components
import {
  TitleComponent,
  TooltipComponent,
  GridComponent,
  DatasetComponent,
  TransformComponent
} from 'echarts/components';

// Features like Universal Transition and Label Layout
import { LabelLayout, UniversalTransition } from 'echarts/features';

// Import the Canvas renderer
// Note that including the CanvasRenderer or SVGRenderer is a required step
import { CanvasRenderer } from 'echarts/renderers';

// Register the required components
echarts.use([
  BarChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  DatasetComponent,
  TransformComponent,
  LabelLayout,
  UniversalTransition,
  CanvasRenderer
]);

export default defineComponent({
  name: 'EChartsDiagram',
  mounted() {
    this.initChart();
  },
  methods: {
    initChart() {
      const chartDom = this.$refs.chartContainer;
      const myChart = echarts.init(chartDom);

      // Specify the configuration items and data for the chart
      const option = {
        color: ['#009FE3', '#003F6F'],
        title: {
          text: 'Strom aus erneuerbaren Energien nach Energieträger (Hilpoltstein) [%]'
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          }
        },
        legend: {
          bottom: 0
        },
        grid: {
          left: '3%',
          right: '4%',
          bottom: '10%',
          containLabel: true
        },
        xAxis: {
          type: 'value',
          name: '%',
          boundaryGap: [0, 0.01]
        },
        yAxis: {
          type: 'category',
          data: ['Wasserkraft', 'Photovoltaik', 'Biomasse', 'Windenergie']
        },
        series: [
          {
            name: 'Gemeinde',
            type: 'bar',
            data: [15.2, 55.7, 5.4, 23.6]
          },
          {
            name: 'Landkreis',
            type: 'bar',
            data: [8.4, 62, 13.8, 15.9]
          }
        ]
      };

      myChart.setOption(option);
    }
  }
});
</script>
