import LineItemEditor from '../../../Components/LineItemEditor';

export default function EstimateForm({ form, customers, serviceTypes, onSubmit, submitLabel }) {
    const { data, setData, errors, processing } = form;

    return (
        <form className="space-y-6 rounded-lg border border-slate-200 bg-white p-4" onSubmit={onSubmit}>
            <div className="grid gap-4 md:grid-cols-2">
                <Select label="Customer" value={data.customer_id} onChange={(value) => setData('customer_id', value)} options={customers.map((customer) => [customer.id, customer.display_name])} error={errors.customer_id} />
                <Select label="Service Type" value={data.service_type_id} onChange={(value) => setData('service_type_id', value)} options={serviceTypes.map((service) => [service.id, service.name])} error={errors.service_type_id} />
                <Input label="Discount" type="number" step="0.01" value={data.discount} onChange={(value) => setData('discount', value)} />
                <Input label="Tax rate" type="number" step="0.0001" value={data.tax_rate} onChange={(value) => setData('tax_rate', value)} />
                <Input label="Expires at" type="date" value={data.expires_at || ''} onChange={(value) => setData('expires_at', value)} />
            </div>
            <LineItemEditor errors={errors} items={data.items} setItems={(items) => setData('items', items)} />
            <Textarea label="Notes" value={data.notes || ''} onChange={(value) => setData('notes', value)} />
            <Textarea label="Terms" value={data.terms || ''} onChange={(value) => setData('terms', value)} />
            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" disabled={processing} type="submit">{submitLabel}</button>
        </form>
    );
}

function Input({ label, value, onChange, ...props }) {
    return <label className="text-sm font-medium text-slate-700">{label}<input className="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2" value={value} onChange={(event) => onChange(event.target.value)} {...props} /></label>;
}

function Textarea({ label, value, onChange }) {
    return <label className="block text-sm font-medium text-slate-700">{label}<textarea className="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2" rows="3" value={value} onChange={(event) => onChange(event.target.value)} /></label>;
}

function Select({ label, value, onChange, options, error }) {
    return (
        <label className="text-sm font-medium text-slate-700">
            {label}
            <select className="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2" value={value} onChange={(event) => onChange(event.target.value)}>
                {options.map(([id, name]) => <option key={id} value={id}>{name}</option>)}
            </select>
            {error ? <p className="text-sm text-red-600">{error}</p> : null}
        </label>
    );
}
