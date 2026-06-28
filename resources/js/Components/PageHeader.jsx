import { Link } from '@inertiajs/react';
import { appUrl } from '../lib/url';

export default function PageHeader({ title, description, actionHref, actionLabel }) {
    return (
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 className="text-2xl font-semibold text-slate-950">{title}</h1>
                {description ? <p className="mt-1 text-sm text-slate-600">{description}</p> : null}
            </div>
            {actionHref && actionLabel ? (
                <Link className="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-800" href={appUrl(actionHref)}>
                    {actionLabel}
                </Link>
            ) : null}
        </div>
    );
}
