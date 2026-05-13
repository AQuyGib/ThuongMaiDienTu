import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import Demo from './components/Demo';
import UserManagement from './components/UserManagement';
import SessionManagement from './components/SessionManagement';

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
