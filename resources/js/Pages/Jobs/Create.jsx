import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import JobForm from './Partials/JobForm';

export default function CreateJob({ customers, serviceTypes, teamMembers, selectedCustomerId }) {
    const form = useForm({ customer_id: selectedCustomerId || customers[0]?.id || '', service_type_id: serviceTypes[0]?.id || '', assigned_user_id: '', title: '', status: 'scheduled', scheduled_date: '', quoted_price: '', job_address: '', notes: '' });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('jobs'));
    }

    return <AppLayout><Head title="Create Job" /><PageHeader title="Create Job" /><JobForm customers={customers} form={form} onSubmit={submit} serviceTypes={serviceTypes} teamMembers={teamMembers} submitLabel="Save Job" /></AppLayout>;
}
