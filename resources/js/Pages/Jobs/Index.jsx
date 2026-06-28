import { Head, Link } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function Jobs({ jobs }) {
    return (
        <AppLayout>
            <Head title="Jobs" />
            <PageHeader title="Jobs" actionHref="jobs/create" actionLabel="Create Job" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {jobs.data.map((job) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-5" href={appUrl(`jobs/${job.id}`)} key={job.id}>
                        <span className="font-medium">{job.title}</span>
                        <span>{job.customer?.display_name}</span>
                        <span>{job.service_type?.name}</span>
                        <StatusBadge status={job.status} />
                        <span>{shortDate(job.scheduled_date)}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}
