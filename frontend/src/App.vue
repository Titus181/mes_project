<template>
  <div id="app">
    <header>
      <h1>製造実行システム (MES) ダッシュボード</h1>
    </header>
    <main v-if="!loading && dashboardData">
      <section class="overview">
        <div class="card">
          <h2>稼働率 (OEE)</h2>
          <p class="metric">{{ dashboardData.oee }}%</p>
        </div>
        <div class="card">
          <h2>良品率</h2>
          <p class="metric">{{ dashboardData.yield_rate }}%</p>
        </div>
        <div class="card">
          <h2>実行中の作業指示</h2>
          <p class="metric">{{ activeWorkOrdersCount }}</p>
        </div>
         <div class="card">
          <h2>アラート中の設備</h2>
          <p class="metric-alert">{{ alertMachinesCount }}</p>
        </div>
      </section>

      <section class="main-content">
        <div class="card full-width">
          <h2>設備ステータス</h2>
          <div class="machine-grid">
            <div v-for="machine in dashboardData.machines" :key="machine.id" :class="['machine-item', machine.status]">
              <h3>{{ machine.name }}</h3>
              <p>状態: <span class="status-text">{{ translateStatus(machine.status) }}</span></p>
              <p>指示書: {{ machine.current_work_order_id || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <div class="card full-width">
          <h2>作業指示書リスト (進行中/待機中)</h2>
          <table>
            <thead>
              <tr>
                <th>指示書番号</th>
                <th>製品名</th>
                <th>目標数量</th>
                <th>完了数量</th>
                <th>進捗</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in dashboardData.work_orders" :key="order.id">
                <td>{{ order.order_number }}</td>
                <td>{{ order.product_name }}</td>
                <td>{{ order.target_quantity }}</td>
                <td>{{ order.completed_quantity }}</td>
                <td>
                   <progress :value="order.completed_quantity" :max="order.target_quantity"></progress>
                   {{ calculateProgress(order) }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
    <div v-else class="loading">
      データをロード中...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios'; // 

// 狀態變數
const dashboardData = ref(null);
const loading = ref(true);
const error = ref(null);

// PHP API 的基礎 URL
// 開發時指向你的本地 PHP 伺服器，部署後需要改成線上 API 的 URL
const API_BASE_URL = 'http://localhost/mes_project/backend';

// 從 API 獲取數據的函式
const fetchData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${API_BASE_URL}/api/get_dashboard_data.php`);
    dashboardData.value = response.data;
  } catch (err) {
    error.value = 'データの取得に失敗しました。';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

// 計算屬性
const activeWorkOrdersCount = computed(() => {
  return dashboardData.value?.work_orders.filter(o => o.status === 'in_progress').length || 0;
});

const alertMachinesCount = computed(() => {
  return dashboardData.value?.machines.filter(m => m.status === 'error').length || 0;
});

// 方法
const translateStatus = (status) => {
  const map = {
    running: '稼働中',
    idle: '待機中',
    error: '異常',
    maintenance: 'メンテナンス中',
  };
  return map[status] || status;
};

const calculateProgress = (order) => {
  if (!order.target_quantity) return 0;
  return Math.round((order.completed_quantity / order.target_quantity) * 100);
}

// 元件掛載時自動獲取數據，並設定每 10 秒刷新一次
onMounted(() => {
  fetchData();
  setInterval(fetchData, 10000); // 10秒ごとにデータを更新
});
</script>

<style>
/* 加上一些基本的 CSS 讓畫面好看 */
body { font-family: 'Meiryo', 'Hiragino Sans', sans-serif; background-color: #f0f2f5; margin: 0; }
#app { padding: 20px; }
header h1 { text-align: center; color: #333; }

.overview { display: flex; justify-content: space-around; gap: 20px; margin-bottom: 20px; }
.card { background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; flex-grow: 1; }
.card h2 { margin-top: 0; font-size: 1rem; color: #666; }
.metric { font-size: 2.5rem; font-weight: bold; text-align: center; color: #00529B; margin: 0; }
.metric-alert { font-size: 2.5rem; font-weight: bold; text-align: center; color: #D8000C; margin: 0; }

.main-content { display: flex; flex-direction: column; gap: 20px; }
.full-width { width: 100%; box-sizing: border-box; }

.machine-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
.machine-item { border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
.machine-item h3 { margin: 0 0 10px 0; }
.machine-item .status-text { font-weight: bold; }

/* 根據狀態改變顏色 */
.machine-item.running { border-left: 5px solid #28a745; } /* 綠色 */
.machine-item.idle { border-left: 5px solid #ffc107; } /* 黃色 */
.machine-item.error { border-left: 5px solid #dc3545; background-color: #ffe0e3; } /* 紅色 */
.machine-item.maintenance { border-left: 5px solid #17a2b8; } /* 藍色 */

table { width: 100%; border-collapse: collapse; }
th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
th { background-color: #f8f9fa; }
progress { width: 100%; }

.loading { text-align: center; font-size: 1.5rem; color: #666; padding-top: 50px; }
</style>