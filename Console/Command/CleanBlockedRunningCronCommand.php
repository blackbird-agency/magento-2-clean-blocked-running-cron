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


use Blackbird\CleanBlockedRunningCron\Helper\CleanBlockedRunningCron;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanBlockedRunningCronCommand extends Command
{
    protected const HOURS = "h";
    protected const MINUTES = "m";
    protected const CRON = "cron";

    /**
     * @var \Blackbird\CleanBlockedRunningCron\Helper\CleanBlockedRunningCron
     */
    protected $cleanBlockedRunningCron;

    /**
     * KillJammedCronCommand constructor.
     * @param \Blackbird\CleanBlockedRunningCron\Helper\CleanBlockedRunningCron $cleanBlockedRunningCron
     */
    public function __construct
    (
        CleanBlockedRunningCron $cleanBlockedRunningCron
    )
    {
        parent::__construct("cron:blocked:clean");
        $this->setDescription('Kill the cron which are jammed in running');
        $this->cleanBlockedRunningCron = $cleanBlockedRunningCron;
    }

    /**
     * @inherit
     */
    protected function configure(): void
    {
        $this->addOption(
            self::HOURS,
            null,
            InputOption::VALUE_REQUIRED,
            'Hours'
        );

        $this->addOption(
            self::MINUTES,
            null,
            InputOption::VALUE_REQUIRED,
            'Minutes'
        );

        $this->addOption(
            self::CRON,
            null,
            InputOption::VALUE_OPTIONAL,
            'Name of the affected cron'
        );

        $this->setHelp(
                "To remove the cron which are blocked, you need to specify their maximum lifetime running.\n"
                ."Put the hours with --h [HOURS] and/or the minutes with --m [MINUTES].\n"
                ."You can specify a cron with --cron [JOBCODE].");
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $hours = $input->getOption(self::HOURS);
        $minutes = $input->getOption(self::MINUTES);
        $cronJobCode = $input->getOption(self::CRON);
        if ($hours || $minutes) {
            $output->writeln("Launching the process ...");
            $this->cleanBlockedRunningCron->execute($output, $hours = 0, $minutes = 0, $cronJobCode);
        }
        else {
            $output->writeln("Bad args ! Type 'php bin/magento cron:blocked:clean -h' to display the help !");
        }
    }
}
