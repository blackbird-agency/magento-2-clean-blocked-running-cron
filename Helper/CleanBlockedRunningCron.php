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
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Symfony\Component\Console\Output\OutputInterface;


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
     * @var \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory
     */
    private $scheduleCollectionFactory;


    /**
     * CleanBlockedRunningCron constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct
    (
        Context $context,
        ScheduleFactory $scheduleFactory,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->scheduleFactory = $scheduleFactory;
        $this->timezone = $timezone;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
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
     * Clean the jammed CRON
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $hours
     * @param string $minutes
     * @param array $cronJobCodes
     */
    public function execute(OutputInterface $output, string $hours, string $minutes, array $cronJobCodes): void
    {
        // Number of stopped CRON
        $cronStopped = 0;
        // Index of the Cron Job Code in the Array
        $cronJobCodeIndex = 0;

        // if $cronJobCodes is not defined ([]), 1 loop where all running CRON are processed
        // else it will loop for every cron Job Code with name as filter
        do {

            // Getting the list of CRON which are running
            $runningJobs = $this->scheduleCollectionFactory->create();
            $runningJobs->addFieldToFilter('status', Schedule::STATUS_RUNNING);

            // If there is specified cron
            if (isset($cronJobCodes[$cronJobCodeIndex])) {
                $runningJobs->addFieldToFilter('job_code', $cronJobCodes[$cronJobCodeIndex]);
            }

            // Checking if items fill filters conditions and get them
            if ($runningJobs->getItems()) {
                // Maximum creation Date and Time of the cron before being considered as jammed
                $lastDateTimeOfCreation = $this->getLastValidCreationDate($hours, $minutes);

                foreach ($runningJobs as $runningJob) {
                    // if the time is up
                    if ($runningJob->getExecutedAt() < $lastDateTimeOfCreation) {
                        $runningJob->setStatus(Schedule::STATUS_ERROR);
                        $runningJob->setMessages('Error: This CRON was jammed.');
                        $output->writeln("Cron {$runningJob->getJobCode()} is jammed. Stopping it.");
                        $cronStopped++;
                    } else if (isset($cronJobCodes[$cronJobCodeIndex])) {
                        $output->writeln("Cron {$runningJob->getJobCode()} is running but not jammed with your parameters.");
                    }
                }

                // Save in database
                $runningJobs->save();

            } else {
                if (isset($cronJobCodes[$cronJobCodeIndex])) {
                    if ($this->scheduleCollectionFactory->create() // fresh new not filtered db call
                    ->addFieldToFilter('job_code', $cronJobCodes[$cronJobCodeIndex])->getItems()) {
                        // We already test the other filter higher, so it can only be status problem
                        $output->writeln("Cron {$cronJobCodes[$cronJobCodeIndex]} is not running.");
                    } else {
                        // Not the good job code
                        $output->writeln("Cron {$cronJobCodes[$cronJobCodeIndex]} was not found. A misspelling ?");
                    }
                }
            }

            $cronJobCodeIndex++;

        } while ($cronJobCodeIndex <= count($cronJobCodes));

        $output->writeln("{$cronStopped} CRON were jammed.");
    }
}
