import {useEffect, useRef} from 'react';
import {usePage} from '@inertiajs/react';
import {Toaster, toast} from 'react-hot-toast';

export default function SystemToasts() {
    const {errors = {}, flash = {}} = usePage().props;
    const lastNotification = useRef('');

    useEffect(() => {
        const messages = [flash?.success, flash?.error, ...Object.values(errors)].filter(Boolean);
        const signature = JSON.stringify(messages);

        if (!messages.length || signature === lastNotification.current) return;

        lastNotification.current = signature;
        if (flash?.success) toast.success(flash.success, {id: `success-${flash.success}`});
        if (flash?.error) toast.error(flash.error, {id: `error-${flash.error}`});
        Object.entries(errors).forEach(([field, message]) => {
            toast.error(message, {id: `validation-${field}-${message}`});
        });
    }, [errors, flash]);

    return <Toaster position="top-right" toastOptions={{
        duration: 5000,
        style: {background: '#2d103f', color: '#fff', borderRadius: '16px', padding: '14px 18px'},
        success: {iconTheme: {primary: '#c061ee', secondary: '#fff'}},
        error: {iconTheme: {primary: '#fb7185', secondary: '#fff'}},
    }}/>;
}
