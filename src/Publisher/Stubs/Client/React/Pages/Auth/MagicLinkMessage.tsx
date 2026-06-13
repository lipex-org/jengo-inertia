import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';

export default function MagicLinkMessage() {
    return (
        <GuestLayout>
            <Head title="Magic Link Sent" />

            <div className="mb-4 text-sm text-gray-600">
                Check your email! We've sent you a magic link to log in.
            </div>

            <div className="mt-4 flex items-center justify-between">
                <Link
                    href="/login"
                    className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Back to login
                </Link>
            </div>
        </GuestLayout>
    );
}
