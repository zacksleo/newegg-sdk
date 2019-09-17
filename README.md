<h1 align="center"> newegg-sdk </h1>

<p align="center"> sdk for newegg api.</p>


## Installing

```shell
$ composer require zacksleo/newegg-sdk -vvv
```

## Usage

### Create Client

```php
        $newegg = new Newegg([
            'key'       => 'app_key',
            'secret'    => 'app_secret',
            'seller_id' => 'seller_id',
            'debug'     => false,
            'log'       => [
                'name'       => 'newegg',
                'file'       => '/path/to/logs/newegg.log',
                'level'      => 'error',
                'permission' => 0777,
            ],
        ]);
        try {
            $res = $newegg->ordermgmt->order->chinaorderinfo([
                'PageIndex'       => 1,
                'PageSize'        => 1,
                'RequestCriteria' => [
                    'OrderNumberList'=> [
                        'OrderNumber'=> ['orderNumber'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            $this->setLogFailed($this->log, $e->getMessage());

            return;
        }
```

### Api Call

调用时，支持两种方式，一种是链式调用

```php
 $res = $newegg->ordermgmt->order->chinaorderinfo([
    'PageIndex'       => 1,
    'PageSize'        => 1,
    'RequestCriteria' => [
        'OrderNumberList'=> [
            'OrderNumber'=> ['orderNumber'],
        ],
    ],
]);
```

```php
$res = $newegg->servicemgmt->rma->rmainfo([
    'PageInfo'=> [
        'PageIndex' => 1,
        'PageSize'  => 1,
    ],
    'KeywordsType'  => 2,
    'KeywordsValue' => 'OrderNumber',
]);
```

另一种是使用 request 方法

```php
    $res = $newegg->request([
        'ordermgmt.orderstatus.orders.'.$orderNumber => null,
    ], [
        'Action' => 2,
        'Value' => [
            'Shipment' => [
                'Header' => [
                    'SellerID' => 'seller_id',
                    'SONumber' => $orderNumber,
                ],
                'PackageList' => [
                    'Package' => array_values($packages),
                ],
            ],
        ],
    ]);
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/zacksleo/newegg-sdk/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/zacksleo/newegg-sdk/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT