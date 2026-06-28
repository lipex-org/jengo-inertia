import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen flex flex-col justify-center items-center py-12 px-4 bg-base-200 font-sans antialiased text-base-content relative overflow-hidden">
            {/* Background decorative elements */}
            <div className="absolute -top-48 -left-48 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
            <div className="absolute bottom-0 right-0 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"></div>

            <div className="w-full max-w-md z-10">
                <div className="flex justify-center mb-6">
                    <Link href="/" className="flex items-center gap-3 group">
                        <div className="w-12 h-12 bg-gradient-to-tr from-primary to-secondary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:scale-105 transition-transform">
                            <span className="text-2xl font-black text-white italic">J</span>
                        </div>
                        <span className="text-3xl font-black tracking-tighter text-base-content">JENGO</span>
                    </Link>
                </div>

                <div className="card bg-base-100 border border-base-300/50 p-8 shadow-xl">
                    {children}
                </div>
            </div>
        </div>
    );
}
