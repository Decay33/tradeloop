import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function Business({ business }) {
    const form = useForm({ ...business });

    function submit(event) {
        event.preventDefault();
        form.put(appUrl('settings/business'));
    }

    const fields = ['name', 'trade_type', 'phone', 'email', 'website', 'address_line_1', 'address_line_2', 'city', 'state', 'zip', 'timezone', 'google_review_url', 'facebook_review_url', 'default_tax_rate'];

    return (
        <AppLayout>
            <Head title="Business Settings" />
            <PageHeader title="Business Profile" />
            <form className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-2" onSubmit={submit}>
                {fields.map((field) => <label className="text-sm font-medium text-slate-700" key={field}>{field.replaceAll('_', ' ')}<input className="mt-1 block w-full rounded-md border px-3 py-2" value={form.data[field] || ''} onChange={(event) => form.setData(field, event.target.value)} /></label>)}
                <label className="text-sm font-medium text-slate-700 md:col-span-2">Default invoice terms<textarea className="mt-1 block w-full rounded-md border px-3 py-2" value={form.data.default_invoice_terms || ''} onChange={(event) => form.setData('default_invoice_terms', event.target.value)} /></label>
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2">Save profile</button>
            </form>
        </AppLayout>
    );
}
