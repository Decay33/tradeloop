import { Head, Link, usePage } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

const links = [
    ['settings/business', 'Business Profile', 'manage_settings'],
    ['settings/service-types', 'Service Types', 'manage_settings'],
    ['settings/message-templates', 'Message Templates', 'manage_settings'],
    ['settings/follow-up-rules', 'Follow-Up Rules', 'manage_settings'],
    ['settings/team', 'Team', 'manage_team'],
];

export default function SettingsIndex() {
    const permissions = usePage().props.auth?.permissions || [];

    return (
        <AppLayout>
            <Head title="Settings" />
            <PageHeader title="Settings" />
            <div className="grid gap-4 md:grid-cols-2">
                {links.filter(([, , permission]) => permissions.includes(permission)).map(([href, label]) => <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl(href)} key={href}>{label}</Link>)}
            </div>
        </AppLayout>
    );
}
