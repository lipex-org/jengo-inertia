import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { FormEventHandler } from 'react';

interface Props {
    title?: string;
    description?: string;
    error?: string;
    errors?: Record<string, string>;
    [key: string]: any;
}

export default function Action({ title, description, error, errors: propErrors }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        token: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(window.location.pathname);
    };

    return (
        <GuestLayout>
            <Head title={title || 'Action Required'} />

            <div className="mb-4 text-sm text-gray-600">
                {description || 'Please complete the following action to continue.'}
            </div>

            {error && <div className="mb-4 font-medium text-sm text-red-600">{error}</div>}

            <form onSubmit={submit}>
                <div>
                    <label htmlFor="token" className="block text-sm font-medium text-gray-700">Code / Token</label>
                    <input
                        id="token"
                        type="text"
                        name="token"
                        value={data.token}
                        className="mt-1 block p-2 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        autoFocus
                        onChange={(e) => setData('token', e.target.value)}
                        required
                    />
                    {(errors.token || propErrors?.token) && (
                        <div className="mt-2 text-sm text-red-600">{errors.token || propErrors?.token}</div>
                    )}
                </div>

                <div className="flex items-center justify-end mt-4">
                    <button
                        type="submit"
                        className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        disabled={processing}
                    >
                        Confirm
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
