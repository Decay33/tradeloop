import { Head, useForm } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function ServiceTypes({ serviceTypes }) {
    const form = useForm({ name: '', category: '', description: '', default_price: 0, default_repeat_months: 12, is_active: true });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('settings/service-types'), { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <AppLayout>
            <Head title="Service Types" />
            <PageHeader title="Service Types" />
            <form className="mb-6 grid gap-3 rounded-lg border bg-white p-4 md:grid-cols-5" onSubmit={submit}>
                <input className="rounded-md border px-3 py-2" placeholder="Name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                <input className="rounded-md border px-3 py-2" placeholder="Category" value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} />
                <input className="rounded-md border px-3 py-2" placeholder="Default price" type="number" step="0.01" value={form.data.default_price} onChange={(event) => form.setData('default_price', event.target.value)} />
                <input className="rounded-md border px-3 py-2" placeholder="Repeat months" type="number" value={form.data.default_repeat_months} onChange={(event) => form.setData('default_repeat_months', event.target.value)} />
                <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white">Add service</button>
            </form>
            <div className="rounded-lg border bg-white">
                {serviceTypes.map((service) => <div className="grid gap-2 border-b p-4 md:grid-cols-4" key={service.id}><span className="font-medium">{service.name}</span><span>{service.category}</span><MoneyText cents={service.default_price_cents} /><span>{service.default_repeat_months || '-'} months</span></div>)}
            </div>
        </AppLayout>
    );
}
