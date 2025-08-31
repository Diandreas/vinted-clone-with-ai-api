<template>
  <div class="w-full h-80">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <LoaderIcon class="w-8 h-8 animate-spin text-gray-400" />
    </div>
    <canvas
      v-else
      ref="chartCanvas"
      class="w-full h-full"
    ></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { LoaderIcon } from 'lucide-vue-next'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  LineController
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  LineController
)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const chartCanvas = ref(null)
let chartInstance = null

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    legend: {
      position: 'top',
      align: 'end',
      labels: {
        usePointStyle: true,
        boxWidth: 6,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: 'white',
      bodyColor: 'white',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        label: function(context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.datasetIndex === 0) {
            // Sales data - format as currency
            label += new Intl.NumberFormat('fr-FR', {
              style: 'currency',
              currency: 'XAF'
            }).format(context.parsed.y);
          } else {
            // Orders data - format as number
            label += new Intl.NumberFormat('fr-FR').format(context.parsed.y);
          }
          return label;
        }
      }
    }
  },
  scales: {
    x: {
      display: true,
      grid: {
        display: false
      },
      ticks: {
        font: {
          size: 11
        },
        color: '#6b7280'
      }
    },
    y: {
      type: 'linear',
      display: true,
      position: 'left',
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      },
      ticks: {
        font: {
          size: 11
        },
        color: '#6b7280',
        callback: function(value) {
                      return new Intl.NumberFormat('fr-FR', {
              style: 'currency',
              currency: 'XAF',
              minimumFractionDigits: 0
            }).format(value);
        }
      }
    },
    y1: {
      type: 'linear',
      display: true,
      position: 'right',
      grid: {
        drawOnChartArea: false,
      },
      ticks: {
        font: {
          size: 11
        },
        color: '#6b7280',
        callback: function(value) {
          return new Intl.NumberFormat('fr-FR').format(value);
        }
      }
    }
  },
  elements: {
    point: {
      radius: 4,
      hoverRadius: 6,
      borderWidth: 2,
      backgroundColor: 'white'
    },
    line: {
      borderWidth: 2
    }
  }
}

const createChart = () => {
  if (!chartCanvas.value || !props.data.labels?.length) return

  const ctx = chartCanvas.value.getContext('2d')
  
  if (chartInstance) {
    chartInstance.destroy()
  }

  chartInstance = new ChartJS(ctx, {
    type: 'line',
    data: props.data,
    options: chartOptions
  })
}

const updateChart = () => {
  if (chartInstance && props.data.labels?.length) {
    chartInstance.data = props.data
    chartInstance.update('none')
  } else {
    createChart()
  }
}

// Watch for data changes
watch(() => props.data, () => {
  nextTick(() => {
    updateChart()
  })
}, { deep: true })

// Watch for loading state changes
watch(() => props.loading, (newLoading) => {
  if (!newLoading) {
    nextTick(() => {
      createChart()
    })
  }
})

onMounted(() => {
  if (!props.loading && props.data.labels?.length) {
    nextTick(() => {
      createChart()
    })
  }
})

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy()
  }
})
</script>

