import { Head, Link, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/guest-layout';

export default function MagicLinkMessage() {
    const { props } = usePage();
    const flash = props.flash as any;

    return (
        <GuestLayout>
            <Head title="Magic Link Sent" />

            {flash?.message && (
                <div className="mb-6 alert alert-success text-sm py-3 px-4 font-medium">
                    {flash.message}
                </div>
            )}

            <div className="mb-6 text-sm text-base-content/75 leading-relaxed">
                We have emailed you a magic link. Please check your inbox and click the link to log in.
            </div>

            <div className="mt-6 text-center">
                <Link href="/login" className="font-bold text-primary hover:text-secondary transition-colors">
                    Back to Log in
                </Link>
            </div>
        </GuestLayout>
    );
}
