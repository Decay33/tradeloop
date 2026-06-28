import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import EstimateForm from './Partials/EstimateForm';

export default function CreateEstimate({ customers, serviceTypes, defaultTaxRate, selectedCustomerId }) {
    const form = useForm({ customer_id: selectedCustomerId || customers[0]?.id || '', service_type_id: serviceTypes[0]?.id || '', tax_rate: defaultTaxRate || 0, discount: 0, expires_at: '', notes: '', terms: '', items: [{ description: '', quantity: 1, unit_price: 0 }] });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('estimates'));
    }

    return (
        <AppLayout>
            <Head title="Create Estimate" />
            <PageHeader title="Create Estimate" />
            <EstimateForm customers={customers} form={form} onSubmit={submit} serviceTypes={serviceTypes} submitLabel="Save Estimate" />
        </AppLayout>
    );
}
