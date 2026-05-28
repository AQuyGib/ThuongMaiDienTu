<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-black text-slate-900 text-lg">Biểu đồ theo ngày</h2>
                <p class="text-xs text-slate-500">30 ngày gần nhất</p>
            </div>
        </div>
        <canvas id="dailyNotificationsChart" height="180"></canvas>
    </div>
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-black text-slate-900 text-lg">Biểu đồ theo tháng</h2>
                <p class="text-xs text-slate-500">12 tháng gần nhất</p>
            </div>
        </div>
        <canvas id="monthlyNotificationsChart" height="180"></canvas>
    </div>
</div>
