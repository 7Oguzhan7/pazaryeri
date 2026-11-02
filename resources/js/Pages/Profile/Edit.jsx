import { router, useForm, usePage } from '@inertiajs/react';
import Field from '../../Components/Field';
import Layout from '../../Components/Layout';

export default function Edit() {
    const { auth } = usePage().props;
    const information = useForm({ name: auth.user.name, email: auth.user.email });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
    const deletion = useForm({ password: '' });

    const submit = (event, form, url, method = 'put') => {
        event.preventDefault();
        form[method](url, { preserveScroll: true });
    };

    const deleteAccount = (event) => {
        event.preventDefault();
        if (window.confirm('Hesabınız kalıcı olarak silinecek. Devam etmek istiyor musunuz?')) {
            deletion.delete('/profile', { preserveScroll: true });
        }
    };

    return (
        <Layout title="Profil">
            <div className="grid gap-6 lg:grid-cols-2">
                <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 className="text-xl font-semibold text-gray-900">Profil bilgileri</h2>
                    <form onSubmit={(event) => submit(event, information, '/profile')} className="mt-5 grid gap-4">
                        <Field label="Ad soyad" name="name" value={information.data.name}
                            onChange={(event) => information.setData('name', event.target.value)} error={information.errors.name} required />
                        <Field label="E-posta" name="email" type="email" value={information.data.email}
                            onChange={(event) => information.setData('email', event.target.value)} error={information.errors.email} required />
                        <button disabled={information.processing} className="justify-self-start rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Kaydet</button>
                    </form>
                </section>
                <section className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 className="text-xl font-semibold text-gray-900">Parolayı güncelle</h2>
                    <form onSubmit={(event) => submit(event, password, '/password')} className="mt-5 grid gap-4">
                        <Field label="Mevcut parola" name="current_password" type="password" autoComplete="current-password"
                            value={password.data.current_password} onChange={(event) => password.setData('current_password', event.target.value)} error={password.errors.current_password} required />
                        <Field label="Yeni parola" name="password" type="password" autoComplete="new-password"
                            value={password.data.password} onChange={(event) => password.setData('password', event.target.value)} error={password.errors.password} required />
                        <Field label="Yeni parola tekrarı" name="password_confirmation" type="password" autoComplete="new-password"
                            value={password.data.password_confirmation} onChange={(event) => password.setData('password_confirmation', event.target.value)} error={password.errors.password_confirmation} required />
                        <button disabled={password.processing} className="justify-self-start rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Parolayı güncelle</button>
                    </form>
                </section>
                <section className="rounded-xl border border-red-200 bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 className="text-xl font-semibold text-red-800">Hesabı sil</h2>
                    <p className="mt-2 text-sm text-gray-600">Hesabınız ve ilişkili bilgileriniz kalıcı olarak silinecektir. Devam etmek için parolanızı doğrulayın.</p>
                    <form onSubmit={deleteAccount} className="mt-5 flex flex-wrap items-end gap-4">
                        <Field label="Parola" name="delete_password" type="password" autoComplete="current-password"
                            value={deletion.data.password} onChange={(event) => deletion.setData('password', event.target.value)} error={deletion.errors.password} required />
                        <button disabled={deletion.processing} className="rounded-md bg-red-700 px-4 py-2 font-semibold text-white hover:bg-red-800 disabled:opacity-50">Hesabı sil</button>
                    </form>
                </section>
            </div>
        </Layout>
    );
}
