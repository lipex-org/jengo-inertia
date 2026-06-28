import { Head } from '@inertiajs/react';
import AppLayout from '../layouts/app-layout';

export default function Dashboard() {
    return (
        <AppLayout
            header="Dashboard"
        >
            <Head title="Dashboard" />

            <div className="card bg-base-100 border border-base-300/50 p-8 shadow-md">
                <h3 className="text-xl font-bold mb-2">Welcome!</h3>
                <p className="text-base-content/70">You are successfully logged in to your Jengo application.</p>
            </div>
        </AppLayout>
    );
}
