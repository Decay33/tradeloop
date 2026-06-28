import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import InvoiceForm from './Partials/InvoiceForm';

export default function EditInvoice({ invoice, customers, defaultTaxRate }) {
    const form = useForm({ customer_id: invoice.customer_id, tax_rate: invoice.tax_rate || defaultTaxRate || 0, discount: (invoice.discount_cents || 0) / 100, due_date: invoice.due_date || '', items: invoice.items.map((item) => ({ ...item, unit_price: item.unit_price_cents / 100 })) });

    function submit(event) {
        event.preventDefault();
        form.put(appUrl(`invoices/${invoice.id}`));
    }

    return (
        <AppLayout>
            <Head title="Edit Invoice" />
            <PageHeader title="Edit Invoice" />
            <InvoiceForm customers={customers} form={form} onSubmit={submit} submitLabel="Save Invoice" />
        </AppLayout>
    );
}
