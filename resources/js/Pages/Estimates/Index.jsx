import { Head, Link } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

const filters = [
    ['all', 'All'],
    ['draft', 'Draft'],
    ['sent', 'Sent'],
    ['accepted', 'Accepted'],
    ['declined', 'Declined'],
    ['expired', 'Expired'],
    ['needs_follow_up', 'Needs Follow-Up'],
    ['accepted_no_job', 'Accepted - No Job'],
    ['accepted_has_job', 'Accepted - Has Job'],
    ['sent_30_days', 'Sent 30+ Days Old'],
];

export default function Estimates({ estimates, filters: activeFilters = {} }) {
    const active = activeFilters.filter || 'all';

    return (
        <AppLayout>
            <Head title="Estimates" />
            <PageHeader title="Estimates" actionHref="estimates/create" actionLabel="Create Estimate" />
            <FilterTabs filters={filters} active={active} base="estimates" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {estimates.data.map((estimate) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`estimates/${estimate.id}`)} key={estimate.id}>
                        <span className="font-medium">{estimate.estimate_number}</span>
                        <span>{estimate.customer?.display_name}</span>
                        <span>{estimate.service_type?.name}</span>
                        <StatusBadge status={estimate.status} />
                        <MoneyText cents={estimate.total_cents} />
                        <span>{shortDate(estimate.expires_at)}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}

function FilterTabs({ filters, active, base }) {
    return <div className="mb-4 flex gap-2 overflow-x-auto">{filters.map(([value, label]) => <Link className={`whitespace-nowrap rounded-md border px-3 py-2 text-sm ${active === value ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-700'}`} href={appUrl(`${base}${value === 'all' ? '' : `?filter=${value}`}`)} key={value}>{label}</Link>)}</div>;
}
