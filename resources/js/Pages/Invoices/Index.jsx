import { Head, Link } from '@inertiajs/react';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

const filters = [
    ['all', 'All'],
    ['draft', 'Draft'],
    ['sent', 'Sent'],
    ['partially_paid', 'Partially Paid'],
    ['paid', 'Paid'],
    ['unpaid', 'Unpaid'],
    ['due_soon', 'Due Soon'],
    ['past_due', 'Past Due'],
    ['30_overdue', '30+ Days Overdue'],
    ['60_overdue', '60+ Days Overdue'],
];

export default function Invoices({ invoices, filters: activeFilters = {} }) {
    const active = activeFilters.filter || 'all';

    return (
        <AppLayout>
            <Head title="Invoices" />
            <PageHeader title="Invoices" actionHref="invoices/create" actionLabel="Create Invoice" />
            <FilterTabs filters={filters} active={active} base="invoices" />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {invoices.data.map((invoice) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`invoices/${invoice.id}`)} key={invoice.id}>
                        <span className="font-medium">{invoice.invoice_number}</span>
                        <span>{invoice.customer?.display_name}</span>
                        <StatusBadge status={invoice.status} />
                        <MoneyText cents={invoice.total_cents} />
                        <MoneyText cents={invoice.balance_due_cents} />
                        <span>{shortDate(invoice.due_date)} {invoice.urgency ? <StatusBadge status={invoice.urgency} /> : null}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}

function FilterTabs({ filters, active, base }) {
    return <div className="mb-4 flex gap-2 overflow-x-auto">{filters.map(([value, label]) => <Link className={`whitespace-nowrap rounded-md border px-3 py-2 text-sm ${active === value ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-700'}`} href={appUrl(`${base}${value === 'all' ? '' : `?filter=${value}`}`)} key={value}>{label}</Link>)}</div>;
}
