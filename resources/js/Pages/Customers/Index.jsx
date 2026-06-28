import { Head, Link, router } from '@inertiajs/react';
import EmptyState from '../../Components/EmptyState';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

export default function Customers({ customers, filters }) {
    function search(event) {
        event.preventDefault();
        router.get(appUrl('customers'), { search: event.target.search.value }, { preserveState: true });
    }

    return (
        <AppLayout>
            <Head title="Customers" />
            <PageHeader title="Customers" actionHref="customers/create" actionLabel="Add Customer" />
            <form className="mb-4 flex gap-2" onSubmit={search}>
                <input className="w-full rounded-md border border-slate-300 px-3 py-2" defaultValue={filters.search || ''} name="search" placeholder="Search customers" />
                <button className="rounded-md border border-slate-300 px-4 py-2 font-semibold" type="submit">Search</button>
            </form>
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {customers.data.length ? customers.data.map((customer) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-4" href={appUrl(`customers/${customer.id}`)} key={customer.id}>
                        <span className="font-medium text-slate-950">{customer.display_name}</span>
                        <span>{customer.phone || 'No phone'}</span>
                        <span>{customer.email || 'No email'}</span>
                        <span>{[customer.city, customer.state].filter(Boolean).join(', ')}</span>
                    </Link>
                )) : <EmptyState title="Add your first customer to create an estimate." />}
            </div>
        </AppLayout>
    );
}
