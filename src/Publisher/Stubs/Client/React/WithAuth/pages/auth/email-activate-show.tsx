import { Head, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';
import { FormEvent } from 'react';

export default function EmailActivateShow() {
    const { props } = usePage();
    const flash = props.flash as any;

    const { data, setData, post, processing, errors } = useForm({
        token: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/auth/a/verify');
    };

    return (
        <GuestLayout>
            <Head title="Activate Account" />

            <div className="mb-8">
                <h1 className="text-2xl font-bold text-base-content">Activate Account</h1>
                <p className="text-base-content/70 mt-1">Please enter the code sent to your email.</p>
            </div>

            {flash?.error && (
                <div className="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
                    {flash.error}
                </div>
            )}

            {flash?.message && (
                <div className="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
                    {flash.message}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label htmlFor="token" className="block text-sm font-semibold text-base-content/85 mb-1">Activation Code</label>
                    <input
                        id="token"
                        type="text"
                        value={data.token}
                        onChange={(e) => setData('token', e.target.value)}
                        className="input input-bordered w-full text-center text-2xl tracking-widest font-black"
                        placeholder="000000"
                        required
                        autoFocus
                        autoComplete="one-time-code"
                    />
                    {errors.token && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.token}</div>
                    )}
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn btn-primary w-full shadow-md"
                    >
                        {processing ? <span className="loading loading-spinner"></span> : 'Verify Code'}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
