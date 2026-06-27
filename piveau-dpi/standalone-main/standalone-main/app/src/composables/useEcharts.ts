import { toValue, type MaybeRefOrGetter } from '@vueuse/core';
import * as echarts from 'echarts';
import type { ECBasicOption } from 'echarts/types/dist/shared';
import { onBeforeUnmount } from 'vue';

/**
 * Custom composable function for using ECharts.
 * @param el - The element or a getter function that returns the element to attach the ECharts instance to.
 * @returns An object with a `setOption` function to set the chart option.
 */
export function useEcharts(el: MaybeRefOrGetter<HTMLElement>) {
  let chart: echarts.ECharts | null = null;
  const setOption = ((option: ECBasicOption) => {
    if (!chart) {
      chart = echarts.init(toValue(el));
    }
    chart.setOption(option);
  })
  onBeforeUnmount(() => {
    if (chart) {
      chart.dispose();
    }
  });
  return {
    setOption
  };
}
