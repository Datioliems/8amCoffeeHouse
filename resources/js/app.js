import Alpine from 'alpinejs';
window.Alpine = Alpine;

// ── Cart store dùng cho layout customer ──────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.data('cart', () => ({
        items: JSON.parse(localStorage.getItem('cart_items') || '[]'),
        openCart: false,
        showCustomizer: false,
        selectedMon: null,
        selectedOptions: {
            temperature: 'Đá',
            sweetness: '50%',
            toppings: [],
            note: '',
            qty: 1,
        },
        toastMessage: '',
        toastTimer: null,
        activeCategory: null,
        checkoutError: '',

        get totalItems() {
            return this.items.reduce((sum, i) => sum + i.qty, 0);
        },

        get totalPrice() {
            return this.items.reduce((sum, i) => sum + i.don_gia * i.qty, 0);
        },

        get checkoutUrl() {
            const maOrder = document.querySelector('meta[name="ma-order"]')?.content;
            return maOrder ? `/order/${maOrder}/cart` : '#';
        },

        addToCart(mon) {
            this.openCustomizer(mon);
        },

        openCustomizer(mon) {
            this.selectedMon = {
                ...mon,
                warnings: this.buildWarnings(mon),
                options: this.buildOptions(mon),
            };
            this.selectedOptions = {
                temperature: this.selectedMon.options.temperature[0] || 'Đá',
                sweetness: this.selectedMon.options.sweetness[1] || this.selectedMon.options.sweetness[0] || '50%',
                toppings: [],
                note: '',
                qty: 1,
            };
            this.showCustomizer = true;
        },

        closeCustomizer() {
            this.showCustomizer = false;
            this.selectedMon = null;
        },

        buildOptions(mon) {
            const text = `${mon.ten_mon || ''} ${mon.mo_ta || ''} ${mon.category || ''}`.toLowerCase();
            const isFood = text.includes('bánh') || text.includes('hạt sen');
            const isTea = text.includes('trà') || text.includes('chanh') || text.includes('ca cao');
            const isCoffee = text.includes('cà phê') || text.includes('coffee') || text.includes('espresso') || text.includes('latte') || text.includes('cold brew') || text.includes('americano');

            if (isFood) {
                return {
                    temperature: ['Thường', 'Làm nóng'],
                    sweetness: ['Không áp dụng'],
                    toppings: ['Kem mặn', 'Sốt caramel', 'Ăn kèm đá lạnh'],
                };
            }

            return {
                temperature: text.includes('cold brew') || isTea ? ['Đá', 'Ít đá', 'Không đá'] : ['Đá', 'Nóng', 'Ít đá'],
                sweetness: ['0%', '30%', '50%', '70%', '100%'],
                toppings: isCoffee
                    ? ['Kem mặn', 'Caramel', 'Shot espresso', 'Sữa tươi']
                    : ['Trân châu', 'Thạch konjac', 'Sương sáo', 'Thạch dừa'],
            };
        },

        buildWarnings(mon) {
            const text = `${mon.ten_mon || ''} ${mon.mo_ta || ''} ${mon.category || ''}`.toLowerCase();
            const warnings = [];

            if (text.includes('sữa') || text.includes('latte') || text.includes('bạc xỉu') || text.includes('ca cao')) {
                warnings.push('Có sữa hoặc lactose. Người dị ứng sữa nên cân nhắc.');
            }
            if (text.includes('hạt') || text.includes('sen')) {
                warnings.push('Có thành phần hạt. Không phù hợp với người dị ứng các loại hạt.');
            }
            if (text.includes('cà phê') || text.includes('coffee') || text.includes('espresso') || text.includes('cold brew') || text.includes('latte') || text.includes('americano')) {
                warnings.push('Có caffeine. Trẻ em, phụ nữ mang thai, người mất ngủ hoặc bệnh tim mạch nên hạn chế.');
            }
            if (text.includes('rượu') || text.includes('bailey')) {
                warnings.push('Có cồn. Chỉ phù hợp khách từ 18 tuổi trở lên.');
            }
            if (text.includes('gừng')) {
                warnings.push('Có gừng. Người đang dùng thuốc chống đông hoặc đau dạ dày nên cân nhắc.');
            }

            return warnings.length ? warnings : ['Vui lòng báo nhân viên nếu bạn có dị ứng thực phẩm hoặc cần tư vấn thành phần.'];
        },

        toggleTopping(topping) {
            const index = this.selectedOptions.toppings.indexOf(topping);
            if (index >= 0) {
                this.selectedOptions.toppings.splice(index, 1);
            } else {
                this.selectedOptions.toppings.push(topping);
            }
        },

        get customizationSummary() {
            if (!this.selectedMon) return '';
            const parts = this.optionPayload.map(option => `${option.label}: ${option.value}`);
            if (this.selectedOptions.note.trim()) parts.push(`Ghi chú: ${this.selectedOptions.note.trim()}`);
            return parts.join(' | ');
        },

        get optionPayload() {
            if (!this.selectedMon) return [];
            const options = [];
            if (this.selectedOptions.temperature) {
                options.push({ type: 'temperature', label: 'Nhiệt độ', value: this.selectedOptions.temperature, price: 0 });
            }
            if (this.selectedOptions.sweetness && this.selectedOptions.sweetness !== 'Không áp dụng') {
                options.push({ type: 'sweetness', label: 'Độ ngọt', value: this.selectedOptions.sweetness, price: 0 });
            }
            for (const topping of this.selectedOptions.toppings) {
                options.push({ type: 'topping', label: 'Topping', value: topping, price: 0 });
            }
            return options;
        },

        addCustomizedToCart() {
            if (!this.selectedMon) return;
            const summary = this.customizationSummary;
            const variantKey = `${this.selectedMon.ma_mon}|${summary}`;
            const existing = this.items.find(i => i.variantKey === variantKey);
            if (existing) {
                existing.qty += this.selectedOptions.qty;
            } else {
                this.items.push({
                    ma_mon: this.selectedMon.ma_mon,
                    ten_mon: this.selectedMon.ten_mon,
                    don_gia: this.selectedMon.don_gia,
                    qty: this.selectedOptions.qty,
                    ghi_chu: summary,
                    options: this.optionPayload,
                    variantKey,
                });
            }
            this.saveCart();
            this.showToast(`Đã thêm ${this.selectedMon.ten_mon} vào giỏ hàng`);
            this.closeCustomizer();
        },

        showToast(message) {
            this.toastMessage = message;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => {
                this.toastMessage = '';
            }, 2600);
        },

        increment(itemKey) {
            const item = this.items.find(i => (i.variantKey || i.ma_mon) === itemKey);
            if (item) { item.qty++; this.saveCart(); }
        },

        decrement(itemKey) {
            const idx = this.items.findIndex(i => (i.variantKey || i.ma_mon) === itemKey);
            if (idx === -1) return;
            if (this.items[idx].qty > 1) {
                this.items[idx].qty--;
            } else {
                this.items.splice(idx, 1);
            }
            this.saveCart();
        },

        saveCart() {
            localStorage.setItem('cart_items', JSON.stringify(this.items));
        },

        formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
        },

        async checkout() {
            const maOrder = document.querySelector('meta[name="ma-order"]')?.content;
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!maOrder) {
                this.checkoutError = 'Không tìm thấy mã đơn hàng.';
                return;
            }

            if (this.items.length === 0) {
                this.checkoutError = 'Giỏ hàng đang trống.';
                return;
            }

            this.checkoutError = '';

            try {
                for (const item of this.items) {
                    const response = await fetch(`/order/${maOrder}/item`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            ma_mon: item.ma_mon,
                            so_luong: item.qty,
                            ghi_chu: item.ghi_chu || null,
                            options: item.options || [],
                        }),
                    });

                    if (!response.ok) {
                        throw new Error('Không thể gửi món lên hệ thống.');
                    }
                }

                this.clearCart();
                window.location.href = `/order/${maOrder}/cart`;
            } catch (error) {
                this.checkoutError = error.message || 'Không thể gửi giỏ hàng.';
            }
        },

        clearCart() {
            this.items = [];
            localStorage.removeItem('cart_items');
        }
    }));
});

Alpine.start();
