import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';

interface Props {
    header?: ReactNode;
}

export default function AppLayout({ header, children }: PropsWithChildren<Props>) {
    const { auth } = usePage().props as any;

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/" className="text-2xl font-bold text-indigo-600">
                                    JENGO
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <Link
                                    href="/dashboard"
                                    className="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                                >
                                    Dashboard
                                </Link>
                            </div>
                        </div>

                        <div className="hidden sm:flex sm:items-center sm:ml-6">
                            {auth?.user ? (
                                <div className="flex items-center space-x-4">
                                    <span className="text-sm text-gray-700">{auth.user.username}</span>
                                    <Link href="/logout" method="post" as="button" className="text-sm text-gray-500 hover:text-gray-700">
                                        Logout
                                    </Link>
                                </div>
                            ) : (
                                <div className="flex items-center space-x-4">
                                    <Link href="/login" className="text-sm text-gray-700 underline">Log in</Link>
                                    <Link href="/register" className="text-sm text-gray-700 underline">Register</Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
