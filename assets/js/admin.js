/**
 * aGo SMTP, admin UI helpers
 *
 * Solo dos responsabilidades:
 *  1. Auto-rellenar host/port/encryption al elegir preset.
 *  2. Mostrar el wizard "Como obtener credenciales" del preset seleccionado.
 */
( function () {
    'use strict';

    var data = window.agoSmtpData || { presets: {}, guides: {} };

    var $preset     = document.getElementById( 'ago-preset' );
    var $host       = document.getElementById( 'ago-host' );
    var $port       = document.getElementById( 'ago-port' );
    var $encryption = document.getElementById( 'ago-encryption' );
    var $guideRow   = document.getElementById( 'ago-guide-row' );
    var $title      = document.getElementById( 'ago-guide-title' );
    var $note       = document.getElementById( 'ago-guide-note' );
    var $steps      = document.getElementById( 'ago-guide-steps' );
    var $showLink   = document.getElementById( 'ago-show-guide' );
    var $hideLink   = document.getElementById( 'ago-hide-guide' );

    if ( ! $preset ) {
        return;
    }

    function applyPreset( key ) {
        var p = data.presets[ key ];
        if ( ! p ) { return; }
        if ( $host )       { $host.value       = p.host; }
        if ( $port )       { $port.value       = p.port; }
        if ( $encryption ) { $encryption.value = p.encryption; }
    }

    function renderGuide( key ) {
        var g = data.guides[ key ];
        if ( ! g ) {
            if ( $guideRow ) { $guideRow.style.display = 'none'; }
            if ( $showLink ) { $showLink.style.display = 'none'; }
            return;
        }
        $title.textContent = g.title;
        $note.textContent  = g.note || '';
        $steps.innerHTML   = '';
        ( g.steps || [] ).forEach( function ( s ) {
            var li = document.createElement( 'li' );
            li.style.marginBottom = '8px';
            if ( s.url ) {
                var a = document.createElement( 'a' );
                a.href        = s.url;
                a.target      = '_blank';
                a.rel         = 'noopener';
                a.textContent = s.text;
                li.appendChild( a );
            } else {
                li.textContent = s.text;
            }
            $steps.appendChild( li );
        } );
        if ( $showLink ) { $showLink.style.display = ''; }
    }

    function showGuide( e ) {
        if ( e ) { e.preventDefault(); }
        if ( $guideRow ) { $guideRow.style.display = ''; }
        if ( $showLink ) { $showLink.style.display = 'none'; }
    }

    function hideGuide( e ) {
        if ( e ) { e.preventDefault(); }
        if ( $guideRow ) { $guideRow.style.display = 'none'; }
        if ( $showLink ) { $showLink.style.display = ''; }
    }

    $preset.addEventListener( 'change', function () {
        var key = $preset.value;
        applyPreset( key );
        renderGuide( key );
        if ( data.guides[ key ] ) {
            showGuide();
        } else {
            hideGuide();
        }
    } );

    if ( $showLink ) { $showLink.addEventListener( 'click', showGuide ); }
    if ( $hideLink ) { $hideLink.addEventListener( 'click', hideGuide ); }

    renderGuide( $preset.value );
    if ( data.guides[ $preset.value ] && $preset.value !== 'custom' ) {
        showGuide();
    }
} )();
