import { Head, Link, usePage } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { localDateTime } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function ShowCustomer({ customer }) {
    const { auth } = usePage().props;
    const canCreateEstimate = ['owner', 'manager'].includes(auth?.role);

    return (
        <AppLayout>
            <Head title={customer.display_name} />
            <PageHeader title={customer.display_name} actionHref={`customers/${customer.id}/edit`} actionLabel="Edit Customer" />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold">Contact</h2>
                    <p className="mt-3 text-sm text-slate-600">{customer.phone || 'No phone'}<br />{customer.email || 'No email'}<br />{customer.full_address}</p>
                    <p className="mt-3 text-sm">Date added: {localDateTime(customer.created_at)}<br />SMS consent: {customer.sms_consent ? 'Yes' : 'No'}<br />Email consent: {customer.email_consent ? 'Yes' : 'No'}</p>
                    <p className="mt-3 text-sm text-slate-600">{customer.notes}</p>
                    {canCreateEstimate ? (
                        <Link className="mt-4 inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800" href={appUrl(`estimates/create?customer_id=${customer.id}`)}>
                            Create Estimate
                        </Link>
                    ) : null}
                </section>
                <List title="Estimates" items={customer.estimates} href="estimates" number="estimate_number" />
                <List title="Invoices" items={customer.invoices} href="invoices" number="invoice_number" moneyField="balance_due_cents" />
                <List title="Jobs" items={customer.jobs} href="jobs" number="title" />
                <List title="Follow-Ups" items={customer.followup_messages || []} href="follow-ups" number="purpose" />
            </div>
        </AppLayout>
    );
}

function List({ title, items, href, number, moneyField }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="font-semibold">{title}</h2>
            <div className="mt-3 space-y-2">
                {items?.map((item) => (
                    <Link className="flex justify-between rounded-md bg-slate-50 p-3 text-sm" href={appUrl(`${href}/${item.id}`)} key={item.id}>
                        <span>{item[number]} {item.status ? <StatusBadge status={item.status} /> : null}</span>
                        {moneyField ? <MoneyText cents={item[moneyField]} /> : null}
                    </Link>
                ))}
            </div>
        </section>
    );
}
