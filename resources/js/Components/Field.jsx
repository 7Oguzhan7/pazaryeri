export default function Field({ label, error, className = '', ...props }) {
    const id = props.id ?? props.name;

    return (
        <div className={className}>
            <label htmlFor={id} className="mb-1 block text-sm font-medium text-gray-700">{label}</label>
            <input id={id} {...props}
                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}
