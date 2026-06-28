import { Head, Link } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

const links = [
    ['settings/business', 'Business Profile'],
    ['settings/service-types', 'Service Types'],
    ['settings/message-templates', 'Message Templates'],
    ['settings/follow-up-rules', 'Follow-Up Rules'],
    ['settings/team', 'Team'],
];

export default function SettingsIndex() {
    return (
        <AppLayout>
            <Head title="Settings" />
            <PageHeader title="Settings" />
            <div className="grid gap-4 md:grid-cols-2">
                {links.map(([href, label]) => <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl(href)} key={href}>{label}</Link>)}
            </div>
        </AppLayout>
    );
}
