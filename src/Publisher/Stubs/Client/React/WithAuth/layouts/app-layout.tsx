import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';

interface Props {
    header?: ReactNode;
}

export default function AppLayout({ header, children }: PropsWithChildren<Props>) {
    const { auth } = usePage().props as any;
    const user = auth?.user;

    return (
        <div className="min-h-screen bg-base-200 text-base-content">
            <nav className="bg-base-100 border-b border-base-300/80 sticky top-0 z-30 shadow-xs">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/" className="flex items-center gap-2 group">
                                    <div className="w-8 h-8 bg-gradient-to-tr from-primary to-secondary rounded-lg flex items-center justify-center shadow-md shadow-primary/20">
                                        <span className="text-sm font-black text-white italic">J</span>
                                    </div>
                                    <span className="text-xl font-black tracking-tighter text-base-content hidden sm:block">JENGO</span>
                                </Link>
                            </div>

                            <div className="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex">
                                <Link
                                    href="/dashboard"
                                    className="inline-flex items-center px-1 pt-1 border-b-2 border-primary text-sm font-bold text-base-content transition duration-150 ease-in-out"
                                >
                                    Dashboard
                                </Link>
                            </div>
                        </div>

                        <div className="flex items-center ml-6">
                            {user && (
                                <div className="flex items-center gap-4">
                                    <div className="hidden md:flex flex-col items-end">
                                        <span className="text-sm font-bold text-base-content leading-none">{user.username}</span>
                                        <span className="text-xs text-base-content/60">{user.email}</span>
                                    </div>
                                    <div className="h-8 w-px bg-base-300 hidden md:block"></div>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        className="btn btn-ghost btn-sm text-base-content/70 hover:text-error transition-colors"
                                    >
                                        Sign Out
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-base-100 border-b border-base-300/80">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <h2 className="font-black text-2xl text-base-content tracking-tight">
                            {header}
                        </h2>
                    </div>
                </header>
            )}

            <main className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                {children}
            </main>
        </div>
    );
}
