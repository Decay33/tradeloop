import { money } from '../lib/format';

export default function MoneyText({ cents, className = '' }) {
    return <span className={className}>{money(cents)}</span>;
}
