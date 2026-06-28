import { Head } from '@inertiajs/react';
import MetricCard from '../../Components/MetricCard';
import MoneyText from '../../Components/MoneyText';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';

export default function Reports({ report }) {
    const metrics = report.metrics;

    return (
        <AppLayout>
            <Head title="Reports" />
            <PageHeader title="Reports" description="Sales, invoice aging, and repeat revenue opportunity." />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <MetricCard label="Revenue This Month" value={metrics.revenue_this_month} money />
                <MetricCard label="Accepted Estimate Value" value={metrics.accepted_estimate_value} money />
                <MetricCard label="Overdue Invoices" value={metrics.overdue_invoices} money />
                <MetricCard label="Estimate Win Rate" value={`${metrics.estimate_win_rate}%`} />
            </div>
            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                <Table title="Revenue by Service Type" rows={report.revenue_by_service_type} first="service_type" />
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
