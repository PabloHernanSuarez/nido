import './stimulus_bootstrap.js';
import './styles/app.scss';
import 'bootstrap';

// Botón de ojo de los campos de contraseña (.password-wrapper).
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('mousedown', (e) => e.preventDefault());
        btn.addEventListener('click', function () {
            const input = btn.closest('.password-wrapper').querySelector('input');
            const icon = btn.querySelector('i');
            const mostrando = input.type === 'text';
            input.type = mostrando ? 'password' : 'text';
            icon.classList.toggle('bi-eye', mostrando);
            icon.classList.toggle('bi-eye-slash', !mostrando);
        });
    });
});
