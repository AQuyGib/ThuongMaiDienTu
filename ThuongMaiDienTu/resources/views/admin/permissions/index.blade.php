<?php
function userInitials($name) {
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) return strtoupper(mb_substr($parts[0],0,1).mb_substr(end($parts),0,1));
    return strtoupper(mb_substr($name,0,2));
}
function avatarColor($name) {
    $colors = ['#7C3AED','#2563EB','#059669','#D97706','#DC2626','#7C3AED','#0891B2','#9333EA'];
    return $colors[array_sum(array_map('ord', str_split($name))) % count($colors)];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Tài Khoản · DIENMAYPRO Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --bg:        #0D1117;
    --surface:   #161B22;
    --surface-2: #1C2230;
    --surface-3: #242B3D;
    --border:    rgba(255,255,255,0.07);
    --border-2:  rgba(255,255,255,0.12);

    --indigo:    #7C3AED;
    --indigo-2:  #6D28D9;
    --indigo-glow: rgba(124,58,237,0.25);
    --emerald:   #10B981;
    --rose:      #F43F5E;
    --amber:     #F59E0B;
    --sky:       #0EA5E9;
    --violet:    #8B5CF6;

    --text-1:    #F0F6FC;
    --text-2:    #8B949E;
    --text-3:    #484F58;

    --radius:    10px;
    --radius-lg: 16px;
    --radius-xl: 22px;

    --sidebar-w: 300px;
}

body.light-mode {
    --bg:        #F6F8FA;
    --surface:   #FFFFFF;
    --surface-2: #F0F2F5;
    --surface-3: #E6E8EB;
    --border:    rgba(0,0,0,0.08);
    --border-2:  rgba(0,0,0,0.15);
    --text-1:    #1F2328;
    --text-2:    #57606A;
    --text-3:    #8C959F;
    --indigo-glow: rgba(124,58,237,0.1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text-1);
    display: flex;
    height: 100vh;
    overflow: hidden;
    font-size: 20px;
    transition: background 0.3s, color 0.3s;
}

/* ── SCROLLBAR ── */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 4px; }

/* ══════════════════ SIDEBAR ══════════════════ */
.sidebar {
    width: var(--sidebar-w);
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    height: 100vh;
    flex-shrink: 0;
    position: relative;
    z-index: 40;
}

.sidebar-logo {
    padding: 24px 20px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid var(--border);
}
.logo-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, var(--indigo), var(--violet));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: #fff;
    box-shadow: 0 4px 12px var(--indigo-glow);
    flex-shrink: 0;
}
.logo-text { font-size: 18px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-1); }
.logo-badge { font-size: 13px; font-weight: 700; letter-spacing: 1px; color: var(--text-2); text-transform: uppercase; }

.sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 12px; }
.nav-section-label {
    font-size: 14px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
    color: var(--text-3); padding: 16px 8px 8px; display: block;
}
.nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-radius: var(--radius);
    color: var(--text-2); font-size: 18px; font-weight: 500;
    text-decoration: none; cursor: pointer;
    transition: all 0.15s ease;
    margin-bottom: 4px;
    position: relative;
}
.nav-item:hover { background: var(--surface-2); color: var(--text-1); }
.nav-item.active {
    background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(139,92,246,0.1));
    color: var(--violet);
    border: 1px solid rgba(124,58,237,0.25);
}
.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; height: 60%; width: 3px;
    background: var(--indigo);
    border-radius: 0 3px 3px 0;
}
.nav-item i { width: 18px; text-align: center; font-size: 14px; }
.nav-badge {
    margin-left: auto;
    background: var(--indigo);
    color: #fff;
    font-size: 14px; font-weight: 700;
    padding: 2px 7px; border-radius: 20px;
}

.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid var(--border);
}
.admin-card {
    display: flex; align-items: center; gap: 10px;
    padding: 10px; border-radius: var(--radius);
    background: var(--surface-2);
    border: 1px solid var(--border);
    margin-bottom: 10px;
}
.admin-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, var(--indigo), var(--violet));
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.admin-name { font-size: 17px; font-weight: 600; color: var(--text-1); }
.admin-role { font-size: 14px; font-weight: 600; color: var(--emerald); text-transform: uppercase; letter-spacing: 0.5px; }
.btn-back-site {
    width: 100%; padding: 9px; border-radius: var(--radius);
    background: var(--surface-3); border: 1px solid var(--border);
    color: var(--text-2); font-size: 12px; font-weight: 600;
    cursor: pointer; font-family: inherit; transition: all 0.15s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-back-site:hover { background: var(--surface-2); color: var(--text-1); border-color: var(--border-2); }

/* ══════════════════ MAIN ══════════════════ */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

.topbar {
    height: 64px; flex-shrink: 0;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 28px; gap: 16px;
}
.topbar-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 17px; color: var(--text-2);
}
.topbar-breadcrumb .current { color: var(--text-1); font-weight: 600; }
.topbar-breadcrumb i { font-size: 14px; }

.topbar-search {
    flex: 1; max-width: 420px; margin-left: auto;
    position: relative;
}
.topbar-search input {
    width: 100%; height: 42px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0 14px 0 40px;
    font-size: 15px; color: var(--text-1);
    font-family: inherit; outline: none;
    transition: all 0.15s;
}
.topbar-search input::placeholder { color: var(--text-3); }
.topbar-search input:focus { border-color: rgba(124,58,237,0.5); background: var(--surface-3); box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
.topbar-search i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-3); }

.topbar-actions { display: flex; align-items: center; gap: 10px; margin-left: 12px; }
.icon-btn {
    width: 36px; height: 36px; border-radius: var(--radius);
    background: var(--surface-2); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-2); font-size: 14px; cursor: pointer;
    transition: all 0.15s; position: relative;
}
.icon-btn:hover { background: var(--surface-3); color: var(--text-1); border-color: var(--border-2); }
.notif-dot {
    position: absolute; top: 7px; right: 7px;
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--rose); border: 2px solid var(--surface);
}

/* ══════════════════ CONTENT ══════════════════ */
.content { flex: 1; overflow-y: auto; padding: 28px; }

/* ── TOAST ── */
.toast {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius);
    margin-bottom: 24px; font-size: 13px; font-weight: 500;
    animation: slideDown 0.3s ease;
    border: 1px solid;
}
.toast.success { background: rgba(16,185,129,0.1); color: #34D399; border-color: rgba(16,185,129,0.25); }
.toast.danger  { background: rgba(244,63,94,0.1);  color: #FB7185; border-color: rgba(244,63,94,0.25); }
@keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:none; } }

/* ── STAT CARDS ── */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    position: relative; overflow: hidden;
    transition: border-color 0.2s, transform 0.2s;
}
.stat-card:hover { border-color: var(--border-2); transform: translateY(-2px); }
.stat-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.stat-card.c-indigo::before { background: linear-gradient(90deg, var(--indigo), var(--violet)); }
.stat-card.c-emerald::before { background: linear-gradient(90deg, var(--emerald), #34D399); }
.stat-card.c-rose::before { background: linear-gradient(90deg, var(--rose), #FB7185); }
.stat-card.c-amber::before { background: linear-gradient(90deg, var(--amber), #FCD34D); }

.stat-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 14px;
}
.stat-icon.indigo { background: rgba(124,58,237,0.15); color: var(--violet); }
.stat-icon.emerald { background: rgba(16,185,129,0.15); color: var(--emerald); }
.stat-icon.rose  { background: rgba(244,63,94,0.15); color: var(--rose); }
.stat-icon.amber { background: rgba(245,158,11,0.15); color: var(--amber); }

.stat-label { font-size: 17px; font-weight: 600; color: var(--text-2); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
.stat-value { font-size: 44px; font-weight: 800; color: var(--text-1); line-height: 1; font-variant-numeric: tabular-nums; }
.stat-sub { font-size: 16px; color: var(--text-3); margin-top: 6px; }

/* ── TABS ── */
.tab-bar {
    display: flex; align-items: center; gap: 4px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 20px;
}
.tab-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 12px 20px; font-size: 19px; font-weight: 600;
    color: var(--text-2); text-decoration: none; border-bottom: 2px solid transparent;
    margin-bottom: -1px; transition: all 0.15s; border-radius: var(--radius) var(--radius) 0 0;
}
.tab-btn:hover { color: var(--text-1); background: var(--surface-2); }
.tab-btn.active { color: var(--violet); border-bottom-color: var(--indigo); }
.tab-count {
    font-size: 14px; font-weight: 700;
    background: var(--surface-3); color: var(--text-2);
    padding: 2px 7px; border-radius: 20px;
}
.tab-btn.active .tab-count { background: var(--indigo-glow); color: var(--violet); }

/* ── TOOLBAR ── */
.toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px; flex-wrap: wrap;
}
.filter-pill {
    display: flex; align-items: center; gap: 6px;
    padding: 0 12px; height: 34px; border-radius: 20px;
    background: var(--surface-2); border: 1px solid var(--border);
    font-size: 12px; font-weight: 500; color: var(--text-2);
    cursor: pointer; text-decoration: none; transition: all 0.15s;
    white-space: nowrap;
}
.filter-pill:hover { border-color: var(--border-2); color: var(--text-1); }
.filter-pill.active { background: rgba(124,58,237,0.15); border-color: rgba(124,58,237,0.4); color: var(--violet); }
.filter-pill i { font-size: 10px; }

.select-filter {
    height: 34px; padding: 0 10px; border-radius: var(--radius);
    background: var(--surface-2); border: 1px solid var(--border);
    color: var(--text-2); font-family: inherit; font-size: 12px; font-weight: 500;
    outline: none; cursor: pointer; transition: all 0.15s;
    appearance: none; padding-right: 28px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%238B949E'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 9px center;
}
.select-filter:focus { border-color: rgba(124,58,237,0.5); }

.toolbar-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }

.btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 0 18px; height: 38px; border-radius: var(--radius);
    font-family: inherit; font-size: 14px; font-weight: 600;
    cursor: pointer; transition: all 0.15s; border: 1px solid;
    text-decoration: none; white-space: nowrap;
}
.btn-ghost { background: var(--surface-2); border-color: var(--border); color: var(--text-2); }
.btn-ghost:hover { background: var(--surface-3); border-color: var(--border-2); color: var(--text-1); }
.btn-primary {
    background: linear-gradient(135deg, var(--indigo), var(--violet));
    border-color: transparent; color: #fff;
    box-shadow: 0 4px 12px var(--indigo-glow);
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px var(--indigo-glow); }
.btn-danger { background: rgba(244,63,94,0.1); border-color: rgba(244,63,94,0.3); color: var(--rose); }
.btn-danger:hover { background: rgba(244,63,94,0.2); }

/* ── BULK BAR ── */
.bulk-bar {
    display: none; align-items: center; gap: 12px;
    padding: 10px 16px; background: rgba(124,58,237,0.1);
    border: 1px solid rgba(124,58,237,0.3); border-radius: var(--radius);
    margin-bottom: 12px; font-size: 13px; font-weight: 500; color: var(--violet);
    animation: slideDown 0.2s ease;
}
.bulk-bar.show { display: flex; }
.bulk-sep { width: 1px; height: 20px; background: rgba(124,58,237,0.3); }

/* ── TABLE ── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th {
    padding: 12px 16px;
    text-align: left; font-size: 12px; font-weight: 700;
    color: var(--text-3); text-transform: uppercase; letter-spacing: 1.5px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
thead th:first-child { padding-left: 8px; }
thead th input[type=checkbox] { accent-color: var(--indigo); width: 14px; height: 14px; }

tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.1s;
}
tbody tr:hover { background: rgba(255,255,255,0.02); }
tbody tr:last-child { border-bottom: none; }

td {
    padding: 16px; font-size: 19px; color: var(--text-1);
    vertical-align: middle;
}
td:first-child { padding-left: 8px; }
td input[type=checkbox] { accent-color: var(--indigo); width: 14px; height: 14px; cursor: pointer; }

.user-cell { display: flex; align-items: center; gap: 12px; }
.user-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff;
    flex-shrink: 0; letter-spacing: 0.5px;
}
.user-name { font-size: 19px; font-weight: 600; color: var(--text-1); margin-bottom: 1px; }
.user-email { font-size: 17px; color: var(--text-2); }
.user-id {
    font-family: 'JetBrains Mono', monospace;
    font-size: 16px; color: var(--text-3);
}

/* Status badges */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px;
    font-size: 16px; font-weight: 600; letter-spacing: 0.3px;
    white-space: nowrap;
}
.badge-dot { width: 5px; height: 5px; border-radius: 50%; }
.b-active  { background: rgba(16,185,129,0.12); color: #34D399; }
.b-active .badge-dot  { background: var(--emerald); }
.b-banned  { background: rgba(244,63,94,0.12);  color: #FB7185; }
.b-banned .badge-dot  { background: var(--rose); }
.b-inactive{ background: rgba(139,147,160,0.12);color: var(--text-2); }
.b-inactive .badge-dot{ background: var(--text-3); }

.role-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 6px;
    font-size: 15px; font-weight: 600;
}
.r-admin    { background: rgba(244,63,94,0.12);  color: #FB7185; }
.r-manager  { background: rgba(14,165,233,0.12); color: #38BDF8; }
.r-staff    { background: rgba(16,185,129,0.12); color: #34D399; }
.r-customer { background: rgba(139,147,160,0.12);color: var(--text-2); }

.tier-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 6px;
    font-size: 15px; font-weight: 600;
}
.t-vang { background: rgba(245,158,11,0.12); color: #FCD34D; }
.t-bac  { background: rgba(148,163,184,0.12); color: #CBD5E1; }
.t-dong { background: rgba(180,120,60,0.12);  color: #C9956A; }

/* Action buttons */
.action-cell { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
.act-btn {
    width: 30px; height: 30px; border-radius: 8px;
    background: var(--surface-2); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-2); font-size: 16px; cursor: pointer;
    transition: all 0.15s;
}
.act-btn:hover { background: var(--surface-3); border-color: var(--border-2); color: var(--text-1); }
.act-btn.edit:hover { border-color: rgba(124,58,237,0.5); color: var(--violet); }
.act-btn.del:hover  { border-color: rgba(244,63,94,0.5);  color: var(--rose); background: rgba(244,63,94,0.08); }
.act-btn.view:hover { border-color: rgba(14,165,233,0.5); color: var(--sky); }

/* ── PAGINATION ── */
.pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 0 0; margin-top: 8px;
    border-top: 1px solid var(--border);
}
.pagination-info { font-size: 12px; color: var(--text-2); }
.pagination-pages { display: flex; align-items: center; gap: 4px; }
.page-btn {
    width: 30px; height: 30px; border-radius: 8px; display: flex;
    align-items: center; justify-content: center;
    background: var(--surface-2); border: 1px solid var(--border);
    font-size: 12px; font-weight: 600; color: var(--text-2);
    cursor: pointer; text-decoration: none; transition: all 0.15s;
}
.page-btn:hover { background: var(--surface-3); color: var(--text-1); }
.page-btn.active { background: var(--indigo); border-color: var(--indigo); color: #fff; }
.page-btn.disabled { opacity: 0.3; pointer-events: none; }

/* ── ROLES TABLE ── */
.roles-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.role-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: all 0.15s;
}
.role-card:hover { border-color: var(--border-2); }
.role-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.role-name { font-size: 16px; font-weight: 700; color: var(--text-1); margin-bottom: 4px; }
.role-desc { font-size: 13px; color: var(--text-2); line-height: 1.5; margin-bottom: 12px; }
.role-meta { display: flex; align-items: center; gap: 10px; }
.role-count {
    font-size: 12px; font-weight: 600; color: var(--text-2);
    background: var(--surface-2); border: 1px solid var(--border);
    padding: 3px 12px; border-radius: 20px;
}
.role-actions { margin-left: auto; display: flex; gap: 6px; flex-shrink: 0; }

/* ── MODAL ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(6px);
    display: none; align-items: center; justify-content: center;
    z-index: 1000;
}
.modal-overlay.open { display: flex; animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

.modal {
    background: var(--surface);
    border: 1px solid var(--border-2);
    border-radius: var(--radius-xl);
    width: 560px; max-width: calc(100vw - 40px);
    max-height: calc(100vh - 60px); overflow-y: auto;
    box-shadow: 0 24px 60px rgba(0,0,0,0.5);
    animation: modalUp 0.25s cubic-bezier(0.22,1,0.36,1);
}
@keyframes modalUp { from { opacity:0; transform:translateY(20px) scale(0.97); } to { opacity:1; transform:none; } }

.modal-header {
    padding: 22px 24px 18px;
    display: flex; align-items: center; gap: 12px;
    border-bottom: 1px solid var(--border);
}
.modal-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
}
.modal-title { font-size: 18px; font-weight: 700; color: var(--text-1); }
.modal-sub   { font-size: 13px; color: var(--text-2); margin-top: 1px; }
.modal-close {
    margin-left: auto; width: 30px; height: 30px;
    border-radius: 8px; background: var(--surface-2); border: 1px solid var(--border);
    color: var(--text-2); font-size: 13px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
}
.modal-close:hover { background: var(--surface-3); color: var(--text-1); }

.modal-body { padding: 22px 24px; }
.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 8px;
}

.field-label {
    display: block; font-size: 12px; font-weight: 700;
    color: var(--text-2); text-transform: uppercase; letter-spacing: 1px;
    margin-bottom: 8px;
}
.field-hint { font-size: 12px; color: var(--text-3); margin-top: 5px; }
.field-group { margin-bottom: 18px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.form-input {
    width: 100%; height: 42px; padding: 0 12px;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text-1);
    font-family: inherit; font-size: 15px; outline: none;
    transition: all 0.15s;
}
.form-input::placeholder { color: var(--text-3); }
.form-input:focus { border-color: rgba(124,58,237,0.5); background: var(--surface-3); box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }
.form-select {
    width: 100%; height: 42px; padding: 0 30px 0 12px;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text-1);
    font-family: inherit; font-size: 15px; outline: none;
    transition: all 0.15s; appearance: none; cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%238B949E'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
}
.form-select:focus { border-color: rgba(124,58,237,0.5); box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }

/* ── DELETE CONFIRM ── */
.delete-modal { text-align: center; }
.del-icon { font-size: 44px; color: var(--rose); margin-bottom: 16px; }
.del-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.del-desc { font-size: 13px; color: var(--text-2); margin-bottom: 6px; }
.del-name { font-weight: 700; color: var(--text-1); }

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: var(--text-3);
}
.empty-state i { font-size: 40px; margin-bottom: 14px; display: block; }
.empty-state p { font-size: 14px; }

/* ── ROLES TAB ── */
.roles-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }

/* divider */
.divider-label {
    display: flex; align-items: center; gap: 10px;
    font-size: 11px; font-weight: 700; color: var(--text-3);
    text-transform: uppercase; letter-spacing: 1.5px;
    margin-bottom: 14px; margin-top: 4px;
}
.divider-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fa-solid fa-bolt-lightning"></i></div>
        <div>
            <div class="logo-text">DIENMAYPRO</div>
            <div class="logo-badge">Admin Panel</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Tổng quan</span>
        <a href="/admin" class="nav-item">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
        <a href="/admin/kpi" class="nav-item">
            <i class="fa-solid fa-chart-pie"></i> Thống kê KPI
        </a>

        <span class="nav-section-label">Quản lý Bán Hàng</span>
        <a href="/admin/orders" class="nav-item">
            <i class="fa-solid fa-cart-shopping"></i> Đơn hàng
        </a>
        <a href="/admin/cashbooks" class="nav-item">
            <i class="fa-solid fa-wallet"></i> Sổ Quỹ
        </a>

        <span class="nav-section-label">Sản phẩm & Nội dung</span>
        <a href="/admin/products" class="nav-item">
            <i class="fa-solid fa-box"></i> Sản phẩm
        </a>
        <a href="/admin/categories" class="nav-item">
            <i class="fa-solid fa-list"></i> Danh mục
        </a>
        <a href="/admin/articles" class="nav-item">
            <i class="fa-solid fa-newspaper"></i> Bài viết & CMS
        </a>

        <span class="nav-section-label">Quản lý Kho</span>
        <a href="/admin/suppliers" class="nav-item">
            <i class="fa-solid fa-truck-field"></i> Nhà cung cấp
        </a>
        <a href="/admin/purchase-orders" class="nav-item">
            <i class="fa-solid fa-file-invoice-dollar"></i> Nhập kho
        </a>
        <a href="/admin/inventory" class="nav-item">
            <i class="fa-solid fa-warehouse"></i> Tồn kho (IMEI)
        </a>

        <span class="nav-section-label">Hệ thống</span>
        <a href="/admin/users" class="nav-item active">
            <i class="fa-solid fa-users"></i> Tài khoản
        </a>
        <a href="/admin/settings/theme" class="nav-item">
            <i class="fa-solid fa-palette"></i> Giao diện
        </a>
        <a href="/admin/settings" class="nav-item">
            <i class="fa-solid fa-gear"></i> Cài đặt
        </a>
        <a href="/admin/logs" class="nav-item">
            <i class="fa-solid fa-clock-rotate-left"></i> Nhật ký
        </a>
    </nav>

    <div class="sidebar-footer">
        <?php $u = Illuminate\Support\Facades\Auth::user(); ?>
        <div class="admin-card">
            <div class="admin-avatar" style="background: <?php echo avatarColor($u->full_name ?? 'AD'); ?>">
                <?php echo userInitials($u->full_name ?? 'AD'); ?>
            </div>
            <div>
                <div class="admin-name"><?php echo htmlspecialchars($u->full_name ?? 'Administrator'); ?></div>
                <div class="admin-role"><?php echo $u->role->name ?? 'Admin'; ?></div>
            </div>
            <div style="margin-left:auto; color:var(--text-3); font-size:12px; cursor:pointer;">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
        <button class="btn-back-site" onclick="window.location.href='/'">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trang chủ
        </button>
    </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <span>Admin</span>
            <i class="fa-solid fa-chevron-right"></i>
            <span class="current">Quản lý Tài Khoản</span>
        </div>

        <div class="topbar-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <form method="GET" id="searchForm">
                <input type="hidden" name="tab" value="<?php echo $active_tab; ?>">
                <input type="text" name="search" placeholder="Tìm tên, email, ID..." value="<?php echo htmlspecialchars($search ?? ''); ?>" oninput="debounceSearch(this.form)">
            </form>
        </div>

        <div class="topbar-actions">
            <div class="icon-btn" id="themeToggle" title="Chuyển chế độ Sáng/Tối">
                <i class="fa-solid fa-moon"></i>
            </div>
            <div class="icon-btn" title="Xuất CSV" onclick="window.location.href='?export=csv'">
                <i class="fa-solid fa-file-arrow-down"></i>
            </div>
            <div class="icon-btn" title="Thông báo" style="position:relative">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-dot"></span>
            </div>
            <div class="icon-btn" title="Trợ giúp">
                <i class="fa-regular fa-circle-question"></i>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <?php if($message): ?>
        <div class="toast <?php echo $msg_type; ?>">
            <i class="fa-solid <?php echo $msg_type=='success' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
            <?php echo $message; ?>
            <button onclick="this.parentElement.remove()" style="margin-left:auto; background:none; border:none; color:inherit; cursor:pointer; font-size:14px;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <?php endif; ?>

        <!-- STATS ROW -->
        <div class="stats-row">
            <div class="stat-card c-indigo">
                <div class="stat-icon indigo"><i class="fa-solid fa-users"></i></div>
                <div class="stat-label">Tổng tài khoản</div>
                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                <div class="stat-sub"><?php echo $status_stats['Active']; ?> đang hoạt động</div>
            </div>
            <div class="stat-card c-emerald">
                <div class="stat-icon emerald"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-label">Hoạt động</div>
                <div class="stat-value"><?php echo $status_stats['Active']; ?></div>
                <div class="stat-sub"><?php echo $status_stats['Banned']; ?> bị khóa</div>
            </div>
            <div class="stat-card c-amber">
                <div class="stat-icon amber"><i class="fa-solid fa-crown"></i></div>
                <div class="stat-label">Thành viên Vàng</div>
                <div class="stat-value"><?php echo $tier_stats['Vang']; ?></div>
                <div class="stat-sub"><?php echo $tier_stats['Bac']; ?> Bạc · <?php echo $tier_stats['Dong']; ?> Đồng</div>
            </div>
            <div class="stat-card c-rose">
                <div class="stat-icon rose"><i class="fa-solid fa-user-shield"></i></div>
                <div class="stat-label">Vai trò</div>
                <div class="stat-value"><?php echo count($roles); ?></div>
                <div class="stat-sub"><?php echo count($role_stats); ?> nhóm phân quyền</div>
            </div>
        </div>

        <!-- TABS -->
        <div class="tab-bar">
            <a href="?tab=users" class="tab-btn <?php echo $active_tab=='users'?'active':''; ?>">
                <i class="fa-solid fa-users"></i> Người dùng
                <span class="tab-count"><?php echo $total_users; ?></span>
            </a>
            <a href="?tab=roles" class="tab-btn <?php echo $active_tab=='roles'?'active':''; ?>">
                <i class="fa-solid fa-shield-halved"></i> Vai trò &amp; Phân quyền
                <span class="tab-count"><?php echo count($roles); ?></span>
            </a>
        </div>

        <?php if($active_tab == 'users'): ?>

        <!-- TOOLBAR: Role pills + Filters -->
        <form method="GET" id="filterForm">
            <input type="hidden" name="tab" value="users">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
            <div class="toolbar">
                <a href="?tab=users" class="filter-pill <?php echo !$filter_role ? 'active' : ''; ?>">
                    Tất cả
                </a>
                <?php foreach($roles as $r): ?>
                <a href="?tab=users&role_id=<?php echo $r->role_id; ?>" class="filter-pill <?php echo $filter_role == $r->role_id ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($r->name); ?>
                    <span style="font-size:10px; opacity:.7"><?php echo $role_stats[$r->role_id]['count'] ?? 0; ?></span>
                </a>
                <?php endforeach; ?>

                <div style="width:1px; height:20px; background:var(--border); margin:0 4px;"></div>

                <select name="status" class="select-filter" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Trạng thái</option>
                    <option value="Active"    <?php echo $filter_status=='Active'?'selected':''; ?>>Hoạt động</option>
                    <option value="Banned"    <?php echo $filter_status=='Banned'?'selected':''; ?>>Đã khóa</option>
                    <option value="Inactive"  <?php echo $filter_status=='Inactive'?'selected':''; ?>>Ngưng hoạt động</option>
                </select>
                <select name="tier" class="select-filter" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Hạng TV</option>
                    <option value="Vang" <?php echo $filter_tier=='Vang'?'selected':''; ?>>Vàng</option>
                    <option value="Bac"  <?php echo $filter_tier=='Bac'?'selected':''; ?>>Bạc</option>
                    <option value="Dong" <?php echo $filter_tier=='Dong'?'selected':''; ?>>Đồng</option>
                </select>
                <select name="sort" class="select-filter" onchange="document.getElementById('filterForm').submit()">
                    <option value="id_desc" <?php echo $sort=='id_desc'?'selected':''; ?>>ID: Giảm dần</option>
                    <option value="id_asc"  <?php echo $sort=='id_asc'?'selected':''; ?>>ID: Tăng dần</option>
                    <option value="newest"  <?php echo $sort=='newest'?'selected':''; ?>>Mới nhất</option>
                    <option value="oldest"  <?php echo $sort=='oldest'?'selected':''; ?>>Cũ nhất</option>
                    <option value="name_az" <?php echo $sort=='name_az'?'selected':''; ?>>Tên A→Z</option>
                    <option value="name_za" <?php echo $sort=='name_za'?'selected':''; ?>>Tên Z→A</option>
                </select>

                <?php if($filter_status || $filter_tier || $search): ?>
                <a href="?tab=users<?php echo $filter_role ? '&role_id='.$filter_role : ''; ?>" class="filter-pill" style="color:var(--rose); border-color:rgba(244,63,94,0.3);">
                    <i class="fa-solid fa-xmark"></i> Xóa lọc
                </a>
                <?php endif; ?>

                <div class="toolbar-right">
                    <button type="button" class="btn btn-ghost" onclick="window.location.href='?export=csv'">
                        <i class="fa-solid fa-file-csv"></i> Xuất CSV
                    </button>
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">
                        <i class="fa-solid fa-plus"></i> Thêm tài khoản
                    </button>
                </div>
            </div>
        </form>

        <!-- BULK BAR -->
        <div class="bulk-bar" id="bulkBar">
            <i class="fa-solid fa-layer-group"></i>
            <span><strong id="bulkCount">0</strong> đã chọn</span>
            <div class="bulk-sep"></div>
            <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()" style="height:28px; font-size:11px;">
                <i class="fa-solid fa-trash"></i> Xóa đã chọn
            </button>
            <button type="button" class="btn btn-ghost" onclick="clearAll()" style="height:28px; font-size:11px;">
                Bỏ chọn
            </button>
        </div>

        <!-- TABLE -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="masterCb" onclick="toggleAll(this)"></th>
                        <th style="width:80px;">ID</th>
                        <th>Người dùng</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò</th>
                        <th>Hạng TV</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng ký</th>
                        <th style="text-align:right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($users->isEmpty()): ?>
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-user-slash"></i>
                            <p>Không tìm thấy tài khoản nào</p>
                        </div>
                    </td></tr>
                <?php else: ?>
                    <?php foreach($users as $u):
                        $initials = userInitials($u->full_name);
                        $avColor  = avatarColor($u->full_name);
                        $roleName = strtolower($u->role->name ?? 'customer');
                        $statusClass = ['Active'=>'b-active','Banned'=>'b-banned','Inactive'=>'b-inactive'][$u->status] ?? 'b-inactive';
                        $tierClass   = ['Vang'=>'t-vang','Bac'=>'t-bac','Dong'=>'t-dong'][$u->member_tier] ?? 't-dong';
                        $tierIcon    = ['Vang'=>'fa-crown','Bac'=>'fa-medal','Dong'=>'fa-award'][$u->member_tier] ?? 'fa-award';
                        $tierLabel   = ['Vang'=>'Vàng','Bac'=>'Bạc','Dong'=>'Đồng'][$u->member_tier] ?? 'Đồng';
                        $roleClass   = 'r-'.($roleName == 'admin'?'admin':($roleName=='manager'?'manager':($roleName=='staff'?'staff':'customer')));
                        $statusLabel = ['Active'=>'Hoạt động','Banned'=>'Đã khóa','Inactive'=>'Ngưng'][$u->status] ?? $u->status;
                    ?>
                    <tr>
                        <td><input type="checkbox" class="user-cb" value="<?php echo $u->user_id; ?>" onchange="updateBulk()"></td>
                        <td>
                            <div class="user-id" style="color:var(--text-1); font-weight:700;">#<?php echo str_pad($u->user_id, 4, '0', STR_PAD_LEFT); ?></div>
                        </td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar" style="background:<?php echo $avColor; ?>;"><?php echo $initials; ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($u->full_name); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($u->email); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:'JetBrains Mono',monospace; color:var(--text-2);">
                            <?php echo $u->phone_number ?? '<span style="opacity:.3">Chưa có</span>'; ?>
                        </td>
                        <td><span class="role-badge <?php echo $roleClass; ?>"><?php echo htmlspecialchars($u->role->name ?? 'Khách'); ?></span></td>
                        <td>
                            <span class="tier-badge <?php echo $tierClass; ?>">
                                <i class="fa-solid <?php echo $tierIcon; ?>" style="font-size:10px;"></i>
                                <?php echo $tierLabel; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <span class="badge-dot"></span>
                                <?php echo $statusLabel; ?>
                            </span>
                        </td>
                        <td style="color:var(--text-2); font-size:12px;">
                            <?php echo $u->created_at ? date('d/m/Y', strtotime($u->created_at)) : '—'; ?>
                        </td>
                        <td>
                            <div class="action-cell">
                                <button class="act-btn view" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></button>
                                <button class="act-btn edit" title="Chỉnh sửa" onclick='openEditModal(<?php echo json_encode($u); ?>)'><i class="fa-solid fa-pen"></i></button>
                                <?php if($u->user_id != 1): ?>
                                <button class="act-btn del" title="Xóa" onclick="confirmDelete(<?php echo $u->user_id; ?>, '<?php echo addslashes($u->full_name); ?>')"><i class="fa-solid fa-trash-can"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if($users->lastPage() > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Hiển thị <?php echo $users->firstItem(); ?>–<?php echo $users->lastItem(); ?> / <?php echo $users->total(); ?> tài khoản
            </div>
            <div class="pagination-pages">
                <a href="<?php echo $users->previousPageUrl() ?? '#'; ?>" class="page-btn <?php echo !$users->onFirstPage() ?: 'disabled'; ?>">
                    <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
                </a>
                <?php for($p = max(1, $users->currentPage()-2); $p <= min($users->lastPage(), $users->currentPage()+2); $p++): ?>
                <a href="<?php echo $users->url($p); ?>" class="page-btn <?php echo $p == $users->currentPage() ? 'active' : ''; ?>"><?php echo $p; ?></a>
                <?php endfor; ?>
                <a href="<?php echo $users->nextPageUrl() ?? '#'; ?>" class="page-btn <?php echo $users->hasMorePages() ? '' : 'disabled'; ?>">
                    <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif($active_tab == 'roles'): ?>

        <!-- ── ROLES TAB ── -->
        <div class="roles-header">
            <div>
                <div style="font-size:16px; font-weight:700; color:var(--text-1);">Vai trò &amp; Phân quyền</div>
                <div style="font-size:12px; color:var(--text-2); margin-top:2px;">Quản lý nhóm quyền và gán cho tài khoản</div>
            </div>
            <button class="btn btn-primary" onclick="openAddRoleModal()">
                <i class="fa-solid fa-plus"></i> Thêm vai trò
            </button>
        </div>

        <div class="divider-label">Danh sách vai trò</div>

        <div class="roles-grid">
            <?php
            $roleIcons  = [1=>'fa-shield-halved',2=>'fa-briefcase',3=>'fa-headset',4=>'fa-user'];
            $roleColors = [1=>'rgba(244,63,94,.15)',2=>'rgba(14,165,233,.15)',3=>'rgba(16,185,129,.15)',4=>'rgba(139,147,160,.15)'];
            $roleIconColors = [1=>'var(--rose)',2=>'var(--sky)',3=>'var(--emerald)',4=>'var(--text-2)'];
            foreach($roles as $r):
                $icon  = $roleIcons[$r->role_id]  ?? 'fa-user-tag';
                $bg    = $roleColors[$r->role_id]  ?? 'rgba(124,58,237,.15)';
                $ic    = $roleIconColors[$r->role_id] ?? 'var(--violet)';
                $count = $role_stats[$r->role_id]['count'] ?? 0;
            ?>
            <div class="role-card">
                <div class="role-icon" style="background:<?php echo $bg; ?>; color:<?php echo $ic; ?>;">
                    <i class="fa-solid <?php echo $icon; ?>"></i>
                </div>
                <div style="flex:1; min-width:0;">
                    <div class="role-name"><?php echo htmlspecialchars($r->name); ?></div>
                    <div class="role-desc"><?php echo htmlspecialchars($r->description ?? 'Chưa có mô tả'); ?></div>
                    <div class="role-meta">
                        <span class="role-count"><i class="fa-solid fa-users" style="font-size:9px;"></i> <?php echo $count; ?> tài khoản</span>
                    </div>
                </div>
                <div class="role-actions">
                    <?php if(!in_array($r->role_id, [1,2,3])): ?>
                    <button class="act-btn edit" onclick='openEditRoleModal(<?php echo json_encode($r); ?>)' title="Sửa"><i class="fa-solid fa-pen"></i></button>
                    <button class="act-btn del" onclick="confirmDeleteRole(<?php echo $r->role_id; ?>, '<?php echo addslashes($r->name); ?>')" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                    <?php else: ?>
                    <button class="act-btn edit" onclick='openEditRoleModal(<?php echo json_encode($r); ?>)' title="Sửa"><i class="fa-solid fa-pen"></i></button>
                    <button class="act-btn" title="Vai trò hệ thống, không thể xóa" style="opacity:.3; cursor:not-allowed;"><i class="fa-solid fa-lock"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="divider-label" style="margin-top:24px;">Phân bổ người dùng theo vai trò</div>
        <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden;">
            <table>
                <thead>
                    <tr>
                        <th>Vai trò</th>
                        <th>Số tài khoản</th>
                        <th>Phân bổ</th>
                        <th style="text-align:right">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($roles as $r):
                    $count = $role_stats[$r->role_id]['count'] ?? 0;
                    $pct   = $total_users > 0 ? round($count/$total_users*100) : 0;
                    $icon  = $roleIcons[$r->role_id]  ?? 'fa-user-tag';
                    $ic    = $roleIconColors[$r->role_id] ?? 'var(--violet)';
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <i class="fa-solid <?php echo $icon; ?>" style="color:<?php echo $ic; ?>; width:16px; text-align:center;"></i>
                            <span style="font-weight:600;"><?php echo htmlspecialchars($r->name); ?></span>
                        </div>
                    </td>
                    <td style="font-family:'JetBrains Mono',monospace; font-size:13px;"><?php echo $count; ?></td>
                    <td style="width:200px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="flex:1; height:6px; background:var(--surface-3); border-radius:10px; overflow:hidden;">
                                <div style="width:<?php echo $pct; ?>%; height:100%; background:<?php echo $ic; ?>; border-radius:10px; transition:width .5s;"></div>
                            </div>
                            <span style="font-size:11px; color:var(--text-2); width:30px; text-align:right;"><?php echo $pct; ?>%</span>
                        </div>
                    </td>
                    <td style="text-align:right;">
                        <a href="?tab=users&role_id=<?php echo $r->role_id; ?>" class="btn btn-ghost" style="height:28px; font-size:11px;">
                            <i class="fa-solid fa-arrow-right" style="font-size:10px;"></i> Xem DS
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>

    </div><!-- /content -->
</div><!-- /main -->


<!-- ══ MODAL: ADD/EDIT USER ══ -->
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-icon" id="modalIcon" style="background:rgba(124,58,237,.15); color:var(--violet);">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <div class="modal-title" id="modalTitle">Thêm tài khoản mới</div>
                <div class="modal-sub" id="modalSub">Điền thông tin để tạo tài khoản</div>
            </div>
            <button class="modal-close" onclick="closeModal('userModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" id="userForm">
            @csrf
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="formUserId">
            <div class="modal-body">
                <div class="field-group">
                    <label class="field-label">Họ và tên <span style="color:var(--rose)">*</span></label>
                    <input type="text" name="full_name" id="fName" class="form-input" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Email <span style="color:var(--rose)">*</span></label>
                        <input type="email" name="email" id="fEmail" class="form-input" placeholder="email@example.com" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Số điện thoại</label>
                        <input type="text" name="phone_number" id="fPhone" class="form-input" placeholder="0901 234 567">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group">
                        <label class="field-label">Vai trò</label>
                        <select name="role_id" id="fRole" class="form-select">
                            <?php foreach($roles as $r): ?>
                            <option value="<?php echo $r->role_id; ?>"><?php echo htmlspecialchars($r->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Trạng thái</label>
                        <select name="status" id="fStatus" class="form-select">
                            <option value="Active">Hoạt động</option>
                            <option value="Banned">Đã khóa</option>
                            <option value="Inactive">Ngưng hoạt động</option>
                        </select>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Hạng thành viên</label>
                    <select name="member_tier" id="fTier" class="form-select">
                        <option value="Dong">Đồng</option>
                        <option value="Bac">Bạc</option>
                        <option value="Vang">Vàng</option>
                    </select>
                </div>
                <div class="field-group">
                    <label class="field-label">Mật khẩu <span id="pwHint" style="color:var(--text-3); font-weight:400; text-transform:none; font-size:10px;">(để trống = giữ nguyên)</span></label>
                    <input type="password" name="password" id="fPassword" class="form-input" placeholder="Tối thiểu 8 ký tự">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('userModal')">Hủy</button>
                <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> <span id="submitLabel">Tạo tài khoản</span></button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: ADD/EDIT ROLE ══ -->
<div class="modal-overlay" id="roleModal">
    <div class="modal" style="width:480px;">
        <div class="modal-header">
            <div class="modal-icon" style="background:rgba(124,58,237,.15); color:var(--violet);">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="modal-title" id="rolModalTitle">Thêm vai trò mới</div>
                <div class="modal-sub">Cấu hình vai trò và mô tả</div>
            </div>
            <button class="modal-close" onclick="closeModal('roleModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            @csrf
            <input type="hidden" name="action" id="roleAction" value="add_role">
            <input type="hidden" name="role_id" id="roleId">
            <div class="modal-body">
                <div class="field-group">
                    <label class="field-label">Tên vai trò <span style="color:var(--rose)">*</span></label>
                    <input type="text" name="name" id="roleName" class="form-input" placeholder="VD: Kế toán, Kho..." required>
                </div>
                <div class="field-group">
                    <label class="field-label">Mô tả</label>
                    <input type="text" name="description" id="roleDesc" class="form-input" placeholder="Mô tả ngắn về vai trò này">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('roleModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Lưu vai trò</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: DELETE USER ══ -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="width:420px;">
        <div class="modal-body delete-modal" style="padding:36px 32px;">
            <div class="del-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="del-title">Xác nhận xóa tài khoản</div>
            <div class="del-desc">Bạn đang chuẩn bị xóa tài khoản</div>
            <div class="del-name" id="delName" style="margin-bottom:8px;"></div>
            <div class="del-desc">Hành động này <strong style="color:var(--rose)">không thể hoàn tác</strong>. Tất cả dữ liệu liên quan sẽ bị xóa vĩnh viễn.</div>
        </div>
        <form method="POST">
            @csrf
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" id="delUserId">
            <div class="modal-footer" style="justify-content:center; gap:12px;">
                <button type="button" class="btn btn-ghost" style="min-width:120px;" onclick="closeModal('deleteModal')">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger" style="min-width:120px;"><i class="fa-solid fa-trash-can"></i> Đồng ý xóa</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: BULK DELETE ══ -->
<div class="modal-overlay" id="bulkDeleteModal">
    <div class="modal" style="width:420px;">
        <div class="modal-body delete-modal" style="padding:36px 32px;">
            <div class="del-icon"><i class="fa-solid fa-users-slash"></i></div>
            <div class="del-title">Xóa hàng loạt</div>
            <div class="del-desc">Bạn đang chuẩn bị xóa <strong id="bulkDelCount" style="color:var(--rose)">0</strong> tài khoản đã chọn.</div>
            <div class="del-desc" style="margin-top:8px;">Hành động này <strong style="color:var(--rose)">không thể hoàn tác</strong>.</div>
        </div>
        <form method="POST">
            @csrf
            <input type="hidden" name="action" value="bulk_delete">
            <input type="hidden" name="ids" id="bulkIds">
            <div class="modal-footer" style="justify-content:center; gap:12px;">
                <button type="button" class="btn btn-ghost" style="min-width:120px;" onclick="closeModal('bulkDeleteModal')">Hủy bỏ</button>
                <button type="submit" class="btn btn-danger" style="min-width:120px;"><i class="fa-solid fa-trash-can"></i> Xóa tất cả</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL: DELETE ROLE ══ -->
<div class="modal-overlay" id="deleteRoleModal">
    <div class="modal" style="width:420px;">
        <div class="modal-body delete-modal" style="padding:36px 32px;">
            <div class="del-icon"><i class="fa-solid fa-shield-slash"></i></div>
            <div class="del-title">Xóa vai trò</div>
            <div class="del-desc">Bạn đang chuẩn bị xóa vai trò <strong id="delRoleName" style="color:var(--rose)"></strong></div>
        </div>
        <form method="POST">
            @csrf
            <input type="hidden" name="action" value="delete_role">
            <input type="hidden" name="role_id" id="delRoleId">
            <div class="modal-footer" style="justify-content:center; gap:12px;">
                <button type="button" class="btn btn-ghost" style="min-width:120px;" onclick="closeModal('deleteRoleModal')">Hủy</button>
                <button type="submit" class="btn btn-danger" style="min-width:120px;"><i class="fa-solid fa-trash-can"></i> Xóa vai trò</button>
            </div>
        </form>
    </div>
</div>

<script>
/* ── Theme Toggle ── */
const themeToggle = document.getElementById('themeToggle');
const body = document.body;
const icon = themeToggle.querySelector('i');

// Check saved theme
if (localStorage.getItem('theme') === 'light') {
    body.classList.add('light-mode');
    icon.classList.replace('fa-moon', 'fa-sun');
}

themeToggle.addEventListener('click', () => {
    body.classList.toggle('light-mode');
    const isLight = body.classList.contains('light-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    
    // Switch icon
    if (isLight) {
        icon.classList.replace('fa-moon', 'fa-sun');
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
    }
});

/* ── Modal helpers ── */
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open'); });
});

/* ── User modal ── */
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Thêm tài khoản mới';
    document.getElementById('modalSub').textContent = 'Điền thông tin để tạo tài khoản';
    document.getElementById('formAction').value = 'add';
    document.getElementById('formUserId').value = '';
    document.getElementById('submitLabel').textContent = 'Tạo tài khoản';
    document.getElementById('pwHint').style.display = 'none';
    document.getElementById('userForm').reset();
    openModal('userModal');
}
function openEditModal(u) {
    document.getElementById('modalTitle').textContent = 'Cập nhật tài khoản';
    document.getElementById('modalSub').textContent = u.email;
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formUserId').value = u.user_id;
    document.getElementById('fName').value    = u.full_name  || '';
    document.getElementById('fEmail').value   = u.email      || '';
    document.getElementById('fPhone').value   = u.phone_number || '';
    document.getElementById('fRole').value    = u.role_id    || '';
    document.getElementById('fStatus').value  = u.status     || 'Active';
    document.getElementById('fTier').value    = u.member_tier || 'Dong';
    document.getElementById('fPassword').value = '';
    document.getElementById('submitLabel').textContent = 'Lưu thay đổi';
    document.getElementById('pwHint').style.display = 'inline';
    openModal('userModal');
}

/* ── Delete user ── */
function confirmDelete(id, name) {
    document.getElementById('delUserId').value = id;
    document.getElementById('delName').textContent = name;
    openModal('deleteModal');
}

/* ── Bulk delete ── */
function confirmBulkDelete() {
    const ids = [...document.querySelectorAll('.user-cb:checked')].map(c => c.value);
    document.getElementById('bulkIds').value = ids.join(',');
    document.getElementById('bulkDelCount').textContent = ids.length;
    openModal('bulkDeleteModal');
}

/* ── Role modal ── */
function openAddRoleModal() {
    document.getElementById('rolModalTitle').textContent = 'Thêm vai trò mới';
    document.getElementById('roleAction').value = 'add_role';
    document.getElementById('roleId').value = '';
    document.getElementById('roleName').value = '';
    document.getElementById('roleDesc').value = '';
    openModal('roleModal');
}
function openEditRoleModal(r) {
    document.getElementById('rolModalTitle').textContent = 'Cập nhật vai trò';
    document.getElementById('roleAction').value = 'edit_role';
    document.getElementById('roleId').value    = r.role_id;
    document.getElementById('roleName').value  = r.name || '';
    document.getElementById('roleDesc').value  = r.description || '';
    openModal('roleModal');
}
function confirmDeleteRole(id, name) {
    document.getElementById('delRoleId').value  = id;
    document.getElementById('delRoleName').textContent = name;
    openModal('deleteRoleModal');
}

/* ── Bulk select ── */
function toggleAll(master) {
    document.querySelectorAll('.user-cb').forEach(cb => cb.checked = master.checked);
    updateBulk();
}
function updateBulk() {
    const count = document.querySelectorAll('.user-cb:checked').length;
    const bar = document.getElementById('bulkBar');
    bar.classList.toggle('show', count > 0);
    document.getElementById('bulkCount').textContent = count;
    document.getElementById('masterCb').indeterminate =
        count > 0 && count < document.querySelectorAll('.user-cb').length;
}
function clearAll() {
    document.querySelectorAll('.user-cb, #masterCb').forEach(cb => cb.checked = false);
    updateBulk();
}

/* ── Debounced search ── */
let searchTimer;
function debounceSearch(form) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => form.submit(), 450);
}

/* ── Auto-dismiss toast after 4s ── */
setTimeout(() => { const t = document.querySelector('.toast'); if(t) t.style.opacity = '0'; }, 4000);
</script>

</body>
</html>