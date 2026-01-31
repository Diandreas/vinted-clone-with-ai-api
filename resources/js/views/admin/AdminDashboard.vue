<template>
  <div class="min-h-screen bg-gray-50 p-3 sm:p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Dashboard Admin</h1>

      <!-- KPI Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-4 mb-4 sm:mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Utilisateurs</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ kpis.users_total }}</div>
          <div class="text-[11px] text-green-600">Vérifiés: {{ kpis.users_verified }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Produits</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ kpis.products_total }}</div>
          <div class="text-[11px] text-green-600">Actifs: {{ kpis.products_active }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Commandes</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ kpis.orders_total }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Frais pub.</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ formatCurrency(kpis.publishing_fee_revenue) }}</div>
          <div class="text-[11px] text-green-600">Payés: {{ kpis.publishing_fee_paid_count }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Paiements OK</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ kpis.payments_completed }}</div>
          <div class="text-[11px] text-gray-500">Attente: {{ kpis.payments_pending }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-[10px] sm:text-sm text-gray-500 uppercase tracking-wide">Paiements KO</div>
          <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ kpis.payments_failed }}</div>
          <div class="text-[11px] text-gray-500">Produits: {{ kpis.products_pending_payment }}</div>
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
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Paiements (statuts)</div>
          <canvas ref="paymentsByStatusChart" height="160"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3 sm:p-4">
          <div class="text-xs sm:text-sm font-medium text-gray-900 mb-2">Revenus frais de publication</div>
          <canvas ref="feeRevenueByDayChart" height="160"></canvas>
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
  orders_total: 0,
  products_pending_payment: 0,
  publishing_fee_revenue: 0,
  publishing_fee_paid_count: 0,
  publishing_fee_pending_count: 0,
  publishing_fee_failed_count: 0,
  payments_completed: 0,
  payments_pending: 0,
  payments_failed: 0
})

const usersByDayChart = ref(null)
const productsByDayChart = ref(null)
const ordersByDayChart = ref(null)
const productsByStatusChart = ref(null)
const paymentsByStatusChart = ref(null)
const feeRevenueByDayChart = ref(null)

const getCanvasContext = (el) => {
  if (!el || typeof el.getContext !== 'function') return null
  return el.getContext('2d')
}

const buildLineChart = (el, labels, data, label) => {
  const ctx = getCanvasContext(el)
  if (!ctx) return null
  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label, data, borderColor: '#2f7f52', tension: 0.3 }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
  })
}

const buildPieChart = (el, labels, data) => {
  const ctx = getCanvasContext(el)
  if (!ctx) return null
  return new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: ['#22c55e','#525252','#737373','#3da066','#3da066'] }] },
    options: { responsive: true }
  })
}

const formatCurrency = (value) => {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA'
}

onMounted(async () => {
  try {
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

    // Payments charts
    const payments = await window.axios.get('/admin/analytics/payments')
    const pay = payments.data.data || {}
    const ps = pay.payments_by_status || {}
    buildPieChart(paymentsByStatusChart.value, Object.keys(ps), Object.values(ps))
    const feeByDay = pay.fee_revenue_by_day || []
    const fLabels = feeByDay.map(x => x.d)
    const fData = feeByDay.map(x => Number(x.s))
    buildLineChart(feeRevenueByDayChart.value, fLabels, fData, 'Frais publication')
  } catch (error) {
    console.error('Failed to load admin analytics:', error)
  }
})
</script>

<style scoped>
</style>

