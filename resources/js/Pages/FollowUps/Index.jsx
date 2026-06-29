import { Head, Link } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

const filters = [
    ['due_today', 'Due Today'],
    ['upcoming', 'Upcoming'],
    ['sales_follow_ups', 'Sales Follow-Ups'],
    ['job_follow_ups', 'Job Follow-Ups'],
    ['sent', 'Sent'],
    ['skipped', 'Skipped'],
    ['canceled', 'Canceled'],
    ['all', 'All'],
];

export default function FollowUps({ messages, filters: activeFilters = {} }) {
    const active = activeFilters.filter || 'due_today';

    return (
        <AppLayout>
            <Head title="Follow-Ups" />
            <PageHeader title="Follow-Ups" description="Scheduled and simulated messages. No real SMS or email is sent." actionHref="follow-ups/create" actionLabel="Create Follow-Up" />
            <FilterTabs filters={filters} active={active} base="follow-ups" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {messages.data.map((message) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`follow-ups/${message.id}`)} key={message.id}>
                        <span className="font-medium">{message.customer?.display_name}</span>
                        <span>{message.channel.toUpperCase()}</span>
                        <span>{message.purpose.replaceAll('_', ' ')}</span>
                        <StatusBadge status={message.status} />
                        <span>{shortDate(message.scheduled_at)}</span>
                        <span className="text-sm text-slate-500">{message.skip_reason}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}

function FilterTabs({ filters, active, base }) {
    return <div className="mb-4 flex gap-2 overflow-x-auto">{filters.map(([value, label]) => <Link className={`whitespace-nowrap rounded-md border px-3 py-2 text-sm ${active === value ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-700'}`} href={appUrl(`${base}${value === 'all' ? '?filter=all' : `?filter=${value}`}`)} key={value}>{label}</Link>)}</div>;
}
