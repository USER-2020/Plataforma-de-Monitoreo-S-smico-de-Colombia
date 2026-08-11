import {useEffect} from 'react';
import {usePage} from '@inertiajs/react';
import {Toaster, toast} from 'react-hot-toast';

export default function SystemToasts() {
    const {errors = {}, flash = {}} = usePage().props;
    useEffect(() => {
        const messages = [flash?.success, flash?.error, ...Object.values(errors)].filter(Boolean);
        if (!messages.length) return;

        if (flash?.success) toast.success(flash.success, {id: `success-${flash.success}`});
        if (flash?.error) toast.error(flash.error, {id: `error-${flash.error}`});
        Object.entries(errors).forEach(([field, message]) => {
            toast.error(message, {id: `validation-${field}-${message}`});
        });
    }, [errors, flash]);

    return <Toaster position="top-right" containerClassName="system-toaster" gutter={10} toastOptions={{
        duration: 5000,
        className: 'system-toast',
        style: {background: '#2d103f', color: '#fff', border: '1px solid rgba(216, 147, 244, .25)', borderRadius: '16px', padding: '14px 18px', boxShadow: '0 16px 40px rgba(45, 16, 63, .22)'},
        success: {iconTheme: {primary: '#c061ee', secondary: '#fff'}},
        error: {iconTheme: {primary: '#fb7185', secondary: '#fff'}},
    }}/>;
}
