import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import JobForm from './Partials/JobForm';

export default function EditJob({ job, customers, serviceTypes }) {
    const form = useForm({ customer_id: job.customer_id, service_type_id: job.service_type_id, title: job.title, status: job.status, scheduled_date: job.scheduled_date || '', job_address: job.job_address || '', notes: job.notes || '' });

    function submit(event) {
        event.preventDefault();
        form.put(appUrl(`jobs/${job.id}`));
    }

    return <AppLayout><Head title="Edit Job" /><PageHeader title="Edit Job" /><JobForm customers={customers} form={form} onSubmit={submit} serviceTypes={serviceTypes} submitLabel="Save Job" /></AppLayout>;
}
