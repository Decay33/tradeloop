export default function LineItemEditor({ items, setItems, errors = {} }) {
    function update(index, key, value) {
        const next = [...items];
        next[index] = { ...next[index], [key]: value };
        setItems(next);
    }

    function add() {
        setItems([...items, { description: '', quantity: 1, unit_price: 0 }]);
    }

    function remove(index) {
        setItems(items.filter((_, i) => i !== index));
    }

    return (
        <div className="space-y-3">
            <div className="grid grid-cols-12 gap-2 text-xs font-semibold uppercase text-slate-500">
                <span className="col-span-6">Description</span>
                <span className="col-span-2">Qty</span>
                <span className="col-span-3">Unit price</span>
                <span className="col-span-1"></span>
            </div>
            {items.map((item, index) => (
                <div className="grid grid-cols-12 gap-2" key={index}>
                    <input className="col-span-6 rounded-md border border-slate-300 px-3 py-2 text-sm" value={item.description || ''} onChange={(event) => update(index, 'description', event.target.value)} />
                    <input className="col-span-2 rounded-md border border-slate-300 px-3 py-2 text-sm" type="number" step="0.01" value={item.quantity} onChange={(event) => update(index, 'quantity', event.target.value)} />
                    <input className="col-span-3 rounded-md border border-slate-300 px-3 py-2 text-sm" type="number" step="0.01" value={item.unit_price ?? ((item.unit_price_cents || 0) / 100)} onChange={(event) => update(index, 'unit_price', event.target.value)} />
                    <button className="col-span-1 rounded-md border border-slate-300 text-sm text-slate-600" type="button" onClick={() => remove(index)}>x</button>
                </div>
            ))}
            {errors.items ? <p className="text-sm text-red-600">{errors.items}</p> : null}
            <button className="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700" type="button" onClick={add}>Add line item</button>
        </div>
    );
}
