<template>
  <div id="visualization_container">
    <div id="visualization_tooltip" style="opacity: 0; position: fixed;">
      <div class="visualization_tooltip by-copy-small-regular"></div>
    </div>
    <svg id="visualization_svg"></svg>
  </div>
</template>

<script>
// @ts-nocheck
import * as d3 from 'd3'

export default {
  name: 'D3Visualization',
  props: {
    data: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      dataSvgs: {
        "ENVI": 'M247.63,39.89a8,8,0,0,0-7.52-7.52c-51.76-3-93.32,12.74-111.18,42.22-11.8,19.49-11.78,43.16-.16,65.74a71.34,71.34,0,0,0-14.17,27L98.33,151c7.82-16.33,7.52-33.35-1-47.49-13.2-21.79-43.67-33.47-81.5-31.25a8,8,0,0,0-7.52,7.52c-2.23,37.83,9.46,68.3,31.25,81.5A45.82,45.82,0,0,0,63.44,168,54.58,54.58,0,0,0,87,162.33l25,25V216a8,8,0,0,0,16,0V186.51a55.61,55.61,0,0,1,12.27-35,73.91,73.91,0,0,0,33.31,8.4,60.9,60.9,0,0,0,31.83-8.86C234.89,133.21,250.67,91.65,247.63,39.89ZM47.81,147.6C32.47,138.31,23.79,116.32,24,88c28.32-.24,50.31,8.47,59.6,23.81,4.85,8,5.64,17.33,2.46,26.94L61.65,114.34a8,8,0,0,0-11.31,11.31l24.41,24.41C65.14,153.24,55.82,152.45,47.81,147.6Zm149.31-10.22c-13.4,8.11-29.15,8.73-45.15,2l53.69-53.7a8,8,0,0,0-11.31-11.31L140.65,128c-6.76-16-6.15-31.76,2-45.15,13.94-23,47-35.82,89.33-34.83C232.94,90.34,220.14,123.44,197.12,137.38Z',
        "GOVE": 'M24,104H48v64H32a8,8,0,0,0,0,16H224a8,8,0,0,0,0-16H208V104h24a8,8,0,0,0,4.19-14.81l-104-64a8,8,0,0,0-8.38,0l-104,64A8,8,0,0,0,24,104Zm40,0H96v64H64Zm80,0v64H112V104Zm48,64H160V104h32ZM128,41.39,203.74,88H52.26ZM248,208a8,8,0,0,1-8,8H16a8,8,0,0,1,0-16H240A8,8,0,0,1,248,208Z',
        "REGI": 'M240,208H224V96a16,16,0,0,0-16-16H144V32a16,16,0,0,0-24.88-13.32L39.12,72A16,16,0,0,0,32,85.34V208H16a8,8,0,0,0,0,16H240a8,8,0,0,0,0-16ZM208,96V208H144V96ZM48,85.34,128,32V208H48ZM112,112v16a8,8,0,0,1-16,0V112a8,8,0,1,1,16,0Zm-32,0v16a8,8,0,0,1-16,0V112a8,8,0,1,1,16,0Zm0,56v16a8,8,0,0,1-16,0V168a8,8,0,0,1,16,0Zm32,0v16a8,8,0,0,1-16,0V168a8,8,0,0,1,16,0Z',
        "TECH": 'M221.69,199.77,160,96.92V40h8a8,8,0,0,0,0-16H88a8,8,0,0,0,0,16h8V96.92L34.31,199.77A16,16,0,0,0,48,224H208a16,16,0,0,0,13.72-24.23ZM110.86,103.25A7.93,7.93,0,0,0,112,99.14V40h32V99.14a7.93,7.93,0,0,0,1.14,4.11L183.36,167c-12,2.37-29.07,1.37-51.75-10.11-15.91-8.05-31.05-12.32-45.22-12.81ZM48,208l28.54-47.58c14.25-1.74,30.31,1.85,47.82,10.72,19,9.61,35,12.88,48,12.88a69.89,69.89,0,0,0,19.55-2.7L208,208Z',
        "ENER": 'M215.79,118.17a8,8,0,0,0-5-5.66L153.18,90.9l14.66-73.33a8,8,0,0,0-13.69-7l-112,120a8,8,0,0,0,3,13l57.63,21.61L88.16,238.43a8,8,0,0,0,13.69,7l112-120A8,8,0,0,0,215.79,118.17ZM109.37,214l10.47-52.38a8,8,0,0,0-5-9.06L62,132.71l84.62-90.66L136.16,94.43a8,8,0,0,0,5,9.06l52.8,19.8Z',
        "TRAN": 'M184,24H72A32,32,0,0,0,40,56V184a32,32,0,0,0,32,32h8L65.6,235.2a8,8,0,1,0,12.8,9.6L100,216h56l21.6,28.8a8,8,0,1,0,12.8-9.6L176,216h8a32,32,0,0,0,32-32V56A32,32,0,0,0,184,24ZM56,120V80h64v40Zm80-40h64v40H136ZM72,40H184a16,16,0,0,1,16,16v8H56V56A16,16,0,0,1,72,40ZM184,200H72a16,16,0,0,1-16-16V136H200v48A16,16,0,0,1,184,200ZM96,172a12,12,0,1,1-12-12A12,12,0,0,1,96,172Zm88,0a12,12,0,1,1-12-12A12,12,0,0,1,184,172Z',
        "AGRI": 'M208,56a87.53,87.53,0,0,0-31.85,6c-14.32-29.7-43.25-44.46-44.57-45.12a8,8,0,0,0-7.16,0c-1.33.66-30.25,15.42-44.57,45.12A87.53,87.53,0,0,0,48,56a8,8,0,0,0-8,8v80a88,88,0,0,0,176,0V64A8,8,0,0,0,208,56ZM120,215.56A72.1,72.1,0,0,1,56,144V128.44A72.1,72.1,0,0,1,120,200Zm0-66.1a88,88,0,0,0-64-37.09V72.44A72.1,72.1,0,0,1,120,144ZM94.15,69.11c9.22-19.21,26.41-31.33,33.85-35.9,7.44,4.58,24.63,16.7,33.84,35.9A88.61,88.61,0,0,0,128,107.36,88.57,88.57,0,0,0,94.15,69.11ZM200,144a72.1,72.1,0,0,1-64,71.56V200a72.1,72.1,0,0,1,64-71.56Zm0-31.63a88,88,0,0,0-64,37.09V144a72.1,72.1,0,0,1,64-71.56Z',
        "ECON": 'M190,192.33a8,8,0,0,1-.63,11.3A80,80,0,0,1,56.4,152H40a8,8,0,0,1,0-16H56V120H40a8,8,0,0,1,0-16H56.4A80,80,0,0,1,189.34,52.37,8,8,0,0,1,178.66,64.3,64,64,0,0,0,72.52,104H136a8,8,0,0,1,0,16H72v16h48a8,8,0,0,1,0,16H72.52a64,64,0,0,0,106.14,39.71A8,8,0,0,1,190,192.33Z',
        "HEAL": 'M72,136H32a8,8,0,0,1,0-16H67.72L81.34,99.56a8,8,0,0,1,13.32,0l25.34,38,9.34-14A8,8,0,0,1,136,120h24a8,8,0,0,1,0,16H140.28l-13.62,20.44a8,8,0,0,1-13.32,0L88,118.42l-9.34,14A8,8,0,0,1,72,136ZM178,32c-20.65,0-38.73,8.88-50,23.89C116.73,40.88,98.65,32,78,32A62.07,62.07,0,0,0,16,94c0,.75,0,1.5,0,2.25a8,8,0,1,0,16-.5c0-.58,0-1.17,0-1.75A46.06,46.06,0,0,1,78,48c19.45,0,35.78,10.36,42.6,27a8,8,0,0,0,14.8,0c6.82-16.67,23.15-27,42.6-27a46.06,46.06,0,0,1,46,46c0,53.61-77.76,102.15-96,112.8-10.83-6.31-42.63-26-66.68-52.21a8,8,0,1,0-11.8,10.82c31.17,34,72.93,56.68,74.69,57.63a8,8,0,0,0,7.58,0C136.21,220.66,240,164,240,94A62.07,62.07,0,0,0,178,32Z',
        "EDUC": 'M251.76,88.94l-120-64a8,8,0,0,0-7.52,0l-120,64a8,8,0,0,0,0,14.12L32,117.87v48.42a15.91,15.91,0,0,0,4.06,10.65C49.16,191.53,78.51,216,128,216a130,130,0,0,0,48-8.76V240a8,8,0,0,0,16,0V199.51a115.63,115.63,0,0,0,27.94-22.57A15.91,15.91,0,0,0,224,166.29V117.87l27.76-14.81a8,8,0,0,0,0-14.12ZM128,200c-43.27,0-68.72-21.14-80-33.71V126.4l76.24,40.66a8,8,0,0,0,7.52,0L176,143.47v46.34C163.4,195.69,147.52,200,128,200Zm80-33.75a97.83,97.83,0,0,1-16,14.25V134.93l16-8.53ZM188,118.94l-.22-.13-56-29.87a8,8,0,0,0-7.52,14.12L171,128l-43,22.93L25,96,128,41.07,231,96Z',
        "SOCI": 'M244.8,150.4a8,8,0,0,1-11.2-1.6A51.6,51.6,0,0,0,192,128a8,8,0,0,1-7.37-4.89,8,8,0,0,1,0-6.22A8,8,0,0,1,192,112a24,24,0,1,0-23.24-30,8,8,0,1,1-15.5-4A40,40,0,1,1,219,117.51a67.94,67.94,0,0,1,27.43,21.68A8,8,0,0,1,244.8,150.4ZM190.92,212a8,8,0,1,1-13.84,8,57,57,0,0,0-98.16,0,8,8,0,1,1-13.84-8,72.06,72.06,0,0,1,33.74-29.92,48,48,0,1,1,58.36,0A72.06,72.06,0,0,1,190.92,212ZM128,176a32,32,0,1,0-32-32A32,32,0,0,0,128,176ZM72,120a8,8,0,0,0-8-8A24,24,0,1,1,87.24,82a8,8,0,1,0,15.5-4A40,40,0,1,0,37,117.51,67.94,67.94,0,0,0,9.6,139.19a8,8,0,1,0,12.8,9.61A51.6,51.6,0,0,1,64,128,8,8,0,0,0,72,120Z',
        "INTR": 'M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24ZM101.63,168h52.74C149,186.34,140,202.87,128,215.89,116,202.87,107,186.34,101.63,168ZM98,152a145.72,145.72,0,0,1,0-48h60a145.72,145.72,0,0,1,0,48ZM40,128a87.61,87.61,0,0,1,3.33-24H81.79a161.79,161.79,0,0,0,0,48H43.33A87.61,87.61,0,0,1,40,128ZM154.37,88H101.63C107,69.66,116,53.13,128,40.11,140,53.13,149,69.66,154.37,88Zm19.84,16h38.46a88.15,88.15,0,0,1,0,48H174.21a161.79,161.79,0,0,0,0-48Zm32.16-16H170.94a142.39,142.39,0,0,0-20.26-45A88.37,88.37,0,0,1,206.37,88ZM105.32,43A142.39,142.39,0,0,0,85.06,88H49.63A88.37,88.37,0,0,1,105.32,43ZM49.63,168H85.06a142.39,142.39,0,0,0,20.26,45A88.37,88.37,0,0,1,49.63,168Zm101.05,45a142.39,142.39,0,0,0,20.26-45h35.43A88.37,88.37,0,0,1,150.68,213Z',
        "JUST": 'M239.43,133l-32-80h0a8,8,0,0,0-9.16-4.84L136,62V40a8,8,0,0,0-16,0V65.58L54.26,80.19A8,8,0,0,0,48.57,85h0v.06L16.57,165a7.92,7.92,0,0,0-.57,3c0,23.31,24.54,32,40,32s40-8.69,40-32a7.92,7.92,0,0,0-.57-3L66.92,93.77,120,82V208H104a8,8,0,0,0,0,16h48a8,8,0,0,0,0-16H136V78.42L187,67.1,160.57,133a7.92,7.92,0,0,0-.57,3c0,23.31,24.54,32,40,32s40-8.69,40-32A7.92,7.92,0,0,0,239.43,133ZM56,184c-7.53,0-22.76-3.61-23.93-14.64L56,109.54l23.93,59.82C78.76,180.39,63.53,184,56,184Zm144-32c-7.53,0-22.76-3.61-23.93-14.64L200,77.54l23.93,59.82C222.76,148.39,207.53,152,200,152Z',
        // Default icon for grouped small categories
        "": 'M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm12-88a12,12,0,1,1-12-12A12,12,0,0,1,140,128Zm44,0a12,12,0,1,1-12-12A12,12,0,0,1,184,128Zm-88,0a12,12,0,1,1-12-12A12,12,0,0,1,96,128Z'
      }  // Copy the dataSvgs object from the original code here
    }
  },
  mounted() {
    this.renderChart()
  },
  created() {
    window.addEventListener("resize", this.renderChart);
  },
  destroyed() {
    window.removeEventListener("resize", this.renderChart);
  },
  watch: {
    data: {
      deep: true,
      handler() {
        this.renderChart()
      }
    }
  },
  methods: {
    renderChart() {
      this.stackedBar(this.data)
    },
    stackedBar(data, {
      width = document.getElementById("visualization_svg").getBoundingClientRect().width,
      height = document.getElementById("visualization_svg").getBoundingClientRect().height,
      barHeight = 10,
      halfBarHeight = barHeight / 2,
      f = d3.format('.1f'),
      margin = {top: 20, right: 10, bottom: 20, left: 10},
      w = width - margin.left - margin.right,
      h = height * 0.66
    } = {}) {

      // Generate the total of values once to be used for relative filtering
      let total = d3.sum(data, d => d.value);

      // filter data regarding minimum value 2,5% and width of 30px
      let graph_categories = data.map(d => {
        return d;
      }).filter(d => (d.value > (total * 0.025) && (d.value / total * w) > 30));

      // Group all data that is smaller into a bigger "other" group
      const remaining_other_data = data.filter(d => !graph_categories.includes(d));
      const total_of_other_data = d3.sum(remaining_other_data, d => d.value);
      const other_category = {
        value: total_of_other_data, short: "", long: "Other"
      }
      graph_categories.push(other_category)

      // Format the data (instead of using d3.stack()):
      function groupDataFunc(data) {
        // use a scale to get percentage values
        const percent = d3.scaleLinear()
          .domain([0, total])
          .range([0, 100])
        // filter out data that has zero values
        // also get mapping for next placement
        // (save having to format data for d3 stack)
        let cumulative = 0
        return data.map(d => {
          cumulative += d.value
          return {
            value: d.value,
            // want the cumulative to prior value (start of rect)
            cumulative: cumulative - d.value,
            label: d.label,
            percent: percent(d.value),
            short: d.short,
            long: d.long
          }
        })
      }

      const groupData = groupDataFunc(graph_categories);
      const svg = document.getElementById("visualization_svg");
      svg.innerHTML = "" // Clears for repainting on resize
      const sel = d3.select(svg);

      // set up scales for horizontal placement
      const xScale = d3.scaleLinear()
        .domain([0, total])
        .range([0, w]);

      // Generate outline for the visualization
      const join = sel.selectAll('g')
        .data(groupData)
        .join('g')
        .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')')
        .append('a')
        .attr('class', 'visualization_bar')
        .attr('href', d => ('/datasets?categories=' + d.short))
        .attr('title', d => (d.long))
        .on('mouseover', function (event, d) {
          const tooltip = d3.select('#visualization_tooltip');
          // Set the text content, thereby updating the width and height
          tooltip.select("div").html(`${d.long}`)

          // Calculate the boxes and center of the rect-anchor
          const rectBoundingBox = this.getBoundingClientRect();
          const tooltipBoundingBox = document.getElementById("visualization_tooltip").querySelector("div").getBoundingClientRect();
          const rectCenterX = rectBoundingBox.left + rectBoundingBox.width / 2;
          let tooltipLeft = rectCenterX - tooltipBoundingBox.width / 2;

          //if ((tooltipLeft + tooltipBoundingBox.width) > window.innerWidth) // Out on the right
          //  tooltipLeft = -tooltipBoundingBox.width;  // Simulate a "right: tipX" position
          const tooltipTop = rectBoundingBox.bottom + 12;

          // Show the tooltip
          tooltip
            .style('left', tooltipLeft + 'px')
            .style('top', tooltipTop + 'px')
            .style('width', tooltipBoundingBox.width + "px")
            .style('opacity', 1);
        })
        .on('mouseout', function () {
          // Hide the tooltip on mouseout
          const tooltip = d3.select('#visualization_tooltip');
          tooltip.style('opacity', 0);
        });


      // add clickable area
      join.append('rect')
        .attr('class', 'rect-anchor')
        .attr('width', d => xScale(d.value) - 5)
        .attr('height', 50)
        .attr('y', 50)
        .attr('x', d => xScale(d.cumulative))

      // add bar and icon for each category
      join.append('rect')
        .attr('class', 'rect-stacked')
        .attr('x', d => xScale(d.cumulative))
        .attr('y', h / 2 - halfBarHeight)
        .attr('height', barHeight)
        .attr('width', d => xScale(d.value) - 5);


      join.append('path')
        .attr('d', d => this.dataSvgs[d.short])
        .attr('class', 'visualization_icon')
        .attr('transform', function (d) {
          var x = xScale(d.cumulative) + (xScale(d.value) / 2) - 12;
          var y = (h / 2) + (halfBarHeight * 1.1) + 20;
          return 'translate(' + x + ',' + y + ') scale(0.08)';
        });

      return svg;
    }
  },
  beforeDestroy() {
    // Any cleanup logic if necessary
  }
}
</script>

<style>
/* VISUALISIERUNG CSS */
svg#visualization_svg a.visualization_bar:hover rect.rect-stacked,
svg#visualization_svg a.visualization_bar:focus rect.rect-stacked,
svg#visualization_svg a.visualization_bar:hover path.visualization_icon,
svg#visualization_svg a.visualization_bar:focus path.visualization_icon {
  fill: #67C5F0;
  cursor: pointer;
}

#visualization_container, #visualization_svg {
  width: 100%;
}


svg#visualization_svg rect.rect-stacked {
  fill: #009FE3;
  height: 8px;
}

svg#visualization_svg rect.rect-anchor {
  fill: transparent;
}

svg#visualization_svg path.visualization_icon {
  fill: #009FE3;
}

.visualization_tooltip {
  margin-right: auto;
  margin-left: auto;
  width: fit-content;
  color: white;
  background-color: #003F6F;
  text-align: center;
  padding: 8px 16px 8px 16px;
  border-radius: 6px;
}

.visualization_tooltip::after {
  content: " ";
  position: absolute;
  top: -12px; /* At the bottom of the tooltip */
  left: 50%;
  margin-left: -6px;
  border-width: 6px;
  border-style: solid;
  border-color: transparent transparent #003F6F transparent;
}

/* VISUALISIERUNG CSS */

</style>
