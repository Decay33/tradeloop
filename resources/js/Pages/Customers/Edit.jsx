import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import CustomerForm from './Partials/CustomerForm';

export default function EditCustomer({ customer }) {
    const form = useForm({ ...customer });

    function submit(event) {
        event.preventDefault();
        form.put(appUrl(`customers/${customer.id}`));
    }

    return (
        <AppLayout>
            <Head title="Edit Customer" />
            <PageHeader title="Edit Customer" />
            <CustomerForm form={form} onSubmit={submit} submitLabel="Save Customer" />
        </AppLayout>
    );
}
