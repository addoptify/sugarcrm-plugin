<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use DRI\SugarCRM\Plugin\Path;
use DRI\SugarCRM\Plugin\StringUtils;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class SyncCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('sync');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');
        $this->addOption('back', 'B', InputOption::VALUE_NONE, 'changes direction: plugin <- sugarcrm');
        $this->setDescription('Synchronizes changes between the plugin source and a sugarcrm project (default direction: plugin -> sugarcrm)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $root = Path::getRootPath();

        $config = $this->getConfig();

        if ($input->getOption('back')) {
            foreach ($config->get('dev') as $source => $remote) {
                $to = "$root/$source";
                $from = "$target/$remote";

                $to = dirname($to);

                Cli::exec("rsync -r $from $to");
            }
        } else {
            foreach ($config->get('dev') as $source => $remote) {
                $from = "$root/$source";
                $to = "$target/$remote";

                if (is_link("$to")) {
                    Cli::exec("rm $to");
                }

                $to = dirname($to);

                Cli::exec("rsync -r $from $to");
            }
        }
    }
}
