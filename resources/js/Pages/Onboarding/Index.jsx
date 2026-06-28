import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { appUrl } from '../../lib/url';

export default function Onboarding() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        trade_type: 'General Handyman',
        phone: '',
        email: '',
        website: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        state: '',
        zip: '',
        timezone: 'America/New_York',
        google_review_url: '',
        facebook_review_url: '',
        default_tax_rate: 0,
        default_invoice_terms: 'Payment due within 14 days.',
    });

    function submit(event) {
        event.preventDefault();
        post(appUrl('onboarding'));
    }

    return (
        <GuestLayout>
            <Head title="Business Setup" />
            <h1 className="text-2xl font-semibold text-slate-950">Set up your business</h1>
            <form className="mt-6 grid gap-4" onSubmit={submit}>
                {['name', 'trade_type', 'phone', 'email', 'website', 'city', 'state', 'zip', 'timezone', 'google_review_url'].map((field) => (
                    <div key={field}>
                        <label className="text-sm font-medium text-slate-700">{field.replaceAll('_', ' ')}</label>
                        <input className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" value={data[field] || ''} onChange={(event) => setData(field, event.target.value)} />
                        {errors[field] ? <p className="text-sm text-red-600">{errors[field]}</p> : null}
                    </div>
                ))}
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" disabled={processing} type="submit">Create business</button>
            </form>
        </GuestLayout>
    );
}
