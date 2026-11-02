import { Link, useForm } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function Login() {
    const form = useForm({ email: '', password: '', remember: false });

    const submit = (event) => {
        event.preventDefault();
        form.post('/login');
    };

    return (
        <Layout title="Giriş yap">
            <section className="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">Giriş yap</h2>
                <form onSubmit={submit} className="mt-6 grid gap-4">
                    <Field label="E-posta" name="email" type="email" autoComplete="username" autoFocus
                        value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} error={form.errors.email} required />
                    <Field label="Parola" name="password" type="password" autoComplete="current-password"
                        value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} error={form.errors.password} required />
                    <label className="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" checked={form.data.remember} onChange={(event) => form.setData('remember', event.target.checked)} />
                        Beni hatırla
                    </label>
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <Link href="/forgot-password" className="text-sm text-indigo-700 hover:underline">Parolanızı mı unuttunuz?</Link>
                        <button disabled={form.processing} className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Giriş yap</button>
                    </div>
                </form>
                <p className="mt-5 text-sm text-gray-600">Hesabınız yok mu? <Link href="/register" className="font-medium text-indigo-700 hover:underline">Kayıt olun</Link></p>
            </section>
        </Layout>
    );
}
