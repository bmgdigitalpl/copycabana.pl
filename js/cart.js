/* ===========================
   CopyCabana — Cart System
   localStorage-based shopping cart
   =========================== */

const Cart = {
  STORAGE_KEY: 'copycabana_cart',

  getItems() {
    const stored = localStorage.getItem(this.STORAGE_KEY);
    if (stored) {
      try { return JSON.parse(stored); } catch (e) { return []; }
    }
    return [];
  },

  saveItems(items) {
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(items));
    this.updateBadge();
    window.dispatchEvent(new CustomEvent('cart-updated'));
  },

  addItem(item) {
    const items = this.getItems();
    item.id = Date.now() + Math.random().toString(36).substr(2, 5);
    item.addedAt = new Date().toISOString();
    items.push(item);
    this.saveItems(items);
    return item.id;
  },

  removeItem(id) {
    const items = this.getItems().filter(i => i.id !== id);
    this.saveItems(items);
  },

  updateItem(id, updates) {
    const items = this.getItems();
    const idx = items.findIndex(i => i.id === id);
    if (idx !== -1) {
      items[idx] = { ...items[idx], ...updates };
      this.saveItems(items);
    }
  },

  clear() {
    localStorage.removeItem(this.STORAGE_KEY);
    this.updateBadge();
    window.dispatchEvent(new CustomEvent('cart-updated'));
  },

  getTotal() {
    return this.getItems().reduce((sum, item) => sum + (item.price * item.quantity), 0);
  },

  getCount() {
    return this.getItems().reduce((sum, item) => sum + item.quantity, 0);
  },

  updateBadge() {
    const badges = document.querySelectorAll('.cart-badge');
    const count = this.getCount();
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'flex' : 'none';
    });
  }
};

/* Auto-update badges on page load */
document.addEventListener('DOMContentLoaded', () => Cart.updateBadge());
