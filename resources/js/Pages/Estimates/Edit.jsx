import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import EstimateForm from './Partials/EstimateForm';

export default function EditEstimate({ estimate, customers, serviceTypes, defaultTaxRate }) {
    const form = useForm({
        customer_id: estimate.customer_id,
        service_type_id: estimate.service_type_id,
        tax_rate: estimate.tax_rate || defaultTaxRate || 0,
        discount: (estimate.discount_cents || 0) / 100,
        expires_at: estimate.expires_at || '',
        notes: estimate.notes || '',
        terms: estimate.terms || '',
        items: estimate.items.map((item) => ({ ...item, unit_price: item.unit_price_cents / 100 })),
    });

    function submit(event) {
        event.preventDefault();
        form.put(appUrl(`estimates/${estimate.id}`));
    }

    return (
        <AppLayout>
            <Head title="Edit Estimate" />
            <PageHeader title="Edit Estimate" />
            <EstimateForm customers={customers} form={form} onSubmit={submit} serviceTypes={serviceTypes} submitLabel="Save Estimate" />
        </AppLayout>
    );
}
