import { Head, router, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { appUrl } from '../../lib/url';

export default function Login({ demoMode }) {
    const { data, setData, post, processing, errors } = useForm({ email: '', password: '', remember: false });

    function submit(event) {
        event.preventDefault();
        post(appUrl('login'));
    }

    return (
        <GuestLayout>
            <Head title="Login" />
            <h1 className="text-2xl font-semibold text-slate-950">Welcome back to TradeLoop.</h1>
            <p className="mt-2 text-sm text-slate-600">Finish the job. TradeLoop handles the follow-up.</p>
            <form className="mt-6 space-y-4" onSubmit={submit}>
                <div>
                    <label className="text-sm font-medium text-slate-700">Email</label>
                    <input className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                    {errors.email ? <p className="mt-1 text-sm text-red-600">{errors.email}</p> : null}
                </div>
                <div>
                    <label className="text-sm font-medium text-slate-700">Password</label>
                    <input className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" type="password" value={data.password} onChange={(event) => setData('password', event.target.value)} />
                </div>
                <label className="flex items-center gap-2 text-sm text-slate-600">
                    <input checked={data.remember} type="checkbox" onChange={(event) => setData('remember', event.target.checked)} />
                    Remember me
                </label>
                <button className="w-full rounded-md bg-teal-700 px-4 py-2 font-semibold text-white hover:bg-teal-800" disabled={processing} type="submit">Log in</button>
            </form>
            {demoMode ? (
                <button className="mt-3 w-full rounded-md border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50" onClick={() => router.post(appUrl('demo-login'))} type="button">
                    Try Demo
                </button>
            ) : null}
        </GuestLayout>
    );
}
