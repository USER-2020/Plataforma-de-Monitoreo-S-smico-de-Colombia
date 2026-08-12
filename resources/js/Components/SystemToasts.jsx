import {useEffect, useRef} from 'react';
import {router, usePage} from '@inertiajs/react';
import {Toaster, toast} from 'react-hot-toast';

export default function SystemToasts() {
    const {errors = {}, flash = {}} = usePage().props;
    const lastFlashRef = useRef(null);
    const lastErrorsRef = useRef(null);

    useEffect(() => {
        const success = flash?.success;
        const error = flash?.error;

        if (!success && !error) {
            lastFlashRef.current = null;
            return;
        }

        const flashKey = JSON.stringify([success ?? null, error ?? null]);

        if (lastFlashRef.current === flashKey) return;

        lastFlashRef.current = flashKey;

        if (success) toast.success(success, {id: `success-${success}`});
        if (error) toast.error(error, {id: `error-${error}`});

        // A partial Inertia reload (for example, a WebSocket update) preserves
        // omitted props. Consume the flash locally so it cannot be shown again.
        router.replaceProp('flash', {});
    }, [flash?.error, flash?.success]);

    useEffect(() => {
        const entries = Object.entries(errors).filter(([, message]) => Boolean(message));

        if (!entries.length) {
            lastErrorsRef.current = null;
            return;
        }

        const errorsKey = JSON.stringify(entries);

        if (lastErrorsRef.current === errorsKey) return;

        lastErrorsRef.current = errorsKey;

        entries.forEach(([field, message]) => {
            toast.error(message, {id: `validation-${field}-${message}`});
        });
    }, [errors]);

    return <Toaster position="top-right" containerClassName="system-toaster" gutter={10} toastOptions={{
        duration: 5000,
        className: 'system-toast',
        style: {background: '#2d103f', color: '#fff', border: '1px solid rgba(216, 147, 244, .25)', borderRadius: '16px', padding: '14px 18px', boxShadow: '0 16px 40px rgba(45, 16, 63, .22)'},
        success: {iconTheme: {primary: '#c061ee', secondary: '#fff'}},
        error: {iconTheme: {primary: '#fb7185', secondary: '#fff'}},
    }}/>;
}
