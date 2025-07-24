<?php
/**
 * Blackbird Clean Blocked Running Cron
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_CleanBlockedRunningCron
 * @copyright       Copyright (c) Blackbird (https://black.bird.eu)
 * @author          Thibaud Ritzenthaler (hello@bird.eu)
 * @license         MIT
 * @support         https://github.com/blackbird-agency/magento-2-clean-blocked-cron/issues/new
 */

declare(strict_types=1);

namespace Blackbird\CleanBlockedRunningCron\Api;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface CleanBlockedRunningCronInterface
 * @package Blackbird\CleanBlockedRunningCron\Api
 */
interface CleanBlockedRunningCronInterface
{
    /**
     * Clean the jammed CRON depending on job code and running time
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string|int $hours
     * @param string|int $minutes
     * @param array $cronJobCodes
     */
    public function execute(OutputInterface $output, $hours, $minutes, array $cronJobCodes);
}
