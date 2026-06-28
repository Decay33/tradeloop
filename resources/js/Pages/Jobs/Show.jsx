import { Head, Link, router } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function ShowJob({ job }) {
    return (
        <AppLayout>
            <Head title={job.title} />
            <PageHeader title={job.title} actionHref={`jobs/${job.id}/edit`} actionLabel="Edit Job" />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div className="flex justify-between"><span>{job.customer?.display_name}</span><StatusBadge status={job.status} /></div>
                    <p className="mt-2 text-sm text-slate-600">{job.service_type?.name} scheduled {shortDate(job.scheduled_date)}</p>
                    <p className="mt-4 whitespace-pre-line text-sm">{job.job_address}</p>
                    <p className="mt-4 text-sm text-slate-600">{job.notes}</p>
                    <div className="mt-6 grid gap-3 sm:grid-cols-3">
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`jobs/${job.id}/start`))}>Start job</button>
                        <button className="rounded-md bg-teal-700 px-3 py-2 text-left font-semibold text-white" onClick={() => router.post(appUrl(`jobs/${job.id}/complete`))}>Complete job</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`jobs/${job.id}/cancel`))}>Cancel job</button>
                    </div>
                    {job.invoice ? <Link className="mt-4 block font-semibold text-teal-800" href={appUrl(`invoices/${job.invoice.id}`)}>Invoice balance: <MoneyText cents={job.invoice.balance_due_cents} /></Link> : null}
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Follow-Ups</h2>
                    <div className="mt-3 space-y-2">
                        {job.followup_messages?.map((message) => (
                            <Link className="block rounded-md bg-slate-50 p-3 text-sm" href={appUrl(`follow-ups/${message.id}`)} key={message.id}>
                                <span className="font-medium">{message.purpose.replaceAll('_', ' ')}</span><br />
                                <span>{shortDate(message.scheduled_at)}</span> <StatusBadge status={message.status} />
                            </Link>
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
