import { Link, Head, usePage, router } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage().props as any;

    return (
        <>
            <Head title="Welcome" />
            <div className="relative sm:flex sm:flex-col sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 selection:bg-indigo-500 selection:text-white">
                <div className="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    {auth?.user ? (
                        <Link href="/dashboard" className="font-semibold text-gray-600 hover:text-gray-900 focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link href="/login" className="font-semibold text-gray-600 hover:text-gray-900 focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                                Log in
                            </Link>
                            <Link href="/register" className="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                                Register
                            </Link>
                        </>
                    )}
                </div>

                <div className="max-w-7xl mx-auto p-6 lg:p-8">
                    <div className="flex justify-center">
                        <h1 className="text-6xl font-black text-indigo-600">JENGO</h1>
                    </div>

                    <div className="mt-16 text-center">
                        <p className="text-2xl text-gray-700 font-medium">The CodeIgniter 4 Powerhouse</p>
                        <p className="mt-4 text-gray-500 text-lg">You have successfully generated a new Jengo application.</p>
                    </div>
                </div>

                <br />

                <div className="p-8 space-y-4 block">
                    {/* 1. Your current component */}
                    <Link href="/login">Inertia Link Component</Link>

                    <br />

                    {/* 2. The Native Fallback Button */}
                    <button
                        type="button"
                        onClick={(e) => {
                            e.preventDefault();
                            console.log('🔄 Triggering programmatic navigation...');
                            router.visit('/login');
                        }}
                        className="px-4 py-2 bg-blue-600 text-white rounded"
                    >
                        Programmatic Router Visit
                    </button>

                    <br />

                    <Link href={"/login"}
                        className="px-4 py-2 bg-blue-600 text-white rounded"
                    >Link Router Visit</Link>
                </div>
            </div>
        </>
    );
}
