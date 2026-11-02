import { useForm } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function ResetPassword({ token, email }) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post('/reset-password');
    };

    return (
        <Layout title="Yeni parola belirle">
            <section className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Yeni parola belirle</h2>
                <form onSubmit={submit} className="mt-6 grid gap-4">
                    <Field label="E-posta" name="email" type="email" autoComplete="username" value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)} error={form.errors.email} required />
                    <Field label="Yeni parola" name="password" type="password" autoComplete="new-password" value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)} error={form.errors.password} required />
                    <Field label="Yeni parola tekrarı" name="password_confirmation" type="password" autoComplete="new-password" value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)} error={form.errors.password_confirmation} required />
                    <button disabled={form.processing} className="justify-self-end rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Parolayı sıfırla</button>
                </form>
            </section>
        </Layout>
    );
}
