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

namespace Blackbird\CleanBlockedRunningCron\Helper;


use DateInterval;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


class CleanBlockedRunningCron extends AbstractHelper
{

    /**
     * @var \Magento\Cron\Model\ScheduleFactory
     */
    protected $scheduleFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;


    /**
     * CleanBlockedRunningCron constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct
    (
        Context $context,
        ScheduleFactory $scheduleFactory,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->scheduleFactory = $scheduleFactory;
        $this->timezone = $timezone;
    }


    /**
     * @param $maxTimeHours
     * @param $maxTimeMinutes
     * @return string
     */
    public function getLastValidCreationDate($maxTimeHours, $maxTimeMinutes): string
    {
        return $this->timezone
            ->date()
            ->sub(new DateInterval("PT{$maxTimeHours}H{$maxTimeMinutes}M"))
            ->format('Y-m-d H:i:s');
    }

    /**
     * Stop a cron which is running for a too long time
     * @param $output
     * @param $maxTimeHours
     * @param $maxTimeMinutes
     * @param $cronJobCode
     */
    public function cleanSpecifiedBlockedCron($output, $maxTimeHours, $maxTimeMinutes, $cronJobCode): void
    {
        // Get the cron data and add filters
        $specifiedCron = $this->scheduleFactory->create()->getCollection();
        $specifiedCron->addFieldToFilter('job_code', $cronJobCode);
        $specifiedCron->addFieldToFilter('status', Schedule::STATUS_RUNNING);

        // Checking if items fill filters conditions and get them
        if ($specifiedCron->getItems()) {
            // if more than one cron is named the same way
            foreach ($specifiedCron as $spe) {
                // Maximum creation Date and Time of the cron before being considered as jammed
                $lastDateTimeOfCreation = $this->getLastValidCreationDate($maxTimeHours, $maxTimeMinutes);
                if ($spe->getExecutedAt() < $lastDateTimeOfCreation) {
                    $spe->setStatus(Schedule::STATUS_ERROR);
                    $spe->setMessages('Error: This CRON was jammed.');
                    $output->writeln("Cron {$spe->getJobCode()} is jammed. Stopping it.");
                } else {
                    // The specified cron is not jammed
                    $output->writeln("Cron {$spe->getJobCode()} is not jammed with your parameters.");
                }
            }
            $specifiedCron->save();
        } else if ($this->scheduleFactory->create()->getCollection() // fresh new not filtered db call
            ->addFieldToFilter('job_code', $cronJobCode)->getItems()) {
            // We already test the other filter higher, so it can only be status problem
            $output->writeln("Cron {$cronJobCode} is not running.");
        } else {
            // Not the good job code
            $output->writeln("Cron {$cronJobCode} was not found. A misspelling ?");
        }
    }

    /**
     * Stop all the cron which are running for a too long time
     * @param $output
     * @param $maxTimeHours
     * @param $maxTimeMinutes
     */
    public function cleanAllBlockedCron($output, $maxTimeHours, $maxTimeMinutes): void
    {
        // Getting the list of CRON which are running
        $runningJobs = $this->scheduleFactory->create()->getCollection();
        $runningJobs->addFieldToFilter('status', Schedule::STATUS_RUNNING);

        // Maximum creation Date and Time of the cron before being considered as jammed
        $lastDateTimeOfCreation = $this->getLastValidCreationDate($maxTimeHours, $maxTimeMinutes);
        $cronStopped = 0;

        foreach ($runningJobs as $runningJob) {
            // if the time is up
            if ($runningJob->getExecutedAt() < $lastDateTimeOfCreation) {
                $runningJob->setStatus(Schedule::STATUS_ERROR);
                $runningJob->setMessages('Error: This CRON was jammed.');
                $output->writeln("Cron {$runningJob->getJobCode()} is jammed. Stopping it.");
                $cronStopped++;
            }
        }

        $runningJobs->save();
        $output->writeln("{$cronStopped} CRON were jammed.");
    }

    public function execute($output, $hours = 0, $minutes = 0, $cronJobCode = null): void
    {
        if ($cronJobCode) {
            $this->cleanSpecifiedBlockedCron($output, $hours, $minutes, $cronJobCode);
        } else {
            $this->cleanAllBlockedCron($output, $hours, $minutes);
        }
    }
}
