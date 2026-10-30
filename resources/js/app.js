

import Alpine from 'alpinejs';
import '../css/login.css';
import '../css/navigation.css';
import '../css/store.css';

window.Alpine = Alpine;

Alpine.data('cartManager', () => ({
    quantities: {},
    prices: {},
    saving: false,
    saveQueued: false,
    savePromise: null,

    init() {
        const data = JSON.parse(document.getElementById('cart-data').textContent);
        this.quantities = data.quantities;
        this.prices = data.prices;
    },

    subtotal() {
        return Object.keys(this.quantities).reduce((total, id) => total + this.lineTotal(id), 0);
    },

    lineTotal(id) {
        return Number(this.prices[id] || 0) * Number(this.quantities[id] || 0);
    },

    money(value) {
        return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
    },

    normalizeQuantity(id, maximum, entered) {
        this.quantities[id] = Math.min(Math.max(Number.isFinite(entered) && entered > 0 ? entered : 1, 1), maximum);
    },

    async saveCart() {
        if (this.saving) {
            this.saveQueued = true;
            return this.savePromise;
        }

        this.saving = true;
        this.savePromise = (async () => {
            try {
                do {
                    this.saveQueued = false;
                    const response = await fetch(this.$refs.form.action, {
                        method: 'PATCH',
                        body: JSON.stringify({
                            quantities: Object.fromEntries(
                                Object.entries(this.quantities).map(([id, quantity]) => [id, Number(quantity)])
                            ),
                        }),
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'No se pudo actualizar el carrito.');
                    }

                    document.querySelectorAll('.store-badge').forEach((badge) => {
                        badge.textContent = result.cartCount;
                    });
                } while (this.saveQueued);

                return true;
            } catch (error) {
                window.alert(error.message);
                return false;
            } finally {
                this.saving = false;
                this.savePromise = null;
            }
        })();

        return this.savePromise;
    },

    async proceedToPayment(url) {
        if (await this.saveCart()) {
            window.location.href = url;
        }
    },
}));

Alpine.start();
