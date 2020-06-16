<?php

// Set default `x-powered-by` value
Lot::set('x-powered-by', 'Mecha/' . VERSION);

// Set default response status
Lot::status(403); // “Forbidden”

// Set default `content-type` value to `text/html`
Lot::type('text/html');
