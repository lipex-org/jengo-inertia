import { Link, Head, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage().props as any;

    return (
        <>
            <Head title="Welcome" />
            <div className="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 selection:bg-indigo-500 selection:text-white">
                <div className="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    {auth?.user ? (
                        <Link href="/dashboard" className="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link href="/login" className="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
                                Log in
                            </Link>
                            <Link href="/register" className="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-indigo-500">
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
            </div>
        </>
    );
}
