import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import JobForm from './Partials/JobForm';

export default function CreateJob({ customers, serviceTypes }) {
    const form = useForm({ customer_id: customers[0]?.id || '', service_type_id: serviceTypes[0]?.id || '', title: '', status: 'scheduled', scheduled_date: '', job_address: '', notes: '' });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('jobs'));
    }

    return <AppLayout><Head title="Create Job" /><PageHeader title="Create Job" /><JobForm customers={customers} form={form} onSubmit={submit} serviceTypes={serviceTypes} submitLabel="Save Job" /></AppLayout>;
}
