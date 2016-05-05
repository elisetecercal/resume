<?php

namespace Cekurte\Resume\Command;

use Cekurte\Resume\Exception\FileNotExistsException;
use Cekurte\Resume\Factory\PdfFactory;
use Cekurte\Resume\Factory\TwigFactory;
use Cekurte\Resume\File\DebugFile;
use Cekurte\Resume\File\I18nFile;
use Cekurte\Resume\File\InputFile;
use Cekurte\Resume\File\OutputFile;
use Cekurte\Resume\File\TemplateFile;
use Cekurte\Resume\File\Yaml\Parser;
use Knp\Snappy\Exception\FileAlreadyExistsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;

class CreateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cekurte:resume:create')
            ->setDescription('Create a new Resume given a yaml source file.')
            ->addOption(
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Overwrite an existent file (if it exists)'
            )
            ->addOption(
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'Template that will be used to create the resume',
                'default'
            )
            ->addOption(
                'language',
                null,
                InputOption::VALUE_REQUIRED,
                'The language that will be used to create the resume',
                'en'
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'Used to debug this command'
            )
            ->setHelp(file_get_contents(
                APP_RESOURCES_HELP_PATH . DS . 'CreateCommand.txt'
            ))
        ;
    }

    /*
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $twig = TwigFactory::create();
            $pdf  = PdfFactory::create();

            $outputFilename = (new OutputFile($input))->getFilename();

            if ($input->getOption('overwrite') && file_exists($outputFilename)) {
                unlink($outputFilename);
            }

            if ($input->getOption('overwrite') && $input->getOption('debug') && file_exists($outputHtmlFile)) {
                unlink($outputHtmlFile);
            }

            if (!file_exists(OUTPUT_PATH)) {
                mkdir(OUTPUT_PATH, 0777, true);
            }

            $resume = (new Parser(new InputFile($input)))->getContentParsed();

            $resume['i18n'] = (new Parser(new I18nFile($input)))->getContentParsed();

            $html = $twig->render((new TemplateFile($input))->getFilename(), $resume);

            if ($input->getOption('debug')) {
                $debugFilename = (new DebugFile($input))->getFilename();

                file_put_contents($debugFilename, $html);

                $output->writeln('<info>Your debug html file was generated with successfully.</info>');

                $output->writeln(sprintf(
                    'Debug file: <comment>%s</comment>',
                    $debugFilename
                ));
            }

            $pdf->generateFromHtml(
                ,
                $outputFilename
            );

            $output->writeln('<info>Your resume was generated with successfully.</info>');

            $output->writeln(sprintf(
                'Generated file: <comment>%s</comment>',
                $outputFilename
            ));
        } catch (FileAlreadyExistsException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));

            $message = 'Note that you can use the '
                . '<comment>--overwrite</comment> '
                . 'option to overwrite an existent resume file.'
            ;

            $output->writeln($message);
        } catch (FileNotExistsException $e) {
            $output->writeln(sprintf(
                '<error>%s</error>',
                $e->getMessage()
            ));
        } catch (ParseException $e) {
            $output->writeln(sprintf(
                '<error>Unable to parse the YAML string: %s</error>',
                $e->getMessage()
            ));
        }
    }
}
