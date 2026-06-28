import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function FollowupRules({ rules, serviceTypes, templates }) {
    const form = useForm({ service_type_id: serviceTypes[0]?.id || '', template_id: templates[0]?.id || '', trigger_event: 'job_completed', delay_amount: 1, delay_unit: 'days', channel: 'sms', purpose: 'thank_you', is_active: true });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('settings/follow-up-rules'), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="Follow-Up Rules" />
            <PageHeader title="Follow-Up Rules" />
            <form className="mb-6 grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-4" onSubmit={submit}>
                <select className="rounded-md border px-3 py-2" value={form.data.service_type_id} onChange={(event) => form.setData('service_type_id', event.target.value)}>{serviceTypes.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}</select>
                <select className="rounded-md border px-3 py-2" value={form.data.template_id} onChange={(event) => form.setData('template_id', event.target.value)}>{templates.map((template) => <option key={template.id} value={template.id}>{template.name}</option>)}</select>
                <input className="rounded-md border px-3 py-2" type="number" value={form.data.delay_amount} onChange={(event) => form.setData('delay_amount', event.target.value)} />
                <select className="rounded-md border px-3 py-2" value={form.data.delay_unit} onChange={(event) => form.setData('delay_unit', event.target.value)}><option value="days">days</option><option value="weeks">weeks</option><option value="months">months</option></select>
                <select className="rounded-md border px-3 py-2" value={form.data.channel} onChange={(event) => form.setData('channel', event.target.value)}><option value="sms">SMS</option><option value="email">Email</option></select>
                <select className="rounded-md border px-3 py-2" value={form.data.purpose} onChange={(event) => form.setData('purpose', event.target.value)}>{['thank_you', 'review_request', 'repeat_service', 'warranty_check', 'seasonal_reminder', 'custom'].map((purpose) => <option key={purpose} value={purpose}>{purpose.replaceAll('_', ' ')}</option>)}</select>
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2">Add rule</button>
            </form>
            <div className="rounded-lg border bg-white">
                {rules.map((rule) => <div className="grid gap-2 border-b p-4 md:grid-cols-5" key={rule.id}><span className="font-medium">{rule.service_type?.name}</span><span>{rule.template?.name}</span><span>{rule.delay_amount} {rule.delay_unit}</span><span>{rule.channel}</span><span>{rule.purpose.replaceAll('_', ' ')}</span></div>)}
            </div>
        </AppLayout>
    );
}
