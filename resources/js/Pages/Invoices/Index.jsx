import { Head, Link } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function Invoices({ invoices }) {
    return (
        <AppLayout>
            <Head title="Invoices" />
            <PageHeader title="Invoices" actionHref="invoices/create" actionLabel="Create Invoice" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {invoices.data.map((invoice) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`invoices/${invoice.id}`)} key={invoice.id}>
                        <span className="font-medium">{invoice.invoice_number}</span>
                        <span>{invoice.customer?.display_name}</span>
                        <StatusBadge status={invoice.status} />
                        <MoneyText cents={invoice.total_cents} />
                        <MoneyText cents={invoice.balance_due_cents} />
                        <span>{shortDate(invoice.due_date)}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}
