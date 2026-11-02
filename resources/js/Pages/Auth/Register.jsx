import { Link, useForm } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function Register() {
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post('/register');
    };

    return (
        <Layout title="Kayıt ol">
            <section className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Hesap oluştur</h2>
                <form onSubmit={submit} className="mt-6 grid gap-4">
                    <Field label="Ad soyad" name="name" autoComplete="name" autoFocus value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)} error={form.errors.name} required />
                    <Field label="E-posta" name="email" type="email" autoComplete="username" value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)} error={form.errors.email} required />
                    <Field label="Parola" name="password" type="password" autoComplete="new-password" value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)} error={form.errors.password} required />
                    <Field label="Parola tekrarı" name="password_confirmation" type="password" autoComplete="new-password" value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)} error={form.errors.password_confirmation} required />
                    <div className="flex items-center justify-between gap-3">
                        <Link href="/login" className="text-sm text-indigo-700 hover:underline">Zaten hesabınız var mı?</Link>
                        <button disabled={form.processing} className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Kayıt ol</button>
                    </div>
                </form>
            </section>
        </Layout>
    );
}
