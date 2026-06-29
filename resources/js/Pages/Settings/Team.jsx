import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import PageHeader from '../../Components/PageHeader';
import AppLayout from '../../Layouts/AppLayout';
import { title } from '../../lib/format';
import { appUrl } from '../../lib/url';

export default function Team({ users, permissions }) {
    const [editing, setEditing] = useState(null);
    const form = useForm(blank());

    function blank() {
        return { name: '', email: '', phone: '', role: 'field_staff', temporary_password: '', is_active: true, permissions: [] };
    }

    function edit(user) {
        setEditing(user);
        form.setData({
            name: user.name || '',
            email: user.email || '',
            phone: user.phone || '',
            role: user.pivot?.role || 'field_staff',
            temporary_password: '',
            is_active: Boolean(user.pivot?.is_active ?? true),
            permissions: parsePermissions(user.pivot?.permissions),
        });
    }

    function submit(event) {
        event.preventDefault();
        if (editing) {
            form.put(appUrl(`settings/team/${editing.id}`), { preserveScroll: true, onSuccess: () => { setEditing(null); form.setData(blank()); } });
        } else {
            form.post(appUrl('settings/team'), { preserveScroll: true, onSuccess: () => form.setData(blank()) });
        }
    }

    function togglePermission(permission) {
        const current = form.data.permissions || [];
        form.setData('permissions', current.includes(permission) ? current.filter((item) => item !== permission) : [...current, permission]);
    }

    return (
        <AppLayout>
            <Head title="Team" />
            <PageHeader title="Team" />
            <form className="mb-6 grid gap-4 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-2" onSubmit={submit}>
                <Input label="Name" value={form.data.name} onChange={(value) => form.setData('name', value)} error={form.errors.name} />
                <Input label="Email" type="email" value={form.data.email} onChange={(value) => form.setData('email', value)} error={form.errors.email} />
                <Input label="Cell phone" value={form.data.phone} onChange={(value) => form.setData('phone', value)} />
                <Input label={editing ? 'New temporary password' : 'Temporary password'} type="password" value={form.data.temporary_password} onChange={(value) => form.setData('temporary_password', value)} error={form.errors.temporary_password} />
                <label className="text-sm font-medium text-slate-700">Role<select className="mt-1 block w-full rounded-md border px-3 py-2" value={form.data.role} onChange={(event) => form.setData('role', event.target.value)}>{['owner', 'manager', 'field_staff', 'custom'].map((role) => <option key={role} value={role}>{title(role)}</option>)}</select></label>
                <label className="flex items-center gap-2 text-sm font-medium text-slate-700"><input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} /> Active</label>
                {form.data.role === 'custom' ? (
                    <div className="md:col-span-2">
                        <h2 className="mb-2 font-semibold">Permissions</h2>
                        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            {permissions.map((permission) => <label className="flex items-center gap-2 rounded-md border px-3 py-2 text-sm" key={permission}><input type="checkbox" checked={form.data.permissions.includes(permission)} onChange={() => togglePermission(permission)} /> {title(permission)}</label>)}
                        </div>
                    </div>
                ) : null}
                <div className="flex gap-2 md:col-span-2">
                    <button className="rounded-md bg-teal-700 px-4 py-2 font-semibold text-white" disabled={form.processing} type="submit">{editing ? 'Save Team Member' : 'Add Team Member'}</button>
                    {editing ? <button className="rounded-md border px-4 py-2 font-semibold text-slate-700" onClick={() => { setEditing(null); form.setData(blank()); }} type="button">Cancel</button> : null}
                </div>
            </form>
            <div className="rounded-lg border bg-white">
                {users.map((user) => (
                    <div className="grid gap-2 border-b p-4 md:grid-cols-5" key={user.id}>
                        <span className="font-medium">{user.name}</span>
                        <span>{user.email}</span>
                        <span>{user.phone || 'No phone'}</span>
                        <span>{title(user.pivot?.role)} {user.pivot?.is_active ? '' : '(Inactive)'}</span>
                        <span className="flex gap-2">
                            <button className="rounded-md border px-3 py-2 text-sm" onClick={() => edit(user)} type="button">Edit</button>
                            <button className="rounded-md border px-3 py-2 text-sm" onClick={() => router.delete(appUrl(`settings/team/${user.id}`), { preserveScroll: true })} type="button">Deactivate</button>
                        </span>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
}

function Input({ label, value, onChange, error, ...props }) {
    return <label className="text-sm font-medium text-slate-700">{label}<input className="mt-1 block w-full rounded-md border px-3 py-2" value={value || ''} onChange={(event) => onChange(event.target.value)} {...props} />{error ? <p className="text-sm text-red-600">{error}</p> : null}</label>;
}

function parsePermissions(value) {
    if (Array.isArray(value)) return value;
    if (!value) return [];

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}
