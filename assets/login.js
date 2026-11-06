import './styles/login.scss';

// "Soy familia / Soy del equipo": las dos entran igual, con su mail; sólo
// cambia la ayuda que se muestra.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-perfil-login]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('[data-perfil-login]').forEach((b) => b.classList.toggle('active', b === btn));
            document.querySelectorAll('[data-ayuda-login]').forEach(function (ayuda) {
                ayuda.classList.toggle('d-none', ayuda.dataset.ayudaLogin !== btn.dataset.perfilLogin);
            });
        });
    });
});
