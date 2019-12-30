<?php

// Set default `X-Powered-By` value
Lot::set('X-Powered-By', 'Mecha/' . VERSION);

// Set default response status
Lot::status(403); // “Forbidden”

// Set default `Content-Type` value to `text/html`
Lot::type('text/html');