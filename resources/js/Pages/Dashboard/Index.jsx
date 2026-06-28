import { Head, Link } from '@inertiajs/react';
import EmptyState from '../../Components/EmptyState';
import MetricCard from '../../Components/MetricCard';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import StatusBadge from '../../Components/StatusBadge';
import AppLayout from '../../Layouts/AppLayout';
import { shortDate } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function Dashboard({ metrics, recentCustomers, recentJobs, upcomingFollowups, unpaidInvoices }) {
    return (
        <AppLayout>
            <Head title="Dashboard" />
            <PageHeader title="Dashboard" description="The numbers that tell you what to follow up on next." />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <MetricCard label="Revenue This Month" value={metrics.revenue_this_month} money />
                <MetricCard label="Open Estimates" value={metrics.open_estimate_value} money />
                <MetricCard label="Unpaid Invoices" value={metrics.unpaid_invoices} money />
                <MetricCard label="Jobs Completed" value={metrics.jobs_completed} />
                <MetricCard label="Repeat Opportunity" value={metrics.repeat_revenue_opportunity} money />
            </div>
            <div className="mt-6 grid gap-4 md:grid-cols-4">
                <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl('customers/create')}>Add Customer</Link>
                <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl('estimates/create')}>Create Estimate</Link>
                <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl('invoices/create')}>Create Invoice</Link>
                <Link className="rounded-lg border border-slate-200 bg-white p-4 font-semibold text-teal-800" href={appUrl('follow-ups')}>View Follow-Ups</Link>
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <Panel title="Recent Jobs">
                    {recentJobs.length ? recentJobs.map((job) => (
                        <Link className="flex items-center justify-between border-b border-slate-100 py-3" href={appUrl(`jobs/${job.id}`)} key={job.id}>
                            <span><span className="font-medium">{job.title}</span><span className="block text-sm text-slate-500">{job.customer?.display_name}</span></span>
                            <StatusBadge status={job.status} />
                        </Link>
                    )) : <EmptyState title="No jobs yet">Create or convert an estimate into a job.</EmptyState>}
                </Panel>
                <Panel title="Upcoming Follow-Ups">
                    {upcomingFollowups.length ? upcomingFollowups.map((message) => (
                        <Link className="flex items-center justify-between border-b border-slate-100 py-3" href={appUrl(`follow-ups/${message.id}`)} key={message.id}>
                            <span><span className="font-medium">{message.customer?.display_name}</span><span className="block text-sm text-slate-500">{message.purpose.replaceAll('_', ' ')} on {shortDate(message.scheduled_at)}</span></span>
                            <StatusBadge status={message.status} />
                        </Link>
                    )) : <EmptyState title="No follow-ups due today">Complete a job to automatically schedule follow-ups.</EmptyState>}
                </Panel>
                <Panel title="Recent Customers">
                    {recentCustomers.map((customer) => <Link className="block border-b border-slate-100 py-3 font-medium" href={appUrl(`customers/${customer.id}`)} key={customer.id}>{customer.display_name}</Link>)}
                </Panel>
                <Panel title="Unpaid Invoices">
                    {unpaidInvoices.length ? unpaidInvoices.map((invoice) => (
                        <Link className="flex items-center justify-between border-b border-slate-100 py-3" href={appUrl(`invoices/${invoice.id}`)} key={invoice.id}>
                            <span>{invoice.invoice_number}<span className="block text-sm text-slate-500">{invoice.customer?.display_name}</span></span>
                            <MoneyText cents={invoice.balance_due_cents} />
                        </Link>
                    )) : <EmptyState title="You have no unpaid invoices." />}
                </Panel>
            </div>
        </AppLayout>
    );
}

function Panel({ title, children }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="mb-2 font-semibold text-slate-950">{title}</h2>
            {children}
        </section>
    );
}
