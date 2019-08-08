<?php

namespace Zacksleo\NeweggSdk\Command;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MapCommand extends Command
{
    const GATEWAY = 'https://developer.newegg.com/newegg_marketplace_api/';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('map:generate')
            ->setDescription('生成map文件')
            ->addOption('fake', '生成FakeCommand专用的map')
            ->addOption('https', '使用 htttps');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $res = $this->parseNode('https://developer.newegg.com/documents/newegg_marketplace_api/item_management/get_manufacturer_request_status/');
        var_dump($res);
        exit;
        */

        $output->writeln('正在解析 API 文档首页...');
        $html = file_get_contents($this->resolveUrl($input, self::GATEWAY));
        $crawler = new Crawler($html);
        $catalogs = $crawler->filterXpath('//*[@id="ajax-content-wrap"]/div[1]/div/div/div[1]/ol[2]/li/a')->each(function (Crawler $node, $i) {
            return [
                'url'  => $node->attr('href'),
                'text' => $node->text(),
            ];
        });
        $bar = new ProgressBar($output, count($catalogs));
        $bar->setMessage('正在解析分类页面');
        $bar->start();
        $bar->setFormat('%message%'.PHP_EOL.'%bar% %percent:3s% %'.PHP_EOL.'time:  %elapsed:6s%/%estimated:-6s%'.PHP_EOL.PHP_EOL);
        $bar->setBarCharacter('<info>'.$bar->getBarCharacter().'</info>');
        $raw = <<<PHP
<?php

return [\n
PHP;
        $menus = [];
        $total = 0;
        foreach ($catalogs as $catalog) {
            $html = file_get_contents($this->resolveUrl($input, $catalog['url']));
            $crawler = new Crawler($html);
            $raw .= <<<BLOCK
    /*
    |--------------------------------------------------------------------------
    | {$catalog['text']}
    |--------------------------------------------------------------------------
    |
    | @see {$catalog['url']}
    |
    */\n
BLOCK;
            $subLinks = $crawler->filterXpath('//*[@id="ajax-content-wrap"]/div[1]/div/div/div[1]/table/tbody/tr/td[1]/a')->each(function (Crawler $node, $i) {
                return [
                    'url'        => $node->attr('href'),
                    'text'      => $node->text(),
                ];
            });

            $menus[] = [
                'links'=> $subLinks,
            ];
            $total += count($subLinks);

            $bar->advance();
        }
        $bar->finish();
        $output->writeln('');
        $bar = new ProgressBar($output, $total);
        $bar->setFormat('%message%'.PHP_EOL.'%bar% %percent:3s% %'.PHP_EOL.'time:  %elapsed:6s%/%estimated:-6s%'.PHP_EOL.PHP_EOL);
        $bar->setBarCharacter('<info>'.$bar->getBarCharacter().'</info>');
        $bar->setMessage('开始解析子页面...');
        $bar->start();
        $keys = [];
        foreach ($menus as $menu) {
            foreach ($menu['links'] as $link) {
                $bar->setMessage($link['text']);
                $node = $this->parseNode($input, $link['url']);
                if (! $node) {
                    $bar->advance();
                    continue;
                }
                $fake = $input->getOption('fake') ?? false;
                if ($fake) {
                    $raw .= <<<BLOCK
                    '{$node['method']}'         => ['{$node['key']}' => '{$link['description']}'], \n
BLOCK;
                } else {
                    $keys[] = $link['text'];
                    $raw .= <<<BLOCK
    /*
    |--------------------------------------------------------------------------
    | {$link['text']}
    |--------------------------------------------------------------------------
    |
    | @see {$link['url']}
    |
    */\n
    '{$node['method']}' => '{$node['key']}',\n
BLOCK;
                }
                $bar->advance();
            }
        }
        $raw .= <<<'PHP'
    ];
PHP;
        $bar->finish();
        $fake = $input->getOption('fake') ?? false;
        file_put_contents(__DIR__.'/'.($fake ? 'map-fake.php' : 'map.php'), $raw);
    }

    private function parseNode($input, $url)
    {
        $html = file_get_contents($this->resolveUrl($input, $url));
        $crawler = new Crawler($html);
        $resource = $crawler->filterXPath('//*[@id="ajax-content-wrap"]/div[1]/div/div/div[1]/pre[1]');
        if ($resource->count() == 0) {
            $resource = $crawler->filterXPath('//*/div[2]/div/div[2]/div/div/div/pre[1]');
            if ($resource->count() == 0) {
                var_dump($url);

                return;
            }
        }
        //*[@id="ajax-content-wrap"]/div[1]/div/div/div[1]/table[3]/tbody/tr[3]/td[4]
        $operation = $crawler->filterXPath('//*[@id="ajax-content-wrap"]/div[1]/div/div/div[1]/table[3]/tbody/tr[2]/td[4]');
        if ($operation->count() == 0) {
            $operation = $crawler->filterXPath('//*/div[2]/div/div[2]/div/div/div/table[3]/tbody/tr[3]/td[4]');
            if ($operation->count() == 0) {
                $key = '';
            } else {
                $key = preg_replace('/Fixed\s+value(s?):/', '', $operation->text());
            }
        } else {
            $key = preg_replace('/Fixed\s+value(s?):/', '', $operation->text());
        }
        $method = '';

        if (preg_match('/marketplace\/([a-z0-9]+\/)+[a-z0-9]+/', $resource->text(), $matches)) {
            $method = str_replace('/', '.', $matches[0]);
        }

        return [
            'method' => $method,
            'key' => $key,
        ];
    }

    private function resolveUrl($input, $url)
    {
        $https = $input->getOption('https') ?? false;
        if ($https) {
            return str_replace('http://', 'https://', $url);
        }

        return str_replace('https://', 'http://', $url);
    }
}
