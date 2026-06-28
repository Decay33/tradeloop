export default function CustomerForm({ form, onSubmit, submitLabel }) {
    const { data, setData, errors, processing } = form;

    return (
        <form className="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-2" onSubmit={onSubmit}>
            {['first_name', 'last_name', 'company_name', 'email', 'phone', 'address_line_1', 'city', 'state', 'zip'].map((field) => (
                <div key={field}>
                    <label className="text-sm font-medium text-slate-700">{field.replaceAll('_', ' ')}</label>
                    <input className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" value={data[field] || ''} onChange={(event) => setData(field, event.target.value)} />
                    {errors[field] ? <p className="text-sm text-red-600">{errors[field]}</p> : null}
                </div>
            ))}
            <label className="flex items-center gap-2 text-sm"><input checked={!!data.sms_consent} type="checkbox" onChange={(event) => setData('sms_consent', event.target.checked)} /> SMS consent</label>
            <label className="flex items-center gap-2 text-sm"><input checked={!!data.email_consent} type="checkbox" onChange={(event) => setData('email_consent', event.target.checked)} /> Email consent</label>
            <div className="md:col-span-2">
                <label className="text-sm font-medium text-slate-700">Notes</label>
                <textarea className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" rows="4" value={data.notes || ''} onChange={(event) => setData('notes', event.target.value)} />
            </div>
            <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white md:col-span-2" disabled={processing} type="submit">{submitLabel}</button>
        </form>
    );
}
