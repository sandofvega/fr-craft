<?php

namespace Fr\CraftPluginList\Commands;

use DateTime;
use Throwable;
use GuzzleHttp\Client as GuzzleClient;
use Fr\CraftPluginList\Models\CraftPluginPackage;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CraftPluginListCommand extends Command
{
    protected static $defaultName = 'craft-plugin-list';

    private $defaultLimit = 2; // TODO
    private $defaultOrder = 'desc';
    private $defaultOrderBy = 'downloads';
    private $allowedOrderBy = ['downloads', 'favers', 'dependents', 'testLibrary', 'updated'];

    // Source: https://knapsackpro.com/testing_frameworks/alternatives_to/phpunit
    private $testFrameworks = ['phpunit/phpunit', 'atoum/atoum', 'behat/behat', 'codeception/codeception', 'kahlan/kahlan',
        'laravel/dusk', 'lens/lens', 'phpspec/phpspec', 'peridot-php/peridot', 'simpletest/simpletest', 'datasift/storyplayer'];

    public function __construct()
    {
        $this->guzzle = new GuzzleClient([
            'base_uri' => 'https://packagist.org/packages/'
        ]);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Get a list of all craft-plugin type packages form packagist.org')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum output number', $this->defaultLimit),
                    new InputOption('orderBy', null, InputOption::VALUE_OPTIONAL, 'Sorting by', $this->defaultOrderBy),
                    new InputOption('order', null, InputOption::VALUE_OPTIONAL, 'Sorting order', $this->defaultOrder),
                    new InputOption('output', null, InputOption::VALUE_OPTIONAL, 'Output file. Must be a json file.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $packages = [];

        /*** Options Validations ***/
        $limit = $input->getOption('limit');
        if (!is_numeric($limit) || $limit < 1) {
            $output->writeln('<error>Invalid limit option</error>');
            return Command::FAILURE;
        }

        $orderBy = $input->getOption('orderBy');
        if (!in_array($orderBy, $this->allowedOrderBy)) {
            $output->writeln('<error>Invalid orderBy option</error>');
            return Command::FAILURE;
        }

        $order = strtolower($input->getOption('order'));
        if (!in_array($order, ['asc', 'desc'])) {
            $output->writeln('<error>Invalid order option</error>');
            return Command::FAILURE;
        }

        $outputPath = $input->getOption('output');
        if ($outputPath && substr($outputPath, -5) != '.json') {
            $output->writeln('<error>Invalid output option. Valid output ends with .json</error>');
            return Command::FAILURE;
        }
        /*** Options Validations END ***/


        /*** Making Dataset ***/
        $progressBar = new ProgressBar($output, $limit);
        $progressBar->start();

        // get all package names
        try {
            $response = $this->guzzle->request('GET', 'list.json', [
                'query' => [
                    'type' => 'craft-plugin'
                ]
            ]);

            $packageNames = json_decode($response->getBody())->packageNames;
        }
        catch (Throwable $error) {
            $output->writeln('<error>Something went wrong</error>');
            return Command::FAILURE;
        }

        foreach ($packageNames as $name) {
            // check if come to limit then stop
            if(count($packages) >= $limit) break;

            // get a package detail
            try {
                $response = $this->guzzle->request('GET', $name.'.json');
                $packageDetail = json_decode($response->getBody())->package;
            }
            catch (Throwable $error) {
                $output->writeln('<error>Something went wrong</error>');
                return Command::FAILURE;
            }

            // get the latest version
            $latestVersion = null;
            foreach ($packageDetail->versions as $version) {
                if ($latestVersion == null || strtotime($version->time) > strtotime($latestVersion->time)) {
                    $latestVersion = $version;
                }
            }

            // check package is not abandoned
            if (isset($packageDetail->abandoned) && $packageDetail->abandoned) continue;

            // check extra.handle is not missing in latest version
            if (!isset($latestVersion->extra) || !isset($latestVersion->extra->handle)) continue;

            // get testLibrary
            $testLibrary = null;
            if (isset($latestVersion->{'require-dev'})) {
                foreach ($latestVersion->{'require-dev'} as $property => $value) {
                    if (in_array($property, $this->testFrameworks)) {
                        $testLibrary = $property;
                        break;
                    }
                }
            }

            // push to main array
            array_push(
                $packages,
                new CraftPluginPackage(
                    $packageDetail->name,
                    $packageDetail->description,
                    $latestVersion->extra->handle,
                    $packageDetail->repository,
                    $testLibrary,
                    $latestVersion->version,
                    $packageDetail->downloads->monthly,
                    $packageDetail->dependents,
                    $packageDetail->favers,
                    (new DateTime)->setTimestamp(strtotime($latestVersion->time)),
                )
            );

            $progressBar->advance(); // progress bar increment
        }

        $progressBar->finish();
        /*** Making Dataset END ***/


        /*** Sorting ***/
        usort($packages, function($a, $b) use ($orderBy, $order) {
            if ($orderBy == 'updated') {
                $result = strcmp($a->updated->getTimestamp(), $b->updated->getTimestamp());
            }
            else {
                $result = strcmp($a->{$orderBy}, $b->{$orderBy});
            }
            
            return $order == 'asc' ? $result : -$result;
        });
        /*** Sorting END ***/


        /*** Output ***/
        echo "\n";
        
        if ($outputPath) {
            // output on file
            file_put_contents($outputPath, json_encode($packages, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            
            $output->writeln('<info>Data saved to '. $outputPath .'</info>');
        }
        else {
            // table output on terminal
            (new Table($output))
                ->setHeaders([
                    'Name', 'Description', 'Handle', 'Repository', 'Test Library',
                    'Version', 'Monthly Downloads', 'Dependents', 'Favers', 'Updated'
                ])
                ->setRows(json_decode(json_encode($packages), true))
                ->render();
        }
        /*** Output END ***/

        return Command::SUCCESS;
    }
}