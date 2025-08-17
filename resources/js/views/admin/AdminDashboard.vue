<template>
  <div class="min-h-screen bg-gray-50 p-3 sm:p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Dashboard Admin</h1>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm text-gray-500">Utilisateurs</div>
          <div class="text-xl sm:text-2xl font-semibold text-gray-900">{{ kpis.users_total }}</div>
          <div class="text-xs text-green-600">Vérifiés: {{ kpis.users_verified }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm text-gray-500">Produits</div>
          <div class="text-xl sm:text-2xl font-semibold text-gray-900">{{ kpis.products_total }}</div>
          <div class="text-xs text-green-600">Actifs: {{ kpis.products_active }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm text-gray-500">Commandes</div>
          <div class="text-xl sm:text-2xl font-semibold text-gray-900">{{ kpis.orders_total }}</div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Inscriptions utilisateurs</div>
          <canvas ref="usersByDayChart" height="160"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Créations produits</div>
          <canvas ref="productsByDayChart" height="160"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Commandes par jour</div>
          <canvas ref="ordersByDayChart" height="160"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Produits par statut</div>
          <canvas ref="productsByStatusChart" height="160"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Chart from 'chart.js/auto'

const kpis = ref({
  users_total: 0,
  users_verified: 0,
  products_total: 0,
  products_active: 0,
  orders_total: 0
})

const usersByDayChart = ref(null)
const productsByDayChart = ref(null)
const ordersByDayChart = ref(null)
const productsByStatusChart = ref(null)

const buildLineChart = (el, labels, data, label) => new Chart(el, {
  type: 'line',
  data: { labels, datasets: [{ label, data, borderColor: '#4f46e5', tension: 0.3 }] },
  options: { responsive: true, plugins: { legend: { display: false } } }
})

const buildPieChart = (el, labels, data) => new Chart(el, {
  type: 'doughnut',
  data: { labels, datasets: [{ data, backgroundColor: ['#22c55e','#ef4444','#f59e0b','#3b82f6','#6366f1'] }] },
  options: { responsive: true }
})

onMounted(async () => {
  // KPIs
  const ov = await window.axios.get('/admin/analytics/overview')
  Object.assign(kpis.value, ov.data.data || {})

  // Users chart
  const users = await window.axios.get('/admin/analytics/users')
  const u = users.data.data || {}
  const uLabels = (u.by_day || []).map(x => x.d)
  const uData = (u.by_day || []).map(x => x.c)
  buildLineChart(usersByDayChart.value, uLabels, uData, 'Users')

  // Products charts
  const prods = await window.axios.get('/admin/analytics/products')
  const p = prods.data.data || {}
  const pLabels = (p.by_day || []).map(x => x.d)
  const pData = (p.by_day || []).map(x => x.c)
  buildLineChart(productsByDayChart.value, pLabels, pData, 'Products')
  const byStatus = p.by_status || {}
  buildPieChart(productsByStatusChart.value, Object.keys(byStatus), Object.values(byStatus))

  // Orders chart
  const sales = await window.axios.get('/admin/analytics/sales')
  const s = sales.data.data || {}
  const oLabels = (s.orders_by_day || []).map(x => x.d)
  const oData = (s.orders_by_day || []).map(x => x.c)
  buildLineChart(ordersByDayChart.value, oLabels, oData, 'Orders')
})
</script>

<style scoped>
</style>




