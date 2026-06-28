export default function EmptyState({ title, children }) {
    return (
        <div className="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 className="text-base font-semibold text-slate-900">{title}</h2>
            {children ? <p className="mt-2 text-sm text-slate-600">{children}</p> : null}
        </div>
    );
}
