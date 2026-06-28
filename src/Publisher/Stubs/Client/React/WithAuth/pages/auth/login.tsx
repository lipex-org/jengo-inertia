import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';
import { FormEvent } from 'react';

interface Props {
    canResetPassword?: boolean;
    status?: string;
}

export default function Login({ canResetPassword, status }: Props) {
    const { props } = usePage();
    const flash = props.flash as any;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => {
                // reset password
            }
        });
    };

    return (
        <GuestLayout>
            <Head title="Welcome Back" />

            <div className="mb-8">
                <h1 className="text-2xl font-bold text-base-content">Welcome Back</h1>
                <p className="text-base-content/70 mt-1">Please enter your details to sign in.</p>
            </div>

            {status && (
                <div className="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
                    {status}
                </div>
            )}

            {flash?.error && (
                <div className="mb-6 alert alert-error text-sm py-3 px-4 font-medium">
                    {flash.error}
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

                <div>
                    <div className="flex items-center justify-between mb-1">
                        <label htmlFor="password" className="block text-sm font-semibold text-base-content/85">Password</label>
                        {canResetPassword && (
                            <Link href="/login/magic-link" className="text-xs font-semibold text-primary hover:text-secondary transition-colors">
                                Forgot password?
                            </Link>
                        )}
                    </div>
                    <input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="input input-bordered w-full"
                        placeholder="••••••••"
                        required
                        autoComplete="current-password"
                    />
                    {errors.password && (
                        <div className="mt-2 text-sm text-error font-medium">{errors.password}</div>
                    )}
                </div>

                <div className="flex items-center">
                    <input
                        id="remember"
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                        className="checkbox checkbox-primary"
                    />
                    <label htmlFor="remember" className="ml-2 text-sm text-base-content/75 font-medium select-none">Keep me signed in</label>
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn btn-primary w-full shadow-md"
                    >
                        {processing ? <span className="loading loading-spinner"></span> : 'Sign In'}
                    </button>
                </div>

                <p className="text-center text-sm text-base-content/70">
                    Don't have an account?{' '}
                    <Link href="/register" className="font-bold text-primary hover:text-secondary transition-colors">
                        Create account
                    </Link>
                </p>
            </form>
        </GuestLayout>
    );
}
