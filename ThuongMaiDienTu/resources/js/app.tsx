import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Demo from './components/Demo';
import UserManagement from './components/UserManagement';
import SessionManagement from './components/SessionManagement';
import KPIDashboard from './components/KPIDashboard';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Demo
    const demoContainer = document.getElementById('joly-demo');
    if (demoContainer) {
        const root = createRoot(demoContainer);
        root.render(<Demo />);
    }

    const userMgmtContainer = document.getElementById('admin-user-management');
    if (userMgmtContainer) {
        const root = createRoot(userMgmtContainer);
        const props = JSON.parse(userMgmtContainer.getAttribute('data-props') || '{}');
        root.render(<UserManagement {...props} />);
    }

    const sessionMgmtContainer = document.getElementById('admin-session-management');
    if (sessionMgmtContainer) {
        const root = createRoot(sessionMgmtContainer);
        const props = JSON.parse(sessionMgmtContainer.getAttribute('data-props') || '{}');
        root.render(<SessionManagement {...props} />);
    }

    // 4. KPI Dashboard
    const kpiDashboardContainer = document.getElementById('admin-kpi-dashboard');
    if (kpiDashboardContainer) {
        const root = createRoot(kpiDashboardContainer);
        try {
            const propsStr = kpiDashboardContainer.getAttribute('data-props');
            if (propsStr) {
                const props = JSON.parse(propsStr);
                root.render(<KPIDashboard {...props} />);
            }
        } catch (e) {
            console.error('Lỗi parse props KPIDashboard:', e);
            kpiDashboardContainer.innerHTML = `<div class="p-10 text-rose-500 font-bold">Lỗi cấu hình dữ liệu (JSON Parse Error): ${e.message}</div>`;
        }
    }
});
