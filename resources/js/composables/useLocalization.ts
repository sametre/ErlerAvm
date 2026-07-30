import { onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';

export function useLocalization() {
    const { locale } = useI18n();

    const fetchUserLocation = async () => {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();

            // If country is Turkey, set TR. Else EN.
            if (data && data.country_code) {
                if (data.country_code === 'TR') {
                    setLocale('tr');
                } else {
                    setLocale('en');
                }
            }
        } catch (error) {
            console.error('Failed to detect location', error);
        }
    };

    const setLocale = (newLocale: string) => {
        locale.value = newLocale;
        localStorage.setItem('preferred_locale', newLocale);
        document.documentElement.lang = newLocale;
    };

    onMounted(() => {
        const savedLocale = localStorage.getItem('preferred_locale');
        if (savedLocale) {
            setLocale(savedLocale);
        } else {
            // Auto detect
            fetchUserLocation();
        }
    });

    return {
        locale,
        setLocale,
    };
}
