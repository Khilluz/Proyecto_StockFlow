document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.querySelector('#loginForm');
  const inventoryForm = document.querySelector('#inventarioForm');
  const saleForm = document.querySelector('#saleForm');
  const gastoForm = document.querySelector('#gastoForm');
  const productSelect = document.querySelector('#productoSelect');
  const quantityInput = document.querySelector('#cantidad');
  const canalVenta = document.querySelector('#canalVenta');
  const itemsBody = document.querySelector('#itemsBody');
  const totalDisplay = document.querySelector('#totalDisplay');
  const saleMessage = document.querySelector('#saleMessage');
  const confirmSaleBtn = document.querySelector('#confirmSaleBtn');

  function loginUsuario(e) {
    e.preventDefault();
    const email = document.querySelector('#email')?.value.trim();
    const password = document.querySelector('#password')?.value.trim();
    const role = document.querySelector('#role')?.value;
    const errorBox = document.querySelector('#loginError');

    try {
      if (!email || !password || !role) {
        throw new Error('Complete todos los campos del formulario de acceso.');
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        throw new Error('Ingrese un email válido.');
      }
      if (password.length < 6) {
        throw new Error('La contraseña debe tener al menos 6 caracteres.');
      }
      errorBox.textContent = '';
      console.log(`Placeholder: loginUsuario() -> credenciales válidas para ${role}.`);
      if (role === 'Administrador') {
        window.location.href = 'Pages/dashboard.html';
      } else if (role === 'Vendedor') {
        window.location.href = 'Pages/vendedor.html';
      } else {
        window.location.href = 'Pages/inventario.html';
      }
    } catch (error) {
      if (errorBox) {
        errorBox.textContent = error.message;
      }
      console.error(error.message);
    }
  }

  function validarAlertaStock() {
    const alertList = document.querySelector('#alertList');
    if (!alertList) return;
    const productos = alertList.querySelectorAll('li');
    productos.forEach((item) => {
      item.style.color = '#8b0000';
    });
    console.log('Placeholder: validarAlertaStock() -> revisar umbrales y mostrar alertas.');
  }

  function agregarProductoPos(e) {
    e.preventDefault();
    console.log('Placeholder: agregarProductoPos() -> agregar item al carrito actual.');
  }

  function calcularTotalPos() {
    const selectedProducts = Array.from(productSelect?.selectedOptions || []);
    const cantidad = Number(quantityInput?.value || 1);
    const canal = canalVenta?.value || 'Presencial';
    const precios = {
      'Producto A': 12.5,
      'Producto B': 3.2,
      'Producto C': 5.0
    };

    let total = 0;
    if (itemsBody) {
      itemsBody.innerHTML = '';
      selectedProducts.forEach((option) => {
        const nombre = option.value;
        const precio = precios[nombre] || 0;
        const subtotal = precio * cantidad;
        total += subtotal;
        const row = document.createElement('tr');
        row.innerHTML = `<td>${nombre}</td><td>${cantidad}</td><td>$${precio.toFixed(2)}</td><td>$${subtotal.toFixed(2)}</td>`;
        itemsBody.appendChild(row);
      });
      if (selectedProducts.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = '<td colspan="4">Seleccione al menos un producto</td>';
        itemsBody.appendChild(emptyRow);
      }
    }
    if (totalDisplay) {
      totalDisplay.textContent = `Total Calculado: $${total.toFixed(2)} (${canal})`;
    }
    if (saleMessage) {
      saleMessage.textContent = `Canal de venta: ${canal}`;
    }
    console.log(`Placeholder: calcularTotalPos() -> total ${total.toFixed(2)} para ${canal}.`);
  }

  function confirmarVentaPos(e) {
    e.preventDefault();
    console.log('Placeholder: confirmarVentaPos() -> registrar venta y descontar stock.');
  }

  function agregarProductoInventario(e) {
    e.preventDefault();
    const nombre = document.querySelector('#nombre')?.value.trim();
    const categoria = document.querySelector('#categoria')?.value.trim();
    const precioCosto = Number(document.querySelector('#precioCosto')?.value);
    const precioVenta = Number(document.querySelector('#precioVenta')?.value);
    const unidadMedida = document.querySelector('#unidadMedida')?.value.trim();
    const stockActual = Number(document.querySelector('#stockActual')?.value);
    const stockMinimo = Number(document.querySelector('#stockMinimo')?.value);
    const errorBox = document.querySelector('#inventoryError');
    const tbody = document.querySelector('#productosBody');

    try {
      if (!nombre || !categoria || !unidadMedida || Number.isNaN(precioCosto) || Number.isNaN(precioVenta) || Number.isNaN(stockActual) || Number.isNaN(stockMinimo)) {
        throw new Error('Complete todos los campos obligatorios del producto.');
      }
      if (precioVenta <= precioCosto) {
        throw new Error('El precio de venta debe ser mayor que el precio de costo.');
      }
      errorBox.textContent = '';
      const row = document.createElement('tr');
      row.innerHTML = `<td>${nombre}</td><td>${categoria}</td><td>$${precioCosto.toFixed(2)}</td><td>$${precioVenta.toFixed(2)}</td><td>${stockActual}</td><td>${stockMinimo}</td>`;
      tbody?.appendChild(row);
      console.log(`Placeholder: producto agregado -> ${nombre}`);
    } catch (error) {
      if (errorBox) {
        errorBox.textContent = error.message;
      }
      console.error(error.message);
    }
  }

  function actualizarStockRepositor(e) {
    e.preventDefault();
    console.log('Placeholder: actualizarStockRepositor() -> recibir mercadería y actualizar existencias.');
  }

  if (loginForm) {
    loginForm.addEventListener('submit', loginUsuario);
  }
  if (inventoryForm) {
    inventoryForm.addEventListener('submit', agregarProductoInventario);
  }
  if (saleForm) {
    saleForm.addEventListener('submit', agregarProductoPos);
  }
  if (confirmSaleBtn) {
    confirmSaleBtn.addEventListener('click', confirmarVentaPos);
  }
  if (productSelect) {
    productSelect.addEventListener('change', calcularTotalPos);
  }
  if (quantityInput) {
    quantityInput.addEventListener('input', calcularTotalPos);
  }
  if (canalVenta) {
    canalVenta.addEventListener('change', calcularTotalPos);
  }

  validarAlertaStock();
  calcularTotalPos();
});
