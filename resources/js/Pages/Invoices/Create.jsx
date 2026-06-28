import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import InvoiceForm from './Partials/InvoiceForm';

export default function CreateInvoice({ customers, defaultTaxRate }) {
    const form = useForm({ customer_id: customers[0]?.id || '', tax_rate: defaultTaxRate || 0, discount: 0, due_date: '', items: [{ description: '', quantity: 1, unit_price: 0 }] });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('invoices'));
    }

    return (
        <AppLayout>
            <Head title="Create Invoice" />
            <PageHeader title="Create Invoice" />
            <InvoiceForm customers={customers} form={form} onSubmit={submit} submitLabel="Save Invoice" />
        </AppLayout>
    );
}
