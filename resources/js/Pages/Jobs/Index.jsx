import { Head, Link } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

const filters = [
    ['all', 'All'],
    ['scheduled', 'Scheduled'],
    ['in_progress', 'In Progress'],
    ['completed', 'Completed'],
    ['canceled', 'Canceled'],
    ['no_invoice', 'No Invoice'],
    ['unassigned', 'Unassigned'],
    ['assigned_to_me', 'Assigned To Me'],
];

export default function Jobs({ jobs, filters: activeFilters = {} }) {
    const active = activeFilters.filter || 'all';

    return (
        <AppLayout>
            <Head title="Jobs" />
            <PageHeader title="Jobs" actionHref="jobs/create" actionLabel="Create Job" />
            <FilterTabs filters={filters} active={active} base="jobs" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {jobs.data.map((job) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`jobs/${job.id}`)} key={job.id}>
                        <span className="font-medium">{job.title}</span>
                        <span>{job.customer?.display_name}</span>
                        <span>{job.service_type?.name}</span>
                        <StatusBadge status={job.status} />
                        <span>{job.assigned_user?.name || 'Unassigned'}</span>
                        <span>{shortDate(job.scheduled_date)}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}

function FilterTabs({ filters, active, base }) {
    return <div className="mb-4 flex gap-2 overflow-x-auto">{filters.map(([value, label]) => <Link className={`whitespace-nowrap rounded-md border px-3 py-2 text-sm ${active === value ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-700'}`} href={appUrl(`${base}${value === 'all' ? '' : `?filter=${value}`}`)} key={value}>{label}</Link>)}</div>;
}
