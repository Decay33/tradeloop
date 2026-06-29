import { Head, Link, router, useForm } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function ShowInvoice({ invoice }) {
    const payment = useForm({ amount: '', payment_method: 'cash', payment_date: new Date().toISOString().slice(0, 10), notes: '' });

    function pay(event) {
        event.preventDefault();
        payment.post(appUrl(`invoices/${invoice.id}/payments`), { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title={invoice.invoice_number} />
            <PageHeader title={`Invoice ${invoice.invoice_number}`} actionHref={`invoices/${invoice.id}/edit`} actionLabel="Edit Invoice" />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
                    <div className="flex justify-between"><span>{invoice.customer?.display_name}</span><StatusBadge status={invoice.status} /></div>
                    <p className="mt-2 text-sm text-slate-600">Due {shortDate(invoice.due_date)}</p>
                    <div className="mt-6 space-y-2">
                        {invoice.items.map((item) => <div className="flex justify-between border-b py-2" key={item.id}><span>{item.description}</span><MoneyText cents={item.line_total_cents} /></div>)}
                    </div>
                    <div className="ml-auto mt-6 max-w-xs space-y-2">
                        <Row label="Total" value={invoice.total_cents} />
                        <Row label="Paid" value={invoice.amount_paid_cents} />
                        <Row label="Balance" value={invoice.balance_due_cents} strong />
                    </div>
                    <div className="mt-6 grid gap-3 sm:grid-cols-2">
                        {invoice.estimate ? <LinkedCard label="Linked Estimate" title={invoice.estimate.estimate_number} href={`estimates/${invoice.estimate.id}`} status={invoice.estimate.status} /> : null}
                        {invoice.job ? <LinkedCard label="Linked Job" title={invoice.job.title} href={`jobs/${invoice.job.id}`} status={invoice.job.status} /> : null}
                    </div>
                    <section className="mt-6">
                        <h2 className="font-semibold">Payments</h2>
                        <div className="mt-3 overflow-hidden rounded-md border border-slate-200">
                            {(invoice.payments || []).map((item) => (
                                <div className="grid gap-2 border-b border-slate-100 p-3 text-sm md:grid-cols-5" key={item.id}>
                                    <span>{shortDate(item.payment_date)}</span>
                                    <MoneyText cents={item.amount_cents} />
                                    <span>{item.payment_method?.replaceAll('_', ' ')}</span>
                                    <span>{item.recorded_by?.name || 'Unknown'}</span>
                                    <span className="text-slate-500">{item.notes}</span>
                                </div>
                            ))}
                            {invoice.payments?.length ? null : <p className="p-3 text-sm text-slate-600">No payments recorded.</p>}
                        </div>
                    </section>
                </section>
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Actions</h2>
                    <div className="mt-4 grid gap-2">
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`invoices/${invoice.id}/mark-sent`))}>Mark sent</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`invoices/${invoice.id}/send-email`))}>Send Invoice Email</button>
                        <button className="rounded-md border px-3 py-2 text-left" onClick={() => router.post(appUrl(`invoices/${invoice.id}/void`))}>Void invoice</button>
                        <a className="rounded-md border px-3 py-2" href={appUrl(`invoices/${invoice.id}/print`)}>Print invoice</a>
                        <a className="rounded-md border px-3 py-2" href={appUrl(`invoices/${invoice.id}/download`)}>Download PDF</a>
                    </div>
                    <form className="mt-6 space-y-3" onSubmit={pay}>
                        <h3 className="font-semibold">{invoice.payments?.length ? 'Record another payment' : 'Record payment'}</h3>
                        <input className="w-full rounded-md border px-3 py-2" placeholder="Amount" type="number" step="0.01" value={payment.data.amount} onChange={(event) => payment.setData('amount', event.target.value)} />
                        <select className="w-full rounded-md border px-3 py-2" value={payment.data.payment_method} onChange={(event) => payment.setData('payment_method', event.target.value)}>
                            {['cash', 'check', 'credit_card', 'bank_transfer', 'other'].map((method) => <option key={method} value={method}>{method.replaceAll('_', ' ')}</option>)}
                        </select>
                        <input className="w-full rounded-md border px-3 py-2" type="date" value={payment.data.payment_date} onChange={(event) => payment.setData('payment_date', event.target.value)} />
                        <textarea className="w-full rounded-md border px-3 py-2" placeholder="Notes" rows="3" value={payment.data.notes} onChange={(event) => payment.setData('notes', event.target.value)} />
                        {payment.errors.amount ? <p className="text-sm text-red-600">{payment.errors.amount}</p> : null}
                        <button className="w-full rounded-md bg-teal-700 px-3 py-2 font-semibold text-white">Record payment</button>
                    </form>
                    <div className="mt-6 space-y-2">
                        <h3 className="font-semibold">Email Events</h3>
                        {(invoice.send_events || []).map((event) => (
                            <div className="rounded-md bg-slate-50 p-3 text-sm" key={event.id}>
                                <div className="flex justify-between"><span>{event.recipient || 'No recipient'}</span><StatusBadge status={event.status} /></div>
                                <p className="mt-1 text-slate-600">{shortDate(event.sent_at || event.created_at)}</p>
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}

function Row({ label, value, strong }) {
    return <div className={`flex justify-between ${strong ? 'text-lg font-semibold' : ''}`}><span>{label}</span><MoneyText cents={value} /></div>;
}

function LinkedCard({ label, title, href, status }) {
    return (
        <Link className="block rounded-md border border-slate-200 p-3 text-sm hover:bg-slate-50" href={appUrl(href)}>
            <span className="text-xs font-semibold uppercase text-slate-500">{label}</span>
            <span className="mt-1 flex items-center justify-between gap-2"><span className="font-semibold text-slate-950">{title}</span><StatusBadge status={status} /></span>
        </Link>
    );
}
