import { router } from '@inertiajs/react';
import Layout from '../../Components/Layout';

export default function VerifyEmail() {
    return (
        <Layout title="E-posta doğrulama">
            <section className="mx-auto max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="text-2xl font-bold text-gray-900">E-posta adresinizi doğrulayın</h2>
                <p className="mt-3 text-sm text-gray-600">Başlamadan önce, e-posta adresinize gönderdiğimiz bağlantıya tıklayarak adresinizi doğrulayın. E-posta ulaşmadıysa yenisini gönderebiliriz.</p>
                <div className="mt-6 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" onClick={() => router.post('/email/verification-notification')}
                        className="rounded-md bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">Doğrulama e-postasını yeniden gönder</button>
                    <button type="button" onClick={() => router.post('/logout')} className="text-sm text-gray-600 underline hover:text-gray-900">Çıkış yap</button>
                </div>
            </section>
        </Layout>
    );
}
