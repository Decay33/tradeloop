import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import LineItemEditor from '../../Components/LineItemEditor';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function ShowEstimate({ estimate, teamMembers = [] }) {
    const [modalOpen, setModalOpen] = useState(false);
    const dueDate = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
    const convert = useForm({
        job_title: `${estimate.service_type?.name || 'Job'} for ${estimate.customer?.display_name || 'customer'}`,
        scheduled_date: '',
        assigned_user_id: '',
        job_address: estimate.customer?.full_address || '',
        job_notes: '',
        create_invoice: true,
        invoice_due_date: dueDate,
        invoice_discount: (estimate.discount_cents || 0) / 100,
        invoice_tax_rate: estimate.tax_rate || 0,
        invoice_items: estimate.items.map((item) => ({ description: item.description, quantity: item.quantity, unit_price: item.unit_price_cents / 100 })),
    });

    function createFromEstimate(createInvoice) {
        router.post(appUrl(`estimates/${estimate.id}/create-job-and-invoice`), { ...convert.data, create_invoice: createInvoice }, { preserveScroll: true });
    }

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
                        {estimate.status === 'accepted' && !estimate.job ? <button className="rounded-md bg-teal-700 px-3 py-2 text-left font-semibold text-white" onClick={() => setModalOpen(true)}>Create job and invoice</button> : null}
                        <Link className="rounded-md border px-3 py-2" href={appUrl(`follow-ups/create?estimate_id=${estimate.id}`)}>Create follow-up</Link>
                        <a className="rounded-md border px-3 py-2" href={appUrl(`estimates/${estimate.id}/print`)}>Print estimate</a>
                    </div>
                    <div className="mt-6 space-y-3">
                        {estimate.job ? <LinkedCard label="Linked Job" title={estimate.job.title} href={`jobs/${estimate.job.id}`} status={estimate.job.status} /> : null}
                        {estimate.invoice ? <LinkedCard label="Linked Invoice" title={estimate.invoice.invoice_number} href={`invoices/${estimate.invoice.id}`} status={estimate.invoice.status} money={estimate.invoice.balance_due_cents} /> : null}
                    </div>
                </section>
            </div>
            {modalOpen ? (
                <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/40 p-4">
                    <div className="mx-auto max-w-3xl rounded-lg bg-white p-5 shadow-xl">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-950">Review Job + Invoice</h2>
                                <p className="text-sm text-slate-600">{estimate.customer?.display_name}</p>
                            </div>
                            <button className="rounded-md border px-3 py-2 text-sm" onClick={() => setModalOpen(false)} type="button">Cancel</button>
                        </div>
                        <div className="mt-5 grid gap-4 md:grid-cols-2">
                            <Input label="Job title" value={convert.data.job_title} onChange={(value) => convert.setData('job_title', value)} />
                            <Input label="Scheduled date" type="date" value={convert.data.scheduled_date} onChange={(value) => convert.setData('scheduled_date', value)} />
                            <label className="text-sm font-medium text-slate-700">Assigned team member<select className="mt-1 block w-full rounded-md border px-3 py-2" value={convert.data.assigned_user_id} onChange={(event) => convert.setData('assigned_user_id', event.target.value)}><option value="">Unassigned</option>{teamMembers.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}</select></label>
                            <Input label="Invoice due date" type="date" value={convert.data.invoice_due_date} onChange={(value) => convert.setData('invoice_due_date', value)} />
                            <Input label="Job address" value={convert.data.job_address} onChange={(value) => convert.setData('job_address', value)} />
                            <Input label="Invoice discount" type="number" step="0.01" value={convert.data.invoice_discount} onChange={(value) => convert.setData('invoice_discount', value)} />
                            <Input label="Invoice tax rate" type="number" step="0.0001" value={convert.data.invoice_tax_rate} onChange={(value) => convert.setData('invoice_tax_rate', value)} />
                            <label className="text-sm font-medium text-slate-700 md:col-span-2">Job notes<textarea className="mt-1 block w-full rounded-md border px-3 py-2" rows="3" value={convert.data.job_notes} onChange={(event) => convert.setData('job_notes', event.target.value)} /></label>
                        </div>
                        <div className="mt-5">
                            <h3 className="mb-3 font-semibold">Invoice line items</h3>
                            <LineItemEditor items={convert.data.invoice_items} setItems={(items) => convert.setData('invoice_items', items)} />
                        </div>
                        <div className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-end">
                            <button className="rounded-md border px-4 py-2 font-semibold text-slate-700" onClick={() => setModalOpen(false)} type="button">Cancel</button>
                            <button className="rounded-md border px-4 py-2 font-semibold text-slate-700" onClick={() => createFromEstimate(false)} type="button">Create Job Only</button>
                            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" onClick={() => createFromEstimate(true)} type="button">Create Job + Invoice</button>
                        </div>
                    </div>
                </div>
            ) : null}
        </AppLayout>
    );
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

function Input({ label, value, onChange, ...props }) {
    return <label className="text-sm font-medium text-slate-700">{label}<input className="mt-1 block w-full rounded-md border px-3 py-2" value={value || ''} onChange={(event) => onChange(event.target.value)} {...props} /></label>;
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
