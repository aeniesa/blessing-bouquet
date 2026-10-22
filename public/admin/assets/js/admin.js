/* admin.js — interaksi panel admin. */
(function () {
    'use strict';

    var jenis = document.getElementById('jenis_harga');
    if (jenis) {
        var medanHarga = document.querySelectorAll('[data-harga]');
        var perbarui = function () {
            var nilai = jenis.value;
            medanHarga.forEach(function (el) {
                el.style.display = (el.getAttribute('data-harga') === nilai) ? '' : 'none';
            });
        };
        jenis.addEventListener('change', perbarui);
        perbarui();
    }

    /* ---------- Slider latar hero ---------- */
    var sliderBlur = document.querySelector('[data-hero-blur]');
    var sliderTrans = document.querySelector('[data-hero-transparansi]');
    var pratinjau = document.getElementById('pratinjau-hero') || document.querySelector('.pratinjau-hero');
    var outBlur = document.getElementById('nilai-hero-blur');
    var outTrans = document.getElementById('nilai-hero-transparansi');

    var tulis = function (el, nilai, satuan) {
        if (el) {
            el.textContent = nilai + (satuan || '');
        }
    };

    var terapkan = function () {
        if (!sliderBlur && !sliderTrans) {
            return;
        }
        var blur = sliderBlur ? parseInt(sliderBlur.value, 10) : 0;
        var trans = sliderTrans ? parseInt(sliderTrans.value, 10) : 0;

        tulis(outBlur, blur, ' px');
        tulis(outTrans, trans, ' %');

        if (pratinjau) {
            pratinjau.style.setProperty('--hero-blur', String(blur));
            pratinjau.style.setProperty('--hero-lapisan', String(100 - trans) + '%');
        }
    };

    if (sliderBlur) {
        sliderBlur.addEventListener('input', terapkan);
    }
    if (sliderTrans) {
        sliderTrans.addEventListener('input', terapkan);
    }
    terapkan();
})();
