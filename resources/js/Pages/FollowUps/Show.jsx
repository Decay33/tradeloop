import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function ShowFollowUp({ message }) {
    const form = useForm({ scheduled_at: message.scheduled_at ? message.scheduled_at.slice(0, 16) : '' });

    function reschedule(event) {
        event.preventDefault();
        form.post(appUrl(`follow-ups/${message.id}/reschedule`), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="Follow-Up" />
            <PageHeader title={`${message.channel.toUpperCase()} ${message.purpose.replaceAll('_', ' ')}`} />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div className="flex justify-between"><span>{message.customer?.display_name}</span><StatusBadge status={message.status} /></div>
                    <p className="mt-2 text-sm text-slate-600">Recipient: {message.recipient || 'None'}<br />Scheduled: {shortDate(message.scheduled_at)}</p>
                    {message.skip_reason ? <p className="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-900">{message.skip_reason}</p> : null}
                    {message.subject ? <h2 className="mt-6 font-semibold">{message.subject}</h2> : null}
                    <p className="mt-3 whitespace-pre-line rounded-md bg-slate-50 p-4 text-sm">{message.body}</p>
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Actions</h2>
                    <div className="mt-4 grid gap-2">
                        <button className="rounded-md bg-teal-700 px-3 py-2 text-left font-semibold text-white" onClick={() => router.post(appUrl(`follow-ups/${message.id}/send-now`))}>Send now</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`follow-ups/${message.id}/cancel`))}>Cancel</button>
                    </div>
                    <form className="mt-5 space-y-2" onSubmit={reschedule}>
                        <label className="text-sm font-medium">Reschedule<input className="mt-1 block w-full rounded-md border px-3 py-2" type="datetime-local" value={form.data.scheduled_at} onChange={(event) => form.setData('scheduled_at', event.target.value)} /></label>
                        <button className="w-full rounded-md border px-3 py-2 font-semibold" type="submit">Save date</button>
                    </form>
                    <h3 className="mt-6 font-semibold">Events</h3>
                    <div className="mt-2 space-y-2">
                        {message.events.map((event) => <div className="rounded-md bg-slate-50 p-2 text-sm" key={event.id}>{event.event_type} - {shortDate(event.created_at)}</div>)}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
