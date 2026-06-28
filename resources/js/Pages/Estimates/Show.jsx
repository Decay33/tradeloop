import { Head, Link, router } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function ShowEstimate({ estimate }) {
    return (
        <AppLayout>
            <Head title={estimate.estimate_number} />
            <PageHeader title={`Estimate ${estimate.estimate_number}`} actionHref={`estimates/${estimate.id}/edit`} actionLabel="Edit Estimate" />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-slate-600">{estimate.customer?.display_name}</p>
                            <p className="font-medium">{estimate.service_type?.name}</p>
                        </div>
                        <StatusBadge status={estimate.status} />
                    </div>
                    <div className="mt-6 space-y-2">
                        {estimate.items.map((item) => (
                            <div className="flex justify-between border-b border-slate-100 py-2" key={item.id}>
                                <span>{item.description}</span>
                                <MoneyText cents={item.line_total_cents} />
                            </div>
                        ))}
                    </div>
                    <Totals record={estimate} />
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Actions</h2>
                    <div className="mt-4 grid gap-2">
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`estimates/${estimate.id}/mark-sent`))}>Mark sent</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`estimates/${estimate.id}/accept`))}>Mark accepted</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`estimates/${estimate.id}/decline`))}>Mark declined</button>
                        {estimate.status === 'accepted' ? <button className="rounded-md bg-teal-700 px-3 py-2 text-left font-semibold text-white" onClick={() => router.post(appUrl(`estimates/${estimate.id}/create-job-and-invoice`))}>Create job and invoice</button> : null}
                        <a className="rounded-md border px-3 py-2" href={appUrl(`estimates/${estimate.id}/print`)}>Print estimate</a>
                    </div>
                    {estimate.job ? <Link className="mt-4 block text-sm font-semibold text-teal-800" href={appUrl(`jobs/${estimate.job.id}`)}>View linked job</Link> : null}
                    {estimate.invoice ? <Link className="mt-2 block text-sm font-semibold text-teal-800" href={appUrl(`invoices/${estimate.invoice.id}`)}>View linked invoice</Link> : null}
                </section>
            </div>
        </AppLayout>
    );
}

function Totals({ record }) {
    return (
        <div className="ml-auto mt-6 w-full max-w-xs space-y-2 text-sm">
            <div className="flex justify-between"><span>Subtotal</span><MoneyText cents={record.subtotal_cents} /></div>
            <div className="flex justify-between"><span>Discount</span><MoneyText cents={record.discount_cents} /></div>
            <div className="flex justify-between"><span>Tax</span><MoneyText cents={record.tax_cents} /></div>
            <div className="flex justify-between text-lg font-semibold"><span>Total</span><MoneyText cents={record.total_cents} /></div>
        </div>
    );
}
