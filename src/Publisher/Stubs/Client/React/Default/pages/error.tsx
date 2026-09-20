import { Head } from '@inertiajs/react';

interface ErrorProps {
    status: number;
    message?: string;
    exception?: {
        title?: string;
        type?: string;
        code?: number;
        message?: string;
        file?: string;
        line?: number;
        trace?: any[];
    };
}

export default function ErrorPage({ status, message, exception }: ErrorProps) {
    const titles: Record<number, string> = {
        401: 'Unauthorized',
        403: 'Forbidden',
        404: 'Page Not Found',
        419: 'Page Expired',
        500: 'Server Error',
        503: 'Service Unavailable',
    };

    const descriptions: Record<number, string> = {
        401: 'You are not authorized to access this resource.',
        403: 'You do not have permission to access this resource.',
        404: 'The page you are looking for could not be found.',
        419: 'The page has expired. Please refresh and try again.',
        500: 'An unexpected server error occurred.',
        503: 'Service is temporarily unavailable. Please try again shortly.',
    };

    const displayTitle = titles[status] || `Error ${status}`;
    const displayDescription = descriptions[status] || message || 'An unexpected error occurred.';

    return (
        <>
            <Head title={`${status}: ${displayTitle}`} />
            <div className="min-h-screen bg-base-200 text-base-content flex flex-col items-center justify-center px-6 py-12">
                <div className="max-w-md w-full text-center">
                    <span className="text-7xl font-black text-primary tracking-tight">{status}</span>
                    <h1 className="mt-4 text-2xl font-bold tracking-tight">{displayTitle}</h1>
                    <p className="mt-2 text-base text-base-content/70">{displayDescription}</p>

                    <div className="mt-6 flex justify-center gap-4">
                        <a href="/" className="btn btn-primary btn-sm">
                            Return Home
                        </a>
                    </div>
                </div>

                {exception && (
                    <div className="mt-10 max-w-3xl w-full p-6 bg-base-100 border border-base-300 rounded-box shadow text-left overflow-auto">
                        <h2 className="text-lg font-bold text-error">{exception.title || 'Exception'}</h2>
                        <p className="mt-1 text-sm font-mono text-base-content/80">{exception.message}</p>
                        {exception.file && (
                            <p className="mt-2 text-xs font-mono text-base-content/60">
                                {exception.file}:{exception.line}
                            </p>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}
