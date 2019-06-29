# Configuration

This feature allows to export all the entity records from the database to spreadsheets.  

## Enabling the feature

To enable this feature, add the `export` node to the entity configuration, for example:

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            export: ~
```

## Global configuration

To configure the export feature globally, use the `config/packages/kreyu_easy_admin_export.yaml` configuration file:

```yaml
# config/packages/kreyu_easy_admin_export.yaml
kreyu_easy_admin_export:
    timestamp_format: d/m/Y
```

> Note that this will not enable the feature for the entities, only set the values to inherit by the entities.

## Entity configuration

To configure the export feature per entity, just add the export node in the entity configuration:

```yaml
# config/packages/easy_admin.yaml
easy_admin:
    entities:
        Product:
            class: App\Entity\Product
            export:
                label: awesome_products
                timestamp_format: d-m-Y
                metadata:
                    title: A set of my favourite products
```

> Values set in the entity configuration overrides the global configuration.

## Customizing the export button

In order to display the export button, the entity list template gets overriden.  
It is possible to change the button label and the icon:

```yaml
# ...
export:
    label: awesome_spreadsheet
    icon: star
```
  
To disable the template overriding and handle it manually, set the `override_template` to `false`:

```yaml
# ...
export: 
    override_template: false
```

For more information, take a look at the template:  
[@KreyuEasyAdminExport\default\list.html.twig](./src/Resources/views/default/list.html.twig)

## Modifying the export data

By default, the exportable fields gets inherited from the entity list fields.  
However, it is possible to describe them manually, for example: 

```yaml
# ...
export: 
    fields:
        - { property: id, label: product.fields.id }
        - { property: name, label: product.fields.name }
```

The labels are translated using the default implementation of the Translator.  
By default, labels are displayed on the first row of the spreadsheet.  
To disable this behavior, set the `use_headers` option to `false`:

```yaml
# ...
export: 
    fields:
        use_headers: false
```

It is possible to provide the custom query builder and handle the data generation manually by overriding the following methods:  

```php 
protected function createExportQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null) 
{
    // return an instance of the QueryBuilder
}
 
protected function createExportData($entityConfig, $queryBuilder)
{
    // return an array of ready-to-export entity data
}
```

## Configuring the spreadsheet filename and metadata

By default, the exported spreadsheets will have the lowercased entity name and current timestamp as the filename.  
To override this behavior, use following options:

```yaml
# ...
export: 
    fields:
        filename: awesome_products # null by default
        timestamp: true
        timestamp_prefix: -
        timestamp_format: d_m_Y
```

The above configuration will generate the following filename:
```
awesome_products-16_06_2019.[xls/xlsx/csv]
```

To override the generated file metadata, use the `metadata` node:

```yaml
#...
export:
    metadata:
        creator: ~
        last_modified_by: ~
        title: ~
        subject: ~
        description: ~
        keywords: ~
        category: ~
```

## Export action events

During the execution of the export action, events are triggered. Using the event listeners/subscribers you can hook to these events.

EasyAdminExport events are defined in the `` class:

```php
final class EasyAdminExportEvents
{
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    public const PRE_EXPORT = 'kreyu_easy_admin_export.pre_export';

    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    public const POST_EXPORT_QUERY_BUILDER = 'kreyu_easy_admin_export.post_export_query_builder';
}
```

## Exceptions and handlers

The event object is based on the [GenericEvent class](https://symfony.com/doc/current/components/event_dispatcher/generic_event.html), just like the EasyAdmin events.  
For more information, you can take a look at the [EasyAdmin documentation section.](https://symfony.com/doc/master/bundles/EasyAdminBundle/book/complex-dynamic-backends.html#the-event-object)

If user tries to export with the format not supported by the entity, the [UnsupportedFormatException](../src/Exception/UnsupportedFormatException.php) is thrown.  
By default, the [ExportExceptionListener](../src/EventListener/ExportExceptionListener.php) handles the exception, redirecting to the referer if exists, otherwise it will redirect to the EasyAdmin homepage route.

## Full configuration reference

```yaml
# config/packages/kreyu_easy_admin_export.yaml
kreyu_easy_admin_export:

    # Label visible on the export button.
    label: Export

    # Icon visible on the export button.
    icon: table

    # Base filename, if null (by default) the lowercase entity name is used.
    filename: ~

    # If true (by default), the headers will be included in the spreadsheets.
    use_headers: true

    # If true (by default), the current datetime will be included in the filename.
    timestamp: true

    # Format of the filename timestamp.
    timestamp_format: d-m-Y_H-i-s

    # String prefix, which separates the base filename from timestamp in filename.
    timestamp_prefix: _

    # If true (by default), the list template gets overriden with the custom one, adding the export button.
    override_template: true

    # An array of enabled export formats.
    formats:

        # Defaults:
        - csv
        - xls
        - xlsx

    # Entity exportable fields. Inherits from entity list fields by default.
    fields:               []

    # Metadata properties applied to the generated spreadsheets.
    metadata:
        creator: ~
        last_modified_by: ~
        title: ~
        subject: ~
        description: ~
        keywords: ~
        category: ~

    # Headers applied to the streamed response with the generated spreadsheet.
    headers:
        content_type: application/vnd.ms-excel
        cache_control: max-age=0
```
