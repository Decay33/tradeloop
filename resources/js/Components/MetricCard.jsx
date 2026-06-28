import MoneyText from './MoneyText';

export default function MetricCard({ label, value, money = false }) {
    return (
        <div className="rounded-lg border border-slate-200 bg-white p-4">
            <p className="text-sm text-slate-600">{label}</p>
            <p className="mt-2 text-2xl font-semibold text-slate-950">{money ? <MoneyText cents={value} /> : value}</p>
        </div>
    );
}
