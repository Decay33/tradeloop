export default function JobForm({ form, customers, serviceTypes, onSubmit, submitLabel }) {
    const { data, setData, errors, processing } = form;

    return (
        <form className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-2" onSubmit={onSubmit}>
            <label className="text-sm font-medium text-slate-700">Customer<select className="mt-1 block w-full rounded-md border px-3 py-2" value={data.customer_id} onChange={(event) => setData('customer_id', event.target.value)}>{customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.display_name}</option>)}</select></label>
            <label className="text-sm font-medium text-slate-700">Service Type<select className="mt-1 block w-full rounded-md border px-3 py-2" value={data.service_type_id} onChange={(event) => setData('service_type_id', event.target.value)}>{serviceTypes.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}</select></label>
            <label className="text-sm font-medium text-slate-700">Title<input className="mt-1 block w-full rounded-md border px-3 py-2" value={data.title} onChange={(event) => setData('title', event.target.value)} />{errors.title ? <p className="text-sm text-red-600">{errors.title}</p> : null}</label>
            <label className="text-sm font-medium text-slate-700">Scheduled date<input className="mt-1 block w-full rounded-md border px-3 py-2" type="date" value={data.scheduled_date || ''} onChange={(event) => setData('scheduled_date', event.target.value)} /></label>
            <label className="text-sm font-medium text-slate-700">Status<select className="mt-1 block w-full rounded-md border px-3 py-2" value={data.status} onChange={(event) => setData('status', event.target.value)}>{['scheduled', 'in_progress', 'completed', 'canceled'].map((status) => <option key={status} value={status}>{status.replaceAll('_', ' ')}</option>)}</select></label>
            <label className="text-sm font-medium text-slate-700">Job address<input className="mt-1 block w-full rounded-md border px-3 py-2" value={data.job_address || ''} onChange={(event) => setData('job_address', event.target.value)} /></label>
            <label className="text-sm font-medium text-slate-700 md:col-span-2">Notes<textarea className="mt-1 block w-full rounded-md border px-3 py-2" rows="4" value={data.notes || ''} onChange={(event) => setData('notes', event.target.value)} /></label>
            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2" disabled={processing} type="submit">{submitLabel}</button>
        </form>
    );
}
