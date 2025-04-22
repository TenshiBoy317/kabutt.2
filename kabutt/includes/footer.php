<?php
        $baseUrl = '/kabutt/';


?>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Columna 1 -->
            <div class="footer-column">
                <h3>Kabutt</h3>
                <p>Tu tienda de calzado favorita con las mejores marcas y estilos.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <!-- Columna 2 -->
            <div class="footer-column">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="<?= $baseUrl ?>">Inicio</a></li>
                    <li><a href="<?= $baseUrl ?>?page=products">Productos</a></li>
                    <li><a href="<?= $baseUrl ?>?page=about">Nosotros</a></li>
                    <li><a href="<?= $baseUrl ?>?page=contact">Contacto</a></li>
                </ul>
            </div>

            <!-- Columna 3 -->
            <div class="footer-column">
                <h3>Contacto</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> Dirección: Av. Principal 123, Lima</li>
                    <li><i class="fas fa-phone"></i> Teléfono: +51 123 456 789</li>
                    <li><i class="fas fa-envelope"></i> Email: info@kabutt.com</li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            <p>&copy; <?= date('Y') ?> Kabutt. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
<style>
    /* Footer styles */
    .footer {
        background-color: #222;
        color: #fff;
        padding: 3rem 0;
        margin-top: 4rem;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }

    .footer-column {
        padding: 0 1rem;
    }

    .footer-column h3 {
        color: #fff;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        position: relative;
        padding-bottom: 0.5rem;
    }

    .footer-column h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: #fff;
    }

    .footer-column ul {
        list-style: none;
        padding: 0;
    }

    .footer-column ul li {
        margin-bottom: 0.8rem;
    }

    .footer-column ul li a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-column ul li a:hover {
        color: #fff;
    }

    .contact-info li {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .social-links a {
        color: #fff;
        font-size: 1.2rem;
        transition: opacity 0.3s;
    }

    .social-links a:hover {
        opacity: 0.8;
    }

    .copyright {
        text-align: center;
        margin-top: 3rem;
        padding-top: 1.5rem;
        border-top: 1px solid #444;
        color: #aaa;
        font-size: 0.9rem;
    }

    /* Responsive footer */
    @media (max-width: 768px) {
        .footer-grid {
            grid-template-columns: 1fr;
        }

        .footer-column {
            margin-bottom: 2rem;
        }
    }
</style>