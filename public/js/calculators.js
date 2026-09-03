/* ===========================
   CopyCabana — Price Calculators
   Calculation logic for each product type
   =========================== */

const Calculators = {
  /* --- Options-based calculator --- */
  options(product, selectedValues) {
    let total = 0;
    for (const [key, option] of Object.entries(product.options)) {
      const val = selectedValues[key] || option.default;
      if (option.type === 'select') {
        const choice = option.values.find(v => v.value === val);
        if (choice && choice.price !== undefined) total += choice.price;
      }
    }
    return Math.max(0, total);
  },

  /* --- Area-based calculator (banery, billboardy, fototapety) --- */
  area(product, selectedValues, widthCm, heightCm) {
    const w = parseFloat(widthCm) || 0;
    const h = parseFloat(heightCm) || 0;
    const areaM2 = (w / 100) * (h / 100);
    let base = areaM2 * product.pricePerM2;
    if (base < product.minPrice) base = product.minPrice;

    let extra = 0;
    for (const [key, option] of Object.entries(product.options || {})) {
      const val = selectedValues[key] || option.default;
      if (option.type === 'select') {
        const choice = option.values.find(v => v.value === val);
        if (choice && choice.price !== undefined) extra += choice.price;
      }
    }
    return Math.round((base + extra) * 100) / 100;
  },

  /* --- Ksero/copy calculator (ksero, skanowanie, rysunki_cad) --- */
  ksero(product, selectedValues) {
    const format = selectedValues.format || 'A4';
    const kolor = selectedValues.kolor || 'czarnobialy';
    const naklad = parseInt(selectedValues.naklad) || 1;
    const unitPrice = (product.prices[format] && product.prices[format][kolor]) || 0;
    return Math.round(unitPrice * naklad * 100) / 100;
  },

  /* --- Fixed-price calculator (pieczatki, zdjecia, projektowanie, uslugi_dodatkowe, oprawa_prac) --- */
  fixed(product, selectedValues) {
    const val = selectedValues.usluga || product.items[0].value;
    const item = product.items.find(i => i.value === val);
    return item ? item.price : 0;
  },

  /* --- Master calculate function --- */
  calculate(product, selectedValues, extra) {
    switch (product.calculatorType) {
      case 'options':
        return this.options(product, selectedValues);
      case 'area':
        return this.area(product, selectedValues, extra?.width, extra?.height);
      case 'ksero':
        return this.ksero(product, selectedValues);
      case 'fixed':
        return this.fixed(product, selectedValues);
      default:
        return 0;
    }
  },

  /* --- Format price as PLN --- */
  formatPrice(amount) {
    return amount.toFixed(2).replace('.', ',') + ' zł';
  },

  /* --- Build order summary text --- */
  buildSummary(product, selectedValues, price, extra) {
    const lines = [product.name];
    for (const [key, option] of Object.entries(product.options || {})) {
      const val = selectedValues[key] || option.default;
      if (option.type === 'select') {
        const choice = option.values.find(v => v.value === val);
        if (choice) lines.push(`${option.label}: ${choice.label}`);
      } else if (option.type === 'number') {
        lines.push(`${option.label}: ${val}`);
      }
    }
    if (product.calculatorType === 'area' && extra) {
      lines.push(`Wymiary: ${extra.width} × ${extra.height} cm`);
    }
    if (product.calculatorType === 'fixed') {
      const item = product.items.find(i => i.value === selectedValues.usluga);
      if (item) lines.push(`Usługa: ${item.label}`);
    }
    lines.push(`Cena: ${this.formatPrice(price)}`);
    return lines.join(' | ');
  }
};
