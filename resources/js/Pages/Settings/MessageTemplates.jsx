import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function MessageTemplates({ templates }) {
    const form = useForm({ name: '', channel: 'sms', purpose: 'thank_you', subject: '', body: '', is_default: false });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('settings/message-templates'), { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <AppLayout>
            <Head title="Message Templates" />
            <PageHeader title="Message Templates" />
            <form className="mb-6 grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-2" onSubmit={submit}>
                <input className="rounded-md border px-3 py-2" placeholder="Name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                <select className="rounded-md border px-3 py-2" value={form.data.channel} onChange={(event) => form.setData('channel', event.target.value)}><option value="sms">SMS</option><option value="email">Email</option></select>
                <select className="rounded-md border px-3 py-2" value={form.data.purpose} onChange={(event) => form.setData('purpose', event.target.value)}>{['thank_you', 'review_request', 'repeat_service', 'warranty_check', 'seasonal_reminder', 'custom'].map((purpose) => <option key={purpose} value={purpose}>{purpose.replaceAll('_', ' ')}</option>)}</select>
                <input className="rounded-md border px-3 py-2" placeholder="Subject" value={form.data.subject || ''} onChange={(event) => form.setData('subject', event.target.value)} />
                <textarea className="rounded-md border px-3 py-2 md:col-span-2" placeholder="Body" rows="4" value={form.data.body} onChange={(event) => form.setData('body', event.target.value)} />
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2">Add template</button>
            </form>
            <div className="space-y-3">
                {templates.map((template) => <div className="rounded-lg border bg-white p-4" key={template.id}><div className="font-semibold">{template.name}</div><div className="text-sm text-slate-600">{template.channel} - {template.purpose}</div><p className="mt-2 whitespace-pre-line text-sm">{template.body}</p></div>)}
            </div>
        </AppLayout>
    );
}
