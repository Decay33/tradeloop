import { Head } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';

export default function Team({ users }) {
    return (
        <AppLayout>
            <Head title="Team" />
            <PageHeader title="Team" />
            <div className="rounded-lg border bg-white">
                {users.map((user) => <div className="grid gap-2 border-b p-4 md:grid-cols-3" key={user.id}><span className="font-medium">{user.name}</span><span>{user.email}</span><span>{user.pivot?.role}</span></div>)}
            </div>
        </AppLayout>
    );
}
