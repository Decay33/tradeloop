import LineItemEditor from '../../../Components/LineItemEditor';

export default function InvoiceForm({ form, customers, onSubmit, submitLabel }) {
    const { data, setData, errors, processing } = form;

    return (
        <form className="space-y-6 rounded-lg border border-slate-200 bg-white p-4" onSubmit={onSubmit}>
            <div className="grid gap-4 md:grid-cols-2">
                <label className="text-sm font-medium text-slate-700">Customer<select className="mt-1 block w-full rounded-md border px-3 py-2" value={data.customer_id} onChange={(event) => setData('customer_id', event.target.value)}>{customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.display_name}</option>)}</select></label>
                <label className="text-sm font-medium text-slate-700">Due date<input className="mt-1 block w-full rounded-md border px-3 py-2" type="date" value={data.due_date || ''} onChange={(event) => setData('due_date', event.target.value)} /></label>
                <label className="text-sm font-medium text-slate-700">Discount<input className="mt-1 block w-full rounded-md border px-3 py-2" type="number" step="0.01" value={data.discount} onChange={(event) => setData('discount', event.target.value)} /></label>
                <label className="text-sm font-medium text-slate-700">Tax rate<input className="mt-1 block w-full rounded-md border px-3 py-2" type="number" step="0.0001" value={data.tax_rate} onChange={(event) => setData('tax_rate', event.target.value)} /></label>
            </div>
            <LineItemEditor errors={errors} items={data.items} setItems={(items) => setData('items', items)} />
            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" disabled={processing} type="submit">{submitLabel}</button>
        </form>
    );
}
