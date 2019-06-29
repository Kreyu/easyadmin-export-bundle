
# Installation

To download the bundle, require it using the Composer:

```bash
$ composer require kreyu/easyadmin-export-bundle
```

Then, enable the bundle in the `config/bundles.php` file: 

```php
// config/bundles.php
return [
    // ...
    Kreyu\Bundle\EasyAdminExportBundle\KreyuEasyAdminExportBundle::class => ['all' => true],
];
```

Next, create the `config/packages/kreyu_easy_admin_export.yaml` configuration file:  
      
```yaml
# config/packages/kreyu_easy_admin_export.yaml
kreyu_easy_admin_export: ~
```

Last but not least, replace the EasyAdmin controller:
                    
```yaml
# config/routes/easy_admin.yaml
easy_admin_bundle:
resource: '@KreyuEasyAdminExportBundle/Controller/EasyAdminExportController.php'
prefix: /admin
type: annotation
```

If you cannot replace the default controller (i.e. already using custom one), you can either:
 - extend from the [`EasyAdminExportController`](../src/Controller/EasyAdminExportController.php) class:  

    ```php
    use Kreyu\Bundle\EasyAdminExportBundle\Controller\EasyAdminExportController;
    
    class MyCustomController extends EasyAdminExportController
    {
        // ...
    }
    ```
 - use controller trait and subscribe to required services:  
 
    ```php
    <?php
    use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
    use Kreyu\Bundle\EasyAdminExportBundle\Controller\ExportControllerTrait;
    
    class MyCustomController extends EasyAdminController
    {
        use ExportControllerTrait;
        
        public static function getSubscribedServices(): array
        {
            return parent::getSubscribedServices() + [
                'translator' => TranslatorInterface::class,
                // ...
            ];
        }
    }
    ```
