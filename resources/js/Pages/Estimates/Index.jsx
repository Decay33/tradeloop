import { Head, Link } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function Estimates({ estimates }) {
    return (
        <AppLayout>
            <Head title="Estimates" />
            <PageHeader title="Estimates" actionHref="estimates/create" actionLabel="Create Estimate" />
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
