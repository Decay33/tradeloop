import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { localDateTime, shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function ShowJob({ job, completionPreview = [] }) {
    const [completionOpen, setCompletionOpen] = useState(false);
    const completion = useForm({
        completed_at: new Date().toISOString().slice(0, 16),
        schedule_followups: true,
        followups: completionPreview,
    });

    function updateFollowup(index, key, value) {
        const next = [...completion.data.followups];
        next[index] = { ...next[index], [key]: value };
        completion.setData('followups', next);
    }

    function removeFollowup(index) {
        completion.setData('followups', completion.data.followups.filter((_, i) => i !== index));
    }

    function complete(scheduleFollowups) {
        router.post(appUrl(`jobs/${job.id}/complete`), { ...completion.data, schedule_followups: scheduleFollowups }, { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title={job.title} />
            <PageHeader title={job.title} actionHref={`jobs/${job.id}/edit`} actionLabel="Edit Job" />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div className="flex justify-between"><span>{job.customer?.display_name}</span><StatusBadge status={job.status} /></div>
                    <p className="mt-2 text-sm text-slate-600">{job.service_type?.name} scheduled {shortDate(job.scheduled_date)}</p>
                    <p className="mt-2 text-sm text-slate-600">Assigned to: {job.assigned_user?.name || 'Unassigned'}</p>
                    {job.quoted_total_cents ? <p className="mt-2 text-sm text-slate-600">Quoted price: <MoneyText cents={job.quoted_total_cents} /></p> : null}
                    <p className="mt-4 whitespace-pre-line text-sm">{job.job_address}</p>
                    <p className="mt-4 text-sm text-slate-600">{job.notes}</p>
                    <div className="mt-4 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                        <span>Started by: {job.started_by?.name || 'Not started'}</span>
                        <span>Completed by: {job.completed_by?.name || 'Not completed'}</span>
                    </div>
                    <div className="mt-6 grid gap-3 sm:grid-cols-3">
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`jobs/${job.id}/start`))}>Start job</button>
                        <button className="rounded-md bg-teal-700 px-3 py-2 text-left font-semibold text-white" onClick={() => setCompletionOpen(true)}>Complete job</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`jobs/${job.id}/cancel`))}>Cancel job</button>
                    </div>
                    <div className="mt-6 grid gap-3 sm:grid-cols-2">
                        {job.estimate ? <LinkedCard label="Linked Estimate" title={job.estimate.estimate_number} href={`estimates/${job.estimate.id}`} status={job.estimate.status} /> : null}
                        {job.invoice ? <LinkedCard label="Linked Invoice" title={job.invoice.invoice_number} href={`invoices/${job.invoice.id}`} status={job.invoice.status} money={job.invoice.balance_due_cents} /> : null}
                    </div>
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Invoice</h2>
                    {job.invoice ? (
                        <div className="mt-3 space-y-2 text-sm">
                            <div className="flex justify-between"><span>{job.invoice.invoice_number}</span><StatusBadge status={job.invoice.status} /></div>
                            <Row label="Total" cents={job.invoice.total_cents} />
                            <Row label="Paid" cents={job.invoice.amount_paid_cents} />
                            <Row label="Balance" cents={job.invoice.balance_due_cents} strong />
                            <p className="text-slate-600">Due {shortDate(job.invoice.due_date)}</p>
                            <Link className="block rounded-md border px-3 py-2 text-center font-semibold text-slate-700" href={appUrl(`invoices/${job.invoice.id}`)}>View Invoice</Link>
                            <button className="w-full rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`invoices/${job.invoice.id}/send-email`))}>Send Invoice Email</button>
                        </div>
                    ) : (
                        <div className="mt-3 space-y-3 text-sm">
                            <p className="text-slate-600">No invoice linked.</p>
                            <button className="w-full rounded-md bg-teal-700 px-3 py-2 font-semibold text-white" onClick={() => router.post(appUrl(`jobs/${job.id}/create-invoice`))}>Create Invoice From Job</button>
                        </div>
                    )}
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Follow-Ups</h2>
                    <Link className="mt-3 block rounded-md border px-3 py-2 text-center text-sm font-semibold text-slate-700" href={appUrl(`follow-ups/create?job_id=${job.id}`)}>Create Follow-Up</Link>
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
            {completionOpen ? (
                <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/40 p-4">
                    <div className="mx-auto max-w-4xl rounded-lg bg-white p-5 shadow-xl">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-950">Review Follow-Ups</h2>
                                <p className="text-sm text-slate-600">Completed date: {localDateTime(completion.data.completed_at)}</p>
                            </div>
                            <button className="rounded-md border px-3 py-2 text-sm" onClick={() => setCompletionOpen(false)} type="button">Cancel</button>
                        </div>
                        <label className="mt-4 block text-sm font-medium text-slate-700">Completed date<input className="mt-1 block w-full rounded-md border px-3 py-2" type="datetime-local" value={completion.data.completed_at} onChange={(event) => completion.setData('completed_at', event.target.value)} /></label>
                        <div className="mt-5 space-y-3">
                            {completion.data.followups.map((message, index) => (
                                <div className="grid gap-3 rounded-md border border-slate-200 p-3 md:grid-cols-6" key={index}>
                                    <select className="rounded-md border px-3 py-2 text-sm" value={message.channel} onChange={(event) => updateFollowup(index, 'channel', event.target.value)}><option value="sms">SMS</option><option value="email">Email</option></select>
                                    <input className="rounded-md border px-3 py-2 text-sm" value={message.purpose} onChange={(event) => updateFollowup(index, 'purpose', event.target.value)} />
                                    <input className="rounded-md border px-3 py-2 text-sm" type="datetime-local" value={message.scheduled_at} onChange={(event) => updateFollowup(index, 'scheduled_at', event.target.value)} />
                                    <input className="rounded-md border px-3 py-2 text-sm" value={message.recipient || ''} onChange={(event) => updateFollowup(index, 'recipient', event.target.value)} />
                                    <textarea className="rounded-md border px-3 py-2 text-sm md:col-span-2" rows="2" value={message.body || ''} onChange={(event) => updateFollowup(index, 'body', event.target.value)} />
                                    <button className="rounded-md border px-3 py-2 text-sm text-slate-700 md:col-span-6" onClick={() => removeFollowup(index)} type="button">Remove</button>
                                </div>
                            ))}
                        </div>
                        <div className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <button className="rounded-md border px-4 py-2 font-semibold text-slate-700" onClick={() => setCompletionOpen(false)} type="button">Cancel</button>
                            <button className="rounded-md border px-4 py-2 font-semibold text-slate-700" onClick={() => complete(false)} type="button">Complete Job Only</button>
                            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" onClick={() => complete(true)} type="button">Complete Job + Schedule Follow-Ups</button>
                        </div>
                    </div>
                </div>
            ) : null}
        </AppLayout>
    );
}

function Row({ label, cents, strong }) {
    return <div className={`flex justify-between ${strong ? 'font-semibold text-slate-950' : ''}`}><span>{label}</span><MoneyText cents={cents} /></div>;
}

function LinkedCard({ label, title, href, status, money }) {
    return (
        <Link className="block rounded-md border border-slate-200 p-3 text-sm hover:bg-slate-50" href={appUrl(href)}>
            <span className="text-xs font-semibold uppercase text-slate-500">{label}</span>
            <span className="mt-1 flex items-center justify-between gap-2"><span className="font-semibold text-slate-950">{title}</span><StatusBadge status={status} /></span>
            {money !== undefined ? <span className="mt-1 block text-slate-600">Balance: <MoneyText cents={money} /></span> : null}
        </Link>
    );
}
