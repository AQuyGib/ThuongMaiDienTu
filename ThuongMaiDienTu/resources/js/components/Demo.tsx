import React from 'react';
import { VercelTabs } from './ui/vercel-tabs';
import { Button } from './ui/button';

const tabs = [
  {
    label: 'Overview',
    value: 'overview',
    content: (
      <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h2 className="text-2xl font-bold mb-4">Welcome to Joly UI</h2>
        <p className="text-slate-600 dark:text-slate-400">
          This is an example of the Vercel Tabs component integrated with shadcn/ui in a Laravel project.
        </p>
        <div className="mt-6">
          <Button>Get Started</Button>
        </div>
      </div>
    ),
  },
  {
    label: 'Features',
    value: 'features',
    content: (
      <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h2 className="text-2xl font-bold mb-4">Key Features</h2>
        <ul className="list-disc list-inside space-y-2 text-slate-600 dark:text-slate-400">
          <li>Smooth animations</li>
          <li>Dark mode support</li>
          <li>Customizable with Tailwind CSS</li>
          <li>Radix UI primitives</li>
        </ul>
      </div>
    ),
  },
  {
    label: 'Settings',
    value: 'settings',
    content: (
      <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <h2 className="text-2xl font-bold mb-4">Settings</h2>
        <p className="text-slate-600 dark:text-slate-400">
          Manage your component settings here.
        </p>
      </div>
    ),
  },
];

export default function Demo() {
  return (
    <div className="max-w-4xl mx-auto p-8">
      <h1 className="text-3xl font-bold mb-8 text-center">Joly UI + Shadcn Demo</h1>
      <VercelTabs tabs={tabs} />
    </div>
  );
}
