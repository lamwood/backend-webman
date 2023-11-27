<?php
namespace Backend\webman\command;

use Backend\webman\ServiceProvider;
use support\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use GuzzleHttp\Client;

class BackendInstall extends Command{
    //
    protected static $defaultName = 'backend:install';
    protected static $defaultDescription = 'Install the backend package';
    /**
     * @return void
     */
    protected function configure(): void{
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite file');
        $this->addOption('versions', null,InputOption::VALUE_REQUIRED, 'version number');
        $this->addOption('username', null,InputOption::VALUE_REQUIRED, 'username');
        $this->addOption('password', null,InputOption::VALUE_REQUIRED, 'password');
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        ServiceProvider::init();
        $filesystem = new Filesystem();
        $filesystem->mirror(base_path().'/vendor/rockys/ex-admin-ui/resources', public_path('exadmin'), null, ['override' => $input->getOption('force')]);
        $path = $this->download('backend', $input->getOption('versions'));
        if($path === false){
            $output->writeln('下载插件失败');
            return 0;
        }
        $result = plugin()->install($path, $input->getOption('force'));
        if($result !== true){
            $output->writeln($result);
            return 0;
        }
        //
        $username = $input->getOption('username');
        $password = $input->getOption('password');
        if($username && $password){
            $table = plugin()->backend->config('database.user_table');
            Db::table($table)->where('id', 1)
                ->update([
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
        }
        $input = new ArrayInput(['webman']);
        $output = new ConsoleOutput();
        $this->getApplication()->find('plugin:composer')->run($input, $output);
        $output->writeln('install success');
        return self::SUCCESS;
    }
    /**
     * download package
     * @param type $name
     * @param type $version
     * @return bool|string
     */
    protected function download($name, $version = null){
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name.'-'.$version.'.zip';
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('very_verbose');
        $client = new Client([
            'base_uri' => 'https://nixi.win/tmp/',
            'verify' => false,
        ]);
        $response = $client->get('backend.zip', [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'query' => [
                'name' => $name,
                'version' => $version,
            ],
            'sink' => $path,
            'progress' => function($totalDownload, $downloaded)use($progressBar, $output){
                if($totalDownload > 0 && $downloaded > 0 && !$progressBar->getMaxSteps()){
                    $progressBar->start($totalDownload);
                }
                $progressBar->setProgress($downloaded);
                if($progressBar && $downloaded > 0 && $totalDownload === $downloaded){
                    $progressBar->finish();
                    $progressBar = null;
                    $output->write(PHP_EOL);
                }
            }
        ]);
        $zip = new \ZipArchive();
        if($zip->open($path) !== true){
            $output->writeln('dowload file can not open');
            return false;
        }
        $zip->close();
        return $path;
    }
}