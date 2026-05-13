import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';

// Lazy load all components to prevent one failure from breaking the whole app
import AdminSidebar from './components/AdminSidebar';

// Lazy load other components
const UserManagement = React.lazy(() => import('./components/UserManagement'));
const SessionManagement = React.lazy(() => import('./components/SessionManagement'));
const KPIDashboard = React.lazy(() => import('./components/KPIDashboard'));
const ThemeSettings = React.lazy(() => import('./components/ThemeSettings'));

const renderComponent = (id: string, Component: React.ElementType) => {
    const container = document.getElementById(id);
    if (!container) return;

    console.log(`[React] Mounting ${id}...`);
    const root = createRoot(container);
    
    try {
        const propsStr = container.getAttribute('data-props');
        const props = propsStr ? JSON.parse(propsStr) : {};
        
        root.render(
            <React.Suspense fallback={<div className="p-4 text-slate-500 animate-pulse text-xs uppercase font-black tracking-widest">Đang tải thành phần...</div>}>
                <Component {...props} />
            </React.Suspense>
        );
        console.log(`[React] ${id} Rendered.`);
    } catch (e) {
        console.error(`[React] Error mounting ${id}:`, e);
        container.innerHTML = `<div class="p-4 bg-rose-50 text-rose-500 border border-rose-200 rounded-xl text-[10px] font-bold">LỖI HỆ THỐNG: ${e.message}</div>`;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mount Admin Sidebar (High Priority - Direct Render)
    const sidebarContainer = document.getElementById('joly-admin-sidebar');
    if (sidebarContainer) {
        console.log('[React] Mounting AdminSidebar (Direct)...');
        const root = createRoot(sidebarContainer);
        try {
            const propsStr = sidebarContainer.getAttribute('data-props');
            const props = propsStr ? JSON.parse(propsStr) : {};
            root.render(<AdminSidebar {...props} />);
            console.log('[React] AdminSidebar Rendered.');
        } catch (e) {
            console.error('[React] Error mounting AdminSidebar:', e);
            sidebarContainer.innerHTML = `<div class="p-4 bg-rose-50 text-rose-500 border border-rose-200 rounded-xl text-[10px] font-bold">LỖI SIDEBAR: ${e.message}</div>`;
        }
    }

    // 2. Other components (Lazy Render)
    renderComponent('admin-user-management', UserManagement);
    renderComponent('admin-session-management', SessionManagement);
    renderComponent('admin-kpi-dashboard', KPIDashboard);
    renderComponent('admin-theme-settings', ThemeSettings);

    // Legacy / Demo
    const demoContainer = document.getElementById('joly-demo');
    if (demoContainer) {
        import('./components/Demo').then(({ default: Demo }) => {
            const root = createRoot(demoContainer);
            root.render(<Demo />);
        });
    }
});
