document.addEventListener('DOMContentLoaded', function() {
    // Cambiar imagen principal en la página de producto
    const thumbnails = document.querySelectorAll('.thumbnails img');
    const mainImage = document.querySelector('.main-image img');

    if (thumbnails && mainImage) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                mainImage.src = this.src;
            });
        });
    }

    // Actualizar cantidad en el carrito
    const quantityForms = document.querySelectorAll('.update-quantity-form');
    quantityForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/cart/update', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al actualizar el carrito');
                    }
                });
        });
    });

    // Validación de formulario de checkout
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                e.preventDefault();
                alert('Por favor selecciona un método de pago');
            }
        });
    }

    // Mostrar/ocultar secciones de administración
    const adminToggles = document.querySelectorAll('[data-toggle]');
    adminToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const target = document.querySelector(this.dataset.toggle);
            target.classList.toggle('d-none');
        });
    });

    // Añadir más variantes de producto en el admin
    const addVariantBtn = document.querySelector('.add-variant');
    if (addVariantBtn) {
        addVariantBtn.addEventListener('click', function() {
            const variantsContainer = document.querySelector('.variants-container');
            const newVariant = document.querySelector('.variant-item').cloneNode(true);

            // Limpiar valores
            const inputs = newVariant.querySelectorAll('input');
            inputs.forEach(input => input.value = '');

            variantsContainer.appendChild(newVariant);
        });
    }

    // Eliminar variante de producto en el admin
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            if (document.querySelectorAll('.variant-item').length > 1) {
                e.target.closest('.variant-item').remove();
            } else {
                alert('Debe haber al menos una variante');
            }
        }
    });

    // Mostrar notificaciones
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});