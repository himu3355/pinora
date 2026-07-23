
// Wishlist toggle helper (used on product cards)
document.addEventListener('DOMContentLoaded', () => {
    // Helper to rebuild wishlist dropdown menu dynamically
    function rebuildWishlistDropdown(items) {
        const itemsList = document.querySelector('.wishlist-dropdown-items-list');
        const emptyState = document.querySelector('.wishlist-dropdown-empty-state');
        
        if (!itemsList || !emptyState) return;
        
        if (!items || items.length === 0) {
            itemsList.classList.add('hidden');
            itemsList.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            itemsList.classList.remove('hidden');
            
            let html = '';
            items.forEach(item => {
                const priceHtml = item.is_price_on_request 
                    ? 'Price on Request' 
                    : '₹' + parseFloat(item.price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    
                html += `
                    <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-wishlist-item="${item.product_id}">
                        <a href="/product/${item.slug}" class="flex items-center gap-3 flex-grow min-w-0">
                            <img src="${item.image_url}" alt="${item.name}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.name}</h4>
                                <p class="text-xs text-amber-600 font-semibold mt-0.5">${priceHtml}</p>
                            </div>
                        </a>
                        <button type="button" data-remove-wishlist="${item.product_id}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Wishlist">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `;
            });
            itemsList.innerHTML = html;
        }
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-wishlist-toggle]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = btn.dataset.wishlistToggle;
            const res = await fetch(`/wishlist/toggle/${productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            if (res.status === 401) {
                window.location.href = '/login';
                return;
            }
            const data = await res.json();
            
            // Toggle active state for heart icons
            btn.classList.toggle('active', data.wishlisted);
            
            // Update button text if it is the detail page button
            if (btn.classList.contains('btn-outline-gold')) {
                btn.textContent = data.wishlisted ? '♥ Remove from Wishlist' : '♡ Add to Wishlist';
            }
            
            // Update all wishlist count badges in real-time
            document.querySelectorAll('.wishlist-count-badge').forEach(badge => {
                badge.textContent = data.count;
                if (data.count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });

            // Rebuild wishlist dropdown
            rebuildWishlistDropdown(data.recent);
        }
    });

    // Account dropdown menu toggle for mobile/touch screens (desktop view fallback & click behavior)
    const accountDropdown = document.getElementById('account-dropdown');
    if (accountDropdown) {
        const btn = accountDropdown.querySelector('button');
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            accountDropdown.classList.toggle('open');
        });
        
        document.addEventListener('click', (e) => {
            if (!accountDropdown.contains(e.target)) {
                accountDropdown.classList.remove('open');
            }
        });

        accountDropdown.addEventListener('mouseleave', () => {
            accountDropdown.classList.remove('open');
        });
    }

    // Desktop Dropdowns (Wishlist & Cart)
    const wishlistDropdown = document.getElementById('desktop-wishlist-dropdown');
    const cartDropdown = document.getElementById('desktop-cart-dropdown');
    
    function toggleDropdown(dropdown) {
        if (!dropdown) return;
        const menu = dropdown.querySelector('.dropdown-menu');
        if (!menu) return;
        
        const isOpen = menu.classList.contains('opacity-100');
        
        closeAllDesktopDropdowns();
        
        if (!isOpen) {
            menu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            menu.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
        }
    }
    
    function closeAllDesktopDropdowns() {
        [wishlistDropdown, cartDropdown].forEach(dropdown => {
            if (!dropdown) return;
            const menu = dropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        });
    }
    
    if (wishlistDropdown) {
        const btn = wishlistDropdown.querySelector('button');
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(wishlistDropdown);
        });
    }
    
    if (cartDropdown) {
        const btn = cartDropdown.querySelector('button');
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown(cartDropdown);
        });
    }

    // Drawer Management Helper
    const backdrop = document.getElementById('mobile-drawer-backdrop');
    
    function openDrawer(drawer) {
        if (!drawer) return;
        closeAllDesktopDropdowns(); // Close dropdowns when mobile menu opens
        drawer.classList.remove('-translate-x-full', 'translate-x-full');
        drawer.classList.add('translate-x-0');
        if (backdrop) {
            backdrop.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
            }, 10);
        }
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeAllDrawers() {
        const menuDrawer = document.getElementById('mobile-menu-drawer');
        const accountDrawer = document.getElementById('mobile-account-drawer');
        
        if (menuDrawer) {
            menuDrawer.classList.remove('translate-x-0');
            menuDrawer.classList.add('-translate-x-full');
        }
        if (accountDrawer) {
            accountDrawer.classList.remove('translate-x-0');
            accountDrawer.classList.add('translate-x-full');
        }
        
        if (backdrop) {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            setTimeout(() => {
                backdrop.classList.add('hidden');
            }, 300);
        }
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Mobile Menu Drawer
    const menuTrigger = document.getElementById('mobile-menu-trigger');
    const menuClose = document.getElementById('mobile-menu-close');
    const menuDrawer = document.getElementById('mobile-menu-drawer');
    
    if (menuTrigger) {
        menuTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            openDrawer(menuDrawer);
        });
    }
    if (menuClose) {
        menuClose.addEventListener('click', closeAllDrawers);
    }
    
    // Mobile Account Drawer
    const accountTrigger = document.getElementById('mobile-account-trigger');
    const accountClose = document.getElementById('mobile-account-close');
    const accountDrawer = document.getElementById('mobile-account-drawer');
    
    if (accountTrigger) {
        accountTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            openDrawer(accountDrawer);
        });
    }
    if (accountClose) {
        accountClose.addEventListener('click', closeAllDrawers);
    }
    
    // Backdrop click close action
    if (backdrop) {
        backdrop.addEventListener('click', closeAllDrawers);
    }

    // Search Overlay Toggles
    const desktopSearchBtn = document.getElementById('desktop-search-trigger');
    const mobileSearchBtn = document.getElementById('mobile-search-trigger');
    const searchOverlay = document.getElementById('global-search-overlay');
    const searchClose = document.getElementById('search-close');
    const searchInput = document.getElementById('global-search-input');
    
    function openSearch() {
        if (!searchOverlay) return;
        closeAllDesktopDropdowns(); // Close dropdowns when search opens
        searchOverlay.classList.remove('opacity-0', 'pointer-events-none');
        setTimeout(() => {
            if (searchInput) searchInput.focus();
        }, 100);
        document.body.style.overflow = 'hidden';
    }
    
    function closeSearch() {
        if (!searchOverlay) return;
        searchOverlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }
    
    if (desktopSearchBtn) desktopSearchBtn.addEventListener('click', openSearch);
    if (mobileSearchBtn) mobileSearchBtn.addEventListener('click', openSearch);
    if (searchClose) searchClose.addEventListener('click', closeSearch);
    
    // Document click listener to close everything when clicking outside
    document.addEventListener('click', (e) => {
        // Close desktop dropdowns if clicked outside
        if (wishlistDropdown && !wishlistDropdown.contains(e.target)) {
            const menu = wishlistDropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        }
        if (cartDropdown && !cartDropdown.contains(e.target)) {
            const menu = cartDropdown.querySelector('.dropdown-menu');
            if (menu) {
                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            }
        }
    });

    // Escape key listener to close overlay/drawers/dropdowns
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSearch();
            closeAllDrawers();
            closeAllDesktopDropdowns();
        }
    });

    // ==========================================
    // DYNAMIC CART & WISHLIST AJAX HANDLERS
    // ==========================================

    // Helper to update all cart count badges in real-time
    function updateCartBadges(count) {
        document.querySelectorAll('.cart-count-badge').forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    // Helper to rebuild cart dropdown menu dynamically
    function rebuildCartDropdown(items, subtotal) {
        const itemsList = document.querySelector('.cart-dropdown-items-list');
        const emptyState = document.querySelector('.cart-dropdown-empty-state');
        const footer = document.querySelector('.cart-dropdown-footer');
        const subtotalEl = document.querySelector('.cart-dropdown-subtotal');
        
        if (!itemsList || !emptyState || !footer) return;
        
        const itemKeys = Object.keys(items);
        
        if (itemKeys.length === 0) {
            itemsList.classList.add('hidden');
            itemsList.innerHTML = '';
            footer.classList.add('hidden');
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            itemsList.classList.remove('hidden');
            footer.classList.remove('hidden');
            if (subtotalEl) {
                subtotalEl.textContent = '₹' + parseFloat(subtotal).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            
            let html = '';
            for (const key of itemKeys) {
                const item = items[key];
                const variantNameHtml = item.variant_name 
                    ? `<p class="text-[0.7rem] text-gray-400 truncate">${item.variant_name}</p>` 
                    : '';
                const unitPriceFormatted = parseFloat(item.unit_price).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                
                html += `
                    <div class="flex items-center justify-between gap-3 py-1.5 hover:bg-gray-50 rounded px-2" data-cart-item="${key}">
                        <a href="/product/${item.slug}" class="flex items-center gap-3 flex-grow min-w-0">
                            <img src="${item.image_url}" alt="${item.product_name}" class="w-12 h-12 object-cover rounded bg-gray-100 flex-shrink-0">
                            <div class="min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 truncate">${item.product_name}</h4>
                                ${variantNameHtml}
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Qty: <span class="cart-dropdown-qty">${item.quantity}</span> &times; <span class="font-semibold text-amber-600">₹${unitPriceFormatted}</span>
                                </p>
                            </div>
                        </a>
                        <button type="button" data-remove-cart="${key}" class="text-gray-400 hover:text-red-500 bg-transparent border-0 cursor-pointer p-1" aria-label="Remove from Cart">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                `;
            }
            itemsList.innerHTML = html;
        }
    }

    // Helper to update the cart summary elements on /cart page
    function updateCartSummary(subtotal, gstAmount, total) {
        const subtotalEl = document.getElementById('cart-summary-subtotal');
        const gstEl = document.getElementById('cart-summary-gst');
        const totalEl = document.getElementById('cart-summary-total');
        
        const formatPrice = val => '₹' + parseFloat(val).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
        if (gstEl) gstEl.textContent = formatPrice(gstAmount);
        if (totalEl) totalEl.textContent = formatPrice(total);
    }

    // Intercept Add to Cart form submission
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Sync quantity value
            const qtyInput = document.getElementById('qty-input');
            const formQtyInput = document.getElementById('form_qty');
            if (qtyInput && formQtyInput) {
                formQtyInput.value = qtyInput.value;
            }
            
            const formData = new FormData(addToCartForm);
            const action = addToCartForm.getAttribute('action');
            
            const btn = addToCartForm.querySelector('button[type="submit"]');
            const originalText = btn ? btn.textContent : '';
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Adding...';
            }
            
            try {
                const response = await fetch(action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                
                const data = await response.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    if (btn) {
                        btn.textContent = 'Added!';
                        btn.classList.remove('btn-gold');
                        btn.classList.add('bg-green-600', 'text-white');
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.textContent = originalText;
                            btn.classList.add('btn-gold');
                            btn.classList.remove('bg-green-600', 'text-white');
                        }, 2000);
                    }
                }
            } catch (err) {
                console.error(err);
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            }
        });
    }

    // Event delegation for Wishlist Dropdown Removals
    document.addEventListener('click', async (e) => {
        const removeWishlistBtn = e.target.closest('[data-remove-wishlist]');
        if (removeWishlistBtn) {
            e.preventDefault();
            e.stopPropagation();
            const productId = removeWishlistBtn.dataset.removeWishlist;
            
            try {
                const res = await fetch(`/wishlist/toggle/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
                const data = await res.json();
                
                // Update heart icons on cards
                document.querySelectorAll(`[data-wishlist-toggle="${productId}"]`).forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.classList.contains('btn-outline-gold')) {
                        btn.textContent = '♡ Add to Wishlist';
                    }
                });
                
                // Update all wishlist count badges
                document.querySelectorAll('.wishlist-count-badge').forEach(badge => {
                    badge.textContent = data.count;
                    if (data.count > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
                
                // Rebuild wishlist dropdown
                rebuildWishlistDropdown(data.recent);
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Event delegation for Cart Dropdown Removals
    document.addEventListener('click', async (e) => {
        const removeCartBtn = e.target.closest('[data-remove-cart]');
        if (removeCartBtn) {
            e.preventDefault();
            e.stopPropagation();
            const key = removeCartBtn.dataset.removeCart;
            
            try {
                const res = await fetch(`/cart/${key}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    // Synchronize with /cart page if the user is currently on it
                    const cartPageItemRow = document.querySelector(`.cart-item-row[data-cart-item="${key}"]`);
                    if (cartPageItemRow) {
                        const vendorGroup = cartPageItemRow.closest('.vendor-group');
                        cartPageItemRow.remove();
                        if (vendorGroup) {
                            const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                            if (remainingItems.length === 0) {
                                vendorGroup.remove();
                            }
                        }
                        updateCartSummary(data.subtotal, data.gst_amount, data.total);
                        
                        if (data.isEmpty) {
                            document.getElementById('cart-main-grid')?.classList.add('hidden');
                            document.getElementById('cart-empty-state')?.classList.remove('hidden');
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Intercept quantity updates (+ / - buttons) on /cart page
    document.addEventListener('submit', async (e) => {
        const updateForm = e.target.closest('.cart-update-form');
        if (updateForm) {
            e.preventDefault();
            
            const submitter = e.submitter;
            if (!submitter) return;
            
            const newQty = submitter.value;
            const action = updateForm.getAttribute('action');
            const itemRow = updateForm.closest('.cart-item-row');
            if (!itemRow) return;
            const key = itemRow.dataset.cartItem;
            
            try {
                const res = await fetch(action, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: newQty })
                });
                
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    if (data.quantity <= 0) {
                        const vendorGroup = itemRow.closest('.vendor-group');
                        itemRow.remove();
                        if (vendorGroup) {
                            const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                            if (remainingItems.length === 0) {
                                vendorGroup.remove();
                            }
                        }
                    } else {
                        // Update line total
                        const lineTotalEl = itemRow.querySelector('.cart-item-line-total');
                        if (lineTotalEl) {
                            lineTotalEl.textContent = '₹' + parseFloat(data.line_total).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        }
                        
                        // Update qty span
                        const qtySpan = itemRow.querySelector('.cart-item-qty');
                        if (qtySpan) {
                            qtySpan.textContent = data.quantity;
                        }
                        
                        // Update forms decrement value
                        const decBtn = updateForm.querySelector('button[value]');
                        if (decBtn) {
                            decBtn.value = Math.max(0, data.quantity - 1);
                        }
                        const incBtn = updateForm.querySelectorAll('button[value]')[1];
                        if (incBtn) {
                            incBtn.value = data.quantity + 1;
                        }
                    }
                    
                    updateCartSummary(data.subtotal, data.gst_amount, data.total);
                    
                    if (data.isEmpty) {
                        document.getElementById('cart-main-grid')?.classList.add('hidden');
                        document.getElementById('cart-empty-state')?.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // Intercept item removals on /cart page
    document.addEventListener('submit', async (e) => {
        const removeForm = e.target.closest('.cart-remove-form');
        if (removeForm) {
            e.preventDefault();
            
            const action = removeForm.getAttribute('action');
            const itemRow = removeForm.closest('.cart-item-row');
            if (!itemRow) return;
            const key = itemRow.dataset.cartItem;
            
            try {
                const res = await fetch(action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                
                const data = await res.json();
                if (data.success) {
                    updateCartBadges(data.count);
                    rebuildCartDropdown(data.items, data.subtotal);
                    
                    const vendorGroup = itemRow.closest('.vendor-group');
                    itemRow.remove();
                    if (vendorGroup) {
                        const remainingItems = vendorGroup.querySelectorAll('.cart-item-row');
                        if (remainingItems.length === 0) {
                            vendorGroup.remove();
                        }
                    }
                    
                    updateCartSummary(data.subtotal, data.gst_amount, data.total);
                    
                    if (data.isEmpty) {
                        document.getElementById('cart-main-grid')?.classList.add('hidden');
                        document.getElementById('cart-empty-state')?.classList.remove('hidden');
                    }
                }
            } catch (err) {
                console.error(err);
            }
        }
    });

    // ==========================================
    // DYNAMIC SHOP FILTERS & PAGINATION AJAX
    // ==========================================

    function syncFormInputsWithUrl() {
        const params = new URLSearchParams(window.location.search);
        const filterForm = document.getElementById('filter-form');
        if (!filterForm) return;

        // Sync radio buttons (category, metal_type, purity)
        ['category', 'metal_type', 'purity'].forEach(name => {
            const val = params.get(name);
            filterForm.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                radio.checked = (val !== null && radio.value === val);
            });
        });

        // Sync checkbox (in_stock)
        const inStockCheckbox = filterForm.querySelector('input[name="in_stock"]');
        if (inStockCheckbox) {
            inStockCheckbox.checked = params.has('in_stock') && params.get('in_stock') === '1';
        }

        // Sync select (vendor_id)
        const vendorSelect = filterForm.querySelector('select[name="vendor_id"]');
        if (vendorSelect) {
            vendorSelect.value = params.get('vendor_id') || '';
        }

        // Sync sort select
        const sortSelect = document.querySelector('#sort-form select[name="sort"]');
        if (sortSelect) {
            sortSelect.value = params.get('sort') || 'newest';
        }
    }

    async function fetchShopProducts(queryString, updateHistory = true) {
        const gridContainer = document.getElementById('products-grid-container');
        if (gridContainer) {
            gridContainer.classList.add('opacity-50');
        }

        try {
            const separator = queryString ? '?' : '';
            const fetchUrl = `/shop${separator}${queryString}`;

            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();

            // Update Page Content
            const titleEl = document.getElementById('shop-title');
            if (titleEl && data.title) titleEl.innerHTML = data.title;

            const countEl = document.getElementById('shop-count');
            if (countEl && data.count) countEl.textContent = data.count;

            const showingEl = document.getElementById('showing-results-text');
            if (showingEl && data.showing) showingEl.textContent = data.showing;

            const activeFiltersEl = document.getElementById('active-filters-container');
            if (activeFiltersEl) activeFiltersEl.innerHTML = data.active_filters || '';

            if (gridContainer && data.grid) gridContainer.innerHTML = data.grid;

            const paginationEl = document.getElementById('pagination-container');
            if (paginationEl) paginationEl.innerHTML = data.pagination || '';

            // Update URL
            if (updateHistory) {
                history.pushState(null, '', fetchUrl);
            }

            // Sync inputs with the new URL state
            syncFormInputsWithUrl();

            // Scroll to the top of the shop section smoothly
            const listHeader = document.querySelector('.max-w-7xl');
            if (listHeader) {
                listHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (err) {
            console.error('Error fetching shop products:', err);
        } finally {
            if (gridContainer) {
                gridContainer.classList.remove('opacity-50');
            }
        }
    }

    // Filter Form listener
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', (e) => e.preventDefault());

        filterForm.addEventListener('change', () => {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            // Preserve search term from current URL if it exists
            const currentParams = new URLSearchParams(window.location.search);
            if (currentParams.has('search')) {
                params.set('search', currentParams.get('search'));
            }

            // Preserve sort parameter
            const sortSelect = document.querySelector('#sort-form select[name="sort"]');
            if (sortSelect && sortSelect.value) {
                params.set('sort', sortSelect.value);
            }

            // Reset page when filters change
            params.delete('page');

            fetchShopProducts(params.toString());
        });
    }

    // Sort Form/Select listener
    const sortForm = document.getElementById('sort-form');
    if (sortForm) {
        sortForm.addEventListener('submit', (e) => e.preventDefault());
    }

    const sortSelect = document.querySelector('#sort-form select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            const currentParams = new URLSearchParams(window.location.search);
            const filterFormData = filterForm ? new FormData(filterForm) : new FormData();
            const params = new URLSearchParams(filterFormData);

            // Preserve search
            if (currentParams.has('search')) {
                params.set('search', currentParams.get('search'));
            }

            // Set new sort
            params.set('sort', sortSelect.value);

            // Reset page
            params.delete('page');

            fetchShopProducts(params.toString());
        });
    }

    // Event delegation for Active Filters (Removal tags and Clear all)
    const activeFiltersContainer = document.getElementById('active-filters-container');
    if (activeFiltersContainer) {
        activeFiltersContainer.addEventListener('click', (e) => {
            const removeLink = e.target.closest('.data-filter-remove');
            const clearLink = e.target.closest('.data-filter-clear');

            if (removeLink || clearLink) {
                e.preventDefault();
                const href = removeLink ? removeLink.getAttribute('href') : clearLink.getAttribute('href');
                if (href) {
                    const url = new URL(href, window.location.origin);
                    fetchShopProducts(url.searchParams.toString());
                }
            }
        });
    }

    // Event delegation for Pagination links
    const paginationContainer = document.getElementById('pagination-container');
    if (paginationContainer) {
        paginationContainer.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link) {
                e.preventDefault();
                const href = link.getAttribute('href');
                if (href) {
                    const url = new URL(href, window.location.origin);
                    fetchShopProducts(url.searchParams.toString());
                }
            }
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        fetchShopProducts(params.toString(), false);
    });

    // Run synchronization once on page load (in case there are existing parameters)
    if (document.getElementById('filter-form')) {
        syncFormInputsWithUrl();
    }
});
