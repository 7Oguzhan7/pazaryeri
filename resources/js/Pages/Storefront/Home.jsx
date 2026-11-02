import Layout from '../../Components/Layout';

export default function Home({ categories, search }) {
    return (
        <Layout title="Ana sayfa">
            <section className="rounded-2xl bg-indigo-700 px-6 py-12 text-white sm:px-10">
                <p className="text-sm font-semibold uppercase tracking-wide text-indigo-100">PazarYeri'ne hoş geldiniz</p>
                <h2 className="mt-3 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">
                    Aradığınız ürünleri güvenle keşfedin.
                </h2>
                <p className="mt-4 max-w-2xl text-indigo-100">
                    Farklı mağazaların ürünlerini tek bir pazaryerinde inceleyin.
                </p>
                <a href="#categories" className="mt-6 inline-flex rounded-md bg-white px-4 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">
                    Kategorileri keşfet
                </a>
            </section>
            <section id="categories" className="mt-10">
                <h2 className="text-2xl font-bold text-gray-900">Alışveriş kategorileri</h2>
                <p className="mt-2 text-gray-600">
                    {search ? `“${search}” için arama yapılıyor.` : 'Bu menüdeki kategoriler veritabanından yüklenir.'}
                </p>
                <div className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {categories?.length ? categories.map((category) => (
                        <article key={category.id} className="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 className="font-semibold text-gray-900">{category.name}</h3>
                            {category.children?.length > 0 && (
                                <ul className="mt-3 grid gap-2 text-sm text-gray-600">
                                    {category.children.map((child) => <li key={child.id}>{child.name}</li>)}
                                </ul>
                            )}
                        </article>
                    )) : <p className="rounded-lg bg-white p-5 text-gray-600">Henüz kategori eklenmemiş.</p>}
                </div>
            </section>
        </Layout>
    );
}
