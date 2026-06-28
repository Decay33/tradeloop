import { Head, Link } from '@inertiajs/react';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function FollowUps({ messages }) {
    return (
        <AppLayout>
            <Head title="Follow-Ups" />
            <PageHeader title="Follow-Ups" description="Scheduled and simulated messages. No real SMS or email is sent." />
            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                {messages.data.map((message) => (
                    <Link className="grid gap-2 border-b border-slate-100 p-4 hover:bg-slate-50 md:grid-cols-6" href={appUrl(`follow-ups/${message.id}`)} key={message.id}>
                        <span className="font-medium">{message.customer?.display_name}</span>
                        <span>{message.channel.toUpperCase()}</span>
                        <span>{message.purpose.replaceAll('_', ' ')}</span>
                        <StatusBadge status={message.status} />
                        <span>{shortDate(message.scheduled_at)}</span>
                        <span className="text-sm text-slate-500">{message.skip_reason}</span>
                    </Link>
                ))}
            </div>
        </AppLayout>
    );
}
