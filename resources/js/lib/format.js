export function money(cents = 0) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format((Number(cents) || 0) / 100);
}

export function shortDate(value) {
    if (!value) return 'Not set';
    return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(value));
}

export function localDateTime(value) {
    if (!value) return 'Not set';

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(value));
}

export function title(value = '') {
    return String(value).replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}
