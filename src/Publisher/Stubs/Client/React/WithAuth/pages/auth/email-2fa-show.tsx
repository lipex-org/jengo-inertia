import { Head, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';
import { FormEvent } from 'react';

export default function Email2FAShow({ user }: { user: any }) {
    const { props } = usePage();
    const flash = props.flash as any;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/auth/a/handle');
    };

    return (
        <GuestLayout>
            <Head title="2FA Verification" />

            <div className="mb-8">
                <h1 className="text-2xl font-bold text-base-content">2FA Verification</h1>
                <p className="text-base-content/70 mt-1">Please confirm your email to receive a code.</p>
            </div>

            {flash?.error && (
                <div className="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
                    {flash.error}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label htmlFor="email" className="block text-sm font-semibold text-base-content/85 mb-1">Confirm Email</label>
                    <input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="name@example.com"
                        required
                        autoFocus
                    />
                    {errors.email && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.email}</div>
                    )}
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn btn-primary w-full shadow-md"
                    >
                        {processing ? <span className="loading loading-spinner"></span> : 'Send Code'}
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
