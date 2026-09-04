<?php

use CodeIgniter\Events\Events;
use Jengo\Base\Validation\FormFailedResponseHolder;
use Jengo\Inertia\Inertia;

Events::on('jengo.form.failed', function (FormFailedResponseHolder $holder) {
    // Passively flash errors for Inertia session
    Inertia::flash('errors', $holder->getErrors());
});