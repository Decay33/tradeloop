import { title } from '../lib/format';

const colors = {
    draft: 'bg-slate-100 text-slate-700',
    sent: 'bg-blue-100 text-blue-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    declined: 'bg-rose-100 text-rose-700',
    paid: 'bg-emerald-100 text-emerald-700',
    partially_paid: 'bg-amber-100 text-amber-800',
    overdue: 'bg-red-100 text-red-700',
    void: 'bg-slate-200 text-slate-600',
    scheduled: 'bg-cyan-100 text-cyan-700',
    in_progress: 'bg-blue-100 text-blue-700',
    completed: 'bg-emerald-100 text-emerald-700',
    canceled: 'bg-slate-200 text-slate-600',
    simulated_sent: 'bg-emerald-100 text-emerald-700',
    skipped: 'bg-amber-100 text-amber-800',
    due_soon: 'bg-amber-100 text-amber-800',
    past_due: 'bg-red-100 text-red-700',
    very_overdue: 'bg-red-100 text-red-800',
    critical: 'bg-red-200 text-red-900',
    sales_follow_up: 'bg-violet-100 text-violet-700',
};

export default function StatusBadge({ status }) {
    return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${colors[status] || 'bg-slate-100 text-slate-700'}`}>{title(status)}</span>;
}
