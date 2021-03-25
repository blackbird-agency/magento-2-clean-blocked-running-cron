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

namespace Blackbird\CleanBlockedRunningCron\Console\Command;


use Blackbird\CleanBlockedRunningCron\Api\CleanBlockedRunningCronInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;

class CleanBlockedRunningCronCommand extends Command
{
    protected const HOURS = "hours";
    protected const HOURS_SHORTCUT = "H";
    protected const MINUTES = "minutes";
    protected const MINUTES_SHORTCUT = "M";
    protected const CRON = "cron";
    protected const CRON_SHORTCUT = "c";

    /**
     * @var \Blackbird\CleanBlockedRunningCron\Model\CleanBlockedRunningCron
     */
    protected $cleanBlockedRunningCron;

    /**
     * CleanBlockedRunningCronCommand constructor.
     * @param \Blackbird\CleanBlockedRunningCron\Api\CleanBlockedRunningCronInterface $cleanBlockedRunningCron
     * @param string|null $name
     */
    public function __construct
    (
        CleanBlockedRunningCronInterface $cleanBlockedRunningCron,
        string $name = null
    ) {
        parent::__construct($name);
        $this->cleanBlockedRunningCron = $cleanBlockedRunningCron;
    }

    /**
     * @inherit
     */
    protected function configure(): void
    {
        $this->setName("cron:blocked:clean");

        $this->setDescription('Kill the cron which are jammed in running');

        $this->addOption(
            self::HOURS,
            self::HOURS_SHORTCUT,
            InputOption::VALUE_REQUIRED,
            'Hours'
        );

        $this->addOption(
            self::MINUTES,
            self::MINUTES_SHORTCUT,
            InputOption::VALUE_REQUIRED,
            'Minutes'
        );

        $this->addOption(
            self::CRON,
            self::CRON_SHORTCUT,
            InputOption::VALUE_OPTIONAL,
            'Name of the affected cron'
        );

        $this->setHelp(
            "To remove the cron which are blocked, you need to specify their maximum lifetime running.\n"
            . "Put the hours with -H [HOURS] and/or the minutes with -M [MINUTES].\n"
            . "You can specify a cron with -c [JOBCODE].");

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $hours = $input->getOption(self::HOURS) ?? "0";     // Default value is 0
        $minutes = $input->getOption(self::MINUTES) ?? "0"; // Default value is 0
        // If $cronJobCode is a list, return the array, if this is not return it (in an array)
        try {
            $cronJobCode = explode(",", $input->getOption(self::CRON));
        } catch (TypeError $typeError) { // If $cronJobCode is null
            $cronJobCode = [];
        }
        if ($hours || $minutes) {
            $output->writeln("Launching the process ...");
            $this->cleanBlockedRunningCron->execute($output, $hours, $minutes, $cronJobCode);
        } else {
            $output->writeln("Bad args ! Type 'php bin/magento cron:blocked:clean -h' to display the help !");
        }
    }
}
