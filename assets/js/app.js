document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const apiBase = new URL('api/', window.location.href);
  const money = (value) => {
    const amount = Number(value || 0);
    return '$' + amount.toLocaleString('es-AR', {
      minimumFractionDigits: Number.isInteger(amount) ? 0 : 2,
      maximumFractionDigits: 2,
    });
  };
  const formatQuantity = (value) => Number(value || 0).toLocaleString('es-AR', {
    maximumFractionDigits: 3,
  });
  const formatStock = (value) => Math.round(Number(value || 0)).toLocaleString('es-AR', {
    maximumFractionDigits: 0,
  });
  const localToday = () => {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
  };
  const notify = (message, success = false) => {
    const notice = document.querySelector('#clientNotice');
    if (!notice) return;
    notice.textContent = message;
    notice.className = `flash ${success ? 'flash-success' : 'flash-error'}`;
    notice.hidden = false;
    notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  };
  const apiRequest = (path, method = 'GET', payload = null) => {
    const headers = { Accept: 'application/json' };
    const options = { method, headers, credentials: 'same-origin' };
    if (payload !== null) {
      headers['Content-Type'] = 'application/json';
      headers['X-CSRF-Token'] = csrfToken;
      options.body = JSON.stringify(payload);
    } else if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
      headers['X-CSRF-Token'] = csrfToken;
    }

    return fetch(new URL(path, apiBase), options)
      .then(async (response) => {
        const text = await response.text();
        let result;
        try {
          result = JSON.parse(text);
        } catch {
          throw new Error('El servidor devolvió una respuesta inválida.');
        }
        if (typeof result?.status !== 'boolean') {
          throw new Error('El servidor devolvió una respuesta inválida.');
        }
        if (!response.ok || !result.status) {
          throw new Error(result.message || 'No se pudo completar la operación.');
        }
        return result;
      })
      .catch((error) => {
        if (error instanceof TypeError) {
          throw new Error('No se pudo conectar con el servidor.');
        }
        throw error;
      });
  };
  const setText = (element, value) => {
    if (element) element.textContent = String(value);
  };
  const cell = (text, strong = false) => {
    const td = document.createElement('td');
    if (strong) {
      const value = document.createElement('strong');
      value.textContent = String(text ?? '');
      td.append(value);
    } else {
      td.textContent = String(text ?? '');
    }
    return td;
  };
  const emptyRow = (colspan, message) => {
    const row = document.createElement('tr');
    row.dataset.emptyRow = '';
    const td = cell(message);
    td.colSpan = colspan;
    td.className = 'empty-state';
    row.append(td);
    return row;
  };

  const toggle = document.querySelector('[data-menu-toggle]');
  const sidebar = document.querySelector('#appSidebar');
  const backdrop = document.querySelector('[data-menu-close]');
  const setMenu = (open) => {
    document.body.classList.toggle('menu-open', open);
    toggle?.setAttribute('aria-expanded', String(open));
  };
  toggle?.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
  backdrop?.addEventListener('click', () => setMenu(false));
  sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setMenu(false);
  });
  document.querySelectorAll('[data-scroll-to]').forEach((button) => {
    button.addEventListener('click', () => {
      document.querySelector(`#${button.dataset.scrollTo}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
  });

  let productCache = [];
  const productBody = document.querySelector('#productosBody');
  const alertList = document.querySelector('#alertList');
  const productCount = document.querySelector('#productCount');
  const productSelect = document.querySelector('#product_id');
  if (productSelect) {
    productCache = Array.from(productSelect.options).slice(1).map((option) => ({
      id: Number(option.value),
      name: option.dataset.name ?? option.textContent.split(' · ')[0],
      price: Number(option.dataset.price),
      stock: Number(option.dataset.stock),
    }));
  }

  const renderProductRow = (product) => {
    const row = document.createElement('tr');
    row.dataset.productId = product.id;

    const nameCell = cell('');
    const name = document.createElement('strong');
    name.textContent = product.name;
    const unit = document.createElement('small');
    unit.textContent = product.unit;
    nameCell.append(name, unit);
    row.append(
      nameCell,
      cell(product.category_name),
      cell(money(product.price)),
      cell(formatStock(product.stock)),
      cell(formatStock(product.minimum_stock))
    );

    const low = Number(product.stock) <= Number(product.minimum_stock);
    const statusCell = cell('');
    const badge = document.createElement('span');
    badge.className = `status-pill ${low ? 'status-low' : 'status-ok'}`;
    badge.textContent = low ? 'Stock bajo' : 'En stock';
    statusCell.append(badge);
    row.append(statusCell);

    const actions = cell('');
    const edit = document.createElement('button');
    edit.className = 'table-action';
    edit.type = 'button';
    edit.dataset.editProduct = product.id;
    edit.textContent = 'Editar';
    const remove = document.createElement('button');
    remove.className = 'table-action danger';
    remove.type = 'button';
    remove.dataset.deleteProduct = product.id;
    remove.dataset.confirm = '¿Retirar este producto?';
    remove.textContent = 'Retirar';
    actions.append(edit, remove);
    row.append(actions);
    return row;
  };

  const productSearch = (new URLSearchParams(window.location.search).get('q') ?? '').toLocaleLowerCase();
  const refreshProducts = () => apiRequest('products')
    .then((response) => {
      productCache = response.data;
      const visibleProducts = productSearch
        ? productCache.filter((product) => `${product.name} ${product.category_name}`.toLocaleLowerCase().includes(productSearch))
        : productCache;
      if (productBody) {
        productBody.replaceChildren(...(visibleProducts.length
          ? visibleProducts.map(renderProductRow)
          : [emptyRow(7, 'No hay productos registrados.')]));
      }
      setText(productCount, visibleProducts.length);
      if (alertList) {
        const lowStock = productCache.filter((product) => Number(product.stock) <= Number(product.minimum_stock));
        alertList.replaceChildren(...(lowStock.length
          ? lowStock.map((product) => {
            const row = document.createElement('li');
            const dot = document.createElement('span');
            dot.className = 'status-dot critical';
            const detail = document.createElement('span');
            const name = document.createElement('strong');
            name.textContent = product.name;
            const stock = document.createElement('small');
            stock.textContent = `${formatStock(product.stock)} unidades · mínimo ${formatStock(product.minimum_stock)}`;
            detail.append(name, stock);
            const label = document.createElement('b');
            label.textContent = 'Revisar';
            row.append(dot, detail, label);
            return row;
          })
          : [Object.assign(document.createElement('li'), {
            className: 'empty-state',
            textContent: 'No hay alertas activas.',
          })]));
      }
      if (productSelect) {
        const selected = productSelect.value;
        const placeholder = productSelect.options[0]?.cloneNode(true);
        productSelect.replaceChildren();
        if (placeholder) productSelect.append(placeholder);
        productCache.forEach((product) => {
          const option = document.createElement('option');
          option.value = product.id;
          option.dataset.price = product.price;
          option.dataset.stock = product.stock;
          option.textContent = `${product.name} · ${money(product.price)} · ${formatStock(product.stock)} disponibles`;
          if (String(product.id) === selected) option.selected = true;
          productSelect.append(option);
        });
      }
      return response;
    });

  const productForm = document.querySelector('[data-product-form]');
  if (productForm?.elements.namedItem('id').value) {
    const button = productForm.querySelector('button[type="submit"]');
    if (button) button.innerHTML = 'Actualizar producto <span>→</span>';
  }
  productForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(productForm);
    const id = String(formData.get('id') ?? '').trim();
    const payload = {
      name: String(formData.get('name') ?? '').trim(),
      category_id: Number(formData.get('category_id')),
      unit: String(formData.get('unit') ?? '').trim(),
      cost: Number(formData.get('cost')),
      price: Number(formData.get('price')),
      stock: Number(formData.get('stock')),
      minimum_stock: Number(formData.get('minimum_stock')),
    };
    apiRequest(id ? `products/${encodeURIComponent(id)}` : 'products', id ? 'PUT' : 'POST', payload)
      .then((response) => refreshProducts().then(() => {
        notify(response.message, true);
        if (!id) productForm.reset();
        productForm.elements.namedItem('id').value = '';
        const button = productForm.querySelector('button[type="submit"]');
        if (button) button.innerHTML = 'Guardar producto <span>→</span>';
      }))
      .catch((error) => notify(error.message));
  });

  document.addEventListener('click', (event) => {
    const editProduct = event.target.closest('[data-edit-product]');
    if (editProduct) {
      apiRequest(`products/${encodeURIComponent(editProduct.dataset.editProduct)}`)
        .then((response) => {
          const product = response.data;
          productForm.elements.namedItem('id').value = product.id;
          productForm.elements.namedItem('name').value = product.name;
          productForm.elements.namedItem('category_id').value = product.category_id;
          productForm.elements.namedItem('unit').value = product.unit;
          productForm.elements.namedItem('cost').value = product.cost;
          productForm.elements.namedItem('price').value = product.price;
          productForm.elements.namedItem('stock').value = product.stock;
          productForm.elements.namedItem('minimum_stock').value = product.minimum_stock;
          const button = productForm.querySelector('button[type="submit"]');
          if (button) button.innerHTML = 'Actualizar producto <span>→</span>';
          productForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch((error) => notify(error.message));
    }

    const deleteProduct = event.target.closest('[data-delete-product]');
    if (deleteProduct && window.confirm(deleteProduct.dataset.confirm)) {
      apiRequest(`products/${encodeURIComponent(deleteProduct.dataset.deleteProduct)}`, 'DELETE')
        .then((response) => refreshProducts().then(() => notify(response.message, true)))
        .catch((error) => notify(error.message));
    }

    const editExpense = event.target.closest('[data-edit-expense]');
    if (editExpense) {
      const row = editExpense.closest('[data-expense-id]');
      const form = document.querySelector('[data-expense-form]');
      if (!row || !form) return;
      apiRequest(`expenses/${encodeURIComponent(row.dataset.expenseId)}`)
        .then((response) => {
          const expense = response.data;
          form.elements.namedItem('id').value = expense.id;
          form.elements.namedItem('category').value = expense.category;
          form.elements.namedItem('description').value = expense.description;
          form.elements.namedItem('amount').value = expense.amount;
          form.elements.namedItem('expense_date').value = expense.expense_date;
          setText(document.querySelector('#expenseFormEyebrow'), 'Editar movimiento');
          setText(document.querySelector('#expenseFormTitle'), 'Modificar gasto');
          form.querySelector('button[type="submit"]').textContent = 'Actualizar gasto';
          form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch((error) => notify(error.message));
    }

    const deleteExpense = event.target.closest('[data-delete-expense]');
    if (deleteExpense && window.confirm(deleteExpense.dataset.confirm)) {
      apiRequest(`expenses/${encodeURIComponent(deleteExpense.dataset.deleteExpense)}`, 'DELETE')
        .then((response) => refreshExpenses().then(() => notify(response.message, true)))
        .catch((error) => notify(error.message));
    }
  });

  const expenseForm = document.querySelector('[data-expense-form]');
  const expenseBody = document.querySelector('#gastosBody');
  const refreshExpenses = () => apiRequest('expenses')
    .then((response) => {
      const expenses = response.data;
      if (expenseBody) {
        expenseBody.replaceChildren(...(expenses.length
          ? expenses.map((expense) => {
            const row = document.createElement('tr');
            row.dataset.expenseId = expense.id;
            row.dataset.category = expense.category;
            row.dataset.description = expense.description;
            row.dataset.amount = expense.amount;
            row.dataset.expenseDate = expense.expense_date;
            row.append(cell(expense.category), cell(expense.description), cell(money(expense.amount), true), cell(expense.expense_date));
            const actions = cell('');
            const edit = document.createElement('button');
            edit.className = 'table-action';
            edit.type = 'button';
            edit.dataset.editExpense = expense.id;
            edit.textContent = 'Editar';
            const remove = document.createElement('button');
            remove.className = 'table-action danger';
            remove.type = 'button';
            remove.dataset.deleteExpense = expense.id;
            remove.dataset.confirm = '¿Eliminar este gasto?';
            remove.textContent = 'Eliminar';
            actions.append(edit, remove);
            row.append(actions);
            return row;
          })
          : [emptyRow(5, 'No hay gastos registrados.')]));
      }
      setText(document.querySelector('#expenseTotal'), money(
        expenses.reduce((total, expense) => total + Math.round(Number(expense.amount) * 100), 0) / 100
      ));
      return response;
    });

  expenseForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(expenseForm);
    const id = String(formData.get('id') ?? '').trim();
    const payload = {
      category: String(formData.get('category') ?? '').trim(),
      description: String(formData.get('description') ?? '').trim(),
      amount: Number(formData.get('amount')),
      expense_date: String(formData.get('expense_date') ?? ''),
    };
    apiRequest(id ? `expenses/${encodeURIComponent(id)}` : 'expenses', id ? 'PUT' : 'POST', payload)
      .then((response) => refreshExpenses().then(() => {
        notify(response.message, true);
        expenseForm.reset();
        expenseForm.elements.namedItem('id').value = '';
        expenseForm.elements.namedItem('expense_date').value = localToday();
        setText(document.querySelector('#expenseFormEyebrow'), 'Nuevo movimiento');
        setText(document.querySelector('#expenseFormTitle'), 'Registrar gasto');
        expenseForm.querySelector('button[type="submit"]').textContent = 'Guardar gasto';
      }))
      .catch((error) => notify(error.message));
  });

  const saleForm = document.querySelector('#saleForm');
  const confirmSaleForm = document.querySelector('#confirmSaleForm');
  const cartBody = document.querySelector('#itemsBody');
  let cart = [];
  const renderCart = () => {
    if (!cartBody) return;
    if (cart.length === 0) {
      cartBody.replaceChildren(emptyRow(5, 'Selecciona un producto para comenzar'));
    } else {
      cartBody.replaceChildren(...cart.map((item) => {
        const row = document.createElement('tr');
        row.append(
          cell(item.name, true),
          cell(formatQuantity(item.quantity)),
          cell(money(item.price)),
          cell(money(item.quantity * item.price), true)
        );
        const removeCell = cell('');
        const remove = document.createElement('button');
        remove.className = 'table-action danger';
        remove.type = 'button';
        remove.dataset.removeCartItem = item.product_id;
        remove.textContent = 'Quitar';
        removeCell.append(remove);
        row.append(removeCell);
        return row;
      }));
    }
    setText(document.querySelector('#saleTotal'), money(
      cart.reduce((total, item) => total + item.quantity * item.price, 0)
    ));
    setText(document.querySelector('#cartCount'), `${cart.length} items`);
    const confirmButton = document.querySelector('#confirmSaleBtn');
    if (confirmButton) confirmButton.disabled = cart.length === 0;
  };

  saleForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    const productId = Number(saleForm.elements.namedItem('product_id').value);
    const quantity = Number(saleForm.elements.namedItem('quantity').value);
    const product = productCache.find((item) => Number(item.id) === productId);
    if (!product || !Number.isFinite(quantity) || quantity <= 0) {
      notify('Selecciona un producto y una cantidad válida.');
      return;
    }
    const existing = cart.find((item) => item.product_id === productId);
    const nextQuantity = quantity + (existing?.quantity ?? 0);
    if (nextQuantity > Number(product.stock)) {
      notify('La cantidad supera el stock disponible.');
      return;
    }
    if (existing) existing.quantity = nextQuantity;
    else cart.push({
      product_id: productId,
      name: product.name,
      price: Number(product.price),
      quantity,
    });
    saleForm.reset();
    renderCart();
  });

  cartBody?.addEventListener('click', (event) => {
    const remove = event.target.closest('[data-remove-cart-item]');
    if (!remove) return;
    cart = cart.filter((item) => item.product_id !== Number(remove.dataset.removeCartItem));
    renderCart();
  });

  const refreshSales = () => apiRequest('sales')
    .then((response) => {
      const body = document.querySelector('#salesHistoryBody');
      if (!body) return response;
      body.replaceChildren(...(response.data.length
        ? response.data.map((sale) => {
          const row = document.createElement('tr');
          row.append(cell(`#${sale.id}`), cell(sale.channel), cell(sale.user_name), cell(money(sale.total), true), cell(sale.created_at));
          return row;
        })
        : [emptyRow(5, 'Todavía no hay ventas registradas.')]));
      return response;
    });

  confirmSaleForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    if (cart.length === 0) {
      notify('Agrega al menos un producto antes de confirmar.');
      return;
    }
    const button = document.querySelector('#confirmSaleBtn');
    if (button) button.disabled = true;
    apiRequest('sales', 'POST', {
      channel: confirmSaleForm.elements.namedItem('channel').value,
      items: cart.map(({ product_id, quantity }) => ({ product_id, quantity })),
    })
      .then((response) => {
        cart = [];
        renderCart();
        return Promise.all([refreshProducts(), refreshSales()])
          .then(() => notify(response.message, true));
      })
      .catch((error) => notify(error.message))
      .finally(() => {
        if (button) button.disabled = cart.length === 0;
      });
  });

  const stockForm = document.querySelector('#repositorForm');
  const refreshMovements = () => apiRequest('stock-movements')
    .then((response) => {
      const body = document.querySelector('#stockMovementsBody');
      if (!body) return response;
      body.replaceChildren(...(response.data.length
        ? response.data.map((movement) => {
          const row = document.createElement('tr');
          row.append(
            cell(movement.product_name),
            cell(movement.type),
            cell(formatQuantity(movement.quantity)),
            cell(movement.user_name),
            cell(movement.note),
            cell(movement.created_at)
          );
          return row;
        })
        : [emptyRow(6, 'Todavía no hay movimientos registrados.')]));
      return response;
    });
  stockForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(stockForm);
    apiRequest('stock-movements', 'POST', {
      product_id: Number(formData.get('product_id')),
      quantity: Number(formData.get('quantity')),
      note: String(formData.get('note') ?? '').trim(),
    })
      .then((response) => Promise.all([refreshProducts(), refreshMovements()])
        .then(() => {
          stockForm.reset();
          stockForm.elements.namedItem('quantity').value = '1';
          notify(response.message, true);
        }))
      .catch((error) => notify(error.message));
  });

  if (productBody || productSelect) {
    refreshProducts().catch((error) => notify(error.message));
  }
  if (expenseBody) {
    refreshExpenses().catch((error) => notify(error.message));
  }
  if (document.querySelector('#salesHistoryBody')) {
    refreshSales().catch((error) => notify(error.message));
  }
  if (document.querySelector('#stockMovementsBody')) {
    refreshMovements().catch((error) => notify(error.message));
  }
});
