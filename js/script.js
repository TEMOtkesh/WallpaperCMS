// ============================================================
// js/script.js
// Requirement: jQuery
// All jQuery features are labelled with their requirement.
// ============================================================

$(document).ready(function () {

    // ============================================================
    // Requirement: jQuery — slideToggle() Search Panel
    // The #search-toggle button shows/hides #search-panel
    // with a smooth slide animation.
    // ============================================================
    $('#search-toggle').on('click', function () {
        $('#search-panel').slideToggle(300);

        // Swap button text so the user knows the panel state
        const isVisible = $('#search-panel').is(':visible');
        $(this).find('.toggle-label').text(isVisible ? 'Hide Search' : 'Search & Filter');
    });


    // ============================================================
    // Requirement: jQuery — fadeIn() Wallpaper Cards
    // Cards start hidden (opacity 0 via inline style set in PHP)
    // and fade in one by one with a staggered delay for a polished
    // gallery-reveal effect.
    // ============================================================
    $('.wallpaper-card').each(function (index) {
        const $card = $(this);
        $card.css('opacity', 0); // ensure starts hidden

        // Stagger each card by 80ms
        setTimeout(function () {
            $card.fadeIn(400);   // Requirement: jQuery fadeIn()
        }, index * 80);
    });


    // ============================================================
    // Requirement: jQuery — Category card fade in (categories page)
    // ============================================================
    $('.category-card').each(function (index) {
        const $card = $(this);
        $card.css('opacity', 0);
        setTimeout(function () {
            $card.fadeIn(350);
        }, index * 100);
    });


    // ============================================================
    // Requirement: jQuery — Mobile Navigation Toggle
    // Toggles the .nav-links open/close on small screens.
    // ============================================================
    $('#nav-toggle').on('click', function () {
        $('.nav-links').toggleClass('open');
    });

    // Close mobile menu when a link is clicked
    $('.nav-links a').on('click', function () {
        $('.nav-links').removeClass('open');
    });


    // ============================================================
    // Requirement: jQuery — Auto-dismiss alerts after 5 seconds
    // ============================================================
    setTimeout(function () {
        $('.alert').fadeOut(600);
    }, 5000);


    // ============================================================
    // Requirement: jQuery + Color Picker
    // Live color picker updates the preview box and outputs
    // HEX, RGBA, and HSL values in real time.
    // Requirement: HEX / RGBA / HSL color examples
    // ============================================================
    $('#theme-color-picker').on('input change', function () {
        const hex = $(this).val();           // e.g. "#6a11cb"

        // Update the visual preview box
        $('#live-preview').css('background-color', hex);

        // --- Convert HEX to RGB components ---
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);

        // Requirement: RGBA color output
        const rgba = 'rgba(' + r + ', ' + g + ', ' + b + ', 1)';
        $('#rgba-output').text(rgba);

        // Requirement: HEX color output
        $('#hex-output').text(hex.toUpperCase());

        // --- Convert RGB to HSL ---
        const rn = r / 255, gn = g / 255, bn = b / 255;
        const max = Math.max(rn, gn, bn), min = Math.min(rn, gn, bn);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case rn: h = ((gn - bn) / d + (gn < bn ? 6 : 0)) / 6; break;
                case gn: h = ((bn - rn) / d + 2) / 6; break;
                case bn: h = ((rn - gn) / d + 4) / 6; break;
            }
        }

        // Requirement: HSL color output
        const hsl = 'hsl(' +
            Math.round(h * 360) + ', ' +
            Math.round(s * 100) + '%, ' +
            Math.round(l * 100) + '%)';
        $('#hsl-output').text(hsl);
    });


    // ============================================================
    // Requirement: jQuery — File upload drag-and-drop visual cue
    // ============================================================
    const $dropZone = $('.file-drop-zone');

    $dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $dropZone.on('dragleave drop', function () {
        $(this).removeClass('dragover');
    });

    // Show chosen filename in drop zone label
    $('input[type="file"]').on('change', function () {
        const fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $dropZone.find('.file-name-display').text(fileName);
        }
    });


    // ============================================================
    // Requirement: jQuery — Wallpaper page live search filter
    // Filters visible cards by title text as the user types,
    // without a page reload.
    // ============================================================
    $('#search-input').on('keyup', function () {
        const query = $(this).val().toLowerCase().trim();

        $('.wallpaper-card').each(function () {
            const title = $(this).find('.card-title').text().toLowerCase();
            if (title.includes(query) || query === '') {
                $(this).parent().show(); // show the grid cell
            } else {
                $(this).parent().hide();
            }
        });

        // Show empty notice if no cards are visible
        const visible = $('.wallpaper-card:visible').length;
        if (visible === 0) {
            $('#no-results').show();
        } else {
            $('#no-results').hide();
        }
    });


    // ============================================================
    // Requirement: jQuery — Confirm before delete actions
    // ============================================================
    $(document).on('click', '.confirm-delete', function (e) {
        if (!confirm('Are you sure you want to delete this? This cannot be undone.')) {
            e.preventDefault();
        }
    });

});
