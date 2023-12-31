<?php
namespace Backend\webman\command;

use Backend\webman\ServiceProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PluginComposer extends Command{
    //
    protected static $defaultName = 'plugin:composer';
    protected static $defaultDescription = 'Install the plugin package';
    //
    public function configure() {
        $this->addArgument('name', InputArgument::OPTIONAL, 'plugin name');
    }
    //
    protected function execute(InputInterface $input, OutputInterface $output){
        ServiceProvider::init();
        $name = $input->getArgument('name');
        $plugs = plugin()->getPlug($name);
        //
        if(!is_array($plugs)){
            $plugs = [$plugs];
        }
        $package = [];
        foreach($plugs as $plug){
            $requires = $plug['require'] ?? [];
            foreach($requires as $require => $version){
                $package[] = $require;
                $package[] = $version;
            }
        }
        if(count($package) == 0){
            $output->writeln('Nothing to install, update or remove');
            return 0;
        }
        $path = base_path();
        $cmd = array_merge(['composer', 'require'], $package);
        $process = new Process($cmd, $path);
        $process->run(function($type, $buffer)use($output){
            $output->writeln($buffer);
        });
        return self::SUCCESS;
    }
}