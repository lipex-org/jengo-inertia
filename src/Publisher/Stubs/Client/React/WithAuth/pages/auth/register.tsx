import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';
import { FormEvent } from 'react';

export default function Register() {
    const { props } = usePage();
    const flash = props.flash as any;

    const { data, setData, post, processing, errors } = useForm({
        username: '',
        email: '',
        password: '',
        password_confirm: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => {
                // reset password
            }
        });
    };

    return (
        <GuestLayout>
            <Head title="Create Account" />

            <div className="mb-8">
                <h1 className="text-2xl font-bold text-base-content">Create Account</h1>
                <p className="text-base-content/70 mt-1">Join the Jengo community today.</p>
            </div>

            {flash?.error && (
                <div className="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
                    {flash.error}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label htmlFor="username" className="block text-sm font-semibold text-base-content/85 mb-1">Username</label>
                    <input
                        id="username"
                        type="text"
                        value={data.username}
                        onChange={(e) => setData('username', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="johndoe"
                        required
                        autoFocus
                        autoComplete="username"
                    />
                    {errors.username && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.username}</div>
                    )}
                </div>

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
                        autoComplete="email"
                    />
                    {errors.email && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.email}</div>
                    )}
                </div>

                <div>
                    <label htmlFor="password" className="block text-sm font-semibold text-base-content/85 mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="••••••••"
                        required
                        autoComplete="new-password"
                    />
                    {errors.password && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.password}</div>
                    )}
                </div>

                <div>
                    <label htmlFor="password_confirm" className="block text-sm font-semibold text-base-content/85 mb-1">Confirm Password</label>
                    <input
                        id="password_confirm"
                        type="password"
                        value={data.password_confirm}
                        onChange={(e) => setData('password_confirm', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="••••••••"
                        required
                        autoComplete="new-password"
                    />
                    {errors.password_confirm && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.password_confirm}</div>
                    )}
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn btn-primary w-full shadow-md"
                    >
                        {processing ? <span className="loading loading-spinner"></span> : 'Create Account'}
                    </button>
                </div>

                <p className="text-center text-sm text-base-content/70">
                    Already have an account?{' '}
                    <Link href="/login" className="font-bold text-primary hover:text-secondary transition-colors">
                        Sign in
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
