import { Head, useForm } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';
import CustomerForm from './Partials/CustomerForm';

export default function CreateCustomer() {
    const form = useForm({ first_name: '', last_name: '', company_name: '', email: '', phone: '', address_line_1: '', city: '', state: '', zip: '', sms_consent: true, email_consent: true, notes: '' });

    function submit(event) {
        event.preventDefault();
        form.post(appUrl('customers'));
    }

    return (
        <AppLayout>
            <Head title="Add Customer" />
            <PageHeader title="Add Customer" />
            <CustomerForm form={form} onSubmit={submit} submitLabel="Create Customer" />
        </AppLayout>
    );
}
