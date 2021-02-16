<?php
/**
 * Blackbird Agency
 *
 * @category    Blackbird
 * Date: 08/02/2021
 * Time: 14:10
 * @copyright   Copyright (c) 2021 Blackbird Agency. (http://black.bird.eu)
 * @author Thibaud Ritzenthaler (hello@bird.eu)
 */

declare(strict_types=1);


\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Blackbird_CleanBlockedRunningCron',
    __DIR__
);
