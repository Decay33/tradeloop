import { Link, router, usePage } from '@inertiajs/react';
import { BarChart3, BriefcaseBusiness, FileText, Home, LogOut, MessageSquareText, Receipt, Settings, Users } from 'lucide-react';
import { appUrl } from '../lib/url';

const nav = [
    ['Dashboard', 'dashboard', Home, ['view_dashboard']],
    ['Customers', 'customers', Users, ['manage_customers']],
    ['Estimates', 'estimates', FileText, ['create_estimates', 'manage_estimates']],
    ['Invoices', 'invoices', Receipt, ['manage_invoices']],
    ['Jobs', 'jobs', BriefcaseBusiness, ['create_jobs', 'start_jobs', 'complete_jobs']],
    ['Follow-Ups', 'follow-ups', MessageSquareText, ['manage_followups']],
    ['Reports', 'reports', BarChart3, ['view_reports']],
    ['Settings', 'settings', Settings, ['manage_settings', 'manage_team']],
];

export default function AppLayout({ children }) {
    const { auth, demoMode, flash } = usePage().props;
    const permissions = auth?.permissions || [];

    function logout() {
        router.post(appUrl('logout'));
    }

    return (
        <div className="min-h-screen bg-slate-50">
            <div className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <Link href={appUrl('dashboard')} className="text-xl font-semibold text-slate-950">TradeLoop</Link>
                        <p className="text-sm text-slate-600">Finish the job. TradeLoop handles the follow-up.</p>
                    </div>
                    <div className="flex items-center gap-3 text-sm text-slate-600">
                        <span>{auth?.business?.name}</span>
                        <button className="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-slate-700" onClick={logout} type="button"><LogOut size={16} /> Logout</button>
                    </div>
                </div>
                <nav className="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 pb-3">
                    {nav.filter((item) => item[3].some((permission) => permissions.includes(permission))).map(([label, href, Icon]) => (
                        <Link key={href} href={appUrl(href)} className="inline-flex min-h-10 items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                            <Icon size={16} /> {label}
                        </Link>
                    ))}
                </nav>
            </div>
            {demoMode ? <div className="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-900">Demo mode is on. SMS and email sends are simulated only.</div> : null}
            <main className="mx-auto max-w-7xl px-4 py-6">
                {flash?.success ? <div className="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{flash.success}</div> : null}
                {flash?.error ? <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div> : null}
                {children}
            </main>
        </div>
    );
}
