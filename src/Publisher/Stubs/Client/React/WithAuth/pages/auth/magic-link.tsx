import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';
import { FormEvent } from 'react';

export default function MagicLink() {
    const { props } = usePage();
    const flash = props.flash as any;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/login/magic-link');
    };

    return (
        <GuestLayout>
            <Head title="Magic Link" />

            <div className="mb-8">
                <h1 className="text-2xl font-bold text-base-content">Forgot Password?</h1>
                <p className="text-base-content/70 mt-1">We'll send you a link to sign in instantly.</p>
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
                    <label htmlFor="email" className="block text-sm font-semibold text-base-content/85 mb-1">Email Address</label>
                    <input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="name@example.com"
                        required
                        autoFocus
                        autoComplete="username"
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
                        {processing ? <span className="loading loading-spinner"></span> : 'Send Magic Link'}
                    </button>
                </div>

                <p className="text-center text-sm text-base-content/70">
                    Wait, I remember!{' '}
                    <Link href="/login" className="font-bold text-primary hover:text-secondary transition-colors">
                        Back to sign in
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
