import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';

// Lazy load all components to prevent one failure from breaking the whole app
import AdminSidebar from './components/AdminSidebar';
import AdminTopbar from './components/AdminTopbar';

// Lazy load other components
const UserManagement = React.lazy(() => import('./components/UserManagement'));
const SessionManagement = React.lazy(() => import('./components/SessionManagement'));
const KPIDashboard = React.lazy(() => import('./components/KPIDashboard'));
const ThemeSettings = React.lazy(() => import('./components/ThemeSettings'));

const mountedRoots = new Map<string, any>();

const renderComponent = (id: string, Component: React.ElementType) => {
    const container = document.getElementById(id);
    if (!container) return;

    // If already mounted, we might need to unmount or just skip if it's persistent
    // For now, let's just re-mount if it's a content component
    if (mountedRoots.has(id)) {
        try { mountedRoots.get(id).unmount(); } catch (e) { }
    }

    console.log(`[React] Mounting ${id}...`);
    const root = createRoot(container);
    mountedRoots.set(id, root);

    try {
        const propsStr = container.getAttribute('data-props');
        const props = propsStr ? JSON.parse(propsStr) : {};

        root.render(
            <React.Suspense fallback={<div className="p-10 flex items-center justify-center text-slate-400 animate-pulse text-xs uppercase font-black tracking-[0.2em]">Đang tải dữ liệu...</div>}>
                <Component {...props} />
            </React.Suspense>
        );
        console.log(`[React] ${id} Rendered.`);
    } catch (e) {
        console.error(`[React] Error mounting ${id}:`, e);
        container.innerHTML = `<div class="p-4 bg-rose-50 text-rose-500 border border-rose-200 rounded-xl text-[10px] font-bold">LỖI HỆ THỐNG: ${e.message}</div>`;
    }
};

const init = () => {
    // 1. Mount Admin Sidebar (High Priority - Direct Render)
    const sidebarContainer = document.getElementById('joly-admin-sidebar');
    if (sidebarContainer && !mountedRoots.has('joly-admin-sidebar')) {
        console.log('[React] Mounting AdminSidebar (Direct)...');
        const root = createRoot(sidebarContainer);
        mountedRoots.set('joly-admin-sidebar', root);
        try {
            const propsStr = sidebarContainer.getAttribute('data-props');
            const props = propsStr ? JSON.parse(propsStr) : {};
            root.render(<AdminSidebar {...props} />);
            console.log('[React] AdminSidebar Rendered.');
        } catch (e) {
            console.error('[React] Error mounting AdminSidebar:', e);
        }
    }

    // 2. Mount Admin Topbar (Direct Render)
    const topbarContainer = document.getElementById('joly-admin-topbar');
    if (topbarContainer && !mountedRoots.has('joly-admin-topbar')) {
        console.log('[React] Mounting AdminTopbar (Direct)...');
        const root = createRoot(topbarContainer);
        mountedRoots.set('joly-admin-topbar', root);
        try {
            const propsStr = topbarContainer.getAttribute('data-props');
            const props = propsStr ? JSON.parse(propsStr) : {};
            root.render(<AdminTopbar {...props} />);
            console.log('[React] AdminTopbar Rendered.');
        } catch (e) {
            console.error('[React] Error mounting AdminTopbar:', e);
        }
    }

    // 3. Other components (Lazy Render)
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
};

// --- SOFT NAVIGATION ENGINE ---
const updateProgressBar = (width: string) => {
    let bar = document.getElementById('spa-progress-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'spa-progress-bar';
        bar.className = 'spa-loading-progress';
        document.body.appendChild(bar);
    }
    bar.style.width = width;
    if (width === '100%') {
        setTimeout(() => {
            if (bar) bar.style.opacity = '0';
            setTimeout(() => bar?.remove(), 400);
        }, 300);
    }
};

const softNavigate = async (url: string) => {
    try {
        console.log(`[SPA] Navigating to ${url}...`);

        const contentArea = document.getElementById('joly-main-container');
        if (contentArea) {
            contentArea.classList.add('spa-content-fading');
        }

        updateProgressBar('30%');

        const response = await fetch(url);
        updateProgressBar('70%');

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // 1. Update Title
        document.title = doc.title;

        // 2. Update Content
        const newContent = doc.getElementById('joly-main-container');
        const currentContainer = document.getElementById('joly-main-container');
        if (newContent && currentContainer) {
            currentContainer.innerHTML = newContent.innerHTML;
            currentContainer.classList.remove('spa-content-fading');
            currentContainer.classList.add('spa-content-entering');

            // Clean up animation class after it finishes
            setTimeout(() => {
                currentContainer.classList.remove('spa-content-entering');
            }, 600);
        }

        // 3. Update Sidebar & Topbar Props (for active state and title)
        const newSidebar = doc.getElementById('joly-admin-sidebar');
        const currentSidebar = document.getElementById('joly-admin-sidebar');
        if (newSidebar && currentSidebar) {
            currentSidebar.setAttribute('data-props', newSidebar.getAttribute('data-props') || '{}');
            if (mountedRoots.has('joly-admin-sidebar')) {
                mountedRoots.get('joly-admin-sidebar').unmount();
                mountedRoots.delete('joly-admin-sidebar');
            }
        }

        const newTopbar = doc.getElementById('joly-admin-topbar');
        const currentTopbar = document.getElementById('joly-admin-topbar');
        if (newTopbar && currentTopbar) {
            currentTopbar.setAttribute('data-props', newTopbar.getAttribute('data-props') || '{}');
            if (mountedRoots.has('joly-admin-topbar')) {
                mountedRoots.get('joly-admin-topbar').unmount();
                mountedRoots.delete('joly-admin-topbar');
            }
        }

        // 4. Re-initialize components
        init();

        // 5. Finalize
        updateProgressBar('100%');
        const scrollContainer = document.getElementById('joly-main-content');
        if (scrollContainer) scrollContainer.scrollTop = 0;

        window.history.pushState({}, '', url);
        console.log(`[SPA] Navigation complete.`);
    } catch (e) {
        console.error(`[SPA] Navigation failed:`, e);
        updateProgressBar('100%');
        window.location.href = url; // Fallback to normal navigation
    }
};

// Intercept all admin clicks
document.addEventListener('click', (e) => {
    const target = e.target as HTMLElement;
    const anchor = target.closest('a');

    if (anchor && anchor.href && anchor.href.startsWith(window.location.origin + '/admin')) {
        // Skip if target is _blank
        if (anchor.target === '_blank') return;
        // Skip logout or special routes
        if (anchor.href.includes('logout')) return;

        e.preventDefault();
        softNavigate(anchor.href);
    }
});

window.addEventListener('popstate', () => {
    softNavigate(window.location.href);
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
