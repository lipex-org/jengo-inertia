<?php

use CodeIgniter\Events\Events;
use CodeIgniter\Filters\FilterInterface;
use Config\Filters;
use Jengo\Base\Validation\FormFailedResponseHolder;
use Jengo\Inertia\Inertia;

Events::on('jengo.form.failed', function (FormFailedResponseHolder $holder) {
    // set errors as flashdata
    Inertia::flash('errors', $holder->getErrors());

    // get the correct response from the middleware
    /**
     * @var \Jengo\Inertia\Config\Inertia
     */
    $config = config('Inertia');
    /**
     * @var Filters
     */
    $filtersConfig = config('Filters');

    $filterClass = $filtersConfig->aliases[$config->filterAlias] ?? null;

    if (!$filterClass) {
        // inertia might not be in use in the current project
        return;
    }

    if (!class_exists($filterClass)) {
        // we might throw an exception here
        return;
    }

    $instance = new $filterClass();

    if (!($instance instanceof FilterInterface)) {
        // We might also throw an exception here instead
        return;
    }

    $response = $instance->after(request(), redirect()->back()->withInput());

    $holder->setResponse($response);
});