<?php
/**
 * Blackbird Clean Blocked Running Cron
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_CleanBlockedRunningCron
 * @copyright       Copyright (c) Blackbird (https://black.bird.eu)
 * @author          Thibaud Ritzenthaler (hello@bird.eu)
 * @license         MIT
 * @support         https://github.com/blackbird-agency/magento-2-clean-blocked-cron/issues/new
 */

declare(strict_types=1);


\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Blackbird_CleanBlockedRunningCron',
    __DIR__
);
