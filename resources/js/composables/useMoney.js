import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Formatea montos (en USD) según la moneda de visualización del usuario.
 * Lee la preferencia y el tipo de cambio compartidos por Inertia.
 */
export function useMoney() {
    const page = usePage();

    const currency = computed(() => page.props.auth?.user?.display_currency ?? 'USD');
    const gtqRate = computed(() => Number(page.props.rates?.usd_gtq ?? 7.75));

    const money = (usdAmount) => {
        const n = Number(usdAmount ?? 0);
        if (currency.value === 'GTQ') {
            return new Intl.NumberFormat('es-GT', { style: 'currency', currency: 'GTQ' }).format(n * gtqRate.value);
        }
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(n);
    };

    return { money, currency, gtqRate };
}
