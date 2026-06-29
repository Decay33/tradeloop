import { Head, Link } from '@inertiajs/react';
import MetricCard from '../../Components/MetricCard';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { appUrl } from '../../lib/url';

const ranges = [
    ['today', 'Today'],
    ['yesterday', 'Yesterday'],
    ['this_week', 'This Week'],
    ['last_week', 'Last Week'],
    ['this_month', 'This Month'],
    ['last_month', 'Last Month'],
    ['this_year', 'This Year'],
];

export default function Reports({ report, range }) {
    const metrics = report.metrics;

    return (
        <AppLayout>
            <Head title="Reports" />
            <PageHeader title="Reports" description="Sales, invoice aging, and repeat revenue opportunity." />
            <div className="mb-4 flex gap-2 overflow-x-auto">
                {ranges.map(([value, label]) => <Link className={`whitespace-nowrap rounded-md border px-3 py-2 text-sm ${range === value ? 'border-teal-700 bg-teal-50 text-teal-800' : 'border-slate-200 bg-white text-slate-700'}`} href={appUrl(`reports?range=${value}`)} key={value}>{label}</Link>)}
            </div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard label="Revenue This Month" value={metrics.revenue_this_month} money />
                <MetricCard label="Accepted Estimate Value" value={metrics.accepted_estimate_value} money />
                <MetricCard label="Overdue Invoices" value={metrics.overdue_invoices} money />
                <MetricCard label="Estimate Win Rate" value={`${metrics.estimate_win_rate}%`} />
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <DailySnapshot rows={report.daily_snapshot} />
                <Pipeline data={report.sales_pipeline} />
                <Collections data={report.collections} />
                <Activity title="Job Activity" data={report.job_activity} />
                <Activity title="Follow-Up Activity" data={report.followup_activity} />
                <ServiceBreakdown rows={report.service_breakdown} />
                <Table title="Invoice Aging" rows={report.invoice_aging} first="bucket" />
                <Table title="Revenue by Month" rows={report.revenue_by_month} first="month" />
                <section className="rounded-lg border border-slate-200 bg-white p-4">
                    <h2 className="font-semibold text-slate-950">Repeat Revenue Opportunity</h2>
                    <p className="mt-2 text-sm text-slate-600">{report.repeat_opportunity.customers_due} past customers are due for follow-up in the next 90 days.</p>
                    <p className="mt-3 text-2xl font-semibold text-slate-950"><MoneyText cents={report.repeat_opportunity.estimated_cents} /></p>
                    <div className="mt-4 space-y-2">
                        {report.repeat_opportunity.top.map((item, index) => (
                            <div className="rounded-md bg-slate-50 p-3 text-sm" key={index}>
                                <span className="font-medium">{item.customer}</span> - {item.service_type} - <MoneyText cents={item.estimated_cents} />
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}

function DailySnapshot({ rows }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
            <h2 className="mb-3 font-semibold text-slate-950">Daily Snapshot</h2>
            <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="text-xs uppercase text-slate-500"><tr><th className="py-2">Date</th><th>Estimates</th><th>Jobs Created</th><th>Jobs Done</th><th>Invoices</th><th>Payments</th><th>Follow-Ups Sent</th></tr></thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr className="border-t border-slate-100" key={row.date}>
                                <td className="py-2">{row.date}</td>
                                <td>{row.estimates_created} / <MoneyText cents={row.estimate_value_cents} /></td>
                                <td>{row.jobs_created}</td>
                                <td>{row.jobs_completed}</td>
                                <td>{row.invoices_created}</td>
                                <td><MoneyText cents={row.payments_collected_cents} /></td>
                                <td>{row.followups_sent}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

function Pipeline({ data }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="mb-3 font-semibold text-slate-950">Sales Pipeline</h2>
            <Line label="Open estimate value" cents={data.open_estimate_value_cents} href="estimates?filter=sent" />
            <Line label="Accepted estimate value" cents={data.accepted_estimate_value_cents} href="estimates?filter=accepted" />
            <Line label="Needs follow-up" value={data.estimates_needing_followup} href="estimates?filter=needs_follow_up" />
            <Line label="Accepted without jobs" value={data.accepted_without_jobs} href="estimates?filter=accepted_no_job" />
            <Line label="Win rate" value={`${data.estimate_win_rate}%`} />
        </section>
    );
}

function Collections({ data }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="mb-3 font-semibold text-slate-950">Collections</h2>
            <Line label="Payments collected" cents={data.payments_collected_cents} />
            <Line label="Unpaid invoices" cents={data.unpaid_invoices_cents} href="invoices?filter=unpaid" />
            <Line label="Past due invoices" cents={data.past_due_invoices_cents} href="invoices?filter=past_due" />
            <Line label="30+ days overdue" cents={data.overdue_30_cents} href="invoices?filter=30_overdue" />
            <Line label="60+ days overdue" cents={data.overdue_60_cents} href="invoices?filter=60_overdue" />
        </section>
    );
}

function Activity({ title, data }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="mb-3 font-semibold text-slate-950">{title}</h2>
            {Object.entries(data).filter(([, value]) => !Array.isArray(value)).map(([key, value]) => <Line key={key} label={key.replaceAll('_', ' ')} value={value} />)}
            {data.by_assigned_user ? <div className="mt-3 space-y-1">{data.by_assigned_user.map((row) => <Line key={row.name} label={row.name} value={row.count} />)}</div> : null}
        </section>
    );
}

function ServiceBreakdown({ rows }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 lg:col-span-2">
            <h2 className="mb-3 font-semibold text-slate-950">Service Breakdown</h2>
            {rows.map((row) => (
                <div className="grid gap-2 border-b border-slate-100 py-2 text-sm md:grid-cols-5" key={row.service_type}>
                    <span className="font-medium">{row.service_type}</span>
                    <span>Revenue: <MoneyText cents={row.revenue_cents} /></span>
                    <span>Jobs: {row.jobs_count}</span>
                    <span>Avg invoice: <MoneyText cents={row.average_invoice_cents} /></span>
                    <span>Repeat: <MoneyText cents={row.repeat_opportunity_cents} /></span>
                </div>
            ))}
        </section>
    );
}

function Line({ label, value, cents, href }) {
    const content = <><span className="capitalize">{label}</span><span className="font-semibold">{cents !== undefined ? <MoneyText cents={cents} /> : value}</span></>;

    return href ? <Link className="flex justify-between border-b border-slate-100 py-2 text-sm text-teal-800" href={appUrl(href)}>{content}</Link> : <div className="flex justify-between border-b border-slate-100 py-2 text-sm">{content}</div>;
}

function Table({ title, rows, first }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4">
            <h2 className="mb-3 font-semibold text-slate-950">{title}</h2>
            {rows.map((row, index) => (
                <div className="flex justify-between border-b border-slate-100 py-2 text-sm" key={index}>
                    <span>{row[first]}</span>
                    <MoneyText cents={row.total_cents} />
                </div>
            ))}
        </section>
    );
}
