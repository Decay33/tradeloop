import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function CreateFollowUp({ customers, estimates, jobs, selectedCustomerId, selectedEstimateId, selectedJobId }) {
    const form = useForm({
        customer_id: selectedCustomerId || customers[0]?.id || '',
        estimate_id: selectedEstimateId || '',
        job_id: selectedJobId || '',
        channel: 'sms',
        purpose: 'sales_follow_up',
        scheduled_at: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().slice(0, 16),
        subject: '',
        body: 'Hi, just following up to see if you had any questions or wanted to get on the schedule.',
    });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('follow-ups'));
    }

    return (
        <AppLayout>
            <Head title="Create Follow-Up" />
            <PageHeader title="Create Follow-Up" />
            <form className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-2" onSubmit={submit}>
                <Select label="Customer" value={form.data.customer_id} onChange={(value) => form.setData('customer_id', value)} options={customers.map((customer) => [customer.id, customer.display_name])} error={form.errors.customer_id} />
                <Select label="Estimate" value={form.data.estimate_id} onChange={(value) => form.setData('estimate_id', value)} options={[['', 'None'], ...estimates.map((estimate) => [estimate.id, `${estimate.estimate_number} - ${estimate.customer?.display_name}`])]} />
                <Select label="Job" value={form.data.job_id} onChange={(value) => form.setData('job_id', value)} options={[['', 'None'], ...jobs.map((job) => [job.id, `${job.title} - ${job.customer?.display_name}`])]} />
                <Select label="Channel" value={form.data.channel} onChange={(value) => form.setData('channel', value)} options={[['sms', 'SMS'], ['email', 'Email']]} />
                <Select label="Purpose" value={form.data.purpose} onChange={(value) => form.setData('purpose', value)} options={[
                    ['sales_follow_up', 'Sales Follow-Up'],
                    ['thank_you', 'Thank You'],
                    ['review_request', 'Review Request'],
                    ['repeat_service', 'Repeat Service'],
                    ['seasonal_reminder', 'Seasonal Reminder'],
                    ['warranty_check', 'Warranty Check'],
                ]} />
                <label className="text-sm font-medium text-slate-700">Scheduled date<input className="mt-1 block w-full rounded-md border px-3 py-2" type="datetime-local" value={form.data.scheduled_at} onChange={(event) => form.setData('scheduled_at', event.target.value)} />{form.errors.scheduled_at ? <p className="text-sm text-red-600">{form.errors.scheduled_at}</p> : null}</label>
                <label className="text-sm font-medium text-slate-700 md:col-span-2">Subject<input className="mt-1 block w-full rounded-md border px-3 py-2" value={form.data.subject} onChange={(event) => form.setData('subject', event.target.value)} /></label>
                <label className="text-sm font-medium text-slate-700 md:col-span-2">Message<textarea className="mt-1 block w-full rounded-md border px-3 py-2" rows="5" value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} />{form.errors.body ? <p className="text-sm text-red-600">{form.errors.body}</p> : null}</label>
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2" disabled={form.processing} type="submit">Save Follow-Up</button>
            </form>
        </AppLayout>
    );
}

function Select({ label, value, onChange, options, error }) {
    return (
        <label className="text-sm font-medium text-slate-700">
            {label}
            <select className="mt-1 block w-full rounded-md border px-3 py-2" value={value || ''} onChange={(event) => onChange(event.target.value)}>
                {options.map(([id, name], index) => <option key={`${id}-${index}`} value={id}>{name}</option>)}
            </select>
            {error ? <p className="text-sm text-red-600">{error}</p> : null}
        </label>
    );
}
