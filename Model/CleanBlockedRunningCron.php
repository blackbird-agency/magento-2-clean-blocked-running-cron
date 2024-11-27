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

namespace Blackbird\CleanBlockedRunningCron\Model;

use Blackbird\CleanBlockedRunningCron\Api\CleanBlockedRunningCronInterface;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanBlockedRunningCron
 * @package Blackbird\CleanBlockedRunningCron\Model
 */
class CleanBlockedRunningCron implements CleanBlockedRunningCronInterface
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
    protected $scheduleCollectionFactory;

    /**
     * @var \Magento\Cron\Model\ResourceModel\Schedule
     */
    protected $scheduleResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * CleanBlockedRunningCron constructor.
     * @param \Magento\Cron\Model\ScheduleFactory $scheduleFactory
     * @param \Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory $scheduleCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        ScheduleFactory $scheduleFactory,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        TimezoneInterface $timezone,
        \Magento\Cron\Model\ResourceModel\Schedule $scheduleResource,
        DateTime $dateTime
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->timezone = $timezone;
        $this->scheduleResource = $scheduleResource;
        $this->dateTime = $dateTime;
    }


    /**
     * @param $maxTimeHours
     * @param $maxTimeMinutes
     * @return string
     */
    protected function getLastValidCreationDate($maxTimeHours = 0, $maxTimeMinutes = 0): string
    {
        return $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            \strtotime('- ' . $maxTimeHours . ' hours - ' . $maxTimeMinutes . ' minutes')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function execute(OutputInterface $output, $hours, $minutes, array $cronJobCodes = [])
    {
        $cronStopped = 0;
        $lastDateTimeOfCreation = $this->getLastValidCreationDate($hours, $minutes);

        /** @var \Magento\Cron\Model\ResourceModel\Schedule\Collection $runningJobs */
        $runningJobs = $this->scheduleCollectionFactory->create()
            ->addFieldToFilter('status', Schedule::STATUS_RUNNING)
            ->addFieldToFilter('executed_at', ['lt' => $lastDateTimeOfCreation]);

        // If there is specified cron
        if (!empty($cronJobCodes)) {
            $runningJobs->addFieldToFilter('job_code', ['in' => $cronJobCodes]);
        }

        // For each jammed cron job change the status to 'error' to unlock the next execution
        /** @var Schedule $job */
        foreach ($runningJobs as $job) {
            try {
                $job->setStatus(Schedule::STATUS_ERROR);
                $job->setMessages('Error: This CRON was jammed. Fixed at ' . (new \DateTime('@' . $this->dateTime->gmtTimestamp()))->format('Y-m-d H:i:s'));
                // Repository for Schedule doesn't exists, need to use resource model
                $this->scheduleResource->save($job);

                $output->writeln("Cron {$job->getJobCode()} is jammed. Stopping it.");
                $cronStopped++;
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln("{$cronStopped} CRON were jammed.");
    }
}
